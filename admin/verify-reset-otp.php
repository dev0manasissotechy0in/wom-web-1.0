<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['success' => false, 'message' => 'Already logged in']);
    exit();
}

// Get OTP
$otp = trim($_POST['otp'] ?? '');

// Validate OTP
if (empty($otp) || strlen($otp) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP format']);
    exit();
}

// Check if reset session exists
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['reset_admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'No password reset request found. Please start over.']);
    exit();
}

// Check OTP expiry
if (strtotime($_SESSION['reset_otp_expiry']) < time()) {
    unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expiry'], $_SESSION['reset_email'], $_SESSION['reset_admin_id']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
    exit();
}

// Verify OTP
if ($otp !== $_SESSION['reset_otp']) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
    exit();
}

// OTP is valid - create a reset token for setting new password
$reset_token = bin2hex(random_bytes(32));
$_SESSION['reset_token'] = $reset_token;
$_SESSION['reset_verified'] = true;

// Clear OTP from session
unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);

echo json_encode([
    'success' => true,
    'message' => 'OTP verified successfully',
    'next_step' => 'set_password'
]);
?>
