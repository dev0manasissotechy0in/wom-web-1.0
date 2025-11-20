<?php
require_once '../config/config.php';

// Check if logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM blogs WHERE status = 'published'");
$total_blogs = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'subscribed'");
$total_subscribers = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM contact_inquiries WHERE status = 'new'");
$new_inquiries = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM user_tracking");
$total_visitors = $stmt->fetch()['total'];

// Recent blogs
$recent_blogs = $db->query("SELECT * FROM blogs ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent inquiries
$recent_inquiries = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Digital Marketing Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!--Summernote CDN Links-->
    
    <!-- jQuery (required for Summernote) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap CSS (optional but recommended) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #000;">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_blogs; ?></h3>
                        <p>Total Blogs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #333;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_subscribers; ?></h3>
                        <p>Subscribers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #666;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $new_inquiries; ?></h3>
                        <p>New Inquiries</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #999;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_visitors; ?></h3>
                        <p>Total Visitors</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="content-grid">
                <!-- Recent Blogs -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Blogs</h3>
                        <a href="blogs.php" class="btn-small">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_blogs as $blog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($blog['title'], 0, 50)); ?></td>
                                    <td><span class="badge <?php echo $blog['status'] == 'published' ? 'badge-success' : 'badge-warning'; ?>"><?php echo $blog['status']; ?></span></td>
                                    <td><?php echo $blog['views']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Inquiries -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Inquiries</h3>
                        <a href="inquiries.php" class="btn-small">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo $inquiry['status']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
