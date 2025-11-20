<?php
// api/unsubscribe.php
header('Content-Type: application/json');

try {
    require_once '../config/config.php';
    require_once '../classes/Newsletter.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        die(json_encode(['success' => false, 'message' => 'Invalid request method']));
    }
    
    $email = trim($_GET['email'] ?? '');
    $newsletter = trim($_GET['newsletter'] ?? 'main');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['success' => false, 'message' => 'Invalid email address']));
    }
    
    $newsletterObj = new Newsletter($db);
    $result = $newsletterObj->unsubscribe($email, $newsletter);
    
    die(json_encode($result));
    
} catch(Exception $e) {
    error_log("Unsubscribe API error: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Error occurred']));
}
?>
