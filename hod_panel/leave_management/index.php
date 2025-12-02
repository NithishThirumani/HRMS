<?php
session_start();
include '../connection.php';

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

// Get counts for dashboard - Updated for new recommender/approver system
$pending_recommendations = 0;
$pending_approvals = 0;
$approved_count = 0;
$rejected_count = 0;

// Count pending leaves for recommendation (where HOD is recommender)
$pending_recommendations_query = "SELECT COUNT(*) as count FROM leaves l 
                                 LEFT JOIN employees e ON l.emp_id = e.eid 
                                 WHERE l.status = 'Pending' 
                                 AND l.recommender_id = ?";
$stmt = $con->prepare($pending_recommendations_query);
$stmt->bind_param("i", $hod_data['id']);
$stmt->execute();
$pending_recommendations_result = $stmt->get_result();
$pending_recommendations = $pending_recommendations_result->fetch_assoc()['count'];

// Count pending leaves for approval (where HOD is approver)
$pending_approvals_query = "SELECT COUNT(*) as count FROM leaves l 
                           LEFT JOIN employees e ON l.emp_id = e.eid 
                           WHERE l.status = 'Pending' 
                           AND l.approver_id = ?";
$stmt = $con->prepare($pending_approvals_query);
$stmt->bind_param("i", $hod_data['id']);
$stmt->execute();
$pending_approvals_result = $stmt->get_result();
$pending_approvals = $pending_approvals_result->fetch_assoc()['count'];

// Count approved leaves (where HOD was recommender or approver)
$approved_query = "SELECT COUNT(*) as count FROM leaves l 
                   WHERE (l.recommender_id = ? OR l.approver_id = ?) 
                   AND l.status = 'Approved'";
$stmt = $con->prepare($approved_query);
$stmt->bind_param("ii", $hod_data['id'], $hod_data['id']);
$stmt->execute();
$approved_result = $stmt->get_result();
$approved_count = $approved_result->fetch_assoc()['count'];

// Count rejected leaves (where HOD was recommender or approver)
$rejected_query = "SELECT COUNT(*) as count FROM leaves l 
                   WHERE (l.recommender_id = ? OR l.approver_id = ?) 
                   AND l.status = 'Rejected'";
$stmt = $con->prepare($rejected_query);
$stmt->bind_param("ii", $hod_data['id'], $hod_data['id']);
$stmt->execute();
$rejected_result = $stmt->get_result();
$rejected_count = $rejected_result->fetch_assoc()['count'];

