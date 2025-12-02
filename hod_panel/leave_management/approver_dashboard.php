<?php
session_start();
include '../connection.php';
include '../../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Debug: Log the current user's EID
error_log("Approver Dashboard - Current EID: " . $eid);

// Get the numeric ID of the current approver
$approver_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $approver_numeric_id = $result->fetch_assoc()['id'];
    error_log("Approver Dashboard - Approver Numeric ID: " . $approver_numeric_id);
} else {
    error_log("Approver Dashboard - Error: Could not find numeric ID for EID: " . $eid);
    $_SESSION['error'] = "Error: Could not find your employee record. Please contact HR.";
    header("Location: ../index.php");
    exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $remarks = $_POST['remarks'] ?? '';
    
    // Get leave details
    $query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email 
              FROM leaves l 
              LEFT JOIN employees e ON l.emp_id = e.eid 
              WHERE l.id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if ($leave) {
        $approval_status = ($action == 'approve') ? 'Approved' : 'Rejected';
        $action_date = date('Y-m-d H:i:s');
        
        // Update leave status
        $update_query = "UPDATE leaves SET 
                        approver_status = ?, 
                        approver_remarks = ?, 
                        approver_action_date = ?, 
                        approver_by = ?,
                        status = ?
                        WHERE id = ?";
        $stmt_update = $con->prepare($update_query);
        $stmt_update->bind_param("sssssi", $approval_status, $remarks, $action_date, $eid, $approval_status, $leave_id);
        
        if ($stmt_update->execute()) {
            // Prepare leave data for email
            $leave_data = array(
                'employee_name' => $leave['employee_name'],
                'leave_type' => $leave['type_of_leave'],
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'total_days' => $leave['total_days'],
                'reason' => $leave['reason'],
                'approver_remarks' => $remarks
            );
            
            // Get approver name and email
            $approver_query = "SELECT full_name, email FROM employees WHERE eid = ?";
            $stmt_approver = $con->prepare($approver_query);
            $stmt_approver->bind_param("s", $eid);
            $stmt_approver->execute();
            $approver_result = $stmt_approver->get_result();
            $approver = $approver_result->fetch_assoc();
            $approver_name = $approver['full_name'] ?? 'Approver';
            $approver_email = $approver['email'] ?? null;
            
            // Get applicant email
            $applicant_email = getEmployeeEmail($con, $leave['emp_id']);
            
            // Get recommender email
            $recommender_email = null;
            if (!empty($leave['recommender_id'])) {
                $recommender_email = getEmployeeEmail($con, $leave['recommender_id']);
            }
            
            // Send email notification
            sendLeaveApprovalEmail($con, $leave_data, $approver_name, $approval_status, $applicant_email, $recommender_email);
            
            $_SESSION['success'] = "Leave application " . strtolower($approval_status) . " successfully!";
        } else {
            $_SESSION['error'] = "Error updating leave approval: " . $con->error;
        }
    } else {
        $_SESSION['error'] = "Leave application not found!";
    }
    
    header("Location: approver_dashboard.php");
    exit();
}

// Query to fetch pending leaves for the current approver based on approver_id in leaves table
// This query finds leaves where the current user is the approver and status is still pending
$query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
          d.name as dept_name, e.department_id,
          r.full_name as recommender_name, r.eid as recommender_eid
          FROM leaves l 
          LEFT JOIN employees e ON (l.emp_id = e.eid OR l.emp_id = e.id) 
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN employees r ON l.recommender_id = r.id
          WHERE l.approver_id = ?
          AND l.status = 'Pending'
          AND e.id IS NOT NULL
          ORDER BY l.applied_at DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $approver_numeric_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Log the number of results
error_log("Approver Dashboard - Number of pending approvals found: " . $result->num_rows);

