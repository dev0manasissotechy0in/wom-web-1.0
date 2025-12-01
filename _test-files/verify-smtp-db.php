<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Current Login SMTP Settings in Database</h2>";

try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($settings as $key => $value) {
            if ($key === 'smtp_password') {
                $display = str_repeat('•', min(strlen($value), 20));
            } else {
                $display = htmlspecialchars($value);
            }
            echo "<tr><td><strong>{$key}</strong></td><td>{$display}</td></tr>";
        }
        echo "</table>";
        
        echo "<hr><h3>Expected vs Actual:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Expected (Hostinger)</th><th>Actual</th><th>Match?</th></tr>";
        
        $expected = [
            'smtp_host' => 'smtp.hostinger.com',
            'smtp_port' => '465',
            'smtp_username' => 'dev@manasissotechy.in',
            'smtp_encryption' => 'ssl'
        ];
        
        foreach ($expected as $field => $expectedValue) {
            $actualValue = $settings[$field];
            $match = ($actualValue == $expectedValue);
            $matchText = $match ? '<span style="color:green;">✓ Yes</span>' : '<span style="color:red;">✗ No</span>';
            echo "<tr><td><strong>{$field}</strong></td><td>{$expectedValue}</td><td>" . htmlspecialchars($actualValue) . "</td><td>{$matchText}</td></tr>";
        }
        echo "</table>";
        
        if ($settings['smtp_host'] !== 'smtp.hostinger.com') {
            echo "<hr><div style='background: #f8d7da; color: #721c24; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
            echo "<h3>❌ Problem Found!</h3>";
            echo "<p>The database still has <strong>" . htmlspecialchars($settings['smtp_host']) . "</strong> instead of <strong>smtp.hostinger.com</strong></p>";
            echo "<p><a href='update-smtp-credentials.php' style='background: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click here to fix it</a></p>";
            echo "</div>";
        } else {
            echo "<hr><div style='background: #d4edda; color: #155724; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;'>";
            echo "<h3>✓ Database Settings Look Correct!</h3>";
            echo "<p>If SMTP still fails, the issue might be with the password or Hostinger server settings.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>No settings found in database!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
