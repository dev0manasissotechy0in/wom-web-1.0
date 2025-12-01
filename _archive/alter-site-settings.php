<?php
require_once 'config/config.php';

echo "=== Checking if new columns exist ===\n";

$columnsToAdd = [
    'dark_mode_enabled',
    'footer_legal_links_enabled', 
    'resource_download_notify_admin',
    'newsletter_auto_send_welcome'
];

$existingColumns = [];
$missingColumns = [];

foreach ($columnsToAdd as $column) {
    try {
        $stmt = $db->query("SHOW COLUMNS FROM site_settings LIKE '$column'");
        $result = $stmt->fetch();
        if ($result) {
            $existingColumns[] = $column;
        } else {
            $missingColumns[] = $column;
        }
    } catch(Exception $e) {
        echo "Error checking column $column: " . $e->getMessage() . "\n";
    }
}

if (!empty($existingColumns)) {
    echo "\n✓ Already exists:\n";
    foreach ($existingColumns as $col) {
        echo "  - $col\n";
    }
}

if (!empty($missingColumns)) {
    echo "\n✗ Missing (will be added):\n";
    foreach ($missingColumns as $col) {
        echo "  - $col\n";
    }
    echo "\nProceeding to add missing columns...\n\n";
    
    // Add each missing column
    foreach ($missingColumns as $column) {
        try {
            $sql = match($column) {
                'dark_mode_enabled' => "ALTER TABLE site_settings ADD COLUMN dark_mode_enabled TINYINT(1) DEFAULT 1 COMMENT 'Enable/disable dark mode feature'",
                'footer_legal_links_enabled' => "ALTER TABLE site_settings ADD COLUMN footer_legal_links_enabled TINYINT(1) DEFAULT 1 COMMENT 'Show legal links in footer'",
                'resource_download_notify_admin' => "ALTER TABLE site_settings ADD COLUMN resource_download_notify_admin TINYINT(1) DEFAULT 1 COMMENT 'Send email notifications for resource downloads'",
                'newsletter_auto_send_welcome' => "ALTER TABLE site_settings ADD COLUMN newsletter_auto_send_welcome TINYINT(1) DEFAULT 1 COMMENT 'Automatically send welcome email to new subscribers'",
            };
            
            $db->exec($sql);
            echo "✅ Added column: $column\n";
        } catch(Exception $e) {
            echo "❌ Error adding $column: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "\n✅ All columns already exist!\n";
}

echo "\n=== Final site_settings structure ===\n";
try {
    $stmt = $db->query('DESCRIBE site_settings');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  {$col['Field']} ({$col['Type']})";
        if ($col['Default']) {
            echo " DEFAULT {$col['Default']}";
        }
        echo "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
