<?php
session_start();

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
    <title>Forgot Password - Admin Panel</title>
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
        
        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #000;
        }
        
        .reset-header p {
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
        
        .otp-input {
            width: 100%;
            padding: 15px !important;
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
        
        .password-input {
            padding-right: 45px !important;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }
        
        .toggle-password:hover {
            color: #000;
        }
        
        .error-message, .success-message, .info-message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
        }
        
        .info-message {
            background: #e7f3ff;
            color: #0066cc;
        }
        
        .btn-submit {
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
        
        .btn-submit:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn-submit:disabled {
            background: #999;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
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
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 13px;
        }
        
        .password-requirements ul {
            margin: 10px 0 0 20px;
            color: #666;
        }
        
        .password-requirements li {
            margin: 5px 0;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }
        
        .step.active {
            background: #000;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1><i class="fas fa-key"></i> Reset Password</h1>
            <p id="header-subtitle">Enter your email to receive a password reset OTP</p>
        </div>
        
        <div class="step-indicator">
            <div class="step active" id="step-1">1</div>
            <div class="step" id="step-2">2</div>
            <div class="step" id="step-3">3</div>
        </div>
        
        <div id="message-container"></div>
        
        <!-- Step 1: Enter Email -->
        <form id="email-form">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" placeholder="Enter your admin email" required autofocus>
                </div>
            </div>
            
            <button type="submit" class="btn-submit" id="email-btn">
                <i class="fas fa-paper-plane"></i> Send Reset OTP
            </button>
        </form>
        
        <!-- Step 2: Verify OTP -->
        <form id="otp-form" class="hidden">
            <div class="form-group">
                <label>Enter OTP</label>
                <input type="text" id="otp" class="otp-input" maxlength="6" placeholder="000000" required>
                <div class="otp-timer" id="otp-timer"></div>
            </div>
            
            <button type="submit" class="btn-submit" id="otp-btn">
                <i class="fas fa-check-circle"></i> Verify OTP
            </button>
            
            <div class="resend-otp">
                <button type="button" id="resend-otp-btn">Resend OTP</button>
            </div>
        </form>
        
        <!-- Step 3: Set New Password -->
        <form id="password-form" class="hidden">
            <div class="form-group">
                <label>New Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="new-password" class="password-input" placeholder="Enter new password" required>
                    <i class="fas fa-eye toggle-password" id="toggle-new"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm-password" class="password-input" placeholder="Confirm new password" required>
                    <i class="fas fa-eye toggle-password" id="toggle-confirm"></i>
                </div>
            </div>
            
            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>At least one uppercase letter</li>
                    <li>At least one lowercase letter</li>
                    <li>At least one number</li>
                </ul>
            </div>
            
            <button type="submit" class="btn-submit" id="password-btn">
                <i class="fas fa-check"></i> Reset Password
            </button>
        </form>
        
        <div class="back-to-login">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
    
    <script>
        let otpExpiryTime = null;
        let timerInterval = null;
        let userEmail = '';
        
        // Step 1: Send OTP
        document.getElementById('email-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            userEmail = document.getElementById('email').value;
            const btn = document.getElementById('email-btn');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            clearMessages();
            
            try {
                const response = await fetch('forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(userEmail)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    showStep(2);
                    startOtpTimer();
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Reset OTP';
            }
        });
        
        // Step 2: Verify OTP
        document.getElementById('otp-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const otp = document.getElementById('otp').value;
            const btn = document.getElementById('otp-btn');
            
            if (otp.length !== 6) {
                showMessage('Please enter a 6-digit OTP', 'error');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            clearMessages();
            
            try {
                const response = await fetch('verify-reset-otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `otp=${encodeURIComponent(otp)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('OTP verified! Set your new password.', 'success');
                    showStep(3);
                    clearInterval(timerInterval);
                } else {
                    showMessage(data.message, 'error');
                    document.getElementById('otp').value = '';
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';
            }
        });
        
        // Step 3: Set New Password
        document.getElementById('password-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const btn = document.getElementById('password-btn');
            
            if (newPassword !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
            clearMessages();
            
            try {
                const response = await fetch('reset-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `new_password=${encodeURIComponent(newPassword)}&confirm_password=${encodeURIComponent(confirmPassword)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message + ' Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Reset Password';
            }
        });
        
        // Resend OTP
        document.getElementById('resend-otp-btn').addEventListener('click', async () => {
            const btn = document.getElementById('resend-otp-btn');
            btn.disabled = true;
            btn.textContent = 'Sending...';
            clearMessages();
            
            try {
                const response = await fetch('forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(userEmail)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('New OTP sent to your email', 'success');
                    startOtpTimer();
                    document.getElementById('otp').value = '';
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                setTimeout(() => {
                    btn.disabled = false;
                    btn.textContent = 'Resend OTP';
                }, 3000);
            }
        });
        
        // Toggle password visibility
        document.getElementById('toggle-new').addEventListener('click', function() {
            togglePassword('new-password', this);
        });
        
        document.getElementById('toggle-confirm').addEventListener('click', function() {
            togglePassword('confirm-password', this);
        });
        
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Show specific step
        function showStep(step) {
            // Hide all forms
            document.getElementById('email-form').classList.add('hidden');
            document.getElementById('otp-form').classList.add('hidden');
            document.getElementById('password-form').classList.add('hidden');
            
            // Update step indicators
            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById(`step-${i}`);
                stepEl.classList.remove('active', 'completed');
                if (i < step) stepEl.classList.add('completed');
                if (i === step) stepEl.classList.add('active');
            }
            
            // Show current form
            if (step === 1) {
                document.getElementById('email-form').classList.remove('hidden');
                document.getElementById('header-subtitle').textContent = 'Enter your email to receive a password reset OTP';
            } else if (step === 2) {
                document.getElementById('otp-form').classList.remove('hidden');
                document.getElementById('header-subtitle').textContent = 'Enter the OTP sent to your email';
                document.getElementById('otp').focus();
            } else if (step === 3) {
                document.getElementById('password-form').classList.remove('hidden');
                document.getElementById('header-subtitle').textContent = 'Create a new secure password';
                document.getElementById('new-password').focus();
            }
        }
        
        // Start OTP timer (10 minutes)
        function startOtpTimer() {
            clearInterval(timerInterval);
            otpExpiryTime = Date.now() + (10 * 60 * 1000);
            
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
            messageDiv.className = type === 'success' ? 'success-message' : (type === 'info' ? 'info-message' : 'error-message');
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-circle')}"></i> ${message}`;
            container.innerHTML = '';
            container.appendChild(messageDiv);
        }
        
        // Clear messages
        function clearMessages() {
            document.getElementById('message-container').innerHTML = '';
        }
        
        // Auto-uppercase OTP
        document.getElementById('otp').addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
