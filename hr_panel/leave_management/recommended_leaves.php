<?php
session_start();
include '../../config/config.php';
include '../connection.php';
include '../../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Handle approval/rejection of recommended leaves
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $remarks = $_POST['remarks'] ?? '';
    
    // Get leave details
    $query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
              r.full_name as recommender_name, r.email as recommender_email
              FROM leaves l 
              LEFT JOIN employees e ON l.emp_id = e.eid 
              LEFT JOIN employees r ON l.recommender_id = r.eid
              WHERE l.id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if ($leave) {
        $new_status = ($action == 'approve') ? 'Approved' : 'Rejected';
        $action_date = date('Y-m-d H:i:s');
        
        // Update leave status
        $update_query = "UPDATE leaves SET 
                        status = ?, 
                        hr_status = ?, 
                        hr_remarks = ?, 
                        hr_action_date = ?, 
                        hr_by = ? 
                        WHERE id = ?";
        $stmt_update = $con->prepare($update_query);
        $stmt_update->bind_param("sssssi", $new_status, $new_status, $remarks, $action_date, $eid, $leave_id);
        
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
            
            // Get approver name
            $approver_query = "SELECT full_name FROM employees WHERE eid = ?";
            $stmt_approver = $con->prepare($approver_query);
            $stmt_approver->bind_param("s", $eid);
            $stmt_approver->execute();
            $approver_result = $stmt_approver->get_result();
            $approver = $approver_result->fetch_assoc();
            $approver_name = $approver['full_name'] ?? 'HR Manager';
            
            // Send email notification
            sendLeaveApprovalEmail($leave_data, $approver_name, $new_status, $leave['employee_email'], $leave['recommender_email']);
            
            $_SESSION['success'] = "Leave application " . strtolower($new_status) . " successfully!";
        } else {
            $_SESSION['error'] = "Error updating leave status: " . $con->error;
        }
    } else {
        $_SESSION['error'] = "Leave application not found!";
    }
    
    header("Location: recommended_leaves.php");
    exit();
}

// Fetch recommended leave applications
$query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
          d.name as dept_name, r.full_name as recommender_name, r.email as recommender_email
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN employees r ON l.recommender_id = r.eid
          WHERE l.recommender_status = 'Recommended' 
          AND l.status = 'Pending'
          ORDER BY l.applied_at DESC";

$result = $con->query($query);

