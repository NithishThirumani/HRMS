<?php
// Initialize error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection if not already included
if (!isset($con)) {
    require_once(__DIR__ . '/../connection.php');
}
?>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <h4 class="text-white text-center mb-4">Super Admin Panel</h4>
    <a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
    </a>
    <a href="manage_admins.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage_admins.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-users-cog mr-2"></i> Manage Admins
    </a>
    <a href="system_settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'system_settings.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-cogs mr-2"></i> System Settings
    </a>
    <a href="backup_database.php" <?php echo basename($_SERVER['PHP_SELF']) == 'backup_database.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-database mr-2"></i> Database Backup
    </a>
    <a href="audit_logs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'audit_logs.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-history mr-2"></i> Audit Logs
    </a>
</div>

<!-- Top Navigation Bar -->
<div class="main-content">
    <div class="top-bar d-flex justify-content-between align-items-center">
        <button class="btn btn-link d-md-none sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></h4>
        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="change_password.php">
                    <i class="fas fa-key mr-2"></i> Change Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
                                </div>
                            </div>
                    </div>