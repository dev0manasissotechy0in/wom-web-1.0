<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check for remember me token
if(!isset($_SESSION['admin_logged_in']) && isset($_COOKIE['admin_remember'])) {
    $token = $_COOKIE['admin_remember'];
    $token_hash = hash('sha256', $token);
    
    try {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE remember_token = ? AND remember_token_expires > NOW() AND status = 'active' LIMIT 1");
        $stmt->execute([$token_hash]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($admin) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['keep_signed_in'] = true;
            
            header('Location: dashboard.php');
            exit();
        }
    } catch(PDOException $e) {
        error_log("Remember token error: " . $e->getMessage());
    }
}

// If already logged in, redirect to dashboard
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Digital Marketing Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #000;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
            outline: none;
        }
        
        .form-group input:focus {
            border-color: #000;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .remember-me:hover {
            background: #f5f5f5;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #000;
        }
        
        .remember-me label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
            color: #333;
        }
        
        .forgot-password {
            color: #000;
            text-decoration: none;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-site a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-site a:hover {
            text-decoration: underline;
        }
        
        .otp-form {
            display: none;
        }
        
        .otp-input {
            width: 100%;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: #000;
            outline: none;
        }
        
        .otp-timer {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            color: #666;
        }
        
        .otp-timer.expired {
            color: #c33;
        }
        
        .resend-otp {
            text-align: center;
            margin: 15px 0;
        }
        
        .resend-otp button {
            background: none;
            border: none;
            color: #000;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
        }
        
        .resend-otp button:hover {
            color: #333;
        }
        
        .resend-otp button:disabled {
            color: #999;
            cursor: not-allowed;
            text-decoration: none;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-login:disabled {
            background: #999;
            cursor: not-allowed;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-lock"></i> Admin Login</h1>
            <p id="login-subtitle">Enter your credentials to access the admin panel</p>
        </div>
        
        <div id="message-container"></div>
        
        <!-- Step 1: Email/Password Form -->
        <form id="credentials-form">
            <div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-key"></i>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            
            <div style="text-align: right; margin-bottom: 20px;">
                <a href="forgot-password-page.php" class="forgot-password" style="font-size: 14px; color: #000; text-decoration: none;">
                    <i class="fas fa-question-circle"></i> Forgot Password?
                </a>
            </div>
            
            <button type="submit" class="btn-login" id="send-otp-btn">
                <i class="fas fa-paper-plane"></i> Send OTP
            </button>
        </form>
        
        <!-- Step 2: OTP Verification Form -->
        <form id="otp-form" class="otp-form">
            <div class="form-group">
                <label>Enter OTP</label>
                <input type="text" id="otp" class="otp-input" maxlength="6" placeholder="000000" required>
                <div class="otp-timer" id="otp-timer"></div>
            </div>
            
            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" id="keep-signed-in">
                    <span><i class="fas fa-clock"></i> Keep me signed in (30 days)</span>
                </label>
            </div>
            
            <button type="submit" class="btn-login" id="verify-otp-btn">
                <i class="fas fa-check-circle"></i> Verify & Login
            </button>
            
            <div class="resend-otp">
                <button type="button" id="resend-otp-btn">Resend OTP</button>
            </div>
            
            <div style="text-align: center; margin-top: 15px;">
                <button type="button" id="back-to-credentials" style="background: none; border: none; color: #666; cursor: pointer; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i> Back to login
                </button>
            </div>
        </form>
        
        <div class="back-to-site">
            <a href="/"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
    </div>
    
    <script>
        let otpExpiryTime = null;
        let timerInterval = null;
        
        // Handle credentials form submission
        document.getElementById('credentials-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('send-otp-btn');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';
            clearMessages();
            
            try {
                const response = await fetch('generate-otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('OTP sent to your email. Please check your inbox.', 'success');
                    showOtpForm();
                    startOtpTimer();
                } else {
                    showMessage(data.message || 'Invalid credentials. Please try again.', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
            }
        });
        
        // Handle OTP form submission
        document.getElementById('otp-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const otp = document.getElementById('otp').value;
            const keepSignedIn = document.getElementById('keep-signed-in').checked;
            const submitBtn = document.getElementById('verify-otp-btn');
            
            if (otp.length !== 6) {
                showMessage('Please enter a 6-character OTP.', 'error');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            clearMessages();
            
            try {
                const response = await fetch('verify-otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `otp=${encodeURIComponent(otp)}&keep_signed_in=${keepSignedIn ? '1' : '0'}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'dashboard.php';
                    }, 1000);
                } else {
                    showMessage(data.message || 'Invalid or expired OTP.', 'error');
                    document.getElementById('otp').value = '';
                    document.getElementById('otp').focus();
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify & Login';
            }
        });
        
        // Handle resend OTP
        document.getElementById('resend-otp-btn').addEventListener('click', async () => {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const resendBtn = document.getElementById('resend-otp-btn');
            
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';
            clearMessages();
            
            try {
                const response = await fetch('generate-otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('New OTP sent to your email.', 'success');
                    startOtpTimer();
                    document.getElementById('otp').value = '';
                    document.getElementById('otp').focus();
                } else {
                    showMessage(data.message || 'Failed to resend OTP.', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                setTimeout(() => {
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend OTP';
                }, 3000);
            }
        });
        
        // Handle back to credentials
        document.getElementById('back-to-credentials').addEventListener('click', () => {
            showCredentialsForm();
        });
        
        // Show OTP form
        function showOtpForm() {
            document.getElementById('credentials-form').style.display = 'none';
            document.getElementById('otp-form').style.display = 'block';
            document.getElementById('login-subtitle').textContent = 'Enter the OTP sent to your email';
            document.getElementById('otp').focus();
        }
        
        // Show credentials form
        function showCredentialsForm() {
            document.getElementById('credentials-form').style.display = 'block';
            document.getElementById('otp-form').style.display = 'none';
            document.getElementById('login-subtitle').textContent = 'Enter your credentials to access the admin panel';
            document.getElementById('otp').value = '';
            clearInterval(timerInterval);
            clearMessages();
        }
        
        // Start OTP timer (5 minutes)
        function startOtpTimer() {
            clearInterval(timerInterval);
            otpExpiryTime = Date.now() + (5 * 60 * 1000); // 5 minutes
            
            timerInterval = setInterval(() => {
                const remaining = otpExpiryTime - Date.now();
                
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById('otp-timer').innerHTML = '<span class="expired">OTP expired. Please resend.</span>';
                    return;
                }
                
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                document.getElementById('otp-timer').textContent = `OTP expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }
        
        // Show message
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            container.innerHTML = '';
            container.appendChild(messageDiv);
        }
        
        // Clear messages
        function clearMessages() {
            document.getElementById('message-container').innerHTML = '';
        }
        
        // Auto-uppercase OTP input
        document.getElementById('otp').addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
