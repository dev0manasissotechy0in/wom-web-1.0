<?php
require 'config/config.php';
$stmt = $db->query('DESCRIBE newsletter_subscribers');
echo "Columns in newsletter_subscribers table:\n\n";
while($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
