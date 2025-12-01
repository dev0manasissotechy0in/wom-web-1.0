<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/smtp.php';

// Must be logged in as admin to test
if(!isset($_SESSION['admin_logged_in'])) {
    // For testing purposes, you can comment this out
    // die("Please login as admin first");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login SMTP Settings</title>
    <script>
    // Define function early to ensure it's available
    function sendTestEmail() {
        alert('Function called! JavaScript is working.');
        console.log('sendTestEmail() called');
        
        const email = document.getElementById('test-email');
        const btn = document.getElementById('send-btn');
        const alertContainer = document.getElementById('alert-container');
        
        if (!email || !email.value) {
            alert('Please enter an email address');
            return false;
        }
        
        alertContainer.innerHTML = '';
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch('test-login-smtp-backend.php', {
            method: 'POST',
            body: new URLSearchParams({
                'test_email': email.value
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            
            const alert = document.createElement('div');
            alert.className = `alert ${data.success ? 'alert-success' : 'alert-error'} show`;
            
            let message = data.message;
            if (data.debug) {
                message += '<br><small style="font-size:11px;">Debug: ' + JSON.stringify(data.debug) + '</small>';
            }
            
            alert.innerHTML = `<i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            alertContainer.appendChild(alert);
            
            if (data.success) {
                email.value = '';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alert = document.createElement('div');
            alert.className = 'alert alert-error show';
            alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> Network error: ${error.message}`;
            alertContainer.appendChild(alert);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test OTP';
        });
        
        return false; // Prevent any default action
    }
    
    // Test that function is defined
    console.log('sendTestEmail function defined:', typeof sendTestEmail);
    </script>
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
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .header h1 {
        font-size: 28px;
        margin-bottom: 10px;
    }

    .content {
        padding: 30px;
    }

    .section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .section h2 {
        margin-bottom: 15px;
        color: #333;
        font-size: 20px;
    }

    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #666;
        min-width: 200px;
    }

    .info-value {
        color: #333;
        flex: 1;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .test-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 2px solid #667eea;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn {
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: none;
    }

    .alert.show {
        display: block;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }

    .code {
        background: #f4f4f4;
        padding: 3px 8px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }

    .no-settings {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .no-settings i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 20px;
    }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope-open-text"></i> Login SMTP Settings Test</h1>
            <p>Check configuration and send test OTP email</p>
        </div>

        <div class="content">
            <div id="alert-container"></div>

            <?php
            // Check if table exists
            try {
                $tableCheck = $db->query("SHOW TABLES LIKE 'login_smtp_settings'")->fetch();
                
                if (!$tableCheck) {
                    echo '<div class="no-settings">';
                    echo '<i class="fas fa-database"></i>';
                    echo '<h2>Table Not Found</h2>';
                    echo '<p>The <code class="code">login_smtp_settings</code> table does not exist.</p>';
                    echo '<p style="margin-top: 20px;">Run the migration file: <code class="code">database/migrations/add-login-smtp-table.sql</code></p>';
                    echo '</div>';
                } else {
                    // Get settings
                    $stmt = $db->query("SELECT * FROM login_smtp_settings ORDER BY id DESC LIMIT 1");
                    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($settings) {
                        ?>
            <div class="section">
                <h2><i class="fas fa-cog"></i> Current SMTP Configuration</h2>

                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span
                            class="status-badge <?php echo $settings['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $settings['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">SMTP Host:</div>
                    <div class="info-value"><code
                            class="code"><?php echo htmlspecialchars($settings['smtp_host']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">SMTP Port:</div>
                    <div class="info-value"><code
                            class="code"><?php echo htmlspecialchars($settings['smtp_port']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Encryption:</div>
                    <div class="info-value"><code
                            class="code"><?php echo strtoupper($settings['smtp_encryption']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">SMTP Username:</div>
                    <div class="info-value"><code
                            class="code"><?php echo htmlspecialchars($settings['smtp_username']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">SMTP Password:</div>
                    <div class="info-value"><code
                            class="code"><?php echo str_repeat('•', strlen($settings['smtp_password'])); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">From Email:</div>
                    <div class="info-value"><code
                            class="code"><?php echo htmlspecialchars($settings['from_email']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">From Name:</div>
                    <div class="info-value"><code
                            class="code"><?php echo htmlspecialchars($settings['from_name']); ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Last Updated:</div>
                    <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($settings['updated_at'])); ?>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-paper-plane"></i> Send Test OTP Email</h2>

                <div class="test-form">
                    <form id="test-form" onsubmit="return false;">
                        <div class="form-group">
                            <label for="test-email">
                                <i class="fas fa-envelope"></i> Test Email Address
                            </label>
                            <input type="email" id="test-email" name="test_email"
                                placeholder="Enter email to receive test OTP" required>
                        </div>

                        <button type="button" class="btn" id="send-btn" onclick="sendTestEmail()">
                            <i class="fas fa-paper-plane"></i> Send Test OTP
                        </button>
                    </form>
                </div>
            </div>
            <?php
                    } else {
                        echo '<div class="no-settings">';
                        echo '<i class="fas fa-inbox"></i>';
                        echo '<h2>No Settings Configured</h2>';
                        echo '<p>Configure Login SMTP settings in: <code class="code">admin/login_smtp.php</code></p>';
                        echo '</div>';
                    }
                }
            } catch (PDOException $e) {
                echo '<div class="no-settings">';
                echo '<i class="fas fa-exclamation-triangle"></i>';
                echo '<h2>Database Error</h2>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>

</html>