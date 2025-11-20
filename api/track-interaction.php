<?php
/**
 * Track Interaction API Endpoint
 * Receives and stores user interaction data from tracking.js
 */

require_once __DIR__ . '/../config/config.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit();
}

// Validate required fields
$required = ['session_id', 'page_url', 'interaction_type'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
        exit();
    }
}

try {
    // Check if user has given consent
    $consent_given = false;
    if (isset($_COOKIE['wom_cookie_consent']) && $_COOKIE['wom_cookie_consent'] === 'accepted') {
        $consent_given = true;
    }

    // Only track if consent is given
    if (!$consent_given) {
        echo json_encode(['success' => true, 'message' => 'Tracking skipped - no consent']);
        exit();
    }

    // Prepare data for insertion
    $session_id = $data['session_id'];
    $page_url = $data['page_url'];
    $interaction_type = $data['interaction_type'];
    $time_spent = $data['time_spent'] ?? 0;
    $interaction_data = isset($data['data']) ? json_encode($data['data']) : null;

    // Get user info
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    // Detect device type
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $user_agent)) {
        $device_type = 'tablet';
    } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $user_agent)) {
        $device_type = 'mobile';
    } else {
        $device_type = 'desktop';
    }

    // Detect browser
    $browser = 'Unknown';
    if (preg_match('/MSIE|Trident/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Edge/i', $user_agent)) {
        $browser = 'Edge';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    }

    // Insert into database
    $stmt = $db->prepare("INSERT INTO user_tracking 
        (session_id, page_url, ip_address, user_agent, device_type, browser, 
         interaction_type, time_spent, interaction_data) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $session_id,
        $page_url,
        $ip_address,
        $user_agent,
        $device_type,
        $browser,
        $interaction_type,
        $time_spent,
        $interaction_data
    ]);

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Interaction tracked successfully'
    ]);

} catch (PDOException $e) {
    error_log("Track interaction error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>