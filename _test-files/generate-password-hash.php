<?php
require_once __DIR__ . '/config/config.php';

// Set your new password here
$new_password = 'Admin@123';
$admin_email = 'wallofmarketing@outlook.com';

// Generate bcrypt hash
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update in database
try {
    $stmt = $db->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE email = ?");
    $result = $stmt->execute([$hashed_password, $admin_email]);
    
    if ($result) {
        echo "<h2 style='color: #28a745;'>✓ Password Reset Successful!</h2>";
        echo "<div style='padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin_email) . "</p>";
        echo "<p><strong>Password:</strong> <code style='background:#fff; padding:5px 10px; border-radius:3px;'>" . htmlspecialchars($new_password) . "</code></p>";
        echo "<p style='margin-top: 20px;'><a href='admin/login.php' style='background: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        echo "</div>";
        
        echo "<div style='padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;'>";
        echo "<strong>⚠️ Security Reminder:</strong><br>";
        echo "Please change this password after logging in for security purposes.";
        echo "</div>";
        
        echo "<h3>Technical Details:</h3>";
        echo "<p><strong>Password Hash:</strong></p>";
        echo "<textarea rows='2' cols='80' readonly style='font-family: monospace; font-size: 12px;'>" . $hashed_password . "</textarea>";
    } else {
        echo "<h2 style='color: #dc3545;'>✗ Update Failed</h2>";
        echo "<p>No rows were updated. Please check if the email exists in the database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color: #dc3545;'>✗ Database Error</h2>";
    echo "<p style='color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "Error: " . htmlspecialchars($e->getMessage());
    echo "</p>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<p style='color: #666; font-size: 14px;'>After successful login, delete this file: <code>generate-password-hash.php</code></p>";
?>
