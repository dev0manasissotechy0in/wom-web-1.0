<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/smtp.php';

$page_title = 'Test Login SMTP';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = $_POST['test_email'] ?? $_SESSION['admin_email'];
    
    $subject = 'Test OTP Email - ' . SITE_NAME;
    $otp = 'ABC123'; // Test OTP
    
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
            .test-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <div class='header'>
                    <div style='font-size: 48px; margin-bottom: 10px;'>🔐</div>
                    <h2>Admin Login Verification <span class='test-badge'>TEST EMAIL</span></h2>
                </div>
                
                <p style='margin-top: 20px;'>This is a <strong>test email</strong> from your Login SMTP configuration.</p>
                
                <p>If you're receiving this, your admin OTP email settings are working correctly!</p>
                
                <div class='otp-section'>
                    <div class='otp-code'>{$otp}</div>
                    <p style='color: #666; font-size: 14px; margin-top: 10px;'>Sample OTP Code</p>
                </div>
                
                <div class='warning'>
                    <p><strong>⚠️ Test Mode:</strong> This is a test email to verify your SMTP configuration.</p>
                </div>
                
                <p style='margin-top: 30px;'>
                    Configuration Status: <strong style='color: #28a745;'>✓ Working</strong><br>
                    Server Time: " . date('Y-m-d H:i:s') . "
                </p>
            </div>
            <div class='footer'>
                <p>This is an automated test email from " . SITE_NAME . "</p>
                <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    ob_start();
    $sent = sendLoginOTPEmail($testEmail, $subject, $message);
    $debug = ob_get_clean();
    
    $result = [
        'success' => $sent,
        'email' => $testEmail,
        'debug' => $debug
    ];
}
?>

<?php include __DIR__ . '/../includes/layout-start.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-vial"></i> Test Login SMTP Connection</h1>
    <a href="../login_smtp.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Settings</a>
</div>

<?php if ($result): ?>
    <?php if ($result['success']): ?>
        <div class="alert alert-success">
            <h4><i class="fas fa-check-circle"></i> Test Email Sent Successfully!</h4>
            <p>A test OTP email has been sent to <strong><?php echo htmlspecialchars($result['email']); ?></strong></p>
            <p>Please check your inbox to confirm delivery.</p>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4><i class="fas fa-times-circle"></i> Test Email Failed</h4>
            <p>Failed to send test email to <strong><?php echo htmlspecialchars($result['email']); ?></strong></p>
            <details style="margin-top: 15px;">
                <summary style="cursor: pointer; font-weight: bold;">View Debug Information</summary>
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 10px;"><?php echo htmlspecialchars($result['debug']); ?></pre>
            </details>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h3>Send Test OTP Email</h3>
        <p style="color: #666; font-size: 14px; margin: 10px 0 0 0;">Test your admin login SMTP configuration</p>
    </div>
    
    <form method="POST" style="padding: 20px;">
        <div class="form-group">
            <label for="test_email">Test Email Address</label>
            <input type="email" 
                   id="test_email" 
                   name="test_email" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?>"
                   required
                   placeholder="your-email@example.com">
            <small>Enter the email address where you want to receive the test OTP</small>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-paper-plane"></i> Send Test Email
        </button>
    </form>
</div>

<div class="content-card" style="margin-top: 20px;">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Current Configuration</h3>
    </div>
    
    <div style="padding: 20px;">
        <?php
        $loginSmtp = getLoginSMTPSettings();
        if ($loginSmtp):
        ?>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: bold; width: 200px;">SMTP Host:</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($loginSmtp['host']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: bold;">Port:</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($loginSmtp['port']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: bold;">Username:</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($loginSmtp['username']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: bold;">Encryption:</td>
                    <td style="padding: 12px;"><?php echo strtoupper($loginSmtp['encryption']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td style="padding: 12px; font-weight: bold;">From Email:</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($loginSmtp['from_email']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px; font-weight: bold;">From Name:</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($loginSmtp['from_name']); ?></td>
                </tr>
            </table>
            <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 5px;">
                <strong style="color: #155724;"><i class="fas fa-check-circle"></i> Login SMTP Configured</strong>
                <p style="color: #155724; margin: 5px 0 0 0; font-size: 14px;">Using database settings for OTP emails</p>
            </div>
        <?php else: ?>
            <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                <strong style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Using Fallback Configuration</strong>
                <p style="color: #856404; margin: 10px 0 0 0; font-size: 14px;">
                    No login SMTP settings configured in database. Using default Outlook settings.<br>
                    <a href="../login_smtp.php" style="color: #0066cc; font-weight: bold;">Configure Login SMTP Settings →</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #000;
}

.btn-primary {
    padding: 12px 24px;
    background: #000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.btn-primary:hover {
    background: #333;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert h4 {
    margin: 0 0 10px 0;
}

.alert p {
    margin: 5px 0;
}
</style>

<?php include __DIR__ . '/../includes/layout-end.php'; ?>
