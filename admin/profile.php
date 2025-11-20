<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>My Profile</h1>
                <a href="admin-settings.php" class="btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
            
            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="https://via.placeholder.com/150/000000/FFFFFF?text=<?php echo substr($admin['full_name'], 0, 1); ?>" 
                             alt="Profile" class="profile-avatar">
                        <h2><?php echo htmlspecialchars($admin['full_name']); ?></h2>
                        <p class="profile-role"><?php echo ucfirst($admin['role']); ?></p>
                        <span class="profile-badge badge-success"><?php echo ucfirst($admin['status']); ?></span>
                    </div>
                    
                    <div class="profile-details">
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <strong>Username</strong>
                                <p><?php echo htmlspecialchars($admin['username']); ?></p>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <p><?php echo htmlspecialchars($admin['email']); ?></p>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Last Login</strong>
                                <p><?php echo $admin['last_login'] ? date('M d, Y h:i A', strtotime($admin['last_login'])) : 'Never'; ?></p>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <strong>Member Since</strong>
                                <p><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            margin-bottom: 20px;
        }
        
        .profile-header h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .profile-role {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
        }
        
        .profile-details {
            padding: 30px;
        }
        
        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-item i {
            font-size: 24px;
            color: #000;
            width: 30px;
        }
        
        .detail-item strong {
            display: block;
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .detail-item p {
            margin: 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</body>
</html>
