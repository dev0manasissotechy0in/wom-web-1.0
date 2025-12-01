<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Newsletter.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$subject = trim($_POST['subject'] ?? '');
$preheader = trim($_POST['preheader'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate input
if (empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit;
}

try {
    // Get all subscribed users
    $stmt = $db->prepare("SELECT id, email, created_at FROM newsletter_subscribers WHERE status = 'subscribed' ORDER BY id");
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($subscribers)) {
        echo json_encode(['success' => false, 'message' => 'No active subscribers found']);
        exit;
    }
    
    // Initialize newsletter class
    $newsletter = new Newsletter($db);
    
    $sent_count = 0;
    $failed_count = 0;
    $failed_emails = [];
    
    // Send to each subscriber
    foreach ($subscribers as $subscriber) {
        try {
            $subscriber_email = $subscriber['email'];
            
            // Generate unsubscribe link
            $unsubscribe_token = md5($subscriber['id'] . $subscriber['email'] . $subscriber['created_at']);
            $unsubscribe_link = "http://" . $_SERVER['HTTP_HOST'] . "/unsubscribe.php?email=" . urlencode($subscriber_email) . "&token=" . $unsubscribe_token;
            
            // Replace placeholders in message
            $personalized_message = str_replace(
                ['{subscriber_email}', '{unsubscribe_link}'],
                [$subscriber_email, $unsubscribe_link],
                $message
            );
            
            // Send the email
            $result = $newsletter->sendNewsletter($subscriber_email, $subject, $personalized_message, $preheader);
            
            if ($result) {
                $sent_count++;
            } else {
                $failed_count++;
                $failed_emails[] = $subscriber_email;
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
            
        } catch (Exception $e) {
            $failed_count++;
            $failed_emails[] = $subscriber_email;
            error_log("Newsletter send error for {$subscriber_email}: " . $e->getMessage());
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Newsletter sent successfully',
        'sent' => $sent_count,
        'failed' => $failed_count,
        'failed_emails' => $failed_emails
    ]);
    
} catch (Exception $e) {
    error_log("Newsletter send error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error sending newsletter: ' . $e->getMessage()
    ]);
}
?>
