<?php
require_once 'config/config.php';

echo "=== Checking SMTP Settings ===\n\n";

// Check smtp_settings table
$stmt = $db->query("SELECT * FROM smtp_settings WHERE is_active=1");
$smtp = $stmt->fetch(PDO::FETCH_ASSOC);

if ($smtp) {
    echo "SMTP Settings Found:\n";
    echo "Host: " . ($smtp['smtp_host'] ?? 'NOT SET') . "\n";
    echo "Port: " . ($smtp['smtp_port'] ?? 'NOT SET') . "\n";
    echo "Username: " . ($smtp['smtp_username'] ?? 'NOT SET') . "\n";
    echo "Password: " . (empty($smtp['smtp_password']) ? 'NOT SET' : '****') . "\n";
    echo "From Email: " . ($smtp['from_email'] ?? 'NOT SET') . "\n";
    echo "From Name: " . ($smtp['from_name'] ?? 'NOT SET') . "\n";
} else {
    echo "❌ No active SMTP settings found in smtp_settings table!\n";
}

echo "\n=== Checking Newsletter Class ===\n\n";

require_once 'classes/Newsletter.php';

try {
    $newsletter = new Newsletter($db);
    echo "✓ Newsletter class instantiated successfully\n";
    
    // Check if sendNewsletter method exists
    if (method_exists($newsletter, 'sendNewsletter')) {
        echo "✓ sendNewsletter() method exists\n";
    } else {
        echo "❌ sendNewsletter() method NOT found\n";
        echo "Available methods:\n";
        $methods = get_class_methods($newsletter);
        foreach ($methods as $method) {
            if (strpos($method, '__') !== 0) {
                echo "  - " . $method . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
