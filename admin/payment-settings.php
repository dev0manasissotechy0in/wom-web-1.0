<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/login.php");
    exit();
}

$page_title = 'Payment Settings';
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        $db->beginTransaction();
        
        // Update each setting
        $settings_to_update = [
            'booking_price',
            'booking_currency',
            'currency_symbol',
            'resource_default_price',
            'enable_paid_resources',
            'calendly_link',
            'booking_confirmation_email',
            'booking_email_subject',
            'razorpay_enabled',
            'paypal_enabled',
            'tax_rate'
        ];
        
        foreach ($settings_to_update as $key) {
            if (isset($_POST[$key])) {
                $value = trim($_POST[$key]);
                $stmt = $db->prepare("UPDATE payment_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            }
        }
        
        $db->commit();
        $success_message = "Settings updated successfully!";
        
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Payment settings update error: " . $e->getMessage());
        $error_message = "Failed to update settings. Please try again.";
    }
}

// Fetch current settings
try {
    $stmt = $db->query("SELECT * FROM payment_settings ORDER BY setting_key");
    $settings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array for easy access
    $settings = [];
    foreach ($settings_raw as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
} catch (PDOException $e) {
    error_log("Payment settings fetch error: " . $e->getMessage());
    $settings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin-left: 260px;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-back {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .settings-section {
            margin-bottom: 40px;
        }
        
        .settings-section h2 {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-group .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #333;
        }
        
        .info-card {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-card i {
            color: #0066cc;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-dollar-sign"></i> <?php echo $page_title; ?></h1>
                <a href="/admin/dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-card">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> These settings control pricing and payment options for both Consultation Bookings and Paid Resources throughout your website.
        </div>
        
        <form method="POST">
            <!-- Booking Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-calendar-check"></i> Consultation Booking Settings</h2>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="booking_price">Booking Price *</label>
                        <input type="number" id="booking_price" name="booking_price" 
                               value="<?php echo htmlspecialchars($settings['booking_price'] ?? '999'); ?>" 
                               required min="0" step="1">
                        <div class="help-text">Price in INR for consultation bookings</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_currency">Currency</label>
                        <select id="booking_currency" name="booking_currency">
                            <option value="INR" <?php echo ($settings['booking_currency'] ?? 'INR') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                            <option value="USD" <?php echo ($settings['booking_currency'] ?? 'INR') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="EUR" <?php echo ($settings['booking_currency'] ?? 'INR') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="currency_symbol">Currency Symbol</label>
                        <input type="text" id="currency_symbol" name="currency_symbol" 
                               value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '₹'); ?>" 
                               maxlength="5">
                        <div class="help-text">Symbol to display (₹, $, €, etc.)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tax_rate">Tax Rate (%)</label>
                        <input type="number" id="tax_rate" name="tax_rate" 
                               value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>" 
                               min="0" max="100" step="0.01">
                        <div class="help-text">Tax percentage to apply on transactions</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="calendly_link">Calendly Scheduling Link</label>
                    <input type="text" id="calendly_link" name="calendly_link" 
                           value="<?php echo htmlspecialchars($settings['calendly_link'] ?? ''); ?>" 
                           placeholder="https://calendly.com/your-username">
                    <div class="help-text">Your Calendly link for appointment scheduling</div>
                </div>
                
                <div class="form-group">
                    <label for="booking_email_subject">Booking Confirmation Email Subject</label>
                    <input type="text" id="booking_email_subject" name="booking_email_subject" 
                           value="<?php echo htmlspecialchars($settings['booking_email_subject'] ?? 'Your Consultation Booking Confirmation'); ?>">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="booking_confirmation_email" name="booking_confirmation_email" 
                               value="1" <?php echo ($settings['booking_confirmation_email'] ?? '1') == '1' ? 'checked' : ''; ?>>
                        <label for="booking_confirmation_email">Send confirmation email after successful booking</label>
                    </div>
                </div>
            </div>
            
            <!-- Resource Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-file-download"></i> Paid Resources Settings</h2>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="resource_default_price">Default Resource Price</label>
                        <input type="number" id="resource_default_price" name="resource_default_price" 
                               value="<?php echo htmlspecialchars($settings['resource_default_price'] ?? '499'); ?>" 
                               min="0" step="1">
                        <div class="help-text">Default price for paid resources (in INR)</div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="enable_paid_resources" name="enable_paid_resources" 
                                   value="1" <?php echo ($settings['enable_paid_resources'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label for="enable_paid_resources">Enable Paid Resources Feature</label>
                        </div>
                        <div class="help-text">Allow resources to have a price and require payment</div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Gateway Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-credit-card"></i> Payment Gateway Settings</h2>
                
                <div class="settings-grid">
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="razorpay_enabled" name="razorpay_enabled" 
                                   value="1" <?php echo ($settings['razorpay_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label for="razorpay_enabled">Enable Razorpay</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="paypal_enabled" name="paypal_enabled" 
                                   value="1" <?php echo ($settings['paypal_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label for="paypal_enabled">Enable PayPal</label>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <i class="fas fa-info-circle"></i>
                    Configure your API keys in <code>config/config.php</code>: RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET, PAYPAL_EMAIL
                </div>
            </div>
            
            <button type="submit" name="update_settings" class="btn-submit">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
        </div>
    </div>
</body>
</html>
