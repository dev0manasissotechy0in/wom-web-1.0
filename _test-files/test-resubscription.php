<?php
/**
 * Test Re-subscription Flow
 * Test that welcome email is sent when someone re-subscribes
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Newsletter.php';

$message = '';
$messageClass = '';
$debugInfo = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? 'Test User');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '❌ Please enter a valid email address';
        $messageClass = 'error';
    } else {
        $newsletter = new Newsletter($db);
        
        if ($action === 'subscribe') {
            // Test subscription
            $result = $newsletter->subscribe($email, $name);
            $message = $result['message'];
            $messageClass = $result['success'] ? 'success' : 'error';
            
            $debugInfo[] = "Action: Subscribe";
            $debugInfo[] = "Email: " . $email;
            $debugInfo[] = "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED');
            
        } elseif ($action === 'unsubscribe') {
            // Test unsubscribe
            $result = $newsletter->unsubscribe($email, $name);
            $message = $result['message'];
            $messageClass = $result['success'] ? 'success' : 'error';
            
            $debugInfo[] = "Action: Unsubscribe";
            $debugInfo[] = "Email: " . $email;
            $debugInfo[] = "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED');
            
        } elseif ($action === 'resubscribe') {
            // Test re-subscription (subscribe again after unsubscribe)
            $result = $newsletter->subscribe($email, $name);
            $message = $result['message'];
            $messageClass = $result['success'] ? 'success' : 'error';
            
            $debugInfo[] = "Action: Re-subscribe";
            $debugInfo[] = "Email: " . $email;
            $debugInfo[] = "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED');
            $debugInfo[] = "Note: Welcome email should be sent!";
        }
        
        // Get current status from database
        $stmt = $db->prepare("SELECT status, name, location, created_at, updated_at FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscriber) {
            $debugInfo[] = "---";
            $debugInfo[] = "Database Status: " . $subscriber['status'];
            $debugInfo[] = "Name: " . $subscriber['name'];
            $debugInfo[] = "Location: " . ($subscriber['location'] ?? 'N/A');
            $debugInfo[] = "Created: " . $subscriber['created_at'];
            $debugInfo[] = "Updated: " . $subscriber['updated_at'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Re-subscription Flow</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000;
            padding: 40px 20px;
            color: white;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 36px;
        }

        .subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 40px;
            font-size: 16px;
        }

        .test-card {
            background: white;
            color: #333;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(255,255,255,0.1);
            border: 2px solid #333;
        }

        .test-card h2 {
            color: #000;
            margin-bottom: 15px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #000;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #000;
        }

        .btn {
            background: #000;
            color: white;
            border: 2px solid #000;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }

        .btn:hover {
            background: white;
            color: #000;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #000;
            border: 2px solid #000;
        }

        .btn-secondary:hover {
            background: #000;
            color: white;
        }

        .message {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 15px;
            border-left: 4px solid;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .debug-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #000;
            margin-top: 20px;
        }

        .debug-info h3 {
            color: #000;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .debug-info ul {
            list-style: none;
            padding: 0;
        }

        .debug-info li {
            color: #333;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-family: monospace;
            font-size: 14px;
        }

        .debug-info li:last-child {
            border-bottom: none;
        }

        .flow-steps {
            background: #f0f0f0;
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .flow-steps h3 {
            color: #000;
            margin-bottom: 15px;
        }

        .flow-steps ol {
            padding-left: 25px;
            color: #333;
        }

        .flow-steps li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .step-number {
            background: #000;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 10px;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 25px;
        }

        @media (max-width: 768px) {
            .button-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Re-subscription Test</h1>
        <p class="subtitle">Test that welcome emails are sent on both initial subscription AND re-subscription</p>

        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($debugInfo)): ?>
            <div class="debug-info">
                <h3>📊 Debug Information</h3>
                <ul>
                    <?php foreach ($debugInfo as $info): ?>
                        <li><?php echo htmlspecialchars($info); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Test Flow Instructions -->
        <div class="test-card">
            <h2>📋 Testing Instructions</h2>
            <div class="flow-steps">
                <h3>Complete Re-subscription Test Flow:</h3>
                <ol>
                    <li><strong>Step 1:</strong> Enter your email and click "Subscribe" → Check inbox for welcome email ✅</li>
                    <li><strong>Step 2:</strong> Click "Unsubscribe" → Check inbox for unsubscribe confirmation ✅</li>
                    <li><strong>Step 3:</strong> Click "Re-subscribe" → Check inbox for welcome email again ✅</li>
                </ol>
                <p style="margin-top: 15px; color: #666;">
                    <strong>Expected Result:</strong> You should receive welcome emails in both Step 1 and Step 3!
                </p>
            </div>
        </div>

        <!-- Subscribe Form -->
        <div class="test-card">
            <h2><span class="step-number">1</span> Subscribe</h2>
            <p>First time subscription - sends welcome email</p>
            <form method="POST">
                <input type="hidden" name="action" value="subscribe">
                <div class="form-group">
                    <label for="email1">Email Address</label>
                    <input type="email" id="email1" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="name1">Name</label>
                    <input type="text" id="name1" name="name" placeholder="John Doe" value="Test User">
                </div>
                <button type="submit" class="btn">Subscribe to Newsletter</button>
            </form>
        </div>

        <!-- Unsubscribe Form -->
        <div class="test-card">
            <h2><span class="step-number">2</span> Unsubscribe</h2>
            <p>Unsubscribe from newsletter - sends unsubscribe confirmation email</p>
            <form method="POST">
                <input type="hidden" name="action" value="unsubscribe">
                <div class="form-group">
                    <label for="email2">Email Address</label>
                    <input type="email" id="email2" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="name2">Name</label>
                    <input type="text" id="name2" name="name" placeholder="John Doe" value="Test User">
                </div>
                <button type="submit" class="btn btn-secondary">Unsubscribe</button>
            </form>
        </div>

        <!-- Re-subscribe Form -->
        <div class="test-card">
            <h2><span class="step-number">3</span> Re-subscribe</h2>
            <p>Subscribe again after unsubscribe - sends welcome email again! 🎉</p>
            <form method="POST">
                <input type="hidden" name="action" value="resubscribe">
                <div class="form-group">
                    <label for="email3">Email Address</label>
                    <input type="email" id="email3" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="name3">Name</label>
                    <input type="text" id="name3" name="name" placeholder="John Doe" value="Test User">
                </div>
                <button type="submit" class="btn">Re-subscribe to Newsletter</button>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="button-grid">
            <a href="test-newsletter.php" class="btn btn-secondary" style="display: block; text-align: center; text-decoration: none; padding: 14px;">
                Newsletter Test
            </a>
            <a href="test-unsubscribe-email.php" class="btn btn-secondary" style="display: block; text-align: center; text-decoration: none; padding: 14px;">
                Email Preview
            </a>
            <a href="admin/newsletter-unsubscribes.php" class="btn btn-secondary" style="display: block; text-align: center; text-decoration: none; padding: 14px;">
                Admin Dashboard
            </a>
        </div>
    </div>
</body>
</html>
