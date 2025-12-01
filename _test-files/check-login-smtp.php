<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Login SMTP Settings Diagnostic</h2>";
echo "<div style='font-family: monospace; padding: 20px; background: #f5f5f5; border-radius: 5px;'>";

// Check if table exists
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'login_smtp_settings'")->fetch();
    
    if (!$tableCheck) {
        echo "<p style='color: red;'>❌ Table 'login_smtp_settings' does not exist!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Table 'login_smtp_settings' exists</p>";
    
    // Get settings
    $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        echo "<p style='color: red;'>❌ No active SMTP settings found!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Active SMTP settings found</p>";
    echo "<hr>";
    
    echo "<h3>Current Settings:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; background: white;'>";
    echo "<tr><th align='left'>Setting</th><th align='left'>Value</th></tr>";
    echo "<tr><td>Host</td><td><strong>" . htmlspecialchars($settings['smtp_host']) . "</strong></td></tr>";
    echo "<tr><td>Port</td><td><strong>" . htmlspecialchars($settings['smtp_port']) . "</strong></td></tr>";
    echo "<tr><td>Encryption</td><td><strong>" . htmlspecialchars($settings['smtp_encryption']) . "</strong></td></tr>";
    echo "<tr><td>Username</td><td><strong>" . htmlspecialchars($settings['smtp_username']) . "</strong></td></tr>";
    echo "<tr><td>Password</td><td><strong>" . (empty($settings['smtp_password']) ? '<span style="color:red;">EMPTY!</span>' : str_repeat('•', 20)) . "</strong></td></tr>";
    echo "<tr><td>From Email</td><td><strong>" . htmlspecialchars($settings['from_email']) . "</strong></td></tr>";
    echo "<tr><td>From Name</td><td><strong>" . htmlspecialchars($settings['from_name']) . "</strong></td></tr>";
    echo "<tr><td>Active</td><td><strong>" . ($settings['is_active'] ? 'Yes' : 'No') . "</strong></td></tr>";
    echo "</table>";
    
    // Check for common issues
    echo "<hr><h3>Diagnostic Checks:</h3>";
    
    if (empty($settings['smtp_password'])) {
        echo "<p style='color: red; background: #fee; padding: 10px; border-left: 4px solid red;'>❌ <strong>PASSWORD IS EMPTY!</strong> You must set the SMTP password in admin/login_smtp.php</p>";
    } else {
        echo "<p style='color: green;'>✓ Password is set (length: " . strlen($settings['smtp_password']) . " characters)</p>";
    }
    
    if ($settings['smtp_host'] === 'smtp.hostinger.com' && $settings['smtp_port'] == 465) {
        echo "<p style='color: orange; background: #fff3cd; padding: 10px; border-left: 4px solid orange;'>⚠️ Hostinger with SSL (port 465) can be strict. Consider trying port 587 with TLS if issues persist.</p>";
    }
    
    if (!filter_var($settings['from_email'], FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>❌ From Email is not a valid email address!</p>";
    } else {
        echo "<p style='color: green;'>✓ From Email is valid</p>";
    }
    
    // Test PHPMailer
    echo "<hr><h3>PHPMailer Test:</h3>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "<p style='color: green;'>✓ PHPMailer loaded successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ PHPMailer vendor directory not found</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>Next Steps:</strong></p>";
    if (empty($settings['smtp_password'])) {
        echo "<ol>";
        echo "<li>Go to <a href='admin/login_smtp.php' target='_blank'>admin/login_smtp.php</a></li>";
        echo "<li>Enter your email password in the SMTP Password field</li>";
        echo "<li>Save the settings</li>";
        echo "<li>Return here and refresh</li>";
        echo "</ol>";
    } else {
        echo "<p style='color: green;'>Settings look good! Try sending OTP again.</p>";
        echo "<p>If it still fails, check: c:/xampp/php/logs/php_error_log for detailed SMTP errors</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>
