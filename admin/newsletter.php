<?php
session_start();
require_once '../config/config.php';
require_once '../classes/Newsletter.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';
$newsletter = new Newsletter($db);

// Get newsletter subscribers count
$subscriberCount = count($newsletter->getSubscribers('main', 'subscribed'));

// Handle newsletter send
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $newsletterName = trim($_POST['newsletter_name'] ?? 'main');
    
    if (empty($subject) || empty($body)) {
        $error = 'Subject and body are required';
    } else {
        $result = $newsletter->bulkSend($subject, $body, $newsletterName);
        
        if ($result['sent'] > 0) {
            $message = "Newsletter sent to {$result['sent']} subscribers";
        } else {
            $error = 'Failed to send newsletter';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Newsletter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .admin-table { margin-top: 30px; }
        .subscribers-count { color: #007bff; font-weight: 600; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-envelope"></i> Send Newsletter</h1>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="container">
        <?php if(!empty($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <h2>Send New Newsletter</h2>
            <p>Active subscribers: <span class="subscribers-count"><?php echo $subscriberCount; ?></span></p>
            
            <form method="POST" class="form">
                <div class="form-group">
                    <label>Newletter Name <span class="required">*</span></label>
                    <input type="text" name="newsletter_name" class="form-control" value="main" required>
                </div>
                
                <div class="form-group">
                    <label>Subject <span class="required">*</span></label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Message (HTML) <span class="required">*</span></label>
                    <textarea name="body" class="form-control" rows="8" required></textarea>
                    <small>You can use HTML tags for formatting</small>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Newsletter
                </button>
            </form>
        </div>
    </div>
</body>
</html>
