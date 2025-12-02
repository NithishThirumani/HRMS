<?php
session_start();
include('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Get HOD's information
$hod_query = "SELECT id, full_name, department_id FROM employees WHERE eid = ?";
$stmt = $con->prepare($hod_query);
$stmt->bind_param("s", $eid);
$stmt->execute();
$hod_result = $stmt->get_result();
$hod_data = $hod_result->fetch_assoc();

if (!$hod_data) {
    header("Location: ../../login.php");
    exit();
}

// Get department name
$dept_query = "SELECT name FROM departments WHERE id = ?";
$stmt = $con->prepare($dept_query);
$stmt->bind_param("i", $hod_data['department_id']);
$stmt->execute();
$dept_result = $stmt->get_result();
$dept_name = $dept_result->fetch_assoc()['name'] ?? 'Unknown Department';

// Get filter parameters
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';
$leave_type = $_GET['leave_type'] ?? '';
$status = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? ''; // 'recommender', 'approver', or ''

// Build WHERE clause for filters - Updated for new recommender/approver system
$where_conditions = ["(l.recommender_id = ? OR l.approver_id = ?)"];
$params = [$hod_data['id'], $hod_data['id']];
$param_types = "ii";

if ($month) {
    $where_conditions[] = "MONTH(l.start_date) = ?";
    $params[] = $month;
    $param_types .= "i";
}

if ($leave_type) {
    $where_conditions[] = "l.type_of_leave = ?";
    $params[] = $leave_type;
    $param_types .= "s";
}

if ($status) {
    $where_conditions[] = "l.status = ?";
    $params[] = $status;
    $param_types .= "s";
}

// Add role filter
if ($role_filter) {
    if ($role_filter == 'recommender') {
        $where_conditions[] = "l.recommender_id = ?";
        $params[] = $hod_data['id'];
        $param_types .= "i";
    } elseif ($role_filter == 'approver') {
        $where_conditions[] = "l.approver_id = ?";
        $params[] = $hod_data['id'];
        $param_types .= "i";
    }
}

$where_clause = implode(" AND ", $where_conditions);

// Get leave statistics - Updated for new system
$stats_query = "SELECT 
    COUNT(*) as total_leaves,
    COUNT(CASE WHEN l.status = 'Pending' THEN 1 END) as pending_leaves,
    COUNT(CASE WHEN l.status = 'Approved' THEN 1 END) as approved_leaves,
    COUNT(CASE WHEN l.status = 'Rejected' THEN 1 END) as rejected_leaves,
    SUM(CASE WHEN l.status = 'Approved' THEN l.total_days ELSE 0 END) as total_approved_days,
    COUNT(CASE WHEN l.recommender_id = ? THEN 1 END) as recommender_leaves,
    COUNT(CASE WHEN l.approver_id = ? THEN 1 END) as approver_leaves
FROM leaves l
WHERE $where_clause";

// Add the additional parameters for the stats query
$stats_params = array_merge($params, [$hod_data['id'], $hod_data['id']]);
$stats_param_types = $param_types . "ii";

$stmt = $con->prepare($stats_query);
$stmt->bind_param($stats_param_types, ...$stats_params);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get leave requests with employee details - Updated query
$leaves_query = "SELECT l.*, e.full_name, e.email, e.designation, d.name as department_name,
                 CASE 
                     WHEN l.recommender_id = ? THEN 'Recommender'
                     WHEN l.approver_id = ? THEN 'Approver'
                     ELSE 'Unknown'
                 END as hod_role
FROM leaves l
JOIN employees e ON l.emp_id = e.eid
JOIN departments d ON e.department_id = d.id
WHERE $where_clause
ORDER BY l.applied_at DESC";

// Add the additional parameters for the leaves query
$leaves_params = array_merge($params, [$hod_data['id'], $hod_data['id']]);
$leaves_param_types = $param_types . "ii";

$stmt = $con->prepare($leaves_query);
$stmt->bind_param($leaves_param_types, ...$leaves_params);
$stmt->execute();
$leaves_result = $stmt->get_result();

// Get leave types for filter
$leave_types_query = "SELECT DISTINCT type_of_leave FROM leaves ORDER BY type_of_leave";
$leave_types_result = $con->query($leave_types_query);

// Get employee summary - Updated for HOD's involvement only
$employees_query = "SELECT e.eid, e.full_name, e.designation, e.email,
                   COUNT(l.id) as total_applications,
                   SUM(CASE WHEN l.status = 'Approved' THEN l.total_days ELSE 0 END) as approved_days,
                   COUNT(CASE WHEN l.recommender_id = ? THEN 1 END) as recommender_applications,
                   COUNT(CASE WHEN l.approver_id = ? THEN 1 END) as approver_applications
                   FROM employees e
                   LEFT JOIN leaves l ON e.eid = l.emp_id AND YEAR(l.applied_at) = ? AND (l.recommender_id = ? OR l.approver_id = ?)
                   WHERE e.department_id = ?
                   GROUP BY e.eid, e.full_name, e.designation, e.email
                   HAVING total_applications > 0
                   ORDER BY e.full_name";

$stmt = $con->prepare($employees_query);
$stmt->bind_param("iiiiii", $hod_data['id'], $hod_data['id'], $year, $hod_data['id'], $hod_data['id'], $hod_data['department_id']);
$stmt->execute();
$employees_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leave Reports - HOD Panel</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet"> 
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">     
    <link rel="stylesheet" href="../css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --info-color: #17a2b8;
            --orange-color: #fd7e14;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .status-pending { color: #f6c23e; }
        .status-approved { color: #1cc88a; }
        .status-rejected { color: #e74a3b; }
        .card-stats {
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('../sidebar.php'); ?>
  
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
    <?php include('../header.php'); ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Leave Reports & Analytics</h1>
            <div>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="alert alert-info">
            <h5><i class="fas fa-chart-bar"></i> Leave Reports for <?php echo htmlspecialchars($hod_data['full_name']); ?></h5>
            <p class="mb-0">Department: <strong><?php echo htmlspecialchars($dept_name); ?></strong></p>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                This report shows leaves where you are involved as a recommender or approver.
            </small>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-2">
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-control">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="month" class="form-label">Month</label>
                        <select name="month" id="month" class="form-control">
                            <option value="">All Months</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="leave_type" class="form-label">Leave Type</label>
                        <select name="leave_type" id="leave_type" class="form-control">
                            <option value="">All Types</option>
                            <?php while ($type = $leave_types_result->fetch_assoc()): ?>
                                <option value="<?php echo $type['type_of_leave']; ?>" 
                                        <?php echo $leave_type == $type['type_of_leave'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['type_of_leave']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo $status == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $status == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="role" class="form-label">Your Role</label>
                        <select name="role" id="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="recommender" <?php echo $role_filter == 'recommender' ? 'selected' : ''; ?>>Recommender</option>
                            <option value="approver" <?php echo $role_filter == 'approver' ? 'selected' : ''; ?>>Approver</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="leave_reports.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Leave Requests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_leaves']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending Requests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_leaves']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Approved</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approved_leaves']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Approved Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_approved_days'] ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-based Statistics -->
        <div class="row mb-4">
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    As Recommender</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['recommender_leaves']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-orange shadow h-100 py-2 card-stats">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-orange text-uppercase mb-1">
                                    As Approver</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approver_leaves']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-gavel fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Requests Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Leave Requests</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="leavesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Days</th>
                                <th>Your Role</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = $leaves_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($leave['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($leave['designation']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                <td><?php echo date('d M Y', strtotime($leave['start_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($leave['end_date'])); ?></td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td>
                                    <?php
                                    $role_class = ($leave['hod_role'] == 'Recommender') ? 'info' : 'orange';
                                    ?>
                                    <span class="badge badge-<?php echo $role_class; ?> role-badge">
                                        <?php echo $leave['hod_role']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-<?php echo strtolower($leave['status']); ?>">
                                        <i class="fas fa-circle"></i> <?php echo $leave['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($leave['applied_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info view-details" data-id="<?php echo $leave['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($leave['status'] == 'Pending'): ?>
                                            <?php if ($leave['hod_role'] == 'Recommender'): ?>
                                                <a href="recommend_leave.php" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-thumbs-up"></i> Recommend
                                                </a>
                                            <?php elseif ($leave['hod_role'] == 'Approver'): ?>
                                                <a href="approver_dashboard.php" class="btn btn-sm btn-success">
                                                    <i class="fas fa-gavel"></i> Approve
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Employee Leave Summary -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Employee Leave Summary</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="employeesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Designation</th>
                                <th>Total Applications</th>
                                <th>As Recommender</th>
                                <th>As Approver</th>
                                <th>Approved Days</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($employee = $employees_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($employee['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($employee['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                                <td>
                                    <span class="badge badge-primary"><?php echo $employee['total_applications']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo $employee['recommender_applications']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-orange"><?php echo $employee['approver_applications']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?php echo $employee['approved_days'] ?? 0; ?> days</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-employee-leaves" data-emp-id="<?php echo $employee['eid']; ?>">
                                        <i class="fas fa-history"></i> View History
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php include('../footer.php'); ?>
</div>
</div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Leave Details Modal -->
    <div class="modal fade" id="leaveDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Request Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
    $(document).ready(function() {
        $('#leavesTable').DataTable({
            "order": [[7, "desc"]] // Sort by applied date (column 7) in descending order
        });
        
        $('#employeesTable').DataTable({
            "order": [[2, "desc"]] // Sort by total applications (column 2) in descending order
        });

        // View leave details
        $('.view-details').click(function() {
            var leaveId = $(this).data('id');
            // You can implement this to show leave details
            alert('Leave details feature coming soon!');
        });

        // View employee leave history
        $('.view-employee-leaves').click(function() {
            var empId = $(this).data('emp-id');
            // You can implement this to show employee's leave history
            alert('Employee leave history feature coming soon!');
        });
    });

    function exportToExcel() {
        // Implement Excel export functionality
        alert('Excel export feature coming soon!');
    }
    </script>
</body>
</html> 