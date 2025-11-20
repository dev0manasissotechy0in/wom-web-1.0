<?php
// classes/Newsletter.php
class Newsletter {
    private $db;
    private $mailer;
    
    public function __construct($db = null) {
        $this->db = $db ?: Database::getConnection();
        $this->mailer = $this->getMailer();
    }
    
    private function getMailer() {
        // Use your existing SMTP settings from config.php
        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // SMTP Settings - use values from config.php
            $mailer->isSMTP();
            $mailer->Host = SMTP_HOST ?? 'smtp.gmail.com';
            $mailer->SMTPAuth = true;
            $mailer->Username = SMTP_USERNAME ?? '';
            $mailer->Password = SMTP_PASSWORD ?? '';
            $mailer->SMTPSecure = SMTP_ENCRYPTION ?? 'tls';
            $mailer->Port = SMTP_PORT ?? 587;
            
            // Additional settings
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Set default sender - use from config.php
            $mailer->setFrom(MAIL_FROM_ADDRESS ?? CONTACT_EMAIL, MAIL_FROM_NAME ?? SITE_NAME);
            $mailer->isHTML(true);
            $mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log('Mailer initialization error: ' . $e->getMessage());
        }
        
        return $mailer;
    }
    
    private function sendEmail($to, $name, $subject, $body, $newsletter = 'main') {
        try {
            // Clone the mailer to avoid configuration conflicts
            $mail = clone $this->mailer;
            
            $mail->addAddress($to, $name);
            $mail->Subject = $subject;
            $mail->Body = $this->formatEmailBody($body, $newsletter);
            
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
    
    private function formatEmailBody($body, $newsletter) {
        $unsubscribeLink = SITE_URL . '/api/unsubscribe.php?email={email}&newsletter=' . urlencode($newsletter);
        
        $footer = "<br><br><hr><p style='color: #666; font-size: 12px;'>You received this email because you subscribed to {$newsletter}.<br>";
        $footer .= "<a href='{$unsubscribeLink}' style='color: #007bff;'>Unsubscribe</a></p>";
        
        return $body . $footer;
    }
    
    // Other methods remain the same...
    
    private function getMailer() {
        // Initialize your mailer (SMTP, PHPMailer, etc.)
        // This should be your existing SMTP setup
        return $mailer;
    }
}
?>
