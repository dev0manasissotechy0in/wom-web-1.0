<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep off for JSON response

// Set JSON header immediately
header('Content-Type: application/json; charset=utf-8');

// Clear any output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Load config and database connection
require_once __DIR__ . '/config/config.php';

// Log all requests with timestamp and full details
$timestamp = date('Y-m-d H:i:s');
file_put_contents(__DIR__ . '/download-log.txt', "[$timestamp] Download request from {$_SERVER['REMOTE_ADDR']}\n", FILE_APPEND);
file_put_contents(__DIR__ . '/download-log.txt', "POST data: " . json_encode($_POST) . "\n\n", FILE_APPEND);
error_log("[$timestamp] Download request received from {$_SERVER['REMOTE_ADDR']}: " . json_encode($_POST));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[$timestamp] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$resource_id = (int)($_POST['resource_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');

$timestamp = date('Y-m-d H:i:s');
error_log("[$timestamp] Processing: Resource ID: $resource_id, Name: $name, Email: $email, Phone: $phone, Company: $company");

// Validate
if (empty($resource_id)) {
    error_log("[$timestamp] Validation failed: Missing resource ID");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Resource ID is required']));
}

if (empty($name)) {
    error_log("[$timestamp] Validation failed: Missing name");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Name is required']));
}

if (empty($email)) {
    error_log("[$timestamp] Validation failed: Missing email");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email is required']));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("[$timestamp] Validation failed: Invalid email: $email");
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid email address']));
}

try {
    $timestamp = date('Y-m-d H:i:s');
    
    // Get resource
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        error_log("[$timestamp] Error: Resource not found with ID: $resource_id");
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Resource not found']));
    }
    
    error_log("[$timestamp] Resource found: {$resource['title']} (ID: {$resource['id']})");
    
    // Check if resource is paid
    $is_paid = ($resource['resource_type'] === 'paid' && $resource['price'] > 0);
    
    if (empty($resource['file_path'])) {
        error_log("[$timestamp] Error: No file attached to resource ID: $resource_id");
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'No file attached to this resource']));
    }
    
    // Check if file exists
    $filePath = __DIR__ . '/assets/images/uploads/resources/' . $resource['file_path'];
    if (!file_exists($filePath)) {
        error_log("[$timestamp] Error: File not found on server: $filePath");
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'File not found on server. Please contact admin.']));
    }
    
    error_log("[$timestamp] File exists: $filePath (Size: " . filesize($filePath) . " bytes)");
    
    // For paid resources, create download record and redirect to payment
    if ($is_paid) {
        // Check if already purchased
        $checkPaidStmt = $db->prepare("SELECT id, payment_status FROM resource_downloads WHERE resource_id = ? AND email = ? ORDER BY downloaded_at DESC LIMIT 1");
        $checkPaidStmt->execute([$resource_id, $email]);
        $existingDownload = $checkPaidStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDownload && $existingDownload['payment_status'] === 'completed') {
            // Already paid - allow download
            $fileUrl = '/download.php?r=' . $resource_id . '&t=' . md5($email . $resource_id . 'secret');
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Resource already purchased. Download link generated.',
                'file_url' => $fileUrl
            ]);
            exit;
        }
        
        // Create new download record with pending payment
        $insertStmt = $db->prepare("
            INSERT INTO resource_downloads 
            (resource_id, name, email, phone, company, payment_status, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)
        ");
        $insertStmt->execute([
            $resource_id, 
            $name, 
            $email, 
            $phone, 
            $company,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        $download_id = $db->lastInsertId();
        
        error_log("[$timestamp] Paid resource - created download record ID: $download_id");
        
        // Return payment URL
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'requires_payment' => true,
            'message' => 'Please complete payment to download this resource.',
            'amount' => floatval($resource['price']),
            'razorpay_url' => '/razorpay-resource-process.php?type=resource&id=' . $download_id,
            'paypal_url' => '/paypal-resource-process.php?type=resource&id=' . $download_id
        ]);
        exit;
    }
    
    // For free resources, continue with normal flow
    // Check if already downloaded
    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM resource_downloads WHERE resource_id = ? AND email = ?");
    $checkStmt->execute([$resource_id, $email]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $alreadyDownloaded = $result['count'] > 0;
    
    // Always increment download count and process download
    file_put_contents(__DIR__ . '/download-log.txt', "[$timestamp] Processing download...\n", FILE_APPEND);
    
    if ($alreadyDownloaded) {
        file_put_contents(__DIR__ . '/download-log.txt', "[$timestamp] DUPLICATE DOWNLOAD: Resource $resource_id, Email $email\n", FILE_APPEND);
        error_log("[$timestamp] Info: Duplicate download detected - Resource ID: $resource_id, Email: $email - Still incrementing counter");
    } else {
        file_put_contents(__DIR__ . '/download-log.txt', "[$timestamp] NEW DOWNLOAD - Inserting record\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/download-log.txt', "  Resource ID: $resource_id\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/download-log.txt', "  Name: $name\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/download-log.txt', "  Email: $email\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/download-log.txt', "  Phone: $phone\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/download-log.txt', "  Company: $company\n", FILE_APPEND);
        
        error_log("[$timestamp] New download - Inserting record for Resource ID: $resource_id, Email: $email");
        
        // Save download record (only for first-time downloads)
        $insertStmt = $db->prepare("INSERT INTO resource_downloads (resource_id, name, email, phone, company, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertResult = $insertStmt->execute([
            $resource_id,
            $name,
            $email,
            $phone,
            $company,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
        if (!$insertResult) {
            $errorInfo = $insertStmt->errorInfo();
            file_put_contents(__DIR__ . '/download-log.txt', "  INSERT FAILED: " . json_encode($errorInfo) . "\n", FILE_APPEND);
            error_log("[$timestamp] Error: Failed to insert into resource_downloads. Error: " . json_encode($insertStmt->errorInfo()));
            throw new Exception('Failed to save download record: ' . $errorInfo[2]);
        }
        
        $insertedId = $db->lastInsertId();
        file_put_contents(__DIR__ . '/download-log.txt', "  INSERT SUCCESS - ID: $insertedId\n", FILE_APPEND);
        error_log("[$timestamp] Success: Download record inserted with ID: $insertedId");
    }
    
    // Always increment download count regardless of duplicate status
    file_put_contents(__DIR__ . '/download-log.txt', "  Incrementing download count for resource $resource_id\n", FILE_APPEND);
    $updateStmt = $db->prepare("UPDATE resources SET downloads = downloads + 1 WHERE id = ?");
    $updateResult = $updateStmt->execute([$resource_id]);
    
    if (!$updateResult) {
        $errorInfo = $updateStmt->errorInfo();
        file_put_contents(__DIR__ . '/download-log.txt', "  UPDATE FAILED: " . json_encode($errorInfo) . "\n", FILE_APPEND);
        error_log("[$timestamp] Error: Failed to update resources table. Error: " . json_encode($updateStmt->errorInfo()));
        throw new Exception('Failed to update download count');
    }
    
    // Verify the count was updated
    $verifyStmt = $db->prepare("SELECT downloads FROM resources WHERE id = ?");
    $verifyStmt->execute([$resource_id]);
    $verifyResult = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    file_put_contents(__DIR__ . '/download-log.txt', "  New download count: {$verifyResult['downloads']}\n", FILE_APPEND);
    error_log("[$timestamp] Verified: Resource downloads count is now: {$verifyResult['downloads']}");
    
    // Return download URL
    $fileUrl = '/download.php?r=' . $resource_id . '&t=' . md5($email . $resource_id . 'secret');
    
    error_log("[$timestamp] Download URL generated: $fileUrl");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your download will start shortly.',
        'file_url' => $fileUrl
    ]);
    
} catch (PDOException $e) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] Database error: " . $e->getMessage() . " - Code: " . $e->getCode());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']));
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] General error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']));
}
?>
