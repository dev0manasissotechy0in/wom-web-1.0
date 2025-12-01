<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['success' => false, 'message' => 'Already logged in']);
    exit();
}

// Check if OTP was verified
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['reset_admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please verify OTP first.']);
    exit();
}

// Get new password
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate passwords
if (empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Both password fields are required']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit();
}

// Check password strength
if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number']);
    exit();
}

try {
    // Hash the new password
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password in database
    $stmt = $db->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ? AND email = ?");
    $stmt->execute([$password_hash, $_SESSION['reset_admin_id'], $_SESSION['reset_email']]);
    
    if ($stmt->rowCount() > 0) {
        // Clear all reset session variables
        unset($_SESSION['reset_token'], $_SESSION['reset_verified'], $_SESSION['reset_email'], $_SESSION['reset_admin_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully. You can now login with your new password.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
