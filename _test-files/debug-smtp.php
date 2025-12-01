<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>SMTP Debug Test</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}pre{background:#fff;padding:15px;border:1px solid #ddd;border-radius:5px;}.success{color:#28a745;}.error{color:#dc3545;}.info{color:#007bff;}</style>";
echo "</head><body>";

echo "<h1>🔍 SMTP Debug Test</h1>";
echo "<hr>";

// Step 1: Check database settings
echo "<h2>Step 1: Database Settings</h2>";
try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        echo "<pre class='success'>✓ Database settings found:\n";
        echo "Host: " . htmlspecialchars($settings['smtp_host']) . "\n";
        echo "Port: " . htmlspecialchars($settings['smtp_port']) . "\n";
        echo "Encryption: " . htmlspecialchars($settings['smtp_encryption']) . "\n";
        echo "Username: " . htmlspecialchars($settings['smtp_username']) . "\n";
        echo "Password: " . (empty($settings['smtp_password']) ? '<span class="error">EMPTY!</span>' : '<span class="success">SET</span>') . "\n";
        echo "From Email: " . htmlspecialchars($settings['from_email']) . "\n";
        echo "From Name: " . htmlspecialchars($settings['from_name']) . "\n";
        echo "</pre>";
    } else {
        echo "<pre class='error'>✗ No settings found in database!</pre>";
    }
} catch (Exception $e) {
    echo "<pre class='error'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}

// Step 2: Check getLoginSMTPSettings() function
echo "<h2>Step 2: Function Output</h2>";
$smtp = getLoginSMTPSettings();
if ($smtp) {
    echo "<pre class='success'>✓ getLoginSMTPSettings() returned:\n";
    echo "Host: " . htmlspecialchars($smtp['host']) . "\n";
    echo "Port: " . htmlspecialchars($smtp['port']) . "\n";
    echo "Encryption: " . htmlspecialchars($smtp['encryption']) . "\n";
    echo "Username: " . htmlspecialchars($smtp['username']) . "\n";
    echo "Password: " . (empty($smtp['password']) ? '<span class="error">EMPTY!</span>' : '<span class="success">SET (length: ' . strlen($smtp['password']) . ')</span>') . "\n";
    echo "Source: " . htmlspecialchars($smtp['source']) . "\n";
    echo "</pre>";
} else {
    echo "<pre class='error'>✗ getLoginSMTPSettings() returned NULL!</pre>";
}

// Step 3: Test PHPMailer connection
echo "<h2>Step 3: PHPMailer Connection Test</h2>";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = $smtp['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp['username'];
    $mail->Password   = $smtp['password'];
    $mail->Port       = $smtp['port'];
    $mail->CharSet    = 'UTF-8';
    
    // Set encryption
    if($smtp['port'] == 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        echo "<pre class='info'>Using STARTTLS (port 587)</pre>";
    } elseif($smtp['port'] == 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        echo "<pre class='info'>Using SSL/SMTPS (port 465)</pre>";
    } elseif($smtp['encryption'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        echo "<pre class='info'>Using SSL</pre>";
    } elseif($smtp['encryption'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        echo "<pre class='info'>Using TLS</pre>";
    }
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Set sender and recipient
    $mail->setFrom($smtp['from_email'], $smtp['from_name']);
    $mail->addAddress($smtp['username']); // Send to same address for testing
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Debug Test - ' . date('Y-m-d H:i:s');
    $mail->Body    = '<h1>Test Email</h1><p>This is a debug test email. If you received this, SMTP is working!</p>';
    $mail->AltBody = 'Test Email - If you received this, SMTP is working!';
    
    echo "<h3>SMTP Debug Output:</h3>";
    echo "<pre style='background:#000;color:#0f0;padding:15px;border-radius:5px;overflow-x:auto;'>";
    ob_start();
    
    // Try to send
    $result = $mail->send();
    
    $debug_output = ob_get_clean();
    echo htmlspecialchars($debug_output);
    echo "</pre>";
    
    if ($result) {
        echo "<pre class='success'><strong>✓ SUCCESS!</strong> Email sent successfully!\nCheck your inbox at: " . htmlspecialchars($smtp['username']) . "</pre>";
    } else {
        echo "<pre class='error'>✗ Send returned false</pre>";
    }
    
} catch (Exception $e) {
    $debug_output = ob_get_clean();
    echo htmlspecialchars($debug_output);
    echo "</pre>";
    
    echo "<pre class='error'><strong>✗ EXCEPTION CAUGHT!</strong>\n\n";
    echo "Error Message: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Error Code: " . htmlspecialchars($e->getCode()) . "\n\n";
    echo "PHPMailer ErrorInfo: " . htmlspecialchars($mail->ErrorInfo) . "\n";
    echo "</pre>";
    
    // Additional troubleshooting
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ul>";
    
    if (strpos($e->getMessage(), 'Authentication') !== false || strpos($e->getMessage(), '535') !== false) {
        echo "<li class='error'><strong>Authentication Failed</strong><br>
        • Check if password is correct: apNON7lpc6j-<br>
        • Verify username is: dev@manasissotechy.in<br>
        • Check if Hostinger requires app-specific password<br>
        • Verify account is active in Hostinger control panel</li>";
    }
    
    if (strpos($e->getMessage(), 'Connection') !== false || strpos($e->getMessage(), 'timed out') !== false) {
        echo "<li class='error'><strong>Connection Failed</strong><br>
        • Port 465 may be blocked by firewall<br>
        • Try port 587 with TLS instead<br>
        • Check if smtp.hostinger.com is reachable</li>";
    }
    
    if (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
        echo "<li class='error'><strong>SSL Certificate Issue</strong><br>
        • SSL verification is disabled, but issue persists<br>
        • Try updating OpenSSL on your system<br>
        • Check PHP SSL extension is enabled</li>";
    }
    
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='admin/login_smtp.php' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin:5px;'>⚙️ Configure SMTP</a>";
echo "<a href='verify-all-smtp.php' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin:5px;'>📊 View Dashboard</a>";
echo "<a href='test-smtp-simple.php' style='display:inline-block;padding:10px 20px;background:#6c757d;color:white;text-decoration:none;border-radius:5px;margin:5px;'>🧪 Simple Test</a>";

echo "</body></html>";
?>
