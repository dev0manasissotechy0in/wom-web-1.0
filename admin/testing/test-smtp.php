<?php
/**
 * SMTP Email Configuration Test Script
 * Tests PHPMailer SMTP settings from database (site_settings table)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch SMTP settings from database
try {
    $stmt = $db->query("SELECT * FROM smtp_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        die('<div style="padding: 40px; text-align: center; font-family: Arial;"><h1>⚠️ Error</h1><p>Site settings not found in database. Please configure your settings first.</p><a href="/admin/site-settings.php">Go to Settings</a></div>');
    }
} catch (Exception $e) {
    die('<div style="padding: 40px; text-align: center; font-family: Arial;"><h1>⚠️ Database Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></div>');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Test - WOM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .config-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        
        .config-info h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .config-info p {
            color: #555;
            font-size: 14px;
            margin: 5px 0;
            font-family: 'Courier New', monospace;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .result {
            margin-top: 25px;
            padding: 20px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .result.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .result.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .result strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 SMTP Email Test</h1>
        <p class="subtitle">Test your email configuration by sending a test email</p>
        
        <div class="config-info">
            <h3>Current SMTP Configuration (from Database):</h3>
            <p><strong>Host:</strong> <?php echo htmlspecialchars($settings['smtp_host'] ?? 'Not set'); ?></p>
            <p><strong>Port:</strong> <?php echo htmlspecialchars($settings['smtp_port'] ?? 'Not set'); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($settings['smtp_username'] ?? 'Not set'); ?></p>
            <p><strong>From Email:</strong> <?php echo htmlspecialchars($settings['from_email'] ?? 'Not set'); ?></p>
            <p><strong>From Name:</strong> <?php echo htmlspecialchars($settings['from_name'] ?? 'Not set'); ?></p>
        </div>
        
        <?php
        $result = '';
        $resultClass = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
            $testEmail = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
            
            if (!$testEmail) {
                $result = '<strong>❌ Invalid Email</strong>Please enter a valid email address.';
                $resultClass = 'error';
            } else {
                $mail = new PHPMailer(true);
                
                try {
                    // Validate required settings
                    if (empty($settings['smtp_host']) || empty($settings['smtp_port']) || 
                        empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
                        throw new Exception('SMTP settings are incomplete. Please configure all SMTP settings in the admin panel.');
                    }
                    
                    // Server settings from database
                    $mail->isSMTP();
                    $mail->Host       = $settings['smtp_host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $settings['smtp_username'];
                    $mail->Password   = $settings['smtp_password'];
                    
                    // Use SSL for port 465, STARTTLS for port 587
                    if ($settings['smtp_port'] == 465) {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    $mail->Port       = $settings['smtp_port'];
                    
                    // Increase timeout
                    $mail->Timeout = 30;
                    
                    // Enable verbose debug output (will be hidden in production)
                    $mail->SMTPDebug = 0; // Set to 2 for detailed debugging
                    
                    // Recipients from database settings
                    $mail->setFrom($settings['from_email'], $settings['from_name']);
                    $mail->addAddress($testEmail);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'SMTP Test Email - ' . date('Y-m-d H:i:s');
                    $mail->Body    = '
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                                .success-icon { font-size: 48px; margin-bottom: 10px; }
                                .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 4px; }
                                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="header">
                                    <div class="success-icon">✅</div>
                                    <h1 style="margin: 0;">SMTP Test Successful!</h1>
                                </div>
                                <div class="content">
                                    <p>Congratulations! Your SMTP email configuration is working correctly.</p>
                                    
                                    <div class="info-box">
                                        <h3 style="margin-top: 0; color: #667eea;">Configuration Details:</h3>
                                        <p><strong>SMTP Host:</strong> ' . htmlspecialchars($settings['smtp_host']) . '</p>
                                        <p><strong>SMTP Port:</strong> ' . htmlspecialchars($settings['smtp_port']) . '</p>
                                        <p><strong>From Email:</strong> ' . htmlspecialchars($settings['from_email']) . '</p>
                                        <p><strong>Test Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
                                    </div>
                                    
                                    <p>This email was sent from your WOM website as a test. If you received this message, your email system is configured properly and ready to send emails.</p>
                                    
                                    <div class="footer">
                                        <p>This is an automated test email from your website.</p>
                                        <p>&copy; ' . date('Y') . ' WOM. All rights reserved.</p>
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>
                    ';
                    $mail->AltBody = 'SMTP Test Successful! Your email configuration is working correctly. Test time: ' . date('Y-m-d H:i:s');
                    
                    $mail->send();
                    $result = '<strong>✅ Success!</strong>Test email sent successfully to <strong>' . htmlspecialchars($testEmail) . '</strong><br><br>Please check your inbox (and spam folder) for the test email.';
                    $resultClass = 'success';
                    
                } catch (Exception $e) {
                    $result = '<strong>❌ Error!</strong>Failed to send test email.<br><br><strong>Error Details:</strong><br>' . htmlspecialchars($mail->ErrorInfo);
                    $resultClass = 'error';
                }
            }
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="test_email">Enter your email address to receive a test email:</label>
                <input type="email" 
                       id="test_email" 
                       name="test_email" 
                       placeholder="your@email.com"
                       required
                       value="<?php echo htmlspecialchars($_POST['test_email'] ?? ''); ?>">
            </div>
            
            <button type="submit">📨 Send Test Email</button>
        </form>
        
        <?php if ($result): ?>
            <div class="result <?php echo $resultClass; ?>">
                <?php echo $result; ?>
            </div>
        <?php endif; ?>
        
        <a href="/admin/dashboard.php" class="back-link">← Back to Admin Dashboard</a>
    </div>
</body>
</html>
