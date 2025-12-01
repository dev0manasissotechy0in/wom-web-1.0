<?php
require_once 'config/config.php';

$resource_id = 1;

echo "Testing leads query for resource ID: $resource_id\n\n";

try {
    $stmt = $db->prepare('SELECT * FROM resource_downloads WHERE resource_id = ? ORDER BY downloaded_at DESC');
    $stmt->execute([$resource_id]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Number of leads found: " . count($leads) . "\n\n";
    
    if (count($leads) > 0) {
        foreach($leads as $lead) {
            echo "Lead ID: {$lead['id']}\n";
            echo "  Name: {$lead['name']}\n";
            echo "  Email: {$lead['email']}\n";
            echo "  Phone: {$lead['phone']}\n";
            echo "  Company: {$lead['company']}\n";
            echo "  Downloaded: {$lead['downloaded_at']}\n\n";
        }
    } else {
        echo "No leads found for this resource.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