// Total pending (both recommendations and approvals)
$total_pending = $pending_recommendations + $pending_approvals;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Leave Management - Dashboard</title>
    
    <!-- Bootstrap 4 CSS -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    
    <!-- Custom styles for this page -->
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .stat-card {
            border-left: 4px solid;
            border-radius: 8px;
        }
        .stat-card.pending { border-left-color: #f6c23e; }
        .stat-card.approved { border-left-color: #1cc88a; }
        .stat-card.rejected { border-left-color: #e74a3b; }
        .stat-card.total { border-left-color: #4e73df; }
        .stat-card.recommendations { border-left-color: #17a2b8; }
        .stat-card.approvals { border-left-color: #fd7e14; }
        
        .quick-action-btn {
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: inherit;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            text-decoration: none;
            color: inherit;
        }
        
        .quick-action-btn i {
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include '../sidebar.php'; ?>
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include '../header.php'; ?>
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Leave Management Dashboard</h1>
                    </div>
                    
                    <!-- Welcome Section -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-user-tie"></i> Welcome, <?php echo htmlspecialchars($hod_data['full_name']); ?>!</h5>
                        <p class="mb-0">You are managing leave requests for the <strong><?php echo htmlspecialchars($dept_name); ?></strong> department.</p>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            You can act as both a recommender and approver for leave applications.
                        </small>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card pending border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label text-warning text-xs font-weight-bold text-uppercase mb-1">
                                                Total Pending
                                            </div>
                                            <div class="stat-number text-gray-800"><?php echo $total_pending; ?></div>
                                            <small class="text-muted">
                                                <?php echo $pending_recommendations; ?> to recommend, 
                                                <?php echo $pending_approvals; ?> to approve
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card recommendations border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label text-info text-xs font-weight-bold text-uppercase mb-1">
                                                Pending Recommendations
                                            </div>
                                            <div class="stat-number text-gray-800"><?php echo $pending_recommendations; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-thumbs-up fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card approvals border-left-orange shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label text-orange text-xs font-weight-bold text-uppercase mb-1">
                                                Pending Approvals
                                            </div>
                                            <div class="stat-number text-gray-800"><?php echo $pending_approvals; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-gavel fa-2x text-orange"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card approved border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="stat-label text-success text-xs font-weight-bold text-uppercase mb-1">
                                                Approved
                                            </div>
                                            <div class="stat-number text-gray-800"><?php echo $approved_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <a href="recommend_leave.php" class="quick-action-btn btn btn-info w-100">
                                                <i class="fas fa-thumbs-up fa-2x"></i>
                                                <span>Recommend Leaves</span>
                                                <?php if ($pending_recommendations > 0): ?>
                                                    <small class="badge badge-warning mt-1"><?php echo $pending_recommendations; ?> pending</small>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <a href="approver_dashboard.php" class="quick-action-btn btn btn-orange w-100">
                                                <i class="fas fa-gavel fa-2x"></i>
                                                <span>Approve Leaves</span>
                                                <?php if ($pending_approvals > 0): ?>
                                                    <small class="badge badge-warning mt-1"><?php echo $pending_approvals; ?> pending</small>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <a href="leave_history.php" class="quick-action-btn btn btn-success w-100">
                                                <i class="fas fa-history fa-2x"></i>
                                                <span>Leave History</span>
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <a href="leave_reports.php" class="quick-action-btn btn btn-secondary w-100">
                                                <i class="fas fa-chart-bar fa-2x"></i>
                                                <span>Reports</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Leave Activity</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get recent leaves where HOD is involved (as recommender or approver)
                                    $recent_query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
                                                    d.name as dept_name,
                                                    CASE 
                                                        WHEN l.recommender_id = ? THEN 'Recommender'
                                                        WHEN l.approver_id = ? THEN 'Approver'
                                                        ELSE 'Unknown'
                                                    END as hod_role
                                                    FROM leaves l 
                                                    LEFT JOIN employees e ON l.emp_id = e.eid 
                                                    LEFT JOIN departments d ON e.department_id = d.id
                                                    WHERE (l.recommender_id = ? OR l.approver_id = ?)
                                                    ORDER BY l.applied_at DESC LIMIT 10";
                                    
                                    $stmt = $con->prepare($recent_query);
                                    $stmt->bind_param("iiii", $hod_data['id'], $hod_data['id'], $hod_data['id'], $hod_data['id']);
                                    $stmt->execute();
                                    $recent_result = $stmt->get_result();
                                    
                                    if ($recent_result->num_rows > 0):
                                    ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Leave Type</th>
                                                        <th>Duration</th>
                                                        <th>Your Role</th>
                                                        <th>Status</th>
                                                        <th>Applied On</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($leave = $recent_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($leave['employee_name']); ?></strong><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($leave['emp_id']); ?></small>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-primary"><?php echo htmlspecialchars($leave['type_of_leave']); ?></span>
                                                            </td>
                                                            <td>
                                                                <?php echo date('d M Y', strtotime($leave['start_date'])); ?> - 
                                                                <?php echo date('d M Y', strtotime($leave['end_date'])); ?>
                                                                <br><small class="text-muted"><?php echo $leave['total_days']; ?> days</small>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $role_class = ($leave['hod_role'] == 'Recommender') ? 'info' : 'orange';
                                                                ?>
                                                                <span class="badge badge-<?php echo $role_class; ?> role-badge">
                                                                    <?php echo $leave['hod_role']; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $status_class = '';
                                                                switch($leave['status']) {
                                                                    case 'Pending': $status_class = 'warning'; break;
                                                                    case 'Approved': $status_class = 'success'; break;
                                                                    case 'Rejected': $status_class = 'danger'; break;
                                                                    default: $status_class = 'secondary';
                                                                }
                                                                ?>
                                                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $leave['status']; ?></span>
                                                            </td>
                                                            <td><?php echo date('d M Y H:i', strtotime($leave['applied_at'])); ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No leave activity yet</h5>
                                            <p class="text-muted">Leave applications will appear here once employees start applying for leaves and you are assigned as recommender or approver.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html> 