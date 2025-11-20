<div class="topbar">
    <div class="topbar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h2><?php echo ucfirst(str_replace('-', ' ', basename($_SERVER['PHP_SELF'], '.php'))); ?></h2>
    </div>
    
    <div class="topbar-right">
        <div class="topbar-item">
            <a href="/" target="_blank" title="View Website">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </div>
        
        <div class="topbar-item">
            <i class="far fa-bell"></i>
            <span class="badge">3</span>
        </div>
        
        <div class="topbar-item user-menu">
            <img src="https://via.placeholder.com/40/000000/FFFFFF?text=<?php echo substr($_SESSION['admin_name'], 0, 1); ?>" 
                 alt="Admin" class="user-avatar">
            <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <i class="fas fa-chevron-down"></i>
            
            <!-- Dropdown Menu -->
            <div class="user-dropdown">
                <div class="dropdown-header">
                    <img src="https://via.placeholder.com/60/000000/FFFFFF?text=<?php echo substr($_SESSION['admin_name'], 0, 1); ?>" 
                         alt="Admin" class="dropdown-avatar">
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
                        <small><?php echo htmlspecialchars($_SESSION['admin_role']); ?></small>
                    </div>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="admin-settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <!--<a href="settings.php" class="dropdown-item">-->
                <!--    <i class="fas fa-sliders-h"></i> Site Settings-->
                <!--</a>-->
                
                <div class="dropdown-divider"></div>
                
                <a href="logout.php" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.topbar {
    background: white;
    padding: 15px 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 999;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.topbar-left h2 {
    font-size: 24px;
    color: #000;
    margin: 0;
}

.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #000;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 25px;
}

.topbar-item {
    position: relative;
    cursor: pointer;
}

.topbar-item a {
    color: #333;
    font-size: 18px;
    text-decoration: none;
    transition: color 0.3s;
}

.topbar-item a:hover {
    color: #000;
}

.topbar-item .badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #f00;
    color: white;
    font-size: 10px;
    padding: 3px 6px;
    border-radius: 10px;
    font-weight: 600;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.3s;
    position: relative;
}

.user-menu:hover {
    background: #f5f5f5;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #000;
}

.user-menu span {
    font-weight: 600;
    color: #333;
}

.user-menu i.fa-chevron-down {
    font-size: 12px;
    color: #666;
    transition: transform 0.3s;
}

.user-menu:hover i.fa-chevron-down {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s;
    z-index: 1000;
}

.user-menu:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
    color: white;
    border-radius: 10px 10px 0 0;
}

.dropdown-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid white;
}

.dropdown-header strong {
    display: block;
    font-size: 16px;
    margin-bottom: 3px;
}

.dropdown-header small {
    display: block;
    color: rgba(255,255,255,0.8);
    font-size: 12px;
    text-transform: capitalize;
}

.dropdown-divider {
    height: 1px;
    background: #e0e0e0;
    margin: 5px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
}

.dropdown-item:hover {
    background: #f5f5f5;
    padding-left: 25px;
}

.dropdown-item i {
    font-size: 16px;
    width: 20px;
    color: #666;
}

.dropdown-item:hover i {
    color: #000;
}

.logout-item {
    color: #d32f2f;
}

.logout-item:hover {
    background: #ffebee;
}

.logout-item i {
    color: #d32f2f;
}

@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block;
    }
    
    .topbar-left h2 {
        font-size: 18px;
    }
    
    .user-menu span {
        display: none;
    }
}
</style>

<script>
// Mobile menu toggle functionality
document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
});
</script>
