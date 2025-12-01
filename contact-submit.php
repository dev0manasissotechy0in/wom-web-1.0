<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 0);
error_reporting(0);

function jsonResponse($success, $message, $data = []) {
    ob_clean();
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function getRealIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'Unknown';
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// 1. Get and Sanitize Basic Inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$raw_phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$ip_address = getRealIpAddress();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// 2. Validate Basic Fields
if (empty($name)) jsonResponse(false, 'Name is required');
if (empty($email)) jsonResponse(false, 'Email is required');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, 'Please enter a valid email address');
if (empty($raw_phone)) jsonResponse(false, 'Phone number is required');
if (empty($subject)) jsonResponse(false, 'Subject is required');
if (empty($message)) jsonResponse(false, 'Message is required');

// 3. Aggressive Phone Processing
// Strip everything except numbers
$phone_digits = preg_replace('/[^0-9]/', '', $raw_phone);

// Ensure we have at least 10 digits
if (strlen($phone_digits) < 10) {
    jsonResponse(false, 'Please enter a valid phone number (at least 10 digits).');
}

// Get the "Subscriber Number" (The last 10 digits)
// This is our unique identifier. 
// e.g. From "09999999999" -> "9999999999"
// e.g. From "919999999999" -> "9999999999"
$subscriber_number = substr($phone_digits, -10);

// (Optional) Logic to guess Country Code for storage, but NOT for checking duplicates
$country_code = '';
$stored_phone = $phone_digits; // Default to storing pure digits

// Simple logic: If more than 10 digits, try to split CC and Phone
if (strlen($phone_digits) > 10) {
    // Remove leading zero if present (common in India 098...)
    if (substr($phone_digits, 0, 1) === '0') {
        $temp_digits = substr($phone_digits, 1);
        if (strlen($temp_digits) >= 10) {
            $phone_digits = $temp_digits;
            $stored_phone = $temp_digits;
        }
    }
    
    // If still > 10, assume the excess at start is CC
    if (strlen($phone_digits) > 10) {
        $cc_len = strlen($phone_digits) - 10;
        // Limit CC to reasonable length (1-3)
        if ($cc_len <= 3) {
            $country_code = substr($phone_digits, 0, $cc_len);
            $stored_phone = substr($phone_digits, $cc_len);
        }
    }
}

// Load DB
try {
    require_once __DIR__ . '/config/config.php';
} catch (Exception $e) {
    error_log('Config error: ' . $e->getMessage());
    jsonResponse(false, 'System error.');
}

try {
    // Ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS contact_inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        country_code VARCHAR(5),
        subject VARCHAR(500) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(50),
        user_agent TEXT,
        status ENUM('new', 'read', 'responded', 'closed') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Check Email Duplicate
    $checkEmail = $db->prepare("SELECT id FROM contact_inquiries WHERE email = ? LIMIT 1");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetch()) {
        jsonResponse(false, 'This email is already registered. We will respond shortly.');
    }

    // ---------------------------------------------------------
    // ROBUST PHONE DUPLICATE CHECK
    // ---------------------------------------------------------
    // We search for the last 10 digits ($subscriber_number) inside the 'phone' column.
    // We use multiple LIKE patterns to catch raw data or formatted data.
    
    $search_term = "%" . $subscriber_number; 
    
    $checkPhone = $db->prepare("
        SELECT id FROM contact_inquiries 
        WHERE 
           -- 1. Direct match of the last 10 digits at the end of the string
           phone LIKE ? 
           
           -- 2. Match even if spaces/dashes exist in DB (e.g. stored '+91 555 555 5555')
           OR REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?
    ");
    
    $checkPhone->execute([$search_term, $search_term]);
    
    if ($checkPhone->fetch()) {
        jsonResponse(false, 'This phone number is already registered. We will respond shortly.');
    }
    // ---------------------------------------------------------

    // Insert New Record
    $stmt = $db->prepare("INSERT INTO contact_inquiries 
        (name, email, phone, country_code, subject, message, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $name, 
        $email, 
        $stored_phone, // We store the clean number
        $country_code,
        $subject, 
        $message,
        $ip_address,
        $user_agent
    ]);

    $id = $db->lastInsertId();
    
    // Formatting for Email Notification
    $displayPhone = $country_code ? "+$country_code $stored_phone" : $stored_phone;
    
    // Send Admin Email
    @mail(
        '[email protected]',
        "New Inquiry: $subject",
        "Name: $name\nEmail: $email\nPhone: $displayPhone\n\nMessage:\n$message",
        "From: noreply@self.manasissotechy.in\r\nReply-To: $email"
    );

    jsonResponse(true, 'Thank you! Your inquiry has been received.', ['id' => $id]);

} catch (Exception $e) {
    error_log("DB Error: " . $e->getMessage());
    jsonResponse(false, 'Unable to submit form. Please try again.');
}
?>