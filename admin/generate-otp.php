<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if(empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

try {
    // Verify admin credentials
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$admin || !password_verify($password, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Generate OTP (6 characters: mix of uppercase letters and numbers)
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed confusing characters like 0, O, 1, I
    $otp = '';
    for($i = 0; $i < 6; $i++) {
        $otp .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    // Store OTP in session with expiry (5 minutes)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_admin_id'] = $admin['id'];
    $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
    
    // Send OTP via email using dedicated login SMTP settings
    require_once '../config/smtp.php';
    
    $subject = 'Your Admin Login OTP - ' . SITE_NAME;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
            .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #000; }
            .header h2 { color: #000; margin: 0; font-size: 24px; }
            .otp-section { text-align: center; margin: 30px 0; }
            .otp-code { font-size: 36px; font-weight: bold; color: #000; letter-spacing: 8px; padding: 20px; background: #f0f0f0; border-radius: 8px; display: inline-block; border: 2px dashed #666; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .warning p { margin: 0; color: #856404; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px; }
            .icon { font-size: 48px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <div class='header'>
                    <div class='icon'>🔐</div>
                    <h2>Admin Login Verification</h2>
                </div>
                
                <p style='margin-top: 20px;'>Hello <strong>" . htmlspecialchars($admin['full_name'] ?? 'Admin') . "</strong>,</p>
                
                <p>You have requested to login to the admin panel at <strong>" . SITE_NAME . "</strong>. Please use the following One-Time Password (OTP) to complete your login:</p>
                
                <div class='otp-section'>
                    <div class='otp-code'>{$otp}</div>
                </div>
                
                <div class='warning'>
                    <p><strong>⚠️ Important:</strong> This OTP is valid for <strong>5 minutes only</strong> and can be used once.</p>
                </div>
                
                <p>If you didn't request this login, please ignore this email and ensure your account is secure. Consider changing your password if you suspect unauthorized access.</p>
                
                <p style='margin-top: 30px;'>
                    Best regards,<br>
                    <strong>" . SITE_NAME . " Team</strong>
                </p>
            </div>
            <div class='footer'>
                <p>This is an automated security email. Please do not reply.</p>
                <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . " | Time: " . date('Y-m-d H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Capture output buffer to catch SMTP debug info
    ob_start();
    $emailSent = sendLoginOTPEmail($email, $subject, $message);
    $smtpDebug = ob_get_clean();
    
    if($emailSent) {
        echo json_encode([
            'success' => true, 
            'message' => 'OTP sent to your email. Please check your inbox.'
        ]);
    } else {
        error_log("OTP Email Failed for: " . $email);
        error_log("SMTP Debug Output: " . $smtpDebug);
        
        // Send debug info in development
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send OTP email. Please check Login SMTP settings in admin panel.',
            'debug' => $smtpDebug // Remove this in production
        ]);
    }
    
} catch(PDOException $e) {
    error_log("OTP Generation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
} catch(Exception $e) {
    error_log("OTP Email Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Email service error. Please try again.']);
}
?>
