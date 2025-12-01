<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/smtp.php';

echo "<h2>Testing SMTP Connection for Outlook</h2>";
echo "<hr>";

echo "<h3>Configuration:</h3>";
echo "Host: " . SMTP_HOST . "<br>";
echo "Port: " . SMTP_PORT . "<br>";
echo "Secure: " . SMTP_SECURE . "<br>";
echo "Username: " . SMTP_USERNAME . "<br>";
echo "Password: " . (SMTP_PASSWORD ? '***hidden***' : 'NOT SET') . "<br>";
echo "<hr>";

echo "<h3>Testing Email Send:</h3>";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS for port 587
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME); // Send to same email for testing
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Email';
    $mail->Body    = '<h1>Test Email</h1><p>If you received this, SMTP is working correctly!</p>';
    $mail->AltBody = 'Test Email - If you received this, SMTP is working correctly!';
    
    // Send email
    $mail->send();
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>✓ SUCCESS!</strong> Email sent successfully. Check your inbox at " . SMTP_USERNAME;
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>✗ FAILED!</strong><br>";
    echo "Error: {$mail->ErrorInfo}<br>";
    echo "Exception: " . $e->getMessage();
    echo "</div>";
}
?>
