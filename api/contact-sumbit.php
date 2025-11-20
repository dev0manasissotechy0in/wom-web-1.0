<?php
// Simple error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load config - adjust path based on your structure
$config_path = __DIR__ . '/../config/config.php';
if(!file_exists($config_path)) {
    // Try alternate path
    $config_path = dirname(dirname(__FILE__)) . '/config/config.php';
}

// if(file_exists($config_path)) {
//     require_once $config_path;
// } else {
//     // Minimal fallback if config doesn't load
//     session_start();
//     define('CONTACT_EMAIL', '[email protected]');
    
//     // Try database connection
//     try {
//         $db = new PDO('mysql:host=localhost;dbname=e342l5tn9cyj_manasisso;charset=utf8mb4', 
//                       'e342l5tn9cyj_manasisso', 
//                       'OjzG69=E2S', 
//                       [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
//     } catch(Exception $e) {
//         echo json_encode(['success' => false, 'message' => 'Database connection failed']);
//         exit();
//     }
// }

// Check POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get form data
$name = htmlspecialchars(trim($_POST['name'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

// Validate
if(empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Get user info
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Insert
    $stmt = $db->prepare("INSERT INTO contact_inquiries 
                          (name, email, phone, subject, message, ip_address, user_agent, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->execute([$name, $email, $phone, $subject, $message, $ip, $agent]);
    $id = $db->lastInsertId();
    
    // Try email (don't fail if it doesn't work)
    $emailSent = false;
    try {
        $to = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : '[email protected]';
        $emailSubject = "Contact Form: " . $subject;
        $emailBody = "New contact inquiry:\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Phone: $phone\n";
        $emailBody .= "Subject: $subject\n";
        $emailBody .= "Message:\n$message\n\n";
        $emailBody .= "ID: #$id\n";
        $emailBody .= "Time: " . date('Y-m-d H:i:s');
        
        $headers = "From: Wall of Marketing <[email protected]>\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        $emailSent = @mail($to, $emailSubject, $emailBody, $headers);
    } catch(Exception $e) {
        // Email failed but don't break the form
    }
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting us! We will respond within 24-48 hours.',
        'inquiry_id' => $id
    ]);
    
} catch(Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to save your message. Please email us at [email protected]'
    ]);
}
?>
