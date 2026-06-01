<?php
session_start();
require_once('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

// Handle log filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query based on filters
$query = "SELECT al.*, a.user_name as admin_name 
          FROM audit_logs al 
          LEFT JOIN admin a ON al.user_id = a.id 
          WHERE 1=1";

if ($filter !== 'all') {
    $query .= " AND al.action_type = '$filter'";
}

if ($search) {
    $query .= " AND (al.description LIKE '%$search%' OR a.user_name LIKE '%$search%')";
}

if ($date_from && $date_to) {
    $query .= " AND DATE(al.created_at) BETWEEN '$date_from' AND '$date_to'";
}

$query .= " ORDER BY al.created_at DESC LIMIT 100";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Logs</title>
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
        .logs-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .logs-table th {
            background: #4e73df;
            color: white;
        }
        .filter-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .activity-login { background: #4e73df; }
        .activity-update { background: #1cc88a; }
        .activity-delete { background: #e74a3b; }
        .activity-create { background: #f6c23e; }
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
                <a href="system_settings.php"><i class="fas fa-cogs me-2"></i> System Settings</a>
                <a href="backup_database.php"><i class="fas fa-database me-2"></i> Database Backup</a>
                <a href="audit_logs.php" class="active"><i class="fas fa-history me-2"></i> Audit Logs</a>
                <a href="/emps/admin_panel/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="container mt-4">
                    <h2 class="mb-4">Audit Logs</h2>

                    <!-- Filters -->
                    <div class="filter-card p-3 mb-4">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Activity Type</label>
                                <select name="filter" class="form-select">
                                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Activities</option>
                                    <option value="login" <?php echo $filter == 'login' ? 'selected' : ''; ?>>Login Activity</option>
                                    <option value="create" <?php echo $filter == 'create' ? 'selected' : ''; ?>>Create Operations</option>
                                    <option value="update" <?php echo $filter == 'update' ? 'selected' : ''; ?>>Update Operations</option>
                                    <option value="delete" <?php echo $filter == 'delete' ? 'selected' : ''; ?>>Delete Operations</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search logs...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Logs Table -->
                    <div class="logs-table p-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="activity-icon activity-<?php echo strtolower($log['action_type']); ?> me-2">
                                                    <?php
                                                    switch($log['action_type']) {
                                                        case 'login':
                                                            echo '<i class="fas fa-sign-in-alt"></i>';
                                                            break;
                                                        case 'create':
                                                            echo '<i class="fas fa-plus"></i>';
                                                            break;
                                                        case 'update':
                                                            echo '<i class="fas fa-edit"></i>';
                                                            break;
                                                        case 'delete':
                                                            echo '<i class="fas fa-trash"></i>';
                                                            break;
                                                        default:
                                                            echo '<i class="fas fa-info"></i>';
                                                    }
                                                    ?>
                                                </div>
                                                <?php echo ucfirst($log['action_type']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['admin_name'] ?? 'System'); ?></td>
                                        <td><?php echo htmlspecialchars($log['description']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-info-circle me-2"></i>No audit logs found matching your criteria
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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