<?php
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$url = $input['url'] ?? '';
$action = $input['action'] ?? 'URL_UPDATED';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'URL is required']);
    exit();
}

// Path to Google service account JSON file
$serviceAccountFile = __DIR__ . '/../config/google-service-account.json';

// Check if service account file exists
if (!file_exists($serviceAccountFile)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Google Service Account file not found. Please upload google-service-account.json to /config/ directory. See API Setup tab for instructions.'
    ]);
    exit();
}

try {
    // Load the Google Client Library
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Initialize the Google Client
    $client = new Google_Client();
    $client->setAuthConfig($serviceAccountFile);
    $client->addScope('https://www.googleapis.com/auth/indexing');
    
    // Get the HTTP client
    $httpClient = $client->authorize();
    
    // Prepare the request body
    $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    
    $content = json_encode([
        'url' => $url,
        'type' => $action // URL_UPDATED or URL_DELETED
    ]);
    
    // Make the API request
    $response = $httpClient->post($endpoint, [
        'headers' => [
            'Content-Type' => 'application/json'
        ],
        'body' => $content
    ]);
    
    $statusCode = $response->getStatusCode();
    $body = json_decode($response->getBody(), true);
    
    if ($statusCode === 200) {
        // Log the successful submission
        $stmt = $db->prepare("INSERT INTO indexing_logs (url, action_type, status, response, created_at) 
                              VALUES (?, ?, 'success', ?, NOW())");
        $stmt->execute([$url, $action, json_encode($body)]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully submitted to Google Indexing API!',
            'details' => $body
        ]);
    } else {
        // Log the failed submission
        $stmt = $db->prepare("INSERT INTO indexing_logs (url, action_type, status, response, created_at) 
                              VALUES (?, ?, 'failed', ?, NOW())");
        $stmt->execute([$url, $action, json_encode($body)]);
        
        echo json_encode([
            'success' => false,
            'message' => 'Failed to submit to Google: ' . ($body['error']['message'] ?? 'Unknown error'),
            'details' => $body
        ]);
    }
    
} catch (Google_Service_Exception $e) {
    // Log the error
    $errorMsg = $e->getMessage();
    $stmt = $db->prepare("INSERT INTO indexing_logs (url, action_type, status, response, created_at) 
                          VALUES (?, ?, 'error', ?, NOW())");
    $stmt->execute([$url, $action, $errorMsg]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Google API Error: ' . $errorMsg
    ]);
    
} catch (Exception $e) {
    // Log general error
    $errorMsg = $e->getMessage();
    $stmt = $db->prepare("INSERT INTO indexing_logs (url, action_type, status, response, created_at) 
                          VALUES (?, ?, 'error', ?, NOW())");
    $stmt->execute([$url, $action, $errorMsg]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $errorMsg
    ]);
}
?>
