<?php
require_once 'config/config.php';

echo "=== Payment Settings Table ===\n";
try {
    $stmt = $db->query("DESCRIBE payment_settings");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Payment Settings Data ===\n";
try {
    $stmt = $db->query("SELECT * FROM payment_settings ORDER BY setting_key");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['setting_key'] . " = " . $row['setting_value'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Payment Methods Table ===\n";
try {
    $stmt = $db->query("DESCRIBE payment_methods");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Payment Methods Data ===\n";
try {
    $stmt = $db->query("SELECT * FROM payment_methods");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Book Call Table (Payment Related Columns) ===\n";
try {
    $stmt = $db->query("SHOW COLUMNS FROM book_call WHERE Field LIKE '%payment%' OR Field = 'amount'");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
