<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/smtp.php';

header('Content-Type: application/json');

// Check if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo json_encode(['success' => false, 'message' => 'Already logged in']);
    exit();
}

// Get email
$email = trim($_POST['email'] ?? '');

// Validate email
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

try {
    // Check if admin exists
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        // Don't reveal if email exists or not for security
        echo json_encode(['success' => true, 'message' => 'If this email exists, you will receive a password reset OTP']);
        exit();
    }
    
    // Generate 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Store OTP in session
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_otp_expiry'] = $otp_expiry;
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_admin_id'] = $admin['id'];
    
    // Send OTP via email
    $email_subject = "Password Reset OTP - Admin Panel";
    $email_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #000; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                .otp-box { background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; border: 2px dashed #000; }
                .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #000; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <h2>Hi " . htmlspecialchars($admin['name']) . ",</h2>
                    <p>You have requested to reset your admin password. Use the OTP below to proceed:</p>
                    
                    <div class='otp-box'>
                        <p style='margin: 0; font-size: 14px; color: #666;'>Your OTP Code</p>
                        <div class='otp-code'>" . $otp . "</div>
                        <p style='margin: 10px 0 0 0; font-size: 14px; color: #666;'>Valid for 10 minutes</p>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong><br>
                        If you did not request this password reset, please ignore this email and ensure your account is secure.
                    </div>
                    
                    <p style='margin-top: 20px;'>This OTP will expire in 10 minutes for security reasons.</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Admin Panel. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // Send email using the configured SMTP
    try {
        $email_sent = sendLoginOTPEmail($email, $email_subject, $email_body);
        
        if ($email_sent) {
            echo json_encode([
                'success' => true,
                'message' => 'Password reset OTP sent to your email'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send OTP. Please check SMTP settings in admin panel.'
            ]);
        }
    } catch (Exception $emailError) {
        error_log("Forgot password - Email sending error: " . $emailError->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Email error: ' . $emailError->getMessage()
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Forgot password - Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Forgot password - General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
