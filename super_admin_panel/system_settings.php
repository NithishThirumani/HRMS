<?php
session_start();
require_once('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update_general':
            $site_name = mysqli_real_escape_string($con, $_POST['site_name']);
            $site_email = mysqli_real_escape_string($con, $_POST['site_email']);
            $site_contact = mysqli_real_escape_string($con, $_POST['site_contact']);
            
            // Update settings in database
            $query = "UPDATE system_settings SET 
                     site_name = '$site_name',
                     site_email = '$site_email',
                     site_contact = '$site_contact'
                     WHERE id = 1";
                     
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "General settings updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating settings: " . mysqli_error($con);
            }
            break;
            
        case 'update_email':
            $smtp_host = mysqli_real_escape_string($con, $_POST['smtp_host']);
            $smtp_user = mysqli_real_escape_string($con, $_POST['smtp_user']);
            $smtp_pass = mysqli_real_escape_string($con, $_POST['smtp_pass']);
            $smtp_port = mysqli_real_escape_string($con, $_POST['smtp_port']);
            
            $query = "UPDATE email_settings SET 
                     smtp_host = '$smtp_host',
                     smtp_user = '$smtp_user',
                     smtp_pass = '$smtp_pass',
                     smtp_port = '$smtp_port'
                     WHERE id = 1";
                     
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Email settings updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating email settings: " . mysqli_error($con);
            }
            break;
            
        case 'update_backup':
            $backup_frequency = mysqli_real_escape_string($con, $_POST['backup_frequency']);
            $backup_retention = mysqli_real_escape_string($con, $_POST['backup_retention']);
            
            $query = "UPDATE backup_settings SET 
                     frequency = '$backup_frequency',
                     retention_days = '$backup_retention'
                     WHERE id = 1";
                     
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Backup settings updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating backup settings: " . mysqli_error($con);
            }
            break;
    }
    
    header("Location: system_settings.php");
    exit();
}

// Get current settings
$general_settings = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM system_settings WHERE id = 1"));
$email_settings = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM email_settings WHERE id = 1"));
$backup_settings = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM backup_settings WHERE id = 1"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Settings</title>
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
            margin-bottom: 1.5rem;
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
        .settings-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        .settings-card .card-header {
            background: #4e73df;
            color: white;
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <h4 class="text-center py-3">Super Admin Panel</h4>
                <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="manage_admins.php"><i class="fas fa-users-cog me-2"></i> Manage Admins</a>
                <a href="system_settings.php" class="active"><i class="fas fa-cogs me-2"></i> System Settings</a>
                <a href="backup_database.php"><i class="fas fa-database me-2"></i> Database Backup</a>
                <a href="audit_logs.php"><i class="fas fa-history me-2"></i> Audit Logs</a>
                <a href="/emps/admin_panel/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="container mt-4">
                    <h2 class="mb-4">System Settings</h2>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- General Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="update_general">
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($general_settings['site_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Site Email</label>
                                    <input type="email" name="site_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($general_settings['site_email'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="site_contact" class="form-control" 
                                           value="<?php echo htmlspecialchars($general_settings['site_contact'] ?? ''); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update General Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="update_email">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?php echo htmlspecialchars($email_settings['smtp_host'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" name="smtp_user" class="form-control" 
                                           value="<?php echo htmlspecialchars($email_settings['smtp_user'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_pass" class="form-control" 
                                           value="<?php echo htmlspecialchars($email_settings['smtp_pass'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" name="smtp_port" class="form-control" 
                                           value="<?php echo htmlspecialchars($email_settings['smtp_port'] ?? ''); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Email Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Backup Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="update_backup">
                                <div class="mb-3">
                                    <label class="form-label">Backup Frequency</label>
                                    <select name="backup_frequency" class="form-control" required>
                                        <option value="daily" <?php echo ($backup_settings['frequency'] ?? '') == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo ($backup_settings['frequency'] ?? '') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo ($backup_settings['frequency'] ?? '') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Retention Period (Days)</label>
                                    <input type="number" name="backup_retention" class="form-control" 
                                           value="<?php echo htmlspecialchars($backup_settings['retention_days'] ?? '30'); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Backup Settings</button>
                            </form>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 