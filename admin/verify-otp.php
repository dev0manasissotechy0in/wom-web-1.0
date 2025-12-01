<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$otp = strtoupper(trim($_POST['otp'] ?? ''));
$keep_signed_in = isset($_POST['keep_signed_in']) && $_POST['keep_signed_in'] === '1';

if(empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required']);
    exit;
}

// Check if OTP session exists
if(!isset($_SESSION['otp']) || !isset($_SESSION['otp_admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'No OTP request found. Please login again.']);
    exit;
}

// Check if OTP has expired
if(time() > $_SESSION['otp_expiry']) {
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_admin_id'], $_SESSION['otp_expiry']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
    exit;
}

// Verify OTP
if($otp !== $_SESSION['otp']) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
    exit;
}

try {
    // Get admin details
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$_SESSION['otp_admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$admin) {
        echo json_encode(['success' => false, 'message' => 'Admin account not found']);
        exit;
    }
    
    // Clear OTP data
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry']);
    
    // Set admin session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['last_activity'] = time();
    
    // Handle "Keep me signed in"
    if($keep_signed_in) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Store token in database
        $stmt = $db->prepare("UPDATE admin_users SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
        $stmt->execute([$token_hash, $expires, $admin['id']]);
        
        // Set cookie (30 days)
        setcookie('admin_remember', $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/admin/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        $_SESSION['keep_signed_in'] = true;
    }
    
    // Update last login
    $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login successful',
        'redirect' => 'dashboard.php'
    ]);
    
} catch(PDOException $e) {
    error_log("OTP Verification Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>
