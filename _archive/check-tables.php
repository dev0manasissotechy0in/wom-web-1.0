<?php
require_once 'config/config.php';

echo "=== Checking site_settings table ===\n";
try {
    $stmt = $db->query('DESCRIBE site_settings');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "site_settings columns:\n";
    foreach($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Checking resource_downloads table ===\n";
try {
    $stmt = $db->query('DESCRIBE resource_downloads');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "resource_downloads columns:\n";
    foreach($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
    // Check if table has data
    $stmt = $db->query('SELECT COUNT(*) as count FROM resource_downloads');
    $count = $stmt->fetch()['count'];
    echo "\nTotal leads in resource_downloads: $count\n";
    
    if ($count > 0) {
        $stmt = $db->query('SELECT id, resource_id, name, email, downloaded_at FROM resource_downloads ORDER BY downloaded_at DESC LIMIT 5');
        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nRecent 5 leads:\n";
        foreach($leads as $lead) {
            echo "  ID: {$lead['id']}, Resource: {$lead['resource_id']}, Name: {$lead['name']}, Email: {$lead['email']}, Date: {$lead['downloaded_at']}\n";
        }
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Checking resources table ===\n";
try {
    $stmt = $db->query('SELECT id, title, downloads FROM resources');
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Resources with download counts:\n";
    foreach($resources as $r) {
        echo "  ID: {$r['id']}, Title: {$r['title']}, Downloads: {$r['downloads']}\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
