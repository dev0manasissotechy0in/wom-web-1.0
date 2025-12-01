<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Site Constants
define('SITE_URL', 'https://wallofmarketing.co');
define('SITE_NAME', 'Wall of Marketing');
define('ADMIN_EMAIL', 'connect@wallofmarketing.co');

// Call Booking

define('RAZORPAY_KEY_ID', 'rzp_live_sNZBLWup032rTm');
define('RAZORPAY_KEY_SECRET', 'iv8NbSawy98Hwi1mwOFwfZOF');
define('PAYPAL_EMAIL', 'your-paypal@business.com');

// Security
define('SECURE_KEY', 'your-secure-random-key-here-change-this');

// In your config.php file
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');
define('SMTP_USERNAME', 'thesaasinsider@wallofmarketing.co');
define('SMTP_PASSWORD', 'U~4nAR1G$9|m');
define('MAIL_FROM_ADDRESS', ADMIN_EMAIL);
define('MAIL_FROM_NAME', SITE_NAME);


// In config.php
error_log("Newsletter API call - Email: " . ($_POST['email'] ?? 'N/A'));
try {
    // Your existing config code
} catch (Exception $e) {
    error_log("Config error: " . $e->getMessage());
}


// Database
require_once __DIR__ . '/database.php';
$database = new Database();
$db = $database->connect();

// Load Settings Class
require_once __DIR__ . '/../classes/Settings.php';
$siteSettings = Settings::getInstance($db);

// Functions
require_once __DIR__ . '/../includes/functions.php';

// Custom Error Handler
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $db;
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error_message);
    
    $stmt = $db->prepare("INSERT INTO error_logs (error_type, error_message, file_path, line_number, user_ip, request_url) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['PHP Error', $errstr, $errfile, $errline, $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']]);
    
    if ($errno === E_ERROR || $errno === E_USER_ERROR) {
        header("Location: /error.php?code=500");
        exit();
    }
}


function customExceptionHandler($exception) {
    global $db;
    $error_message = "Uncaught Exception: " . $exception->getMessage();
    error_log($error_message);
    
    $stmt = $db->prepare("INSERT INTO error_logs (error_type, error_message, file_path, line_number, user_ip, request_url) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Exception', $exception->getMessage(), $exception->getFile(), $exception->getLine(), $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']]);
    
    header("Location: /error.php?code=500");
    exit();
}
?>
