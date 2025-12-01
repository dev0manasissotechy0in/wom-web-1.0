<?php
// Razorpay Payment Success Handler
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

$payment_type = $_GET['type'] ?? '';
$item_id = (int)($_GET['id'] ?? 0);
$payment_id = $_GET['payment_id'] ?? '';

if (!in_array($payment_type, ['booking', 'resource']) || empty($item_id) || empty($payment_id)) {
    header("Location: /");
    exit();
}

try {
    $db->beginTransaction();
    
    if ($payment_type === 'booking') {
        // Update booking payment status
        $stmt = $db->prepare("UPDATE book_call SET payment_status = 'completed', razorpay_payment_id = ? WHERE id = ?");
        $stmt->execute([$payment_id, $item_id]);
        
        // Fetch booking details
        $stmt = $db->prepare("SELECT * FROM book_call WHERE id = ?");
        $stmt->execute([$item_id]);
        $booking = $stmt->fetch();
        
        // Get payment settings
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM payment_settings WHERE setting_key IN ('calendly_link', 'booking_confirmation_email', 'booking_email_subject')");
        $stmt->execute();
        $settings_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Send confirmation email
        if (($settings_raw['booking_confirmation_email'] ?? '1') == '1') {
            $calendly_link = $settings_raw['calendly_link'] ?? $booking['calendly_link'];
            $email_subject = $settings_raw['booking_email_subject'] ?? 'Your Consultation Booking Confirmation';
            
            $email_body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #000; color: white; padding: 20px; text-align: center; }
                        .content { padding: 30px; background: #f9f9f9; }
                        .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                        .detail-row { padding: 10px 0; border-bottom: 1px solid #eee; }
                        .detail-label { font-weight: bold; color: #666; }
                        .button { display: inline-block; padding: 15px 30px; background: #000; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🎉 Booking Confirmed!</h1>
                        </div>
                        <div class='content'>
                            <h2>Hi " . htmlspecialchars($booking['name']) . ",</h2>
                            <p>Thank you for booking a consultation with us! Your payment has been successfully processed.</p>
                            
                            <div class='booking-details'>
                                <h3>Booking Details</h3>
                                <div class='detail-row'>
                                    <span class='detail-label'>Booking ID:</span> #" . $booking['id'] . "
                                </div>
                                <div class='detail-row'>
                                    <span class='detail-label'>Amount Paid:</span> ₹" . number_format($booking['amount'], 2) . "
                                </div>
                                <div class='detail-row'>
                                    <span class='detail-label'>Payment ID:</span> " . htmlspecialchars($payment_id) . "
                                </div>
                                <div class='detail-row'>
                                    <span class='detail-label'>Email:</span> " . htmlspecialchars($booking['email']) . "
                                </div>
                                <div class='detail-row'>
                                    <span class='detail-label'>Phone:</span> " . htmlspecialchars($booking['phone']) . "
                                </div>
                            </div>
                            
                            <h3>Next Steps:</h3>
                            <p>Please schedule your consultation appointment using the link below:</p>
                            <center>
                                <a href='" . htmlspecialchars($calendly_link) . "' class='button'>Schedule Your Appointment</a>
                            </center>
                            
                            <p style='margin-top: 30px;'>If you have any questions, feel free to reply to this email.</p>
                        </div>
                        <div class='footer'>
                            <p>© " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Send email
            sendEmail(
                $booking['email'],
                $booking['name'],
                $email_subject,
                $email_body
            );
        }
        
        $success_message = "Payment successful! Your consultation booking is confirmed.";
        $calendly_link_display = $settings_raw['calendly_link'] ?? $booking['calendly_link'];
        
    } else if ($payment_type === 'resource') {
        // Update resource download payment status
        $stmt = $db->prepare("UPDATE resource_downloads SET payment_status = 'completed', razorpay_payment_id = ? WHERE id = ?");
        $stmt->execute([$payment_id, $item_id]);
        
        // Fetch download details
        $stmt = $db->prepare("
            SELECT rd.*, r.title, r.file_path 
            FROM resource_downloads rd 
            JOIN resources r ON rd.resource_id = r.id 
            WHERE rd.id = ?
        ");
        $stmt->execute([$item_id]);
        $download = $stmt->fetch();
        
        // Send confirmation email
        $email_subject = "Your Resource Download is Ready";
        $download_link = SITE_URL . "/download.php?r=" . $download['resource_id'] . "&t=" . md5($download['email'] . $download['resource_id'] . 'secret');
        
        $email_body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #000; color: white; padding: 20px; text-align: center; }
                    .content { padding: 30px; background: #f9f9f9; }
                    .button { display: inline-block; padding: 15px 30px; background: #000; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📥 Download Ready!</h1>
                    </div>
                    <div class='content'>
                        <h2>Hi " . htmlspecialchars($download['name']) . ",</h2>
                        <p>Thank you for your purchase! Your payment has been processed successfully.</p>
                        <p><strong>Resource:</strong> " . htmlspecialchars($download['title']) . "</p>
                        <p><strong>Amount Paid:</strong> ₹" . number_format($download['price'], 2) . "</p>
                        <center>
                            <a href='" . $download_link . "' class='button'>Download Now</a>
                        </center>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        sendEmail(
            $download['email'],
            $download['name'],
            $email_subject,
            $email_body
        );
        
        $success_message = "Payment successful! You can now download your resource.";
        $download_link_display = $download_link;
    }
    
    $db->commit();
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log('Payment success error: ' . $e->getMessage());
    header("Location: /?error=payment_update_failed");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 15px;
            padding: 50px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        
        .info-box p {
            margin: 10px 0;
            color: #555;
        }
        
        .info-box strong {
            color: #333;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #000;
            color: white;
        }
        
        .btn-primary:hover {
            background: #333;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .email-notice {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        
        .email-notice i {
            color: #0066cc;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Payment Successful!</h1>
        
        <p class="success-message"><?php echo $success_message; ?></p>
        
        <div class="info-box">
            <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment_id); ?></p>
            <p><strong>Transaction Date:</strong> <?php echo date('F d, Y h:i A'); ?></p>
        </div>
        
        <div class="email-notice">
            <i class="fas fa-envelope"></i>
            <strong>Confirmation Email Sent!</strong><br>
            A confirmation email with details has been sent to your registered email address.
        </div>
        
        <?php if ($payment_type === 'booking'): ?>
            <a href="<?php echo htmlspecialchars($calendly_link_display); ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-calendar"></i> Schedule Appointment
            </a>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($download_link_display); ?>" class="btn btn-primary">
                <i class="fas fa-download"></i> Download Resource
            </a>
        <?php endif; ?>
        
        <a href="/" class="btn btn-secondary">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</body>
</html>
