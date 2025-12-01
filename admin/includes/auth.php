<?php
/**
 * Admin Authentication Check
 * Include this file at the top of every admin page to ensure user is logged in
 * 
 * Usage: require_once __DIR__ . '/includes/auth.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Clear any stale session data
    session_destroy();
    
    // Redirect to login page
    header('Location: ' . dirname($_SERVER['PHP_SELF'], 1) . '/login.php');
    exit();
}

// Optional: Check if session has expired (30 minutes of inactivity)
$session_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $session_timeout) {
        session_destroy();
        header('Location: ' . dirname($_SERVER['PHP_SELF'], 1) . '/login.php?timeout=1');
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Verify admin user still exists in database
if (!isset($_SESSION['_admin_verified'])) {
    require_once __DIR__ . '/../../config/database.php';
    
    $database = new Database();
    $db = $database->connect();
    
    $stmt = $db->prepare("SELECT id, role FROM admin_users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['admin_id'] ?? null]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        session_destroy();
        header('Location: ' . dirname($_SERVER['PHP_SELF'], 1) . '/login.php?error=invalid');
        exit();
    }
    
    // Mark as verified for this session
    $_SESSION['_admin_verified'] = true;
}
?>
