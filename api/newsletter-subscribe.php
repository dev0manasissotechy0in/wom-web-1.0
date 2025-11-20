<?php
// Start with output buffering to catch any errors
ob_start();

// Set error reporting for API
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header
header('Content-Type: application/json');

try {
    // Load config
    require_once __DIR__ . '/../config/config.php';
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['success' => false, 'message' => 'Method not allowed']));
    }
    
    // Sanitize input
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? 'Anonymous');
    $newsletter = trim($_POST['newsletter'] ?? 'main');
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Please enter a valid email address']));
    }
    
    // Check if newsletter class exists
    if (!class_exists('Newsletter')) {
        require_once __DIR__ . '/../classes/Newsletter.php';
    }
    
    // Initialize newsletter
    $newsletterObj = new Newsletter($db);
    
    // Subscribe user
    $result = $newsletterObj->subscribe($email, $name, $newsletter);
    
    http_response_code(200);
    die(json_encode($result));
    
} catch (Exception $e) {
    error_log("Newsletter API error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']));
} finally {
    // Clean any output
    ob_clean();
}
?>
