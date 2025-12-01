<?php
require_once 'config/config.php';

// Check subscribed count
$stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'subscribed'");
$result = $stmt->fetch();
echo "Subscribed count: " . $result['total'] . "\n";

// Check all statuses
$stmt = $db->query("SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status");
$results = $stmt->fetchAll();
echo "\nAll statuses:\n";
foreach ($results as $row) {
    echo "  " . $row['status'] . ": " . $row['count'] . "\n";
}
?>
