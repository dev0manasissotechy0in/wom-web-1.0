<?php
require_once __DIR__ . '/config/config.php';

echo "=== Checking site_settings table structure ===\n\n";

try {
    // Check current structure
    $stmt = $db->query("DESCRIBE site_settings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check if we need to add columns
    $column_names = array_column($columns, 'Field');
    
    if (!in_array('setting_key', $column_names)) {
        echo "\n✗ Missing 'setting_key' column. Attempting to add...\n";
        
        // Check what columns exist and adapt
        if (in_array('key', $column_names)) {
            $db->exec("ALTER TABLE site_settings CHANGE `key` setting_key VARCHAR(100) NOT NULL");
            echo "✓ Renamed 'key' to 'setting_key'\n";
        } else {
            $db->exec("ALTER TABLE site_settings ADD COLUMN setting_key VARCHAR(100) NOT NULL");
            echo "✓ Added 'setting_key' column\n";
        }
    }
    
    if (!in_array('setting_value', $column_names)) {
        if (in_array('value', $column_names)) {
            $db->exec("ALTER TABLE site_settings CHANGE `value` setting_value TEXT");
            echo "✓ Renamed 'value' to 'setting_value'\n";
        } else {
            $db->exec("ALTER TABLE site_settings ADD COLUMN setting_value TEXT");
            echo "✓ Added 'setting_value' column\n";
        }
    }
    
    if (!in_array('setting_type', $column_names)) {
        $db->exec("ALTER TABLE site_settings ADD COLUMN setting_type VARCHAR(50) DEFAULT 'text'");
        echo "✓ Added 'setting_type' column\n";
    }
    
    if (!in_array('description', $column_names)) {
        $db->exec("ALTER TABLE site_settings ADD COLUMN description TEXT");
        echo "✓ Added 'description' column\n";
    }
    
    echo "\n=== Final structure ===\n";
    $stmt = $db->query("DESCRIBE site_settings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
