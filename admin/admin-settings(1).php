<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';

// Get current admin data
$stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $username = sanitize($_POST['username']);
        
        // Check if email/username already exists for other users
        $check = $db->prepare("SELECT id FROM admin_users WHERE (email = ? OR username = ?) AND id != ?");
        $check->execute([$email, $username, $admin_id]);
        
        if($check->fetch()) {
            $error = 'Email or username already exists!';
        } else {
            $stmt = $db->prepare("UPDATE admin_users SET full_name = ?, email = ?, username = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $username, $admin_id]);
            
            $_SESSION['admin_name'] = $full_name;
            $_SESSION['admin_username'] = $username;
            
            $message = 'Profile updated successfully!';
            
            // Refresh admin data
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();
        }
    }
    
    if($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if(!password_verify($current_password, $admin['password'])) {
            $error = 'Current password is incorrect!';
        } elseif($new_password !== $confirm_password) {
            $error = 'New passwords do not match!';
        } elseif(strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long!';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $admin_id]);
            
            $message = 'Password changed successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Account Settings</h1>
            </div>
            
            <?php if(!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="settings-grid">
                <!-- Profile Information -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Profile Information</h3>
                    </div>
                    <form method="POST" style="padding: 20px;">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Username <span class="required">*</span></label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst($admin['role']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Last Login</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo $admin['last_login'] ? date('M d, Y h:i A', strtotime($admin['last_login'])) : 'Never'; ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                    </div>
                    <form method="POST" style="padding: 20px;">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Current Password <span class="required">*</span></label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password <span class="required">*</span></label>
                            <input type="password" name="new_password" class="form-control" 
                                   minlength="6" required>
                            <small>Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password <span class="required">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   minlength="6" required>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
                
                <!-- Account Info -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    </div>
                    <div style="padding: 20px;">
                        <div class="info-item">
                            <strong>Account Created:</strong>
                            <span><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Account Status:</strong>
                            <span class="badge badge-success"><?php echo ucfirst($admin['status']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>User ID:</strong>
                            <span><?php echo $admin['id']; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="content-card danger-card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                    </div>
                    <div style="padding: 20px;">
                        <p style="color: #666; margin-bottom: 15px;">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        <button class="btn-danger" onclick="alert('Please contact super admin to delete your account.')">
                            <i class="fas fa-trash"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .danger-card .card-header {
            background: #ffebee;
            color: #c62828;
        }
        
        .btn-danger {
            background: #d32f2f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
