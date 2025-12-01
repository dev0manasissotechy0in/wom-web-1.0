<?php
require_once 'auth.php';
require_once '../db_config.php';

// Get counts
$testimonials_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM testimonials"))['count'];
$gallery_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM gallery"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <div class="dashboard">
            <h1>Admin Dashboard</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Testimonials</h3>
                    <p class="stat-number"><?php echo $testimonials_count; ?></p>
                    <a href="manage_testimonials.php" class="btn-secondary">Manage</a>
                </div>
                
                <div class="stat-card">
                    <h3>Gallery Items</h3>
                    <p class="stat-number"><?php echo $gallery_count; ?></p>
                    <a href="manage_gallery.php" class="btn-secondary">Manage</a>
                </div>
            </div>

            <?php
                // Add this with other counts
                $features_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM features WHERE is_active = 1"))['count'];
            ?>

            <!-- Add this stat card -->
            <div class="stat-card">
                <h3>Active Features</h3>
                <p class="stat-number"><?php echo $features_count; ?></p>
                <a href="manage_features.php" class="btn-secondary">Manage</a>
            </div>
            
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="manage_testimonials.php?action=add" class="btn-primary">Add Testimonial</a>
                    <a href="manage_gallery.php?action=add" class="btn-primary">Add Gallery Item</a>
                    <a href="../index.php" target="_blank" class="btn-secondary">View Website</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
