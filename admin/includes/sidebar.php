<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<style>
.admin-sidebar {
    width: 260px;
    background: #1a1a1a;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 25px 20px;
    background: #000;
    border-bottom: 1px solid #333;
}

.sidebar-header h2 {
    color: white;
    font-size: 1.5rem;
    margin: 0;
    font-weight: 700;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    border-bottom: 1px solid #2a2a2a;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 15px;
}

.sidebar-menu a:hover {
    background: #2a2a2a;
    color: white;
    padding-left: 25px;
}

.sidebar-menu a.active {
    background: #000;
    color: white;
    border-left: 4px solid white;
    padding-left: 16px;
}

.sidebar-menu i {
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid #333;
    margin-top: 20px;
}

.sidebar-footer .user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #ccc;
    font-size: 14px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

@media (max-width: 768px) {
    .admin-sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
}
</style>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li>
            <a href="blogs.php" class="<?php echo in_array($current_page, ['blogs', 'blog-add', 'blog-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-blog"></i>
                <span>Blogs</span>
            </a>
        </li>
        
        <li>
            <a href="case-studies.php" class="<?php echo in_array($current_page, ['case-studies', 'case-study-add', 'case-study-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i>
                <span>Case Studies</span>
            </a>
        </li>
        
        <li>
            <a href="services.php" class="<?php echo in_array($current_page, ['services', 'service-add', 'service-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                <span>Services</span>
            </a>
        </li>
        <li>
            <a href="products.php" class="<?php echo in_array($current_page, ['products', 'product-add', 'product-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                <span>Products</span>
            </a>
        </li>
        
        <li>
            <a href="inquiries.php" class="<?php echo $current_page == 'inquiries' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Contact Inquiries</span>
            </a>
        </li>
        
        <li>
            <a href="subscribers.php" class="<?php echo $current_page == 'subscribers' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Newsletter Subscribers</span>
            </a>
        </li>
        
        <li>
            <a href="analytics.php" class="<?php echo $current_page == 'analytics' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
        </li>
        
        <li>
            <a href="admin-settings.php" class="<?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
        <li>
    <a href="/admin/resources.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'resource') !== false ? 'active' : ''; ?>">
        <i class="fas fa-download"></i> Resources
    </a>
</li>

    </ul>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: white;">Admin</div>
                <div style="font-size: 12px;"><?php echo $_SESSION['admin_email'] ?? 'admin@example.com'; ?></div>
            </div>
        </div>
    </div>
</aside>
