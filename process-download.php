<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep off for JSON response

header('Content-Type: application/json');

// Load config and database connection
require_once __DIR__ . '/config/config.php';

// Log all requests
error_log("Download request received: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$resource_id = (int)($_POST['resource_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');

error_log("Processing: Resource ID: $resource_id, Name: $name, Email: $email");

// Validate
if (empty($resource_id)) {
    error_log("Missing resource ID");
    die(json_encode(['success' => false, 'message' => 'Resource ID is required']));
}

if (empty($name)) {
    error_log("Missing name");
    die(json_encode(['success' => false, 'message' => 'Name is required']));
}

if (empty($email)) {
    error_log("Missing email");
    die(json_encode(['success' => false, 'message' => 'Email is required']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email: $email");
    die(json_encode(['success' => false, 'message' => 'Invalid email address']));
}

try {
    // Get resource
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        error_log("Resource not found: $resource_id");
        die(json_encode(['success' => false, 'message' => 'Resource not found']));
    }
    
    if (empty($resource['file_path'])) {
        error_log("No file attached to resource: $resource_id");
        die(json_encode(['success' => false, 'message' => 'No file attached to this resource']));
    }
    
    // Check if file exists
    $filePath = __DIR__ . '/uploads/resources/' . $resource['file_path'];
    if (!file_exists($filePath)) {
        error_log("File not found on server: $filePath");
        die(json_encode(['success' => false, 'message' => 'File not found on server. Please contact admin.']));
    }
    
    // Check if already downloaded
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM resource_downloads WHERE resource_id = ? AND email = ?");
    $checkStmt->execute([$resource_id, $email]);
    $alreadyDownloaded = $checkStmt->fetchColumn() > 0;
    
    if (!$alreadyDownloaded) {
        // Save download record
        $stmt = $db->prepare("INSERT INTO resource_downloads (resource_id, name, email, phone, company, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $resource_id,
            $name,
            $email,
            $phone,
            $company,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
        // Increment download count
        $db->prepare("UPDATE resources SET downloads = downloads + 1 WHERE id = ?")->execute([$resource_id]);
        
        error_log("New download recorded for resource: $resource_id by $email");
    } else {
        error_log("Duplicate download detected for resource: $resource_id by $email");
    }
    
    // Return download URL
    $fileUrl = '/download.php?r=' . $resource_id . '&t=' . md5($email . $resource_id . 'secret');
    
    error_log("Download URL generated: $fileUrl");
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your download will start shortly.',
        'file_url' => $fileUrl
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']));
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']));
}
?>
