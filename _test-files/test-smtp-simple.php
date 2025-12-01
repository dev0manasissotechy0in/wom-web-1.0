<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $test_email = trim($_POST['test_email']);
    
    if (empty($test_email)) {
        $message = 'Please enter an email address';
    } elseif (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format';
    } else {
        // Generate test OTP
        $test_otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $email_subject = "Test OTP - Login SMTP Configuration";
        $email_body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #000; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 30px; }
                    .otp-box { background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; border: 2px dashed #000; }
                    .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #000; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🧪 Test Email</h1>
                    </div>
                    <div class='content'>
                        <h2>SMTP Test Successful!</h2>
                        <p>This is a test email from your Login SMTP configuration.</p>
                        
                        <div class='otp-box'>
                            <p style='margin: 0; font-size: 14px; color: #666;'>Test OTP Code</p>
                            <div class='otp-code'>" . $test_otp . "</div>
                        </div>
                        
                        <p><strong>✓ If you're reading this, your SMTP is working correctly!</strong></p>
                        <p style='font-size: 12px; color: #666;'>Sent: " . date('F d, Y h:i:s A') . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        try {
            $email_sent = sendLoginOTPEmail($test_email, $email_subject, $email_body);
            
            if ($email_sent) {
                $message = "✓ Test email sent successfully to {$test_email}! Check your inbox (and spam folder).";
                $success = true;
            } else {
                $message = "✗ Failed to send email. Check SMTP settings.";
            }
        } catch (Exception $e) {
            $message = "✗ Error: " . $e->getMessage();
        }
    }
}

// Get current settings
try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $settings = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login SMTP - Simple</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .settings {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .settings table {
            width: 100%;
            border-collapse: collapse;
        }
        .settings td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .settings td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background: #000;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background: #333;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Test Login SMTP Settings</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($settings): ?>
            <div class="settings">
                <h3>Current SMTP Configuration</h3>
                <table>
                    <tr>
                        <td>Status:</td>
                        <td><strong style="color: green;">Active</strong></td>
                    </tr>
                    <tr>
                        <td>Host:</td>
                        <td><?php echo htmlspecialchars($settings['smtp_host']); ?></td>
                    </tr>
                    <tr>
                        <td>Port:</td>
                        <td><?php echo htmlspecialchars($settings['smtp_port']); ?></td>
                    </tr>
                    <tr>
                        <td>Encryption:</td>
                        <td><?php echo strtoupper($settings['smtp_encryption']); ?></td>
                    </tr>
                    <tr>
                        <td>Username:</td>
                        <td><?php echo htmlspecialchars($settings['smtp_username']); ?></td>
                    </tr>
                    <tr>
                        <td>From Email:</td>
                        <td><?php echo htmlspecialchars($settings['from_email']); ?></td>
                    </tr>
                </table>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Test Email Address</label>
                    <input type="email" name="test_email" placeholder="Enter email to receive test OTP" required>
                </div>
                
                <button type="submit" class="btn">📨 Send Test OTP</button>
            </form>
        <?php else: ?>
            <div class="message error">
                ✗ No SMTP settings configured. Please configure settings first.
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px;">
            <a href="admin/login_smtp.php">Configure SMTP Settings</a> | 
            <a href="check-login-smtp.php">Run Diagnostics</a> | 
            <a href="admin/forgot-password-page.php">Test Forgot Password</a>
        </p>
    </div>
</body>
</html>
