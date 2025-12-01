<?php
// Start by checking authentication
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Admin Login SMTP Settings';
$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = (int)($_POST['smtp_port'] ?? 587);
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $smtp_password = trim($_POST['smtp_password'] ?? '');
    $smtp_encryption = $_POST['smtp_encryption'] ?? 'tls';
    $from_email = trim($_POST['from_email'] ?? '');
    $from_name = trim($_POST['from_name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if(empty($smtp_host) || empty($smtp_username) || empty($from_email)) {
        $error = 'Please fill in all required fields.';
    } elseif(!filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if settings exist
            $checkStmt = $db->query("SELECT COUNT(*) as count FROM login_smtp_settings");
            $result = $checkStmt->fetch();
            
            if($result['count'] > 0) {
                // Update existing
                $stmt = $db->prepare("UPDATE login_smtp_settings SET 
                    smtp_host=?, smtp_port=?, smtp_username=?, smtp_password=?, 
                    smtp_encryption=?, from_email=?, from_name=?, is_active=?, 
                    updated_at=NOW() WHERE id=1");
            } else {
                // Insert new
                $stmt = $db->prepare("INSERT INTO login_smtp_settings 
                    (smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, from_email, from_name, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            }
            
            $stmt->execute([
                $smtp_host,
                $smtp_port,
                $smtp_username,
                $smtp_password,
                $smtp_encryption,
                $from_email,
                $from_name,
                $is_active
            ]);
            
            $success = 'Admin Login SMTP settings saved successfully!';
            
        } catch(PDOException $e) {
            error_log("Login SMTP Settings Error: " . $e->getMessage());
            $error = 'Database error occurred. Please try again.';
        }
    }
}

// Fetch current settings
try {
    $stmt = $db->query("SELECT * FROM login_smtp_settings ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch();
} catch(PDOException $e) {
    $settings = null;
}
?>

<?php include 'includes/layout-start.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-shield-alt"></i> Admin Login SMTP Settings</h1>
    <a href="settings.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Settings</a>
</div>

<?php if($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="alert" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px;">
    <h4 style="margin: 0 0 10px 0; color: #856404;"><i class="fas fa-info-circle"></i> Dedicated OTP Email Server</h4>
    <p style="margin: 0; color: #856404; line-height: 1.6;">
        This SMTP configuration is <strong>exclusively for admin login OTP emails</strong>. 
        It operates independently from your main newsletter SMTP settings, ensuring reliable authentication even if the main email system has issues.
    </p>
</div>

<div class="content-card">
    <div class="card-header">
        <h3>OTP Email Server Configuration</h3>
        <p style="color: #666; font-size: 14px; margin: 10px 0 0 0;">Configure SMTP settings for sending admin login OTP verification emails</p>
    </div>
    
    <!-- Quick Setup Presets -->
    <div style="padding: 20px; border-bottom: 1px solid #e0e0e0; background: #f8f9fa;">
        <h4 style="margin: 0 0 15px 0; font-size: 16px;"><i class="fas fa-bolt"></i> Quick Setup (Recommended Providers)</h4>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="preset-btn" onclick="applyPreset('outlook')">
                <i class="fas fa-envelope"></i> Outlook/Office 365
            </button>
            <button type="button" class="preset-btn" onclick="applyPreset('gmail')">
                <i class="fab fa-google"></i> Gmail
            </button>
            <button type="button" class="preset-btn" onclick="applyPreset('hostinger')">
                <i class="fas fa-server"></i> Hostinger
            </button>
            <button type="button" class="preset-btn" onclick="applyPreset('sendgrid')">
                <i class="fas fa-paper-plane"></i> SendGrid
            </button>
            <button type="button" class="preset-btn" onclick="applyPreset('mailgun')">
                <i class="fas fa-rocket"></i> Mailgun
            </button>
        </div>
        <small style="color: #666; display: block; margin-top: 10px;">
            <i class="fas fa-lightbulb"></i> Click a preset to auto-fill common settings. You'll still need to enter your credentials.
        </small>
    </div>
    
    <form method="POST" action="" style="padding: 20px;" id="loginSmtpForm">
        <div class="form-group">
            <label for="smtp_host">SMTP Host <span style="color: red;">*</span></label>
            <input type="text" 
                   id="smtp_host" 
                   name="smtp_host" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"
                   placeholder="smtp.example.com"
                   required>
            <small>Examples: smtp-mail.outlook.com, smtp.gmail.com, smtp.hostinger.com</small>
        </div>

        <div class="form-group">
            <label for="smtp_port">SMTP Port <span style="color: red;">*</span></label>
            <input type="number" 
                   id="smtp_port" 
                   name="smtp_port" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>"
                   placeholder="587"
                   required>
            <small>Common ports: 587 (TLS), 465 (SSL), 25 (Unencrypted)</small>
        </div>

        <div class="form-group">
            <label for="smtp_username">SMTP Username <span style="color: red;">*</span></label>
            <input type="text" 
                   id="smtp_username" 
                   name="smtp_username" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"
                   placeholder="your-email@example.com"
                   required>
            <small>Usually your email address</small>
        </div>

        <div class="form-group">
            <label for="smtp_password">SMTP Password <span style="color: red;">*</span></label>
            <input type="password" 
                   id="smtp_password" 
                   name="smtp_password" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>"
                   placeholder="Enter SMTP password">
            <div style="margin-top: 8px; padding: 10px; background: #fff3cd; border-radius: 5px; font-size: 13px;">
                <strong style="color: #856404;"><i class="fas fa-key"></i> Important - App Password Required:</strong>
                <ul style="margin: 8px 0 0 20px; color: #856404;">
                    <li><strong>Outlook/Office 365:</strong> Generate at <a href="https://account.microsoft.com/security" target="_blank" style="color: #0066cc;">account.microsoft.com/security</a> → Advanced security options → App passwords</li>
                    <li><strong>Gmail:</strong> Enable 2FA first, then generate at <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #0066cc;">myaccount.google.com/apppasswords</a></li>
                    <li><strong>Other providers:</strong> Use regular email password or API key</li>
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label for="smtp_encryption">Encryption Type</label>
            <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
            </select>
            <small>Recommended: TLS for port 587, SSL for port 465</small>
        </div>

        <div class="form-group">
            <label for="from_email">From Email Address <span style="color: red;">*</span></label>
            <input type="email" 
                   id="from_email" 
                   name="from_email" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>"
                   placeholder="noreply@example.com"
                   required>
            <small>Email address for OTP emails (usually same as username)</small>
        </div>

        <div class="form-group">
            <label for="from_name">From Name</label>
            <input type="text" 
                   id="from_name" 
                   name="from_name" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>"
                   placeholder="Your Company Name - Admin">
            <small>Name displayed in OTP emails (e.g., "Your Company - Admin Login")</small>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" 
                       name="is_active" 
                       value="1" 
                       <?php echo ($settings['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <span>Enable Login SMTP (Activate OTP email sending)</span>
            </label>
        </div>

        <div class="form-actions" style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="testing/test-login-smtp.php" class="btn-secondary" target="_blank">
                <i class="fas fa-paper-plane"></i> Test OTP Email
            </a>
            <a href="smtp-settings.php" class="btn-info">
                <i class="fas fa-envelope"></i> Newsletter SMTP Settings
            </a>
        </div>
    </form>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #000;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 13px;
}

.form-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-primary {
    padding: 12px 24px;
    background: #000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #333;
}

.btn-secondary {
    padding: 12px 24px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-info {
    padding: 12px 24px;
    background: #17a2b8;
    color: white;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-info:hover {
    background: #138496;
}

.preset-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid #000;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.preset-btn:hover {
    background: #000;
    color: white;
}

.preset-btn i {
    font-size: 16px;
}
</style>

<script>
// SMTP Provider Presets
const smtpPresets = {
    outlook: {
        name: 'Outlook/Office 365',
        host: 'smtp-mail.outlook.com',
        port: 587,
        encryption: 'tls',
        note: 'Use your Outlook email as username. For accounts with 2FA, generate an App Password at: https://account.microsoft.com/security'
    },
    gmail: {
        name: 'Gmail',
        host: 'smtp.gmail.com',
        port: 587,
        encryption: 'tls',
        note: 'Use your Gmail address as username. You MUST use an App Password (not your regular password). Enable 2FA first, then generate at: https://myaccount.google.com/apppasswords'
    },
    hostinger: {
        name: 'Hostinger',
        host: 'smtp.hostinger.com',
        port: 465,
        encryption: 'ssl',
        note: 'Use your full email address as username and your email password.'
    },
    sendgrid: {
        name: 'SendGrid',
        host: 'smtp.sendgrid.net',
        port: 587,
        encryption: 'tls',
        note: 'Username is always "apikey" (literal string). Use your SendGrid API key as the password.'
    },
    mailgun: {
        name: 'Mailgun',
        host: 'smtp.mailgun.org',
        port: 587,
        encryption: 'tls',
        note: 'Use your Mailgun SMTP credentials from your domain settings. Format: postmaster@your-domain.mailgun.org'
    }
};

function applyPreset(provider) {
    const preset = smtpPresets[provider];
    if (!preset) return;
    
    // Apply preset values
    document.getElementById('smtp_host').value = preset.host;
    document.getElementById('smtp_port').value = preset.port;
    document.getElementById('smtp_encryption').value = preset.encryption;
    
    // Show helpful information
    alert('✅ ' + preset.name + ' settings applied!\n\n' + 
          '📋 Next steps:\n' +
          '1. Enter your email address in Username\n' +
          '2. Enter your password (or App Password)\n' +
          '3. Set the From Email and From Name\n\n' +
          '💡 Note: ' + preset.note);
    
    // Focus on username field
    document.getElementById('smtp_username').focus();
}

// Form validation
document.getElementById('loginSmtpForm').addEventListener('submit', function(e) {
    const host = document.getElementById('smtp_host').value.trim();
    const username = document.getElementById('smtp_username').value.trim();
    const password = document.getElementById('smtp_password').value.trim();
    const fromEmail = document.getElementById('from_email').value.trim();
    
    if (!host || !username || !password || !fromEmail) {
        e.preventDefault();
        alert('❌ Please fill in all required fields (marked with *)');
        return false;
    }
    
    // Warn about common mistakes
    if (host.includes('gmail') && !password.includes(' ') && password.length < 16) {
        if (!confirm('⚠️ Gmail requires App Passwords (16 characters).\n\nAre you sure you\'re using an App Password and not your regular password?')) {
            e.preventDefault();
            return false;
        }
    }
    
    if (host.includes('outlook') && username.includes('@') && !username.includes('@outlook.com') && !username.includes('@hotmail.com') && !username.includes('@live.com')) {
        if (!confirm('⚠️ You\'re using Outlook SMTP with a non-Microsoft email address.\n\nThis may not work. Continue anyway?')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>

<?php include 'includes/layout-end.php'; ?>
