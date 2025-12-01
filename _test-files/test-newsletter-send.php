<?php
require_once 'config/config.php';
require_once 'classes/Newsletter.php';

echo "=== Testing Newsletter Send ===\n\n";

try {
    $newsletter = new Newsletter($db);
    
    // Get a test subscriber
    $stmt = $db->query("SELECT email FROM newsletter_subscribers WHERE status = 'subscribed' LIMIT 1");
    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscriber) {
        echo "❌ No subscribers found to test with\n";
        exit;
    }
    
    $testEmail = $subscriber['email'];
    echo "Testing with: " . $testEmail . "\n\n";
    
    $subject = "Test Newsletter";
    $message = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Test Newsletter</h1>
        <p>This is a test newsletter email.</p>
        <p>If you received this, the newsletter system is working correctly!</p>
        <hr>
        <p style="font-size: 12px; color: #999;">
            <a href="{unsubscribe_link}">Unsubscribe</a>
        </p>
    </div>';
    
    $result = $newsletter->sendNewsletter($testEmail, $subject, $message, 'Testing newsletter system');
    
    if ($result) {
        echo "✓ Newsletter sent successfully!\n";
    } else {
        echo "❌ Newsletter send failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
