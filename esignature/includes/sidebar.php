<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Initialize pending count if not set
if (!isset($pending_count)) {
    $pending_count = 0;
}
?>

<!-- E-Signature Sidebar -->
<div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="index.php">
                <span class="text-primary">E-Signature</span>
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">
                    <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php if ($user_type == 'admin' || $user_type == 'hr'): ?>
                    <li class="<?php echo $current_page == 'create.php' ? 'active' : ''; ?>">
                        <a href="create.php">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Document</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="<?php echo $current_page == 'pending.php' ? 'active' : ''; ?>">
                        <a href="pending.php">
                            <i class="fas fa-clock"></i>
                            <span>Pending Documents</span>
                            <?php if ($pending_count > 0): ?>
                            <span class="badge badge-danger"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="<?php echo $current_page == 'signed.php' ? 'active' : ''; ?>">
                        <a href="signed.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Signed Documents</span>
                        </a>
                    </li>
                    <?php if ($user_type == 'admin' || $user_type == 'hr'): ?>
                    <li class="<?php echo $current_page == 'templates.php' ? 'active' : ''; ?>">
                        <a href="templates.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Templates</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($user_type == 'admin'): ?>
                    <li class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li>
    <a href="documents.php">
        <i class="fas fa-list"></i>
        <span>All Documents</span>
    </a>
</li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="sidebar-footer">
        <div class="navigation-links">
            <a href="../<?php echo $user_type == 'admin' ? 'admin_panel' : ($user_type == 'hr' ? 'hr_panel' : ($user_type == 'department_head' ? 'hod_panel' : 'user_panel')); ?>/index.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Back to Main</span>
            </a>
             <a href="https://communik.san-solutions.in/hr_panel/index.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="https://communik.san-solutions.in/login.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-role"><?php echo ucfirst($user_type); ?></div>
        </div>
    </div>
</div>

<style>
.sidebar-menu {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background: #fff;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.sidebar-header .logo a {
    font-size: 24px;
    font-weight: 600;
    text-decoration: none;
}

.main-menu {
    flex: 1;
    overflow-y: auto;
}

.menu-inner {
    padding: 20px 0;
}

.metismenu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.metismenu li {
    margin: 2px 0;
}

.metismenu li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s;
}

.metismenu li a:hover {
    color: #007bff;
    background: #f8f9fa;
}

.metismenu li.active a {
    color: #007bff;
    background: #e9ecef;
}

.metismenu li a i {
    width: 20px;
    margin-right: 10px;
}

.badge {
    margin-left: auto;
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 50px;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.navigation-links {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.navigation-links .nav-link {
    display: flex;
    align-items: center;
    padding: 10px 0;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s;
}

.navigation-links .nav-link:hover {
    color: #007bff;
}

.navigation-links .nav-link i {
    width: 20px;
    margin-right: 10px;
}

.user-info {
    text-align: center;
}

.user-name {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 5px;
}

.user-role {
    font-size: 12px;
    color: #6c757d;
    text-transform: capitalize;
}
</style> 