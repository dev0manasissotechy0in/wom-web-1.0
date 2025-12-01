<?php
/**
 * Test Unsubscribe Email Template
 * Preview what the unsubscribe confirmation email looks like
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Newsletter.php';

$siteUrl = defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST'];
$siteName = defined('SITE_NAME') ? SITE_NAME : 'Our Website';
$testName = 'John Doe';

// Create Newsletter instance
$newsletter = new Newsletter($db);

// Use reflection to access private method for testing
$reflection = new ReflectionClass($newsletter);
$method = $reflection->getMethod('getUnsubscribeEmailTemplate');
$method->setAccessible(true);

// Get the email template
$emailTemplate = $method->invoke($newsletter, $testName, $siteName, $siteUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Email Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        .preview-header {
            background: #000;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        .preview-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .preview-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .email-preview {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .email-content {
            padding: 0;
        }

        .actions {
            padding: 20px;
            background: #f8f9fa;
            border-top: 2px solid #e0e0e0;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: #000;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: white;
            color: #000;
            border: 2px solid #000;
        }

        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px;
            border-radius: 4px;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="email-preview">
        <div class="preview-header">
            <h1>📭 Unsubscribe Confirmation Email Preview</h1>
            <p>This is how your unsubscribe confirmation email will look</p>
        </div>

        <div class="info-box">
            <strong>⚠️ Test Mode</strong>
            This is a preview of the unsubscribe confirmation email that will be sent when someone unsubscribes from your newsletter.
        </div>

        <div class="email-content">
            <?php echo $emailTemplate; ?>
        </div>

        <div class="actions">
            <a href="test-newsletter.php" class="btn btn-secondary">
                ← Back to Newsletter Test
            </a>
            <a href="unsubscribe.php" class="btn">
                Test Unsubscribe Page →
            </a>
        </div>
    </div>
</body>
</html>
