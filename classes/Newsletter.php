<?php
// classes/Newsletter.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Newsletter {
    private $db;
    private $mailer;
    
    public function __construct($db = null) {
        $this->db = $db;
        $this->mailer = $this->getMailer();
    }
    
    private function getMailer() {
        // Fetch SMTP settings from database
        $mailer = new PHPMailer(true);
        
        try {
            // Get SMTP settings from database
            $smtpSettings = null;
            if($this->db) {
                $stmt = $this->db->query("SELECT * FROM smtp_settings WHERE is_active=1 ORDER BY id DESC LIMIT 1");
                $smtpSettings = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // SMTP Settings - use database values or fall back to config
            $mailer->isSMTP();
            $mailer->Host = $smtpSettings['smtp_host'] ?? (defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com');
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpSettings['smtp_username'] ?? (defined('SMTP_USERNAME') ? SMTP_USERNAME : '');
            $mailer->Password = $smtpSettings['smtp_password'] ?? (defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '');
            
            // Handle encryption based on port and encryption setting
            $port = (int)($smtpSettings['smtp_port'] ?? (defined('SMTP_PORT') ? SMTP_PORT : 587));
            $encryption = $smtpSettings['smtp_encryption'] ?? (defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : null);
            
            if ($port == 465 || strtolower($encryption) == 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            } else {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
            }
            $mailer->Port = $port;
            
            // Additional settings
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Set default sender - use database or config
            $fromEmail = $smtpSettings['from_email'] ?? (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : (defined('CONTACT_EMAIL') ? CONTACT_EMAIL : ''));
            $fromName = $smtpSettings['from_name'] ?? (defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (defined('SITE_NAME') ? SITE_NAME : ''));
            
            $mailer->setFrom($fromEmail, $fromName);
            $mailer->addReplyTo($fromEmail, $fromName);
            $mailer->isHTML(true);
            $mailer->CharSet = 'UTF-8';
            
            // Add headers to improve deliverability and avoid spam
            $mailer->XMailer = ' '; // Remove X-Mailer header
            $mailer->addCustomHeader('X-Priority', '3');
            $mailer->addCustomHeader('X-MSMail-Priority', 'Normal');
            $mailer->addCustomHeader('Importance', 'Normal');
            $mailer->addCustomHeader('List-Unsubscribe', '<mailto:' . $fromEmail . '?subject=unsubscribe>');
            $mailer->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            $mailer->addCustomHeader('X-Mailer-Type', 'Newsletter');
            $mailer->addCustomHeader('Precedence', 'bulk');
            $mailer->addCustomHeader('MIME-Version', '1.0');
            $mailer->Priority = 3;
            
            // Disable SMTP Debug output
            $mailer->SMTPDebug = 0;
            
            // Enable DKIM if available (helps with spam filtering)
            // Note: DKIM should be configured at the domain level
            
        } catch (Exception $e) {
            error_log('Mailer initialization error: ' . $e->getMessage());
        }
        
        return $mailer;
    }
    
    private function sendEmail($to, $name, $subject, $body, $newsletter = 'main', $isWelcomeEmail = false) {
        try {
            // Clone the mailer to avoid configuration conflicts
            $mail = clone $this->mailer;
            
            $mail->addAddress($to, $name);
            $mail->Subject = $subject;
            
            // For welcome emails, replace email placeholder with actual email (URL encoded for links)
            if ($isWelcomeEmail) {
                $mail->Body = str_replace('{email}', urlencode($to), $body);
            } else {
                $mail->Body = $this->formatEmailBody($body, $newsletter);
            }
            
            if ($mail->send()) {
                return true;
            } else {
                error_log("Email send failed to: " . $to . " - " . $mail->ErrorInfo);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email send exception: " . $e->getMessage());
            return false;
        }
    }
    
    private function getWelcomeEmailTemplate($name, $siteName, $siteUrl) {
        $currentYear = date('Y');
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to ' . htmlspecialchars($siteName) . '</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f4f4; padding: 20px 0;">
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            
                            <!-- Header with Black Background -->
                            <tr>
                                <td style="background: #000000; padding: 40px 30px; text-align: center; border-bottom: 3px solid #000000;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700;">
                                        🎉 Welcome Aboard!
                                    </h1>
                                    <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">
                                        Thank you for subscribing to our newsletter
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Main Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <h2 style="color: #333333; margin: 0 0 20px 0; font-size: 24px;">
                                        Hi ' . htmlspecialchars($name) . '! 👋
                                    </h2>
                                    
                                    <p style="color: #555555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                        We\'re thrilled to have you join our community at <strong>' . htmlspecialchars($siteName) . '</strong>! 
                                        You\'ve successfully subscribed to our newsletter, and we can\'t wait to share valuable content with you.
                                    </p>
                                    
                                    <div style="background: #f5f5f5; border-left: 4px solid #000000; padding: 20px; margin: 30px 0; border-radius: 6px; border: 2px solid #000000;">
                                        <h3 style="color: #000000; margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">
                                            📬 What to Expect:
                                        </h3>
                                        <ul style="color: #333333; font-size: 15px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                            <li>Latest industry insights and trends</li>
                                            <li>Exclusive tips and best practices</li>
                                            <li>Special offers and promotions</li>
                                            <li>Updates on new products and services</li>
                                        </ul>
                                    </div>
                                    
                                    <p style="color: #555555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                        Stay tuned for our upcoming newsletters packed with valuable information tailored just for you!
                                    </p>
                                    
                                    <!-- CTA Button -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="' . htmlspecialchars($siteUrl) . '" style="display: inline-block; background: #000000; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 6px; font-size: 16px; font-weight: 600; border: 2px solid #000000;">
                                                    Visit Our Website
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
                                        Have questions? Feel free to reach out to us anytime!
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #000000; padding: 30px; text-align: center; border-top: 3px solid #000000;">
                                    <p style="color: #ffffff; font-size: 14px; margin: 0 0 10px 0; line-height: 1.5;">
                                        <strong>' . htmlspecialchars($siteName) . '</strong><br>
                                        Helping businesses grow through innovative solutions
                                    </p>
                                    
                                    <p style="color: #cccccc; font-size: 12px; margin: 15px 0 0 0; line-height: 1.5;">
                                        You received this email because you subscribed to our newsletter.<br>
                                        <a href="' . htmlspecialchars($siteUrl) . '/unsubscribe.php?email={email}" style="color: #ffffff; text-decoration: none; font-weight: 600;">Unsubscribe</a> | 
                                        <a href="' . htmlspecialchars($siteUrl) . '" style="color: #ffffff; text-decoration: none;">Visit Website</a>
                                    </p>
                                    
                                    <p style="color: #999999; font-size: 11px; margin: 15px 0 0 0;">
                                        &copy; ' . $currentYear . ' ' . htmlspecialchars($siteName) . '. All rights reserved.
                                    </p>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }
    
    private function getUnsubscribeEmailTemplate($name, $siteName, $siteUrl) {
        $currentYear = date('Y');
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Unsubscribed from ' . htmlspecialchars($siteName) . '</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f4f4; padding: 20px 0;">
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            
                            <!-- Header with Black Background -->
                            <tr>
                                <td style="background: #000000; padding: 40px 30px; text-align: center; border-bottom: 3px solid #000000;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700;">
                                        👋 We\'re Sorry to See You Go
                                    </h1>
                                    <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">
                                        You have been unsubscribed from our newsletter
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Main Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <h2 style="color: #333333; margin: 0 0 20px 0; font-size: 24px;">
                                        Hi ' . htmlspecialchars($name) . ',
                                    </h2>
                                    
                                    <p style="color: #555555; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                        We\'ve received your request to unsubscribe from <strong>' . htmlspecialchars($siteName) . '</strong> newsletter. 
                                        Your email address has been successfully removed from our mailing list.
                                    </p>
                                    
                                    <div style="background: #f5f5f5; border-left: 4px solid #000000; padding: 20px; margin: 30px 0; border-radius: 6px; border: 2px solid #000000;">
                                        <h3 style="color: #000000; margin: 0 0 15px 0; font-size: 18px; font-weight: 700;">
                                            ✅ Confirmed:
                                        </h3>
                                        <ul style="color: #333333; font-size: 15px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                            <li>You will no longer receive emails from us</li>
                                            <li>Your request has been processed immediately</li>
                                            <li>You can re-subscribe anytime if you change your mind</li>
                                        </ul>
                                    </div>
                                    
                                    <p style="color: #555555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                        If you unsubscribed by mistake or would like to give us another chance, you can easily 
                                        re-subscribe anytime by visiting our website.
                                    </p>
                                    
                                    <!-- CTA Buttons -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="' . htmlspecialchars($siteUrl) . '" style="display: inline-block; background: #000000; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 6px; font-size: 16px; font-weight: 600; border: 2px solid #000000; margin: 0 10px;">
                                                    Visit Website
                                                </a>
                                                <a href="' . htmlspecialchars($siteUrl) . '#newsletter" style="display: inline-block; background: #ffffff; color: #000000; text-decoration: none; padding: 15px 40px; border-radius: 6px; font-size: 16px; font-weight: 600; border: 2px solid #000000; margin: 0 10px;">
                                                    Re-subscribe
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 30px 0; text-align: center;">
                                        <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>We Value Your Feedback!</strong><br>
                                            Would you mind sharing why you\'re leaving? Your feedback helps us improve.<br>
                                            <a href="' . htmlspecialchars($siteUrl) . '/contact.php" style="color: #000000; font-weight: 600; text-decoration: none;">Share Your Thoughts →</a>
                                        </p>
                                    </div>
                                    
                                    <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0; text-align: center;">
                                        Thank you for being part of our community. We hope to see you again soon!
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #000000; padding: 30px; text-align: center; border-top: 3px solid #000000;">
                                    <p style="color: #ffffff; font-size: 14px; margin: 0 0 10px 0; line-height: 1.5;">
                                        <strong>' . htmlspecialchars($siteName) . '</strong><br>
                                        Helping businesses grow through innovative solutions
                                    </p>
                                    
                                    <p style="color: #cccccc; font-size: 12px; margin: 15px 0 0 0; line-height: 1.5;">
                                        This is a confirmation that you have unsubscribed from our newsletter.<br>
                                        <a href="' . htmlspecialchars($siteUrl) . '" style="color: #ffffff; text-decoration: none;">Visit Website</a>
                                    </p>
                                    
                                    <p style="color: #999999; font-size: 11px; margin: 15px 0 0 0;">
                                        &copy; ' . $currentYear . ' ' . htmlspecialchars($siteName) . '. All rights reserved.
                                    </p>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }
    
    private function formatEmailBody($body, $newsletter) {
        $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST'];
        $unsubscribeLink = $siteUrl . '/api/unsubscribe.php?email={email}&newsletter=' . urlencode($newsletter);
        
        $footer = "<br><br><hr><p style='color: #666; font-size: 12px;'>You received this email because you subscribed to {$newsletter}.<br>";
        $footer .= "<a href='{$unsubscribeLink}' style='color: #007bff;'>Unsubscribe</a></p>";
        
        return $body . $footer;
    }
    
    public function subscribe($email, $name = 'Anonymous', $newsletter = 'main') {
        try {
            error_log("Newsletter API call - Email: " . $email);
            
            // Get user's IP and location
            require_once __DIR__ . '/GeoLocation.php';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $geoData = GeoLocation::getLocation($ip);
            $location = $geoData['formatted'];
            
            error_log("Subscriber location: IP={$ip}, Location={$location}");
            
            // Check if email already exists in database
            $stmt = $this->db->prepare("SELECT id, status, name FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Email already exists
                if ($existing['status'] === 'subscribed') {
                    // Already actively subscribed
                    return [
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter. Thank you for your continued interest!'
                    ];
                } else {
                    // Previously unsubscribed, re-subscribe them
                    $stmt = $this->db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', name = ?, newsletter_name = ?, ip_address = ?, location = ?, updated_at = NOW() WHERE email = ?");
                    $stmt->execute([$name, $newsletter, $ip, $location, $email]);
                    
                    // Send welcome email for re-subscription
                    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST'];
                    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Our Website';
                    
                    $subject = "🎉 Welcome Back to " . $siteName . " Newsletter!";
                    $body = $this->getWelcomeEmailTemplate($name, $siteName, $siteUrl);
                    
                    // Send welcome email (pass true for isWelcomeEmail parameter)
                    @$this->sendEmail($email, $name, $subject, $body, $newsletter, true);
                    
                    return [
                        'success' => true,
                        'message' => 'Welcome back! You have been re-subscribed to our newsletter. Check your email for confirmation.'
                    ];
                }
            }
            
            // Subscribe to newsletter with location data
            $stmt = $this->db->prepare("
                INSERT INTO newsletter_subscribers 
                (email, name, status, newsletter_name, ip_address, location) 
                VALUES (?, ?, 'subscribed', ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $email,
                $name,
                $newsletter,
                $ip,
                $location
            ]);
            
            if ($result) {
                // Send welcome email
                $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST'];
                $siteName = defined('SITE_NAME') ? SITE_NAME : 'Our Website';
                
                $subject = "🎉 Welcome to " . $siteName . " Newsletter!";
                $body = $this->getWelcomeEmailTemplate($name, $siteName, $siteUrl);
                
                // Send welcome email (pass true for isWelcomeEmail parameter)
                @$this->sendEmail($email, $name, $subject, $body, $newsletter, true);
                
                return [
                    'success' => true,
                    'message' => 'Successfully subscribed! Check your email for confirmation.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to subscribe. Please try again.'
            ];
            
        } catch (Exception $e) {
            error_log("Newsletter subscribe error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }
    
    public function unsubscribe($email, $name = null) {
        try {
            // Check if email exists
            $stmt = $this->db->prepare("SELECT id, name, status FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscriber) {
                return [
                    'success' => false,
                    'message' => 'This email address is not subscribed to our newsletter.'
                ];
            }
            
            if ($subscriber['status'] === 'unsubscribed') {
                return [
                    'success' => false,
                    'message' => 'You have already unsubscribed from our newsletter.'
                ];
            }
            
            // Update status to unsubscribed
            $stmt = $this->db->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', updated_at = NOW() WHERE email = ?");
            $stmt->execute([$email]);
            
            // Send unsubscribe confirmation email
            $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://' . $_SERVER['HTTP_HOST'];
            $siteName = defined('SITE_NAME') ? SITE_NAME : 'Our Website';
            $subscriberName = $name ?? $subscriber['name'] ?? 'Subscriber';
            
            $subject = "Unsubscribed from " . $siteName . " Newsletter";
            $body = $this->getUnsubscribeEmailTemplate($subscriberName, $siteName, $siteUrl);
            
            // Send confirmation email
            @$this->sendEmail($email, $subscriberName, $subject, $body, 'unsubscribe', false);
            
            // Log unsubscribe
            error_log("Newsletter unsubscribe: " . $email);
            
            return [
                'success' => true,
                'message' => 'You have been successfully unsubscribed from our newsletter. A confirmation email has been sent to you.'
            ];
            
        } catch (Exception $e) {
            error_log("Newsletter unsubscribe error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.'
            ];
        }
    }
    
    /**
     * Send newsletter to a single subscriber
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @param string $preheader Optional preheader text
     * @return bool Success status
     */
    public function sendNewsletter($to, $subject, $message, $preheader = '') {
        try {
            // Clone the mailer to avoid configuration conflicts
            $mail = clone $this->mailer;
            
            $mail->addAddress($to);
            $mail->Subject = $subject;
            
            // Build complete HTML email with proper structure
            $htmlBody = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
            $htmlBody .= '<html xmlns="http://www.w3.org/1999/xhtml">';
            $htmlBody .= '<head>';
            $htmlBody .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
            $htmlBody .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
            $htmlBody .= '<title>' . htmlspecialchars($subject) . '</title>';
            $htmlBody .= '</head>';
            $htmlBody .= '<body style="margin:0;padding:0;font-family:Arial,sans-serif;">';
            
            // Add preheader if provided
            if (!empty($preheader)) {
                $htmlBody .= '<div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">';
                $htmlBody .= htmlspecialchars($preheader);
                $htmlBody .= '</div>';
            }
            
            $htmlBody .= $message;
            $htmlBody .= '</body></html>';
            
            $mail->Body = $htmlBody;
            
            // Add plain text alternative to reduce spam score
            $mail->AltBody = strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $message));
            
            // Set message ID to improve deliverability
            $mail->MessageID = '<' . md5(uniqid(time())) . '@' . $_SERVER['HTTP_HOST'] . '>';
            
            if ($mail->send()) {
                return true;
            } else {
                error_log("Newsletter send failed to: " . $to . " - " . $mail->ErrorInfo);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Newsletter send exception to " . $to . ": " . $e->getMessage());
            return false;
        }
    }
}
?>
