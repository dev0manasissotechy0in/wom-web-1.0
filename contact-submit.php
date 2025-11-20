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

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$ip_address = getRealIpAddress();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Validate required fields
if (empty($name)) {
    jsonResponse(false, 'Name is required');
}

if (empty($email)) {
    jsonResponse(false, 'Email is required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address');
}

if (empty($phone)) {
    jsonResponse(false, 'Phone number is required');
}

// Validate phone number (must contain at least 10 digits)
$phoneDigits = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phoneDigits) < 10) {
    jsonResponse(false, 'Please enter a valid phone number with at least 10 digits');
}

if (empty($subject)) {
    jsonResponse(false, 'Subject is required');
}

if (empty($message)) {
    jsonResponse(false, 'Message is required');
}

// Load config and database connection
try {
    require_once __DIR__ . '/config/config.php';
} catch (Exception $e) {
    error_log('Contact form config load error: ' . $e->getMessage());
    jsonResponse(false, 'System error. Please try again later.');
}

// Save to database
try {
    // Create table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS contact_inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(500) NOT NULL,
        message TEXT NOT NULL,
        ip_address VARCHAR(50),
        user_agent TEXT,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_email (email),
        INDEX idx_phone (phone),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Check if email already exists
    $checkEmail = $db->prepare("SELECT COUNT(*) FROM contact_inquiries WHERE email = ?");
    $checkEmail->execute([$email]);
    $emailExists = $checkEmail->fetchColumn();
    
    if ($emailExists > 0) {
        jsonResponse(false, 'This email is already registered. If you submitted a previous inquiry, we will respond shortly. For urgent matters, please call us directly.');
    }
    
    // Check if phone number already exists (only if phone is provided)
    if (!empty($phone)) {
        $checkPhone = $db->prepare("SELECT COUNT(*) FROM contact_inquiries WHERE phone = ?");
        $checkPhone->execute([$phone]);
        $phoneExists = $checkPhone->fetchColumn();
        
        if ($phoneExists > 0) {
            jsonResponse(false, 'This phone number is already registered. If you submitted a previous inquiry, we will respond shortly. Please try with a different number or email us directly.');
        }
    }
    
    // Insert data with IP address and user agent
    $stmt = $db->prepare("INSERT INTO contact_inquiries 
        (name, email, phone, subject, message, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $name, 
        $email, 
        $phone, 
        $subject, 
        $message,
        $ip_address,
        $user_agent
    ]);
    
    if ($result) {
        $id = $db->lastInsertId();
        
        // Log successful submission
        error_log("Contact form submitted - ID: {$id}, IP: {$ip_address}, Email: {$email}");
        
        // Try to send email (don't fail if it doesn't work)
        @mail(
            '[email protected]',
            'New Contact: ' . $subject,
            "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\nMessage:\n{$message}\n\n---\nIP: {$ip_address}\nUser Agent: {$user_agent}",
            "From: noreply@self.manasissotechy.in\r\nReply-To: {$email}"
        );
        
        jsonResponse(true, 'Thank you for contacting us! Your inquiry has been received. We will get back to you within 24-48 hours.', [
            'id' => $id
        ]);
    } else {
        throw new Exception('Failed to save');
    }
    
} catch (Exception $e) {
    error_log('Contact form save error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to save your message. Please try again or email us directly at [email protected]');
}
?>
