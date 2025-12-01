<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing subscriber count:\n";

// Test 1: Direct query
$stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'subscribed'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Direct query result: " . print_r($result, true) . "\n";
echo "Total subscribers: " . ($result['total'] ?? 'NULL') . "\n";

// Test 2: Check data type
echo "Data type: " . gettype($result['total']) . "\n";

// Test 3: number_format test
$total_subscribers = $result['total'] ?? 0;
echo "number_format output: " . number_format($total_subscribers) . "\n";
?>
