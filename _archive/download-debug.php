<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

echo "<h2>Download System Debug Report</h2>";

// Check resource_downloads table
echo "<h3>Recent Downloads (Last 10)</h3>";
try {
    $stmt = $db->query("SELECT * FROM resource_downloads ORDER BY downloaded_at DESC LIMIT 10");
    $downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($downloads) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Resource ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Downloaded At</th></tr>";
        foreach ($downloads as $download) {
            echo "<tr>";
            echo "<td>" . $download['id'] . "</td>";
            echo "<td>" . $download['resource_id'] . "</td>";
            echo "<td>" . $download['name'] . "</td>";
            echo "<td>" . $download['email'] . "</td>";
            echo "<td>" . $download['phone'] . "</td>";
            echo "<td>" . $download['company'] . "</td>";
            echo "<td>" . $download['downloaded_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'><strong>No downloads recorded yet!</strong></p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check resources download counts
echo "<h3>Resources with Download Counts</h3>";
try {
    $stmt = $db->query("SELECT id, title, slug, downloads FROM resources WHERE file_path IS NOT NULL ORDER BY downloads DESC LIMIT 5");
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>Slug</th><th>Downloads</th></tr>";
    foreach ($resources as $resource) {
        echo "<tr>";
        echo "<td>" . $resource['id'] . "</td>";
        echo "<td>" . $resource['title'] . "</td>";
        echo "<td>" . $resource['slug'] . "</td>";
        echo "<td>" . $resource['downloads'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check process-download.php log
echo "<h3>Process-Download.php Recent Logs</h3>";
$errorLog = __DIR__ . '/error_log';
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -20);
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
    foreach ($recentLines as $line) {
        if (strpos($line, 'Download') !== false || strpos($line, 'download') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>error_log file not found</p>";
}

echo "<h3>Test Form Submission</h3>";
echo "<p>Click the button below to test the download system:</p>";
?>

<style>
form { margin: 20px 0; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; }
input { display: block; margin: 10px 0; padding: 8px; width: 300px; }
button { padding: 10px 20px; background: #000; color: white; cursor: pointer; }
</style>

<?php
// Check if we have any published resources with files
$stmt = $db->query("SELECT id, title FROM resources WHERE status = 'published' AND file_path IS NOT NULL LIMIT 1");
$testResource = $stmt->fetch(PDO::FETCH_ASSOC);

if ($testResource):
?>

<form id="testForm">
    <input type="hidden" name="resource_id" value="<?php echo $testResource['id']; ?>">
    <input type="text" name="name" placeholder="Your Name" required>
    <input type="email" name="email" placeholder="Your Email" required>
    <input type="tel" name="phone" placeholder="Your Phone">
    <input type="text" name="company" placeholder="Your Company">
    <button type="submit">Test Download</button>
</form>

<script>
document.getElementById('testForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    console.log('Submitting form...');
    console.log('Resource ID:', formData.get('resource_id'));
    console.log('Name:', formData.get('name'));
    console.log('Email:', formData.get('email'));
    
    try {
        const response = await fetch('/process-download.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        console.log('Response:', text);
        
        const result = JSON.parse(text);
        if (result.success) {
            alert('Success! Download URL: ' + result.file_url);
            console.log('Download URL:', result.file_url);
            // Uncomment to actually download:
            // window.location.href = result.file_url;
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
});
</script>

<?php
else:
    echo "<p style='color: red;'>No published resources with files found. Please add a resource first.</p>";
endif;
?>
