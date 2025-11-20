<?php
/**
 * SMTP Email Configuration
 * Using PHPMailer for reliable email delivery
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * SMTP Configuration Settings
 * Update these with your actual SMTP credentials
 */
define('SMTP_HOST', 'smtp.gmail.com');              // Gmail SMTP server (or your provider)
define('SMTP_PORT', 587);                            // TLS port (465 for SSL)
define('SMTP_SECURE', 'ssl');                        // 'tls' or 'ssl'
define('SMTP_USERNAME', 'support@manasissotechy.in');    // Your email
define('SMTP_PASSWORD', '!q[9Ua4EY.hk');        // Your app password
define('SMTP_FROM_EMAIL', 'newsbox@wallofmarketing.co');  // From email
define('SMTP_FROM_NAME', 'Wall of Marketing');   // From name

/**
 * Send Email Function
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $replyTo Reply-to email (optional)
 * @param array $attachments Array of file paths to attach (optional)
 * @return bool True on success, false on failure
 */
function sendEmail($to, $subject, $body, $replyTo = null, $attachments = []) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Disable debug output for production
        $mail->SMTPDebug  = 0; // Set to 2 for debugging
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Reply-to
        if($replyTo) {
            $mail->addReplyTo($replyTo);
        } else {
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        }
        
        // Attachments
        if(!empty($attachments)) {
            foreach($attachments as $attachment) {
                if(file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        // Send email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send Contact Form Email to Admin
 */
function sendContactNotification($name, $email, $phone, $subject, $message, $inquiryId = null) {
    $to = ADMIN_EMAIL;
    $emailSubject = "New Contact Inquiry: " . $subject;
    
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #000; color: white; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { background: #f9f9f9; padding: 30px 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; }
            .field:last-child { border-bottom: none; }
            .label { font-weight: bold; color: #000; margin-bottom: 5px; font-size: 14px; text-transform: uppercase; }
            .value { color: #666; font-size: 15px; line-height: 1.6; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f0f0f0; }
            .button { display: inline-block; padding: 12px 30px; background: #000; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 New Contact Inquiry</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>👤 Name</div>
                    <div class='value'>" . htmlspecialchars($name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>✉️ Email</div>
                    <div class='value'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
                </div>
                <div class='field'>
                    <div class='label'>📱 Phone</div>
                    <div class='value'>" . ($phone ? htmlspecialchars($phone) : 'Not provided') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>📋 Subject</div>
                    <div class='value'>" . htmlspecialchars($subject) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>💬 Message</div>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
                " . ($inquiryId ? "<div class='field'><div class='label'>🔖 Inquiry ID</div><div class='value'>#" . $inquiryId . "</div></div>" : "") . "
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . SITE_URL . "/admin/inquiries.php' class='button'>View in Admin Panel</a>
                </div>
            </div>
            <div class='footer'>
                <p>This email was sent from your contact form at " . SITE_NAME . "</p>
                <p>Received on: " . date('F j, Y \a\t g:i A') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $emailSubject, $emailBody, $email);
}

/**
 * Send Thank You Email to User
 */
function sendContactThankYou($name, $email, $subject) {
    $emailSubject = "Thank You for Contacting Us - " . SITE_NAME;
    
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%); color: white; padding: 40px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { background: white; padding: 40px 30px; }
            .content p { font-size: 16px; line-height: 1.8; color: #666; margin-bottom: 20px; }
            .highlight { background: #f8f8f8; padding: 20px; border-left: 4px solid #000; margin: 20px 0; }
            .footer { text-align: center; padding: 30px 20px; color: #666; font-size: 13px; background: #f8f8f8; }
            .social-links { margin-top: 20px; }
            .social-links a { display: inline-block; margin: 0 10px; color: #000; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Reaching Out!</h1>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                
                <p>Thank you for contacting <strong>" . SITE_NAME . "</strong>. We have received your inquiry regarding \"<em>" . htmlspecialchars($subject) . "</em>\" and appreciate you taking the time to reach out to us.</p>
                
                <div class='highlight'>
                    <p style='margin: 0;'><strong>What happens next?</strong></p>
                    <p style='margin: 10px 0 0 0;'>Our team will review your message and get back to you within 24-48 hours. We're committed to providing you with the best possible service and look forward to discussing how we can help you achieve your goals.</p>
                </div>
                
                <p>In the meantime, feel free to:</p>
                <ul>
                    <li>Explore our <a href='" . SITE_URL . "/services' style='color: #000; font-weight: bold;'>Services</a></li>
                    <li>Read our latest <a href='" . SITE_URL . "/blogs' style='color: #000; font-weight: bold;'>Blog Posts</a></li>
                    <li>Follow us on social media for updates and tips</li>
                </ul>
                
                <p>If you have any urgent questions, please don't hesitate to call us at <strong>+91 1234567890</strong>.</p>
                
                <p style='margin-top: 30px;'>
                    Best regards,<br>
                    <strong>The " . SITE_NAME . " Team</strong>
                </p>
            </div>
            <div class='footer'>
                <p><strong>" . SITE_NAME . "</strong></p>
                <p>Mumbai, Maharashtra, India</p>
                <p>Email: " . ADMIN_EMAIL . " | Phone: +91 1234567890</p>
                <div class='social-links'>
                    <a href='#'>Facebook</a> | 
                    <a href='#'>Twitter</a> | 
                    <a href='#'>LinkedIn</a> | 
                    <a href='#'>Instagram</a>
                </div>
                <p style='margin-top: 20px; font-size: 11px; color: #999;'>
                    You received this email because you contacted us through our website.<br>
                    © 2025 " . SITE_NAME . ". All rights reserved.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $emailSubject, $emailBody);
}

/**
 * Send Newsletter Email
 */
function sendNewsletterEmail($to, $name, $subject, $content) {
    $unsubscribeLink = SITE_URL . "/unsubscribe.php?email=" . urlencode($to);
    
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: #000; color: white; padding: 30px 20px; text-align: center; }
            .content { padding: 40px 30px; background: white; }
            .footer { text-align: center; padding: 20px; background: #f8f8f8; font-size: 12px; color: #666; }
            .button { display: inline-block; padding: 12px 30px; background: #000; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                " . $content . "
            </div>
            <div class='footer'>
                <p>You're receiving this email because you subscribed to our newsletter.</p>
                <p><a href='" . $unsubscribeLink . "' style='color: #666;'>Unsubscribe</a> | <a href='" . SITE_URL . "' style='color: #666;'>Visit Website</a></p>
                <p>© 2025 " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $emailBody);
}

/**
 * Send Bulk Newsletters
 */
function sendNewsletterBulk($subscribers, $subject, $content) {
    $successCount = 0;
    $failCount = 0;
    
    foreach($subscribers as $subscriber) {
        $name = $subscriber['name'] ?? 'Subscriber';
        $email = $subscriber['email'];
        
        if(sendNewsletterEmail($email, $name, $subject, $content)) {
            $successCount++;
        } else {
            $failCount++;
        }
        
        // Add small delay to avoid rate limiting
        usleep(100000); // 0.1 second delay
    }
    
    return [
        'success' => $successCount,
        'failed' => $failCount,
        'total' => count($subscribers)
    ];
}

/**
 * Test SMTP Connection
 */
function testSMTPConnection() {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        return ['success' => true, 'message' => 'SMTP connection successful'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}
?>