// Debug: Log the first result to check data
if ($result->num_rows > 0) {
    $result->data_seek(0);
    $first_leave = $result->fetch_assoc();
    error_log("Approver Dashboard - First leave data: " . json_encode($first_leave));
    $result->data_seek(0); // Reset pointer back to beginning
} else {
    // Debug: Check if there are any leaves at all and hierarchy records
    $all_leaves = $con->query("SELECT COUNT(*) as total FROM leaves WHERE status = 'Pending'");
    $total_pending = $all_leaves->fetch_assoc()['total'];
    error_log("Approver Dashboard - Total pending leaves in system: " . $total_pending);
    
    $hierarchy_check = $con->prepare("SELECT COUNT(*) as total FROM leave_hierarchy WHERE approver_id = ?");
    $hierarchy_check->bind_param("i", $approver_numeric_id);
    $hierarchy_check->execute();
    $hierarchy_count = $hierarchy_check->get_result()->fetch_assoc()['total'];
    error_log("Approver Dashboard - Hierarchy records where current user is approver: " . $hierarchy_count);
    
    // Debug: Check what employees the current user is responsible for as approver
    $emp_debug_query = "SELECT lh.*, e.eid, e.full_name 
                        FROM leave_hierarchy lh 
                        LEFT JOIN employees e ON lh.employee_id = e.id 
                        WHERE lh.approver_id = ?";
    $emp_debug_stmt = $con->prepare($emp_debug_query);
    $emp_debug_stmt->bind_param("i", $approver_numeric_id);
    $emp_debug_stmt->execute();
    $emp_debug_result = $emp_debug_stmt->get_result();
    error_log("Approver Dashboard - Employees under current user's responsibility as approver:");
    while ($emp_debug = $emp_debug_result->fetch_assoc()) {
        error_log("Employee ID: " . $emp_debug['employee_id'] . ", EID: " . $emp_debug['eid'] . ", Name: " . $emp_debug['full_name'] . ", Type: " . $emp_debug['type']);
    }
    
    // Debug: Check what leaves exist and their employee IDs
    $leaves_debug_query = "SELECT l.*, e.eid, e.full_name, e.id as emp_numeric_id 
                           FROM leaves l 
                           LEFT JOIN employees e ON (l.emp_id = e.eid OR l.emp_id = e.id)
                           WHERE l.status = 'Pending'";
    $leaves_debug_result = $con->query($leaves_debug_query);
    error_log("Approver Dashboard - Pending leaves in system:");
    while ($leave_debug = $leaves_debug_result->fetch_assoc()) {
        error_log("Leave ID: " . $leave_debug['id'] . ", Employee EID: " . $leave_debug['emp_id'] . ", Employee Numeric ID: " . $leave_debug['emp_numeric_id'] . ", Employee Name: " . $leave_debug['full_name']);
    }
}

// Get approved/rejected counts for today
$today = date('Y-m-d');
$approved_today_query = "SELECT COUNT(*) as count FROM leaves 
                        WHERE approver_id = ? AND approver_status = 'Approved' 
                        AND DATE(approver_action_date) = ?";
$stmt_approved = $con->prepare($approved_today_query);
$stmt_approved->bind_param("is", $approver_numeric_id, $today);
$stmt_approved->execute();
$approved_today = $stmt_approved->get_result()->fetch_assoc()['count'];

$rejected_today_query = "SELECT COUNT(*) as count FROM leaves 
                         WHERE approver_id = ? AND approver_status = 'Rejected' 
                         AND DATE(approver_action_date) = ?";
