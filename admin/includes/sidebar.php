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

/* Dropdown Styles */
.dropdown-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 15px;
    cursor: pointer;
    user-select: none;
}

.dropdown-toggle:hover {
    background: #2a2a2a;
    color: white;
}

.dropdown-toggle.active {
    background: #000;
    color: white;
}

.dropdown-toggle .left-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dropdown-toggle .chevron {
    transition: transform 0.3s;
    font-size: 12px;
}

.dropdown-toggle.open .chevron {
    transform: rotate(180deg);
}

.submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: #0f0f0f;
}

.submenu.show {
    max-height: 500px;
}

.submenu li {
    border-bottom: 1px solid #1a1a1a;
}

.submenu a {
    padding: 12px 20px 12px 52px;
    font-size: 14px;
}

.submenu a:hover {
    background: #1a1a1a;
    padding-left: 57px;
}

.submenu a.active {
    background: #000;
    border-left: 4px solid #666;
    padding-left: 48px;
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
        
        <!-- Blogs Dropdown -->
        <li>
            <div class="dropdown-toggle <?php echo in_array($current_page, ['blogs', 'blog-add', 'blog-edit', 'tags', 'categories']) ? 'active' : ''; ?>" onclick="toggleDropdown(this)">
                <div class="left-section">
                    <i class="fas fa-blog"></i>
                    <span>Blogs</span>
                </div>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <ul class="submenu">
                <li>
                    <a href="blogs.php" class="<?php echo in_array($current_page, ['blogs', 'blog-add', 'blog-edit']) ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i>
                        <span>All Blogs</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="<?php echo $current_page == 'categories' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="tags.php" class="<?php echo $current_page == 'tags' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Tags</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Case Studies Dropdown -->
        <li>
            <div class="dropdown-toggle <?php echo in_array($current_page, ['case-studies', 'case-study-add', 'case-study-edit', 'case-study-categories', 'case-study-tags']) ? 'active' : ''; ?>" onclick="toggleDropdown(this)">
                <div class="left-section">
                    <i class="fas fa-briefcase"></i>
                    <span>Case Studies</span>
                </div>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <ul class="submenu">
                <li>
                    <a href="case-studies.php" class="<?php echo in_array($current_page, ['case-studies', 'case-study-add', 'case-study-edit']) ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i>
                        <span>All Case Studies</span>
                    </a>
                </li>
                <li>
                    <a href="case-study-categories.php" class="<?php echo $current_page == 'case-study-categories' ? 'active' : ''; ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="case-study-tags.php" class="<?php echo $current_page == 'case-study-tags' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Tags</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <li>
            <a href="services.php" class="<?php echo in_array($current_page, ['services', 'service-add', 'service-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                <span>Services</span>
            </a>
        </li>
        
        <li>
            <a href="products.php" class="<?php echo in_array($current_page, ['products', 'product-add', 'product-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
        </li>
        
        <li>
            <a href="resources.php" class="<?php echo in_array($current_page, ['resources', 'resource-add', 'resource-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-download"></i>
                <span>Resources</span>
            </a>
        </li>
        
        <li>
            <a href="pages.php" class="<?php echo in_array($current_page, ['pages', 'page-add', 'page-edit']) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Page Management</span>
            </a>
        </li>
        
        <li>
            <a href="inquiries.php" class="<?php echo $current_page == 'inquiries' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Contact Inquiries</span>
            </a>
        </li>
        
        <!-- Newsletter Dropdown -->
        <li>
            <div class="dropdown-toggle <?php echo in_array($current_page, ['subscribers', 'newsletter', 'newsletter-unsubscribes']) ? 'active' : ''; ?>" onclick="toggleDropdown(this)">
                <div class="left-section">
                    <i class="fas fa-paper-plane"></i>
                    <span>Newsletter</span>
                </div>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <ul class="submenu">
                <li>
                    <a href="subscribers.php" class="<?php echo $current_page == 'subscribers' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Subscribers</span>
                    </a>
                </li>
                <li>
                    <a href="newsletter.php" class="<?php echo $current_page == 'newsletter' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>Send Newsletter</span>
                    </a>
                </li>
                <li>
                    <a href="newsletter-unsubscribes.php" class="<?php echo $current_page == 'newsletter-unsubscribes' ? 'active' : ''; ?>">
                        <i class="fas fa-user-times"></i>
                        <span>Unsubscribes</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <li>
            <a href="analytics.php" class="<?php echo $current_page == 'analytics' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
        </li>
        
        <!-- Payments Dropdown -->
        <li>
            <div class="dropdown-toggle <?php echo in_array($current_page, ['payment-dashboard', 'payment-transactions', 'payment-view', 'payment-methods', 'payment-settings']) ? 'active' : ''; ?>" onclick="toggleDropdown(this)">
                <div class="left-section">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Payments</span>
                </div>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <ul class="submenu">
                <li>
                    <a href="payment-dashboard.php" class="<?php echo $current_page == 'payment-dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="payment-transactions.php" class="<?php echo in_array($current_page, ['payment-transactions', 'payment-view']) ? 'active' : ''; ?>">
                        <i class="fas fa-receipt"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="payment-methods.php" class="<?php echo $current_page == 'payment-methods' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Methods</span>
                    </a>
                </li>
                <li>
                    <a href="payment-settings.php" class="<?php echo $current_page == 'payment-settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Settings Dropdown -->
        <li>
            <div class="dropdown-toggle <?php echo in_array($current_page, ['smtp-settings', 'site-settings', 'admin-settings', 'sitemap-manager']) ? 'active' : ''; ?>" onclick="toggleDropdown(this)">
                <div class="left-section">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <ul class="submenu">
                <li>
                    <a href="site-settings.php" class="<?php echo $current_page == 'site-settings' ? 'active' : ''; ?>">
                        <i class="fas fa-globe"></i>
                        <span>Site Settings</span>
                    </a>
                </li>
                <li>
                    <a href="smtp-settings.php" class="<?php echo $current_page == 'smtp-settings' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>SMTP Settings</span>
                    </a>
                </li>
                <li>
                    <a href="sitemap-manager.php" class="<?php echo $current_page == 'sitemap-manager' ? 'active' : ''; ?>">
                        <i class="fas fa-sitemap"></i>
                        <span>Sitemap & SEO</span>
                    </a>
                </li>
                <li>
                    <a href="admin-settings.php" class="<?php echo $current_page == 'admin-settings' ? 'active' : ''; ?>">
                        <i class="fas fa-user-cog"></i>
                        <span>Admin Settings</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
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

<script>
function toggleDropdown(element) {
    const submenu = element.nextElementSibling;
    const isOpen = element.classList.contains('open');
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        if (toggle !== element) {
            toggle.classList.remove('open');
            const otherSubmenu = toggle.nextElementSibling;
            if (otherSubmenu && otherSubmenu.classList.contains('submenu')) {
                otherSubmenu.classList.remove('show');
            }
        }
    });
    
    // Toggle current dropdown
    if (isOpen) {
        element.classList.remove('open');
        submenu.classList.remove('show');
        localStorage.removeItem('activeDropdown');
    } else {
        element.classList.add('open');
        submenu.classList.add('show');
        // Save state to localStorage
        const dropdownText = element.querySelector('.left-section span').textContent;
        localStorage.setItem('activeDropdown', dropdownText);
    }
}

// Restore dropdown state on page load
document.addEventListener('DOMContentLoaded', function() {
    const activeDropdown = localStorage.getItem('activeDropdown');
    
    // Auto-open dropdown if any child page is active
    document.querySelectorAll('.dropdown-toggle.active').forEach(toggle => {
        toggle.classList.add('open');
        const submenu = toggle.nextElementSibling;
        if (submenu && submenu.classList.contains('submenu')) {
            submenu.classList.add('show');
        }
    });
    
    // Or restore from localStorage if no active child
    if (activeDropdown && !document.querySelector('.dropdown-toggle.active')) {
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            const dropdownText = toggle.querySelector('.left-section span').textContent;
            if (dropdownText === activeDropdown) {
                toggle.classList.add('open');
                const submenu = toggle.nextElementSibling;
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.classList.add('show');
                }
            }
        });
    }
});
</script>
