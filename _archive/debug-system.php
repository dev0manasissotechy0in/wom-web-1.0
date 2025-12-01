<?php
/**
 * Debug Script - Check System Health
 * Tests all critical components
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>System Health Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .check { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Health Check</h1>
        
        <h2>1. PHP Configuration</h2>
        <?php
        echo '<div class="check success">';
        echo '<strong>✅ PHP Version:</strong> ' . PHP_VERSION . '<br>';
        echo '<strong>✅ Error Reporting:</strong> ' . error_reporting() . '<br>';
        echo '<strong>✅ Display Errors:</strong> ' . ini_get('display_errors');
        echo '</div>';
        ?>
        
        <h2>2. Config File</h2>
        <?php
        try {
            require_once __DIR__ . '/config/config.php';
            echo '<div class="check success">';
            echo '<strong>✅ Config loaded successfully</strong><br>';
            echo 'SITE_NAME: ' . (defined('SITE_NAME') ? SITE_NAME : 'Not defined') . '<br>';
            echo 'SITE_URL: ' . (defined('SITE_URL') ? SITE_URL : 'Not defined');
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ Config Error:</strong> ' . $e->getMessage();
            echo '</div>';
        }
        ?>
        
        <h2>3. Database Connection</h2>
        <?php
        try {
            if (isset($db) && $db instanceof PDO) {
                echo '<div class="check success">';
                echo '<strong>✅ Database connected</strong><br>';
                
                // Test query
                $stmt = $db->query("SELECT DATABASE() as dbname");
                $dbInfo = $stmt->fetch();
                echo 'Database: ' . $dbInfo['dbname'];
                echo '</div>';
            } else {
                echo '<div class="check error"><strong>❌ Database not connected</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ Database Error:</strong> ' . $e->getMessage();
            echo '</div>';
        }
        ?>
        
        <h2>4. Newsletter Class</h2>
        <?php
        try {
            require_once __DIR__ . '/classes/Newsletter.php';
            echo '<div class="check success">';
            echo '<strong>✅ Newsletter class loaded</strong><br>';
            
            $newsletter = new Newsletter($db);
            echo 'Newsletter object created successfully';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ Newsletter Error:</strong> ' . $e->getMessage() . '<br>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
            echo '</div>';
        }
        ?>
        
        <h2>5. SMTP Settings</h2>
        <?php
        try {
            $stmt = $db->query("SELECT smtp_host, smtp_port, smtp_username, from_email, is_active FROM smtp_settings WHERE id = 1");
            $smtp = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($smtp) {
                echo '<div class="check success">';
                echo '<strong>✅ SMTP Settings found</strong><br>';
                echo 'Host: ' . htmlspecialchars($smtp['smtp_host']) . '<br>';
                echo 'Port: ' . htmlspecialchars($smtp['smtp_port']) . '<br>';
                echo 'Username: ' . htmlspecialchars($smtp['smtp_username']) . '<br>';
                echo 'From: ' . htmlspecialchars($smtp['from_email']) . '<br>';
                echo 'Active: ' . ($smtp['is_active'] ? 'Yes' : 'No');
                echo '</div>';
            } else {
                echo '<div class="check warning"><strong>⚠️ No SMTP settings found</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ SMTP Check Error:</strong> ' . $e->getMessage();
            echo '</div>';
        }
        ?>
        
        <h2>6. Newsletter Subscribers Table</h2>
        <?php
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'newsletter_subscribers'");
            if ($stmt->rowCount() > 0) {
                $count = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers")->fetch();
                echo '<div class="check success">';
                echo '<strong>✅ Table exists</strong><br>';
                echo 'Total subscribers: ' . $count['total'];
                echo '</div>';
            } else {
                echo '<div class="check error"><strong>❌ Table does not exist</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ Table Check Error:</strong> ' . $e->getMessage();
            echo '</div>';
        }
        ?>
        
        <h2>7. Test Newsletter Subscribe</h2>
        <?php
        try {
            $testEmail = 'test' . time() . '@example.com';
            $result = $newsletter->subscribe($testEmail, 'Test User', 'main');
            
            if ($result['success']) {
                echo '<div class="check success">';
                echo '<strong>✅ Subscribe test passed</strong><br>';
                echo 'Message: ' . htmlspecialchars($result['message']);
                echo '</div>';
                
                // Clean up test data
                $db->prepare("DELETE FROM newsletter_subscribers WHERE email = ?")->execute([$testEmail]);
            } else {
                echo '<div class="check warning">';
                echo '<strong>⚠️ Subscribe returned false</strong><br>';
                echo 'Message: ' . htmlspecialchars($result['message']);
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="check error">';
            echo '<strong>❌ Subscribe Test Error:</strong> ' . $e->getMessage() . '<br>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 5px;">
            <strong>✅ All Critical Tests Complete</strong><br>
            <small>If all checks passed, your system is working correctly.</small>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="/" style="color: #667eea; text-decoration: none;">← Back to Homepage</a> | 
            <a href="/test-newsletter.php" style="color: #667eea; text-decoration: none;">Test Newsletter Form</a>
        </div>
    </div>
</body>
</html>
