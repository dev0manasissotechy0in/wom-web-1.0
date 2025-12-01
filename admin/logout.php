<?php
require_once '../config/config.php';

// Clear remember token from database if exists
if (isset($_COOKIE['admin_remember'])) {
    $token = $_COOKIE['admin_remember'];
    $hashedToken = hash('sha256', $token);
    
    // Remove token from database
    $stmt = $db->prepare("UPDATE admin_users SET remember_token = NULL, remember_token_expires = NULL WHERE remember_token = ?");
    $stmt->execute([$hashedToken]);
    
    // Delete cookie
    setcookie('admin_remember', '', time() - 3600, '/admin/', '', true, true);
}

// Clear session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
