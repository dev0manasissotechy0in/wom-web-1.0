<?php
require_once __DIR__ . '/config/config.php';

// Hostinger SMTP credentials
$smtp_host = 'smtp.hostinger.com';
$smtp_port = 465;
$smtp_username = 'dev@manasissotechy.in';
$smtp_password = 'apNON7lpc6j-';
$smtp_encryption = 'ssl';
$from_email = 'dev@manasissotechy.in';
$from_name = 'WOM ADMIN';
$is_active = 1;

try {
    // Check if settings exist
    $checkStmt = $db->query("SELECT COUNT(*) as count FROM login_smtp_settings");
    $result = $checkStmt->fetch();
    
    if ($result['count'] > 0) {
        // Update existing
        $stmt = $db->prepare("UPDATE login_smtp_settings SET 
            smtp_host = ?,
            smtp_port = ?,
            smtp_username = ?,
            smtp_password = ?,
            smtp_encryption = ?,
            from_email = ?,
            from_name = ?,
            is_active = ?,
            updated_at = NOW()
        ");
        
        $stmt->execute([
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption,
            $from_email,
            $from_name,
            $is_active
        ]);
        
        echo "<h2 style='color: #28a745;'>✓ SMTP Settings Updated Successfully!</h2>";
    } else {
        // Insert new
        $stmt = $db->prepare("INSERT INTO login_smtp_settings 
            (smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, from_email, from_name, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->execute([
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption,
            $from_email,
            $from_name,
            $is_active
        ]);
        
        echo "<h2 style='color: #28a745;'>✓ SMTP Settings Created Successfully!</h2>";
    }
    
    echo "<div style='padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>Configured Settings:</h3>";
    echo "<table style='width: 100%;'>";
    echo "<tr><td><strong>Host:</strong></td><td>" . htmlspecialchars($smtp_host) . "</td></tr>";
    echo "<tr><td><strong>Port:</strong></td><td>" . htmlspecialchars($smtp_port) . "</td></tr>";
    echo "<tr><td><strong>Encryption:</strong></td><td>" . strtoupper($smtp_encryption) . "</td></tr>";
    echo "<tr><td><strong>Username:</strong></td><td>" . htmlspecialchars($smtp_username) . "</td></tr>";
    echo "<tr><td><strong>From Email:</strong></td><td>" . htmlspecialchars($from_email) . "</td></tr>";
    echo "<tr><td><strong>From Name:</strong></td><td>" . htmlspecialchars($from_name) . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    echo "<div style='padding: 20px; margin: 20px 0;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='admin/forgot-password-page.php' style='color: #007bff; font-weight: bold;'>Test Forgot Password</a> - Try requesting OTP now</li>";
    echo "<li><a href='check-login-smtp.php' style='color: #007bff; font-weight: bold;'>Run Diagnostics</a> - Verify configuration</li>";
    echo "<li><a href='test-login-smtp.php' style='color: #007bff; font-weight: bold;'>Send Test Email</a> - Send a test OTP</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<strong>⚠️ Security Note:</strong><br>";
    echo "Delete this file after confirming SMTP is working: <code>update-smtp-credentials.php</code>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: #dc3545;'>✗ Database Error</h2>";
    echo "<p style='color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "Error: " . htmlspecialchars($e->getMessage());
    echo "</p>";
}
?>
