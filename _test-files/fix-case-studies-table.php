<?php
require_once 'config/config.php';

echo "=== Checking and Fixing Case Studies Table ===\n\n";

try {
    // Check current columns
    echo "Current columns:\n";
    $stmt = $db->query('DESCRIBE case_studies');
    $existing_columns = [];
    while($row = $stmt->fetch()) {
        $existing_columns[] = $row['Field'];
        echo "- {$row['Field']}\n";
    }
    
    echo "\n=== Adding Missing Columns ===\n\n";
    
    // Add excerpt column if missing
    if(!in_array('excerpt', $existing_columns)) {
        $db->exec("ALTER TABLE case_studies ADD COLUMN excerpt TEXT DEFAULT NULL AFTER short_description");
        echo "✓ Added excerpt column\n";
    } else {
        echo "⚠ excerpt column already exists\n";
    }
    
    // Add key_results column if missing
    if(!in_array('key_results', $existing_columns)) {
        $db->exec("ALTER TABLE case_studies ADD COLUMN key_results TEXT DEFAULT NULL AFTER results");
        echo "✓ Added key_results column\n";
    } else {
        echo "⚠ key_results column already exists\n";
    }
    
    // Add project_type column if missing
    if(!in_array('project_type', $existing_columns)) {
        $db->exec("ALTER TABLE case_studies ADD COLUMN project_type VARCHAR(100) DEFAULT NULL AFTER industry");
        echo "✓ Added project_type column\n";
    } else {
        echo "⚠ project_type column already exists\n";
    }
    
    echo "\n✅ Case studies table updated successfully!\n";
    
} catch(PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
