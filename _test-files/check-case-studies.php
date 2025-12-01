<?php
require_once 'config/config.php';

echo "=== Case Studies Table Structure ===\n\n";
try {
    $stmt = $db->query('DESCRIBE case_studies');
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== Sample Case Study Data ===\n\n";
    $stmt = $db->query('SELECT id, title, category, tags FROM case_studies LIMIT 2');
    while($row = $stmt->fetch()) {
        print_r($row);
        echo "\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
