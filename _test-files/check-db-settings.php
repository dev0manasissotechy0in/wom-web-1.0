<?php
require_once __DIR__ . '/config/config.php';

try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        echo "Database Settings Found:\n";
        echo "Host: " . $settings['smtp_host'] . "\n";
        echo "Port: " . $settings['smtp_port'] . "\n";
        echo "Encryption: " . $settings['smtp_encryption'] . "\n";
        echo "Username: " . $settings['smtp_username'] . "\n";
        echo "Password: " . (empty($settings['smtp_password']) ? 'EMPTY!' : 'SET (length: ' . strlen($settings['smtp_password']) . ')') . "\n";
        echo "From Email: " . $settings['from_email'] . "\n";
        echo "From Name: " . $settings['from_name'] . "\n";
        echo "Is Active: " . ($settings['is_active'] ? 'YES' : 'NO') . "\n";
    } else {
        echo "No settings found in database!\n";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
