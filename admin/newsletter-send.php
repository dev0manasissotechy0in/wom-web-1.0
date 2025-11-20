<?php
declare(strict_types=1);
require_once '../config/config.php';
require_once '../classes/Newsletter.php';

// Authentication and CSRF protection
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die(json_encode(['error' => 'Unauthorized']));
}

// REST API endpoint for sending newsletter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $newsletter = new Newsletter($db);
        
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $newsletterName = trim($_POST['newsletter_name'] ?? 'main');
        
        // Validate input
        if (empty($subject) || empty($body)) {
            http_response_code(400);
            die(json_encode(['error' => 'Subject and body are required']));
        }
        
        // Send newsletter
        $result = $newsletter->bulkSend($subject, $body, $newsletterName);
        
        // Log action
        error_log("Newsletter sent: Sent={$result['sent']}, Failed={$result['failed']}");
        
        // Return JSON response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'message' => "Newsletter sent to {$result['sent']} subscribers",
            'newsletter_name' => $newsletterName
        ]);
        
    } catch (Exception $e) {
        error_log("Error sending newsletter: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error sending newsletter',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>
