<?php
require 'config/config.php';

try {
    $db->exec("ALTER TABLE newsletter_subscribers ADD COLUMN ip_address VARCHAR(45) NULL AFTER newsletter_name");
    echo "✅ Success! ip_address column added to newsletter_subscribers table.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