$stmt_rejected = $con->prepare($rejected_today_query);
$stmt_rejected->bind_param("is", $approver_numeric_id, $today);
$stmt_rejected->execute();
$rejected_today = $stmt_rejected->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approver Dashboard - HOD Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .badge {
            border-radius: 20px;
            padding: 0.5em 1em;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">HOD Panel</h4>
                        <small class="text-white-50">Leave Management</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="recommend_leave.php">
                                <i class="fas fa-thumbs-up me-2"></i>
                                Recommend Leaves
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="approver_dashboard.php">
                                <i class="fas fa-gavel me-2"></i>
                                Approve Leaves
                            </a>
                        </li>
                        
                        
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-gavel text-primary me-2"></i>
                        Approver Dashboard
                    </h1>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="window.location.reload()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                                 <?php if (isset($_SESSION['error'])): ?>
                     <div class="alert alert-danger alert-dismissible fade show" role="alert">
                         <i class="fas fa-exclamation-circle me-2"></i>
                         <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                     </div>
                 <?php endif; ?>
                 

                 


        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                                                 <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                     Pending Approvals</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $result->num_rows; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Approved Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $approved_today; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2 stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Rejected Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $rejected_today; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                 <h6 class="m-0 font-weight-bold text-primary">
                     <i class="fas fa-list"></i> Pending Approvals
                 </h6>
                 <div>
                     <span class="badge bg-primary"><?php echo $result->num_rows; ?> pending</span>
                 </div>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="approvalsTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Total Days</th>
                                    <th>Recommender</th>
                                    <th>Applied On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($leave = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($leave['employee_name'] ?? 'Unknown Employee'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?php echo htmlspecialchars($leave['emp_id']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($leave['dept_name'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($leave['type_of_leave']); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></strong>
                                                <br>
                                                <small class="text-muted">to <?php echo date('M d, Y', strtotime($leave['end_date'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning"><?php echo $leave['total_days']; ?> days</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($leave['recommender_name'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($leave['recommender_action_date'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($leave['recommender_action_date'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($leave['applied_at'])); ?>
                                            </small>
                                        </td>
                                                                                 <td>
                                             <span class="badge bg-warning">Pending Approval</span>
                                         </td>
                                                                                 <td>
                                             <div class="btn-group" role="group">
                                                 <button type="button" 
                                                         class="btn btn-info btn-sm" 
                                                         onclick="viewLeaveDetails(<?php echo $leave['id']; ?>)"
                                                         title="View Details">
                                                     <i class="fas fa-eye"></i>
                                                 </button>
                                                 <button type="button" 
                                                         class="btn btn-success btn-sm" 
                                                         onclick="showApproveModal(<?php echo $leave['id']; ?>)"
                                                         title="Approve">
                                                     <i class="fas fa-check"></i>
                                                 </button>
                                                 <button type="button" 
                                                         class="btn btn-danger btn-sm" 
                                                         onclick="showRejectModal(<?php echo $leave['id']; ?>)"
                                                         title="Reject">
                                                     <i class="fas fa-times"></i>
                                                 </button>
                                             </div>
                                         </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                                         <div class="text-center py-5">
                         <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                         <h5 class="text-gray-500">No Pending Approvals</h5>
                         <p class="text-gray-400">You have no leave applications waiting for your approval.</p>
                     </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check text-success"></i> Approve Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="approve_leave_id">
                        <input type="hidden" name="action" value="approve">
                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" rows="3" 
                                      placeholder="Add any comments or remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Approve Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times text-danger"></i> Reject Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="reject_leave_id">
                        <input type="hidden" name="action" value="reject">
                        <div class="mb-3">
                            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="remarks" rows="3" required
                                      placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#approvalsTable').DataTable({
                "pageLength": 10,
                "order": [[6, "desc"]], // Sort by applied date
                "language": {
                    "search": "Search approvals:",
                    "lengthMenu": "Show _MENU_ approvals per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ approvals"
                }
            });
        });

        function showApproveModal(leaveId) {
            $('#approve_leave_id').val(leaveId);
            $('#approveModal').modal('show');
        }

        function showRejectModal(leaveId) {
            $('#reject_leave_id').val(leaveId);
            $('#rejectModal').modal('show');
        }

        function viewLeaveDetails(leaveId) {
            // You can implement a detailed view modal here
            alert('View details for leave ID: ' + leaveId);
        }
    </script>
</body>
</html>