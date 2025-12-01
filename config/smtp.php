<?php
/**
 * SMTP Email Configuration
 * Using PHPMailer for reliable email delivery
 * Supports database-driven SMTP settings with fallback to constants
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Get SMTP Settings from Database or Constants
 * Used for newsletters, contact forms, and general emails
 * @return array SMTP configuration array
 */
function getSMTPSettings() {
    global $db; // Access global $db variable
    
    // Try to load from database first (no caching for fresh settings)
    try {
        if (!isset($db)) {
            require_once __DIR__ . '/config.php';
        }
        
        if (isset($db)) {
            $stmt = $db->query("SELECT * FROM smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settings) {
                return [
                    'host' => $settings['smtp_host'],
                    'port' => (int)$settings['smtp_port'],
                    'username' => $settings['smtp_username'],
                    'password' => $settings['smtp_password'],
                    'encryption' => $settings['smtp_encryption'],
                    'from_email' => $settings['from_email'],
                    'from_name' => $settings['from_name'],
                    'source' => 'database'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Failed to load SMTP settings from database: " . $e->getMessage());
    }
    
    // Fallback to constants or default Outlook settings
    return [
        'host' => defined('SMTP_HOST') ? SMTP_HOST : 'smtp-mail.outlook.com',
        'port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
        'username' => defined('SMTP_USERNAME') ? SMTP_USERNAME : 'wallofmarketing@outlook.com',
        'password' => defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '',
        'encryption' => defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls',
        'from_email' => defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'wallofmarketing@outlook.com',
        'from_name' => defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Wall of Marketing',
        'source' => 'constants'
    ];
}

/**
 * SMTP Configuration Settings - Fallback Constants
 * These are used only if database settings are not available
 * 
 * OUTLOOK/OFFICE 365 SETUP:
 * - Host: smtp-mail.outlook.com or smtp.office365.com
 * - Port: 587 (TLS recommended) or 25
 * - Encryption: TLS (STARTTLS)
 * - IMPORTANT: Use App Password, not regular password
 * - Generate App Password at: https://account.microsoft.com/security
 * - Navigate to: Advanced security options > App passwords > Create new app password
 * 
 * OTHER COMMON SMTP PROVIDERS:
 * - Gmail: smtp.gmail.com, Port 587, TLS (requires App Password if 2FA enabled)
 * - Hostinger: smtp.hostinger.com, Port 465, SSL
 * - SendGrid: smtp.sendgrid.net, Port 587, TLS
 * - Mailgun: smtp.mailgun.org, Port 587, TLS
 */
if(!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp-mail.outlook.com');
}
if(!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}
if(!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', 'tls');
    define('SMTP_SECURE', 'tls');
} else {
    if(!defined('SMTP_SECURE')) {
        define('SMTP_SECURE', SMTP_ENCRYPTION);
    }
}
if(!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', 'wallofmarketing@outlook.com');
}
if(!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', '');
}
if(!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'wallofmarketing@outlook.com');
}
if(!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Wall of Marketing');
}

/**
 * Get Login SMTP Settings from Database
 * Used specifically for admin OTP emails
 * @return array|null SMTP configuration array or null if not configured
 */
function getLoginSMTPSettings() {
    global $db; // Access global $db variable
    
    // Try to load from database (no caching to ensure fresh settings)
    try {
        if (!isset($db)) {
            require_once __DIR__ . '/config.php';
        }
        
        if (isset($db)) {
            $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settings) {
                return [
                    'host' => $settings['smtp_host'],
                    'port' => (int)$settings['smtp_port'],
                    'username' => $settings['smtp_username'],
                    'password' => $settings['smtp_password'],
                    'encryption' => $settings['smtp_encryption'],
                    'from_email' => $settings['from_email'],
                    'from_name' => $settings['from_name'],
                    'source' => 'database'
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Failed to load login SMTP settings from database: " . $e->getMessage());
    }
    
    // Return null if not configured - will use fallback Outlook settings
    return null;
}

/**
 * Send Login OTP Email Function
 * Uses dedicated login SMTP settings with fallback to Outlook constants
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @return bool True on success, false on failure
 */
function sendLoginOTPEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // Try to get dedicated login SMTP settings first
        $smtp = getLoginSMTPSettings();
        
        // If no login SMTP configured, use Outlook fallback
        if ($smtp === null) {
            $smtp = [
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'username' => 'wallofmarketing@outlook.com',
                'password' => defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '',
                'encryption' => 'tls',
                'from_email' => 'wallofmarketing@outlook.com',
                'from_name' => 'Wall of Marketing - Admin',
                'source' => 'outlook_fallback'
            ];
            error_log("Login OTP Email: Using Outlook fallback settings");
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->Port       = $smtp['port'];
        $mail->CharSet    = 'UTF-8';
        
        // Set encryption based on port and settings
        if($smtp['port'] == 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif($smtp['port'] == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif($smtp['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif($smtp['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Debug output disabled for production (set to 2 for troubleshooting)
        $mail->SMTPDebug  = 0;
        
        // Provider-specific options (works for Outlook, Gmail, etc.)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($to);
        $mail->addReplyTo($smtp['from_email'], $smtp['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        // Send email
        $mail->send();
        
        // Log success
        error_log("Login OTP Email sent successfully to: {$to} via {$smtp['source']} ({$smtp['host']})");
        
        return true;
        
    } catch (Exception $e) {
        error_log("Login OTP Email Error: {$mail->ErrorInfo}");
        error_log("Login OTP Email Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Send Email Function (Original - for newsletters and general emails)
 * Uses database SMTP settings
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
        // Get SMTP settings from database
        $smtp = getSMTPSettings();
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->Port       = $smtp['port'];
        $mail->CharSet    = 'UTF-8';
        
        // Set encryption based on port and settings
        if($smtp['port'] == 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif($smtp['port'] == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif($smtp['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif($smtp['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        // Debug output disabled for production (set to 2 for troubleshooting)
        $mail->SMTPDebug  = 0;
        
        // Provider-specific options
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($to);
        
        // Reply-to
        if($replyTo) {
            $mail->addReplyTo($replyTo);
        } else {
            $mail->addReplyTo($smtp['from_email'], $smtp['from_name']);
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
        error_log("Email Exception: " . $e->getMessage());
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
