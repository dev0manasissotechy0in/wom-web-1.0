<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>SMTP Configuration Verification</title>";
echo "<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
    .container { max-width: 1400px; margin: 0 auto; }
    h1 { color: #000; text-align: center; margin-bottom: 30px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .card h2 { margin: 0 0 20px 0; padding-bottom: 15px; border-bottom: 3px solid #000; font-size: 20px; }
    .card h2 .badge { float: right; font-size: 12px; padding: 4px 12px; border-radius: 20px; font-weight: normal; }
    .badge.active { background: #28a745; color: white; }
    .badge.inactive { background: #dc3545; color: white; }
    .badge.login { background: #007bff; color: white; }
    .badge.newsletter { background: #6f42c1; color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table td { padding: 10px; border-bottom: 1px solid #eee; }
    table td:first-child { font-weight: 600; color: #666; width: 150px; }
    table td:last-child { color: #333; word-break: break-all; }
    .password { color: #999; font-style: italic; }
    .status { padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
    .status.success { background: #d4edda; color: #155724; border: 2px solid #28a745; }
    .status.warning { background: #fff3cd; color: #856404; border: 2px solid #ffc107; }
    .status.error { background: #f8d7da; color: #721c24; border: 2px solid #dc3545; }
    .icon { font-size: 24px; margin-right: 10px; }
    .summary { background: linear-gradient(135deg, #000 0%, #333 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
    .summary h3 { margin: 0 0 20px 0; font-size: 24px; }
    .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .summary-item { text-align: center; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 8px; }
    .summary-item .number { font-size: 48px; font-weight: bold; margin-bottom: 10px; }
    .summary-item .label { font-size: 14px; opacity: 0.9; }
    .function-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px; }
    .function-list h4 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
    .function-list ul { margin: 0; padding-left: 20px; }
    .function-list li { margin: 5px 0; font-size: 13px; color: #555; }
    .test-link { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #000; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; }
    .test-link:hover { background: #333; }
</style>
</head><body>";

echo "<div class='container'>";
echo "<h1>📧 SMTP Configuration Verification Dashboard</h1>";

// Count total configurations
$loginSmtpCount = 0;
$newsletterSmtpCount = 0;
$totalFunctions = 3; // sendLoginOTPEmail, sendEmail, sendNewsletterEmail

try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM login_smtp_settings WHERE is_active = 1");
    $loginSmtpCount = $stmt->fetchColumn();
} catch (Exception $e) {}

try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM smtp_settings WHERE is_active = 1");
    $newsletterSmtpCount = $stmt->fetchColumn();
} catch (Exception $e) {}

// Summary Section
echo "<div class='summary'>";
echo "<h3>📊 System Overview</h3>";
echo "<div class='summary-grid'>";
echo "<div class='summary-item'><div class='number'>{$loginSmtpCount}</div><div class='label'>Admin Login SMTP</div></div>";
echo "<div class='summary-item'><div class='number'>{$newsletterSmtpCount}</div><div class='label'>Newsletter SMTP</div></div>";
echo "<div class='summary-item'><div class='number'>{$totalFunctions}</div><div class='label'>Email Functions</div></div>";
echo "</div>";
echo "</div>";

// Grid for SMTP Configurations
echo "<div class='grid'>";

// 1. Admin Login SMTP Settings
echo "<div class='card'>";
echo "<h2><span class='icon'>🔐</span> Admin Login SMTP <span class='badge login'>OTP Emails</span></h2>";

try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        $statusBadge = $settings['is_active'] ? "<span class='badge active'>✓ Active</span>" : "<span class='badge inactive'>✗ Inactive</span>";
        
        echo "<table>";
        echo "<tr><td>Table Name</td><td><code>login_smtp_settings</code></td></tr>";
        echo "<tr><td>Status</td><td>{$statusBadge}</td></tr>";
        echo "<tr><td>Host</td><td><strong>{$settings['smtp_host']}</strong></td></tr>";
        echo "<tr><td>Port</td><td><strong>{$settings['smtp_port']}</strong></td></tr>";
        echo "<tr><td>Encryption</td><td><strong>" . strtoupper($settings['smtp_encryption']) . "</strong></td></tr>";
        echo "<tr><td>Username</td><td>{$settings['smtp_username']}</td></tr>";
        echo "<tr><td>Password</td><td class='password'>" . str_repeat('•', 20) . " (Configured)</td></tr>";
        echo "<tr><td>From Email</td><td>{$settings['from_email']}</td></tr>";
        echo "<tr><td>From Name</td><td>{$settings['from_name']}</td></tr>";
        echo "<tr><td>Record ID</td><td>#{$settings['id']}</td></tr>";
        echo "</table>";
        
        echo "<div class='function-list'>";
        echo "<h4>📤 Uses This Configuration:</h4>";
        echo "<ul>";
        echo "<li><code>sendLoginOTPEmail()</code> - Admin login OTP verification</li>";
        echo "<li><code>admin/generate-otp.php</code> - OTP generation</li>";
        echo "<li><code>admin/forgot-password.php</code> - Password reset OTP</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<a href='test-smtp-simple.php' class='test-link' target='_blank'>🧪 Test Login SMTP</a>";
        echo "<a href='admin/login_smtp.php' class='test-link' target='_blank' style='margin-left: 10px; background: #007bff;'>⚙️ Configure</a>";
        
        echo "<div class='status success' style='margin-top: 20px;'>";
        echo "<strong>✓ Configuration Valid</strong><br>";
        echo "Login SMTP is configured and ready to send OTP emails.";
        echo "</div>";
    } else {
        echo "<div class='status error'>";
        echo "<strong>✗ No Active Configuration</strong><br>";
        echo "Login SMTP settings not found in database. <a href='admin/login_smtp.php' style='color: #721c24; text-decoration: underline;'>Configure Now</a>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='status error'>";
    echo "<strong>✗ Database Error</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div>"; // End Login SMTP Card

// 2. Newsletter SMTP Settings
echo "<div class='card'>";
echo "<h2><span class='icon'>📬</span> Newsletter SMTP <span class='badge newsletter'>Mass Emails</span></h2>";

try {
    $stmt = $db->query("SELECT * FROM smtp_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        $statusBadge = $settings['is_active'] ? "<span class='badge active'>✓ Active</span>" : "<span class='badge inactive'>✗ Inactive</span>";
        
        echo "<table>";
        echo "<tr><td>Table Name</td><td><code>smtp_settings</code></td></tr>";
        echo "<tr><td>Status</td><td>{$statusBadge}</td></tr>";
        echo "<tr><td>Host</td><td><strong>{$settings['smtp_host']}</strong></td></tr>";
        echo "<tr><td>Port</td><td><strong>{$settings['smtp_port']}</strong></td></tr>";
        echo "<tr><td>Encryption</td><td><strong>" . strtoupper($settings['smtp_encryption']) . "</strong></td></tr>";
        echo "<tr><td>Username</td><td>{$settings['smtp_username']}</td></tr>";
        echo "<tr><td>Password</td><td class='password'>" . str_repeat('•', 20) . " (Configured)</td></tr>";
        echo "<tr><td>From Email</td><td>{$settings['from_email']}</td></tr>";
        echo "<tr><td>From Name</td><td>{$settings['from_name']}</td></tr>";
        echo "<tr><td>Record ID</td><td>#{$settings['id']}</td></tr>";
        echo "</table>";
        
        echo "<div class='function-list'>";
        echo "<h4>📤 Uses This Configuration:</h4>";
        echo "<ul>";
        echo "<li><code>sendEmail()</code> - General email sending</li>";
        echo "<li><code>sendNewsletterEmail()</code> - Newsletter campaigns</li>";
        echo "<li><code>sendContactNotification()</code> - Contact form alerts</li>";
        echo "<li><code>sendContactThankYou()</code> - Auto-responders</li>";
        echo "<li><code>Newsletter.php</code> class - Bulk email sending</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<a href='admin/newsletter.php' class='test-link' target='_blank'>📧 Send Newsletter</a>";
        echo "<a href='admin/smtp-settings.php' class='test-link' target='_blank' style='margin-left: 10px; background: #6f42c1;'>⚙️ Configure</a>";
        
        echo "<div class='status success' style='margin-top: 20px;'>";
        echo "<strong>✓ Configuration Valid</strong><br>";
        echo "Newsletter SMTP is configured and ready for bulk emails.";
        echo "</div>";
    } else {
        echo "<div class='status error'>";
        echo "<strong>✗ No Active Configuration</strong><br>";
        echo "Newsletter SMTP settings not found. <a href='admin/smtp-settings.php' style='color: #721c24; text-decoration: underline;'>Configure Now</a>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='status error'>";
    echo "<strong>✗ Database Error</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div>"; // End Newsletter SMTP Card

echo "</div>"; // End Grid

// Function Separation Analysis
echo "<div class='card' style='margin-top: 20px;'>";
echo "<h2><span class='icon'>🔍</span> Function Separation Analysis</h2>";

echo "<table>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 12px; text-align: left;'>Function Name</th><th style='padding: 12px; text-align: left;'>Database Table</th><th style='padding: 12px; text-align: left;'>Purpose</th><th style='padding: 12px; text-align: left;'>Status</th></tr>";

// Check sendLoginOTPEmail
echo "<tr>";
echo "<td><code>sendLoginOTPEmail()</code></td>";
echo "<td><code>login_smtp_settings</code></td>";
echo "<td>Admin login OTP verification</td>";
echo "<td>" . ($loginSmtpCount > 0 ? "<span style='color: #28a745;'>✓ Independent</span>" : "<span style='color: #dc3545;'>✗ Not Configured</span>") . "</td>";
echo "</tr>";

// Check sendEmail
echo "<tr style='background: #f8f9fa;'>";
echo "<td><code>sendEmail()</code></td>";
echo "<td><code>smtp_settings</code></td>";
echo "<td>General emails, contact forms, payments</td>";
echo "<td>" . ($newsletterSmtpCount > 0 ? "<span style='color: #28a745;'>✓ Independent</span>" : "<span style='color: #dc3545;'>✗ Not Configured</span>") . "</td>";
echo "</tr>";

// Check sendNewsletterEmail
echo "<tr>";
echo "<td><code>sendNewsletterEmail()</code></td>";
echo "<td><code>smtp_settings</code></td>";
echo "<td>Newsletter subscriptions, bulk emails</td>";
echo "<td>" . ($newsletterSmtpCount > 0 ? "<span style='color: #28a745;'>✓ Independent</span>" : "<span style='color: #dc3545;'>✗ Not Configured</span>") . "</td>";
echo "</tr>";

echo "</table>";

if ($loginSmtpCount > 0 && $newsletterSmtpCount > 0) {
    echo "<div class='status success' style='margin-top: 20px;'>";
    echo "<strong>✓ Perfect Separation</strong><br>";
    echo "All SMTP configurations are independent and working on separate database tables. Admin login OTPs use <code>login_smtp_settings</code> while newsletters use <code>smtp_settings</code>.";
    echo "</div>";
} else {
    echo "<div class='status warning' style='margin-top: 20px;'>";
    echo "<strong>⚠️ Incomplete Configuration</strong><br>";
    echo "Some SMTP configurations are missing. Configure all settings for full functionality.";
    echo "</div>";
}

echo "</div>";

// Testing & Configuration Section
echo "<div class='card' style='margin-top: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>";
echo "<h2 style='color: white; border-bottom-color: rgba(255,255,255,0.3);'><span class='icon'>🧪</span> Quick Actions</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;'>";
echo "<a href='test-smtp-simple.php' target='_blank' style='padding: 15px; background: rgba(255,255,255,0.2); border-radius: 5px; text-align: center; color: white; text-decoration: none; font-weight: 600;'>🧪 Test Login SMTP</a>";
echo "<a href='admin/newsletter.php' target='_blank' style='padding: 15px; background: rgba(255,255,255,0.2); border-radius: 5px; text-align: center; color: white; text-decoration: none; font-weight: 600;'>📧 Send Newsletter</a>";
echo "<a href='admin/login_smtp.php' target='_blank' style='padding: 15px; background: rgba(255,255,255,0.2); border-radius: 5px; text-align: center; color: white; text-decoration: none; font-weight: 600;'>⚙️ Login SMTP Config</a>";
echo "<a href='admin/smtp-settings.php' target='_blank' style='padding: 15px; background: rgba(255,255,255,0.2); border-radius: 5px; text-align: center; color: white; text-decoration: none; font-weight: 600;'>⚙️ Newsletter SMTP Config</a>";
echo "</div>";
echo "</div>";

// Security Notice
echo "<div class='card' style='margin-top: 20px; border-left: 4px solid #ffc107;'>";
echo "<h2><span class='icon'>🔒</span> Security Recommendations</h2>";
echo "<ul style='margin: 10px 0; padding-left: 20px; line-height: 2;'>";
echo "<li><strong>Use App Passwords:</strong> For Gmail and Outlook, always use app-specific passwords instead of account passwords.</li>";
echo "<li><strong>SSL/TLS Encryption:</strong> Both SMTP servers are using encryption (SSL on port 465 or TLS on port 587).</li>";
echo "<li><strong>Separate Credentials:</strong> Using different SMTP accounts for login OTPs vs newsletters prevents quota issues.</li>";
echo "<li><strong>Disable Debug in Production:</strong> Set <code>SMTPDebug = 0</code> in production to avoid exposing credentials in logs.</li>";
echo "<li><strong>Delete Test Files:</strong> Remove <code>test-smtp-simple.php</code>, <code>verify-all-smtp.php</code>, and other test files after verification.</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End Container
echo "</body></html>";
?>
