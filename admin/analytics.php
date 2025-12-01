<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Analytics';

// Get analytics data
try {
    $total_visitors = $db->query("SELECT COUNT(*) as count FROM user_tracking")->fetch()['count'];
    $total_views = $db->query("SELECT SUM(views) as count FROM blogs")->fetch()['count'] ?? 0;
    $total_inquiries = $db->query("SELECT COUNT(*) as count FROM contact_inquiries")->fetch()['count'];
    $total_subscribers = $db->query("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'subscribed'")->fetch()['count'];
    
    // Get recent inquiries
    $recent_inquiries = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT 10")->fetchAll();
    
    // Get top blogs
    $top_blogs = $db->query("SELECT id, title, views FROM blogs ORDER BY views DESC LIMIT 5")->fetchAll();
    
    // ===== USER TRACKING ANALYTICS =====
    
    // Peak visit hours (most common visit time)
    $peak_hours = $db->query("
        SELECT HOUR(created_at) as hour, COUNT(*) as visits
        FROM user_tracking
        WHERE created_at IS NOT NULL
        GROUP BY HOUR(created_at)
        ORDER BY visits DESC
        LIMIT 5
    ")->fetchAll();
    
    // Most visited pages
    $most_visited_pages = $db->query("
        SELECT page_url, COUNT(*) as visits
        FROM user_tracking
        WHERE page_url IS NOT NULL AND page_url != ''
        GROUP BY page_url
        ORDER BY visits DESC
        LIMIT 5
    ")->fetchAll();
    
    // Least visited pages
    $least_visited_pages = $db->query("
        SELECT page_url, COUNT(*) as visits
        FROM user_tracking
        WHERE page_url IS NOT NULL AND page_url != ''
        GROUP BY page_url
        ORDER BY visits ASC
        LIMIT 5
    ")->fetchAll();
    
    // Device types
    $device_types = $db->query("
        SELECT device_type, COUNT(*) as count
        FROM user_tracking
        WHERE device_type IS NOT NULL AND device_type != ''
        GROUP BY device_type
        ORDER BY count DESC
    ")->fetchAll();
    
    // Browser types
    $browser_types = $db->query("
        SELECT browser, COUNT(*) as count
        FROM user_tracking
        WHERE browser IS NOT NULL AND browser != ''
        GROUP BY browser
        ORDER BY count DESC
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Analytics error: " . $e->getMessage());
    $total_visitors = 0;
    $total_views = 0;
    $total_inquiries = 0;
    $total_subscribers = 0;
    $recent_inquiries = [];
    $top_blogs = [];
    $peak_hours = [];
    $most_visited_pages = [];
    $least_visited_pages = [];
    $device_types = [];
    $browser_types = [];
}
?>
<?php include 'includes/layout-start.php'; ?>

    <div class="page-header">
        <h1>Analytics Dashboard</h1>
        <p>Comprehensive website traffic and user behavior analysis</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #000;">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_visitors); ?></h3>
                <p>Total Visitors</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #333;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_views); ?></h3>
                <p>Blog Views</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #666;">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_inquiries); ?></h3>
                <p>Contact Inquiries</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #999;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_subscribers); ?></h3>
                <p>Subscribers</p>
            </div>
        </div>
    </div>
    
    <!-- User Tracking Analytics -->
    <div style="margin-top: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 20px; color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">
            <i class="fas fa-chart-bar"></i> User Behavior Analytics
        </h2>
    </div>
    
    <div class="content-grid">
        <!-- Peak Visit Hours -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Peak Visit Times (by Hour)</h3>
            </div>
            <div class="table-responsive">
                <?php if (!empty($peak_hours)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hour of Day</th>
                                <th>Visits</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_peak = array_sum(array_column($peak_hours, 'visits'));
                            foreach ($peak_hours as $hour): 
                            ?>
                            <tr>
                                <td><?php echo str_pad($hour['hour'], 2, '0', STR_PAD_LEFT); ?>:00 - <?php echo str_pad($hour['hour'], 2, '0', STR_PAD_LEFT); ?>:59</td>
                                <td><strong><?php echo $hour['visits']; ?></strong></td>
                                <td>
                                    <div style="background: #f0f0f0; border-radius: 4px; padding: 4px 8px; font-size: 12px;">
                                        <?php echo round(($hour['visits'] / $total_peak) * 100, 1); ?>%
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No visitor data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Most Visited Pages -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-up"></i> Most Visited Pages</h3>
            </div>
            <div class="table-responsive">
                <?php if (!empty($most_visited_pages)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Page URL</th>
                                <th>Visits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($most_visited_pages as $page): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo htmlspecialchars($page['page_url']); ?>" target="_blank" style="color: #0066FF; text-decoration: none;">
                                        <?php echo htmlspecialchars(substr($page['page_url'], 0, 60)); ?>
                                    </a>
                                </td>
                                <td><strong><?php echo $page['visits']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No page data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Least Visited Pages -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-down"></i> Least Visited Pages</h3>
            </div>
            <div class="table-responsive">
                <?php if (!empty($least_visited_pages)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Page URL</th>
                                <th>Visits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($least_visited_pages as $page): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo htmlspecialchars($page['page_url']); ?>" target="_blank" style="color: #0066FF; text-decoration: none;">
                                        <?php echo htmlspecialchars(substr($page['page_url'], 0, 60)); ?>
                                    </a>
                                </td>
                                <td><strong><?php echo $page['visits']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No page data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Device Types -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-mobile-alt"></i> Device Types</h3>
            </div>
            <div class="table-responsive">
                <?php if (!empty($device_types)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_devices = array_sum(array_column($device_types, 'count'));
                            foreach ($device_types as $device): 
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                    $icon = 'desktop';
                                    if (stripos($device['device_type'], 'mobile') !== false) $icon = 'mobile-alt';
                                    if (stripos($device['device_type'], 'tablet') !== false) $icon = 'tablet-alt';
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                    <?php echo htmlspecialchars($device['device_type']); ?>
                                </td>
                                <td><strong><?php echo $device['count']; ?></strong></td>
                                <td>
                                    <div style="background: #f0f0f0; border-radius: 4px; padding: 4px 8px; font-size: 12px;">
                                        <?php echo round(($device['count'] / $total_devices) * 100, 1); ?>%
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No device data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Browser Types -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-browser"></i> Browser Types</h3>
            </div>
            <div class="table-responsive">
                <?php if (!empty($browser_types)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Browser</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_browsers = array_sum(array_column($browser_types, 'count'));
                            foreach ($browser_types as $browser): 
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                    $icon = 'globe';
                                    if (stripos($browser['browser'], 'chrome') !== false) $icon = 'chrome';
                                    if (stripos($browser['browser'], 'firefox') !== false) $icon = 'firefox';
                                    if (stripos($browser['browser'], 'safari') !== false) $icon = 'safari';
                                    if (stripos($browser['browser'], 'edge') !== false) $icon = 'edge';
                                    ?>
                                    <i class="fab fa-<?php echo $icon; ?>"></i>
                                    <?php echo htmlspecialchars($browser['browser']); ?>
                                </td>
                                <td><strong><?php echo $browser['count']; ?></strong></td>
                                <td>
                                    <div style="background: #f0f0f0; border-radius: 4px; padding: 4px 8px; font-size: 12px;">
                                        <?php echo round(($browser['count'] / $total_browsers) * 100, 1); ?>%
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No browser data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Top Blog Posts -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-blog"></i> Top Blog Posts</h3>
                <a href="blogs.php" class="btn-small">View All</a>
            </div>
            <div class="table-responsive">
                <?php if (!empty($top_blogs)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Blog Title</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_blogs as $blog): ?>
                            <tr>
                                <td>
                                    <a href="/blog-detailed.php?id=<?php echo $blog['id']; ?>" target="_blank">
                                        <?php echo htmlspecialchars(substr($blog['title'], 0, 50)); ?>
                                    </a>
                                </td>
                                <td><?php echo $blog['views']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No blog data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Inquiries -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-envelope"></i> Recent Inquiries</h3>
                <a href="inquiries.php" class="btn-small">View All</a>
            </div>
            <div class="table-responsive">
                <?php if (!empty($recent_inquiries)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_inquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #999;">No inquiries available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include 'includes/layout-end.php'; ?>

