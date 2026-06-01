<?php
session_start();
require_once('../connection.php');

// Basic auth check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

// Get counts
$admin_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM admin WHERE role = 'admin'"))['total'];
$emp_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM employees"))['total'];
$dept_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM departments"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background: #454d55;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background: #f8f9fa;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <h4 class="text-center py-3">Super Admin Panel</h4>
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="manage_admins.php"><i class="fas fa-users-cog me-2"></i> Manage Admins</a>
                <a href="system_settings.php"><i class="fas fa-cogs me-2"></i> System Settings</a>
                <a href="backup_database.php"><i class="fas fa-database me-2"></i> Database Backup</a>
                <a href="audit_logs.php"><i class="fas fa-history me-2"></i> Audit Logs</a>
                <a href="/emps/admin_panel/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="container mt-4">
                    <h2 class="mb-4">Dashboard Overview</h2>
                    
                    <!-- Count Cards -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Admins</h5>
                                    <h2 class="mb-0"><?php echo $admin_count; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Employees</h5>
                                    <h2 class="mb-0"><?php echo $emp_count; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Departments</h5>
                                    <h2 class="mb-0"><?php echo $dept_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <span>Copyright © <?php echo date('Y'); ?> Employeeshub. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>