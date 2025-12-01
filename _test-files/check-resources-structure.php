<?php
require_once 'config/config.php';

echo "=== Resources Table Structure ===\n";
$stmt = $db->query("DESCRIBE resources");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== Resource Downloads Table Structure ===\n";
$stmt = $db->query("DESCRIBE resource_downloads");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== Sample Resources ===\n";
$stmt = $db->query("SELECT id, title, resource_type, price FROM resources LIMIT 5");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | {$row['title']} | Type: " . ($row['resource_type'] ?? 'N/A') . " | Price: " . ($row['price'] ?? 'N/A') . "\n";
}
?>
