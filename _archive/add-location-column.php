<?php
require 'config/config.php';

try {
    $db->exec("ALTER TABLE newsletter_subscribers ADD COLUMN location VARCHAR(255) NULL AFTER ip_address");
    echo "✅ Success! Location column added to newsletter_subscribers table.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "ℹ️ Location column already exists.\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
