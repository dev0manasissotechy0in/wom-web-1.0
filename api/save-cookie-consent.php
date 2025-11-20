<?php
/**
 * Save Cookie Consent API Endpoint
 * Saves user cookie preferences to database
 */

// Enable CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['necessary'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid input data'
    ]);
    exit();
}

// Load database connection
require_once __DIR__ . '/../config/config.php';

try {
    // Get session ID
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $session_id = session_id();

    // Get user info
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // Prepare consent data
    $necessary = (bool)($data['necessary'] ?? true);
    $functional = (bool)($data['functional'] ?? false);
    $analytics = (bool)($data['analytics'] ?? false);
    $marketing = (bool)($data['marketing'] ?? false);

    // Check if consent already exists for this session
    $stmt = $db->prepare("SELECT id FROM cookie_consent WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing consent
        $stmt = $db->prepare("UPDATE cookie_consent 
                             SET necessary = ?, functional = ?, analytics = ?, marketing = ?, 
                                 ip_address = ?, updated_at = NOW() 
                             WHERE session_id = ?");
        $stmt->execute([$necessary, $functional, $analytics, $marketing, $ip_address, $session_id]);

        $message = 'Cookie consent updated successfully';
    } else {
        // Insert new consent
        $stmt = $db->prepare("INSERT INTO cookie_consent 
                             (session_id, necessary, functional, analytics, marketing, ip_address, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$session_id, $necessary, $functional, $analytics, $marketing, $ip_address]);

        $message = 'Cookie consent saved successfully';
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'consent' => [
            'necessary' => $necessary,
            'functional' => $functional,
            'analytics' => $analytics,
            'marketing' => $marketing
        ]
    ]);

} catch (PDOException $e) {
    error_log("Cookie consent save error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>