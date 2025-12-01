<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load config and database connection
require_once __DIR__ . '/config/config.php';

$resource_id = (int)($_GET['r'] ?? 0);
$token = $_GET['t'] ?? '';

error_log("Download request - Resource: $resource_id, Token: $token");

if (empty($resource_id) || empty($token)) {
    die('Invalid download link. Missing parameters.');
}

try {
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        die('Resource not found in database.');
    }
    
    if (empty($resource['file_path'])) {
        die('No file attached to this resource.');
    }
    
    $filePath = __DIR__ . '/assets/images/uploads/resources/' . $resource['file_path'];
    
    error_log("Attempting to download file: $filePath");
    
    if (!file_exists($filePath)) {
        die('File does not exist on server: ' . $resource['file_path'] . '<br>Expected path: ' . $filePath);
    }
    
    if (!is_readable($filePath)) {
        die('File is not readable. Check permissions.');
    }
    
    // Get file info
    $fileSize = filesize($filePath);
    $fileName = basename($resource['file_path']);
    
    // Clean output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');
    
    // Output file
    readfile($filePath);
    
    error_log("File downloaded successfully: $fileName");
    exit;
    
} catch (PDOException $e) {
    error_log("Download error: " . $e->getMessage());
    die('Download error: ' . $e->getMessage());
}
?>
