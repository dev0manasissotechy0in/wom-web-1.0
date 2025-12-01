<?php
// Start by checking authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'SMTP Settings';
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
            $checkStmt = $db->query("SELECT COUNT(*) as count FROM smtp_settings");
            $result = $checkStmt->fetch();
            
            if($result['count'] > 0) {
                // Update existing
                $stmt = $db->prepare("UPDATE smtp_settings SET 
                    smtp_host=?, smtp_port=?, smtp_username=?, smtp_password=?, 
                    smtp_encryption=?, from_email=?, from_name=?, is_active=?, 
                    updated_at=NOW() WHERE id=1");
            } else {
                // Insert new
                $stmt = $db->prepare("INSERT INTO smtp_settings 
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
            
            $success = 'SMTP settings saved successfully!';
            
        } catch(PDOException $e) {
            error_log("SMTP Settings Error: " . $e->getMessage());
            $error = 'Database error occurred. Please try again.';
        }
    }
}

// Fetch current settings
try {
    $stmt = $db->query("SELECT * FROM smtp_settings ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch();
} catch(PDOException $e) {
    $settings = null;
}
?>

<?php include 'includes/layout-start.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-envelope-open-text"></i> SMTP Settings</h1>
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

<div class="content-card">
    <div class="card-header">
        <h3>Email Server Configuration</h3>
        <p style="color: #666; font-size: 14px; margin: 10px 0 0 0;">Configure SMTP settings for sending newsletters and notifications</p>
    </div>
    
    <form method="POST" action="" style="padding: 20px;">
        <div class="form-group">
            <label for="smtp_host">SMTP Host <span style="color: red;">*</span></label>
            <input type="text" 
                   id="smtp_host" 
                   name="smtp_host" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"
                   placeholder="smtp.example.com"
                   required>
            <small>Example: smtp.gmail.com, smtp.hostinger.com</small>
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
            <small>Your email account password or app-specific password</small>
        </div>

        <div class="form-group">
            <label for="smtp_encryption">Encryption Type</label>
            <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
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
            <small>Email address that will appear in the "From" field</small>
        </div>

        <div class="form-group">
            <label for="from_name">From Name</label>
            <input type="text" 
                   id="from_name" 
                   name="from_name" 
                   class="form-control" 
                   value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>"
                   placeholder="Your Company Name">
            <small>Name that will appear in the "From" field</small>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" 
                       name="is_active" 
                       value="1" 
                       <?php echo ($settings['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <span>Enable SMTP (Activate email sending)</span>
            </label>
        </div>

        <div class="form-actions" style="border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="testing/test-smtp.php" class="btn-secondary" target="_blank">
                <i class="fas fa-paper-plane"></i> Test SMTP Connection
            </a>
        </div>
    </form>
</div>

<style>
.form-actions {
    display: flex;
    gap: 10px;
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
</style>

<?php include 'includes/layout-end.php'; ?>
