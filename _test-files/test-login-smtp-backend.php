<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

header('Content-Type: application/json');

// Check if it's a GET request (form not submitted via AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => false, 
        'message' => 'Please use the form on test-login-smtp.php to send test email',
        'debug' => [
            'error' => 'Direct GET access not allowed',
            'request_method' => 'GET'
        ]
    ]);
    exit();
}

// Get test email
$test_email = trim($_POST['test_email'] ?? '');

// Validate email
if (empty($test_email)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Email is required. Please enter an email address.',
        'debug' => [
            'post_data' => $_POST,
            'post_keys' => array_keys($_POST),
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
        ]
    ]);
    exit();
}

if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Generate test OTP
$test_otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Create test email
$email_subject = "Test OTP - Login SMTP Configuration";
$email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
            .otp-box { background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; border: 2px dashed #667eea; }
            .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #667eea; }
            .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
            .success-badge { background: #d4edda; color: #155724; padding: 10px 20px; border-radius: 20px; display: inline-block; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧪 Test Email - Login SMTP</h1>
            </div>
            <div class='content'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <span class='success-badge'>✓ Configuration Working</span>
                </div>
                
                <h2>Hello!</h2>
                <p>This is a <strong>test email</strong> to verify your Login SMTP configuration is working correctly.</p>
                
                <div class='otp-box'>
                    <p style='margin: 0; font-size: 14px; color: #666;'>Test OTP Code</p>
                    <div class='otp-code'>" . $test_otp . "</div>
                    <p style='margin: 10px 0 0 0; font-size: 14px; color: #666;'>This is a test - no action required</p>
                </div>
                
                <div class='info-box'>
                    <strong>📧 Email Details:</strong><br>
                    <strong>Sent to:</strong> " . htmlspecialchars($test_email) . "<br>
                    <strong>Date:</strong> " . date('F d, Y h:i:s A') . "<br>
                    <strong>Purpose:</strong> Login SMTP Configuration Test
                </div>
                
                <p style='margin-top: 20px;'><strong>✓ Success!</strong> If you're reading this, your Login SMTP settings are configured correctly.</p>
                
                <p style='margin-top: 20px; font-size: 13px; color: #666;'>
                    This email confirms that:
                </p>
                <ul style='color: #666; font-size: 13px;'>
                    <li>Database connection is working</li>
                    <li>login_smtp_settings table is configured</li>
                    <li>SMTP credentials are valid</li>
                    <li>Email delivery is functional</li>
                </ul>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " Admin Panel. Test email generated at " . date('h:i:s A') . "</p>
            </div>
        </div>
    </body>
    </html>
";

// Try to send email
try {
    $email_sent = sendLoginOTPEmail($test_email, $email_subject, $email_body);
    
    if ($email_sent) {
        echo json_encode([
            'success' => true,
            'message' => '✓ Test email sent successfully! Check ' . $test_email . ' (including spam folder)'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '✗ Failed to send email. Check SMTP settings and credentials.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Test Login SMTP Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '✗ Error: ' . $e->getMessage()
    ]);
}
?>