// Check for query errors
if (!$result) {
    error_log("Recommended leaves query failed: " . $con->error);
    $_SESSION['error'] = "Database query failed: " . $con->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Leave Applications - HR Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
        }
        
        .content-wrapper {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 0;
        }
        
        .page-title {
            font-size: 2.2rem;
            font-weight: 600;
            margin: 0;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 10px 0 0 0;
        }
        
        .content-body {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #495057;
            margin: 0;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
            padding: 15px 12px;
        }
        
        .table td {
            padding: 15px 12px;
            vertical-align: middle;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 20px;
        }
        
        .btn-group .btn {
            margin-right: 5px;
            border-radius: 6px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        }
        
        .stats-card.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_info {
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .content-body {
                padding: 20px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-group .btn {
                margin-bottom: 5px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="page-title">Recommended Leave Applications</h1>
                            <p class="page-subtitle">Review and approve leave applications recommended by HODs</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="./dashboard.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Content Body -->
                <div class="content-body">
                    <!-- Alert Messages -->
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
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number">
                                    <?php echo $result ? $result->num_rows : '0'; ?>
                                </div>
                                <div class="stats-label">Total Recommended</div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="stats-card bg-success">
                                <div class="stats-number">
                                    <?php echo $result ? $result->num_rows : '0'; ?>
                                </div>
                                <div class="stats-label">Ready for Approval</div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="stats-card bg-info">
                                <div class="stats-number">
                                    <?php 
                                    $month_query = "SELECT COUNT(*) as count FROM leaves 
                                                   WHERE recommender_status = 'Recommended' 
                                                   AND status = 'Pending'
                                                   AND MONTH(applied_at) = MONTH(CURRENT_DATE())
                                                   AND YEAR(applied_at) = YEAR(CURRENT_DATE())";
                                    $month_result = $con->query($month_query);
                                    if ($month_result) {
                                        $month_count = $month_result->fetch_assoc()['count'];
                                        echo $month_count;
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </div>
                                <div class="stats-label">This Month</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Content Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list-alt me-2"></i>
                                Leave Applications Recommended by HODs
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="leaveTable">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Leave Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Days</th>
                                                <th>Reason</th>
                                                <th>Recommender</th>
                                                <th>Status</th>
                                                <th>Applied On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                                <i class="fas fa-user text-white"></i>
                                                            </div>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($row['employee_name'] ?? 'N/A'); ?></strong><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($row['emp_id'] ?? 'N/A'); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($row['dept_name'] ?? 'N/A'); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($row['type_of_leave']); ?></span>
                                                    </td>
                                                    <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                                                    <td><?php echo date('d M Y', strtotime($row['end_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $row['total_days']; ?> days</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                data-bs-toggle="tooltip" data-bs-placement="top" 
                                                                title="<?php echo htmlspecialchars($row['reason']); ?>">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($row['recommender_name'] ?? 'N/A'); ?></strong><br>
                                                            <small class="text-muted"><?php echo date('d M Y', strtotime($row['applied_at'])); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-thumbs-up me-1"></i>Recommended
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d M Y H:i', strtotime($row['applied_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    onclick="approveLeave(<?php echo $row['id']; ?>)">
                                                                <i class="fas fa-check me-1"></i>Approve
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" 
                                                                    onclick="rejectLeave(<?php echo $row['id']; ?>)">
                                                                <i class="fas fa-times me-1"></i>Reject
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif ($result && $result->num_rows == 0): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5 class="text-muted">No recommended leave applications</h5>
                                    <p class="text-muted">All recommended leave applications have been processed or none are currently pending.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Error</h5>
                                    <p>Unable to fetch recommended leave applications. Please check the error logs.</p>
                                    <p><strong>Error:</strong> <?php echo $con->error ?? 'Unknown error'; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-check-circle me-2"></i>Approve Recommended Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approvalForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="leaveId" name="leave_id">
                        <input type="hidden" id="actionType" name="action">
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">
                                <i class="fas fa-comment me-2"></i>Remarks (Optional)
                            </label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                      placeholder="Add any remarks or comments..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="modalMessage">Are you sure you want to approve this recommended leave application?</span>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> This leave has been recommended by the HOD. Please review carefully before making your decision.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-check me-1"></i><span id="submitText">Approve</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#leaveTable').DataTable({
                order: [[9, 'desc']], // Sort by applied date descending
                pageLength: 25,
                responsive: true,
                language: {
                    search: "Search applications:",
                    lengthMenu: "Show _MENU_ applications per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ applications",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [6, 10] } // Disable sorting for reason and actions columns
                ]
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        function approveLeave(leaveId) {
            $('#leaveId').val(leaveId);
            $('#actionType').val('approve');
            $('#modalTitle').html('<i class="fas fa-check-circle me-2"></i>Approve Recommended Leave');
            $('#modalMessage').text('Are you sure you want to approve this recommended leave application?');
            $('#submitBtn').removeClass('btn-danger').addClass('btn-success');
            $('#submitText').text('Approve');
            $('#approvalModal').modal('show');
        }
        
        function rejectLeave(leaveId) {
            $('#leaveId').val(leaveId);
            $('#actionType').val('reject');
            $('#modalTitle').html('<i class="fas fa-times-circle me-2"></i>Reject Recommended Leave');
            $('#modalMessage').text('Are you sure you want to reject this recommended leave application?');
            $('#submitBtn').removeClass('btn-success').addClass('btn-danger');
            $('#submitText').text('Reject');
            $('#approvalModal').modal('show');
        }
    </script>
</body>
</html> 