<?php
require_once 'config/config.php';

echo "=== Services Table Structure ===\n\n";
$stmt = $db->query('DESCRIBE services');
while($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n=== Sample Services Data ===\n\n";
$stmt = $db->query('SELECT * FROM services LIMIT 2');
while($row = $stmt->fetch()) {
    print_r($row);
    echo "\n---\n\n";
}
