<?php
/**
 * Newsletter Unsubscribe Page
 * Allows users to unsubscribe from newsletters
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Newsletter.php';

$message = '';
$messageClass = '';
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Handle unsubscribe request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $message = 'Please enter a valid email address.';
        $messageClass = 'error';
    } else {
        try {
            // Use Newsletter class to unsubscribe and send email
            $newsletter = new Newsletter($db);
            $result = $newsletter->unsubscribe($email);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageClass = 'success';
            } else {
                $message = $result['message'];
                $messageClass = $result['message'] === 'You have already unsubscribed from our newsletter.' ? 'info' : 'error';
            }
        } catch (Exception $e) {
            error_log("Unsubscribe error: " . $e->getMessage());
            $message = 'An error occurred. Please try again or contact us directly.';
            $messageClass = 'error';
        }
    }
}

$customSeoData = [
    'title' => 'Unsubscribe from Newsletter | ' . SITE_NAME,
    'description' => 'Unsubscribe from our newsletter',
    'robots' => 'noindex, nofollow'
];
?>

<?php require_once 'includes/header.php'; ?>

<style>
.unsubscribe-page {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #000000;
}

.unsubscribe-container {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    border: 2px solid #000000;
    max-width: 550px;
    width: 100%;
    padding: 50px 40px;
    text-align: center;
}

.unsubscribe-icon {
    font-size: 60px;
    color: #000000;
    margin-bottom: 20px;
}

.unsubscribe-container h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 15px;
}

.unsubscribe-container p {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 25px;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 14px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #000000;
}

.btn-unsubscribe {
    background: #000000;
    color: white;
    border: 2px solid #000000;
    padding: 15px 40px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
}

.btn-unsubscribe:hover {
    background: white;
    color: #000000;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.message {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-size: 15px;
    line-height: 1.6;
}

.message.success {
    background: #f0f0f0;
    color: #000000;
    border-left: 4px solid #000000;
}

.message.error {
    background: #f0f0f0;
    color: #000000;
    border-left: 4px solid #000000;
}

.message.info {
    background: #f0f0f0;
    color: #000000;
    border-left: 4px solid #000000;
}

.back-link {
    display: inline-block;
    margin-top: 25px;
    color: #000000;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: color 0.3s;
}

.back-link:hover {
    color: #666666;
    text-decoration: underline;
}

.feedback-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 25px;
    text-align: left;
}

.feedback-box h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 10px;
}

.feedback-box p {
    font-size: 14px;
    color: #666;
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .unsubscribe-container {
        padding: 40px 25px;
    }
    
    .unsubscribe-container h1 {
        font-size: 26px;
    }
    
    .unsubscribe-icon {
        font-size: 50px;
    }
}
</style>

<div class="unsubscribe-page">
    <div class="unsubscribe-container">
        <div class="unsubscribe-icon">
            <?php if ($messageClass === 'success'): ?>
                ✅
            <?php else: ?>
                📭
            <?php endif; ?>
        </div>
        
        <h1>Unsubscribe from Newsletter</h1>
        
        <?php if ($messageClass === 'success'): ?>
            <div class="message success">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="feedback-box">
                <h3>We'd Love Your Feedback</h3>
                <p>Would you mind telling us why you're leaving? Your feedback helps us improve.</p>
                <a href="/contact.php" class="btn-unsubscribe" style="display: inline-block; width: auto; margin-top: 15px; text-decoration: none;">
                    Share Feedback
                </a>
            </div>
            
        <?php else: ?>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageClass; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php else: ?>
                <p>We're sorry to see you go! If you no longer wish to receive our newsletters, please enter your email address below.</p>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($email); ?>"
                           required>
                </div>
                
                <button type="submit" class="btn-unsubscribe">
                    Unsubscribe
                </button>
            </form>
            
            <div class="feedback-box">
                <h3>Changed your mind?</h3>
                <p>You can always re-subscribe later from our homepage. We'd love to have you back!</p>
            </div>
            
        <?php endif; ?>
        
        <a href="/" class="back-link">← Return to Homepage</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
