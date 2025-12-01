<?php
require_once 'config/config.php';

// Get all downloads
$stmt = $db->query("SELECT * FROM resource_downloads ORDER BY downloaded_at DESC");
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>Resource Downloads (" . count($downloads) . " total)</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Resource ID</th><th>Name</th><th>Email</th><th>Company</th><th>Downloaded At</th></tr>";

foreach ($downloads as $download) {
    echo "<tr>";
    echo "<td>" . $download['id'] . "</td>";
    echo "<td>" . $download['resource_id'] . "</td>";
    echo "<td>" . htmlspecialchars($download['name']) . "</td>";
    echo "<td>" . htmlspecialchars($download['email']) . "</td>";
    echo "<td>" . htmlspecialchars($download['company'] ?? '-') . "</td>";
    echo "<td>" . $download['downloaded_at'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Get download counts for resources
echo "<h2>Download Counts by Resource</h2>";
$stmt = $db->query("SELECT r.id, r.title, r.downloads, COUNT(rd.id) as actual_downloads FROM resources r LEFT JOIN resource_downloads rd ON r.id = rd.resource_id GROUP BY r.id ORDER BY r.id");
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Resource ID</th><th>Title</th><th>Recorded Downloads</th><th>Actual Downloads</th><th>Match</th></tr>";

foreach ($stats as $stat) {
    $match = ($stat['downloads'] == $stat['actual_downloads']) ? "✓" : "✗";
    echo "<tr>";
    echo "<td>" . $stat['id'] . "</td>";
    echo "<td>" . htmlspecialchars($stat['title']) . "</td>";
    echo "<td>" . $stat['downloads'] . "</td>";
    echo "<td>" . $stat['actual_downloads'] . "</td>";
    echo "<td>" . $match . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
