<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

// Load config and database connection
require_once __DIR__ . '/../config/config.php';

$resource_id = (int)($_GET['resource_id'] ?? 0);

try {
    $stmt = $db->prepare("SELECT * FROM resource_downloads WHERE resource_id = ? ORDER BY downloaded_at DESC");
    $stmt->execute([$resource_id]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($leads)) {
        die('No leads found for this resource');
    }
    
} catch(PDOException $e) {
    error_log("CSV export error: " . $e->getMessage());
    die('Error fetching leads data');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
$titles = $leads[0];
fputcsv($output, array_keys($leads[0]));

foreach ($leads as $lead) {
    fputcsv($output, [
        $lead['name'],
        $lead['email'],
        $lead['phone'],
        $lead['company'],
        $lead['ip_address'],
        date('Y-m-d H:i:s', strtotime($lead['downloaded_at']))
    ]);
}

fclose($output);
?>
