<?php
define('SECURE_ACCESS', true);
session_start();

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           isset($_SESSION['admin_id']) &&
           isset($_SESSION['admin_ip']) &&
           $_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR'];
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Admin login function
function adminLogin($username, $password, $db) {
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!checkRateLimit('admin_login_' . $ip, 5, 900)) {
        return ['success' => false, 'message' => 'Too many login attempts. Try again in 15 minutes.'];
    }
    
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password'])) {
        // Log failed attempt
        error_log("Failed admin login attempt: $username from IP: $ip");
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    // Successful login
    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['created'] = time();
    
    // Update last login
    $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW(), last_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $admin['id']]);
    
    // Log successful login
    error_log("Admin login successful: $username from IP: $ip");
    
    return ['success' => true];
}

// Admin logout
function adminLogout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}
?>
