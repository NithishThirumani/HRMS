<?php
session_start();
include '../../config/config.php';
include '../../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

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
            
            // Get approver name and email
            $approver_query = "SELECT full_name, email FROM employees WHERE eid = ?";
            $stmt_approver = $con->prepare($approver_query);
            $stmt_approver->bind_param("s", $eid);
            $stmt_approver->execute();
            $approver_result = $stmt_approver->get_result();
            $approver = $approver_result->fetch_assoc();
            $approver_name = $approver['full_name'] ?? 'HR Manager';
            $approver_email = $approver['email'] ?? null;
            
            // Get recommender email if exists
            $recommender_email = null;
            if (!empty($leave['recommender_id'])) {
                $recommender_email = getEmployeeEmail($con, $leave['recommender_id']);
            }
            
            // Get applicant email
            $applicant_email = getEmployeeEmail($con, $leave['emp_id']);
            
            // Send email notification
            sendLeaveApprovalEmail($leave_data, $approver_name, $new_status, $applicant_email, $recommender_email);
            
            $_SESSION['success'] = "Leave application " . strtolower($new_status) . " successfully!";
        } else {
            $_SESSION['error'] = "Error updating leave status: " . $con->error;
        }
    } else {
        $_SESSION['error'] = "Leave application not found!";
    }
    
    header("Location: approve_leave.php");
    exit();
}

// Fetch pending leave applications
$query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
          d.dept_name, r.full_name as recommender_name
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN employees r ON l.recommender_id = r.eid
          WHERE l.status = 'Pending' 
          ORDER BY l.applied_at DESC";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Leave Applications - HR Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Approve Leave Applications</h1>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pending Leave Applications</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
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
                                            <th>Applied On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['employee_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['emp_id']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['dept_name'] ?? 'N/A'); ?></td>
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
                                                        View Reason
                                                    </button>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['recommender_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d M Y H:i', strtotime($row['applied_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="approveLeave(<?php echo $row['id']; ?>)">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                onclick="rejectLeave(<?php echo $row['id']; ?>)">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </div>
                                                    <a href="leave_slip.php?leave_id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-pdf"></i> Download Leave Slip
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No pending leave applications</h5>
                                <p class="text-muted">All leave applications have been processed.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Approve Leave Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approvalForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="leaveId" name="leave_id">
                        <input type="hidden" id="actionType" name="action">
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                      placeholder="Add any remarks or comments..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <span id="modalMessage">Are you sure you want to approve this leave application?</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-check"></i> <span id="submitText">Approve</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#leaveTable').DataTable({
                order: [[8, 'desc']], // Sort by applied date descending
                pageLength: 25,
                responsive: true
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
            $('#modalTitle').text('Approve Leave Application');
            $('#modalMessage').text('Are you sure you want to approve this leave application?');
            $('#submitBtn').removeClass('btn-danger').addClass('btn-success');
            $('#submitText').text('Approve');
            $('#approvalModal').modal('show');
        }
        
        function rejectLeave(leaveId) {
            $('#leaveId').val(leaveId);
            $('#actionType').val('reject');
            $('#modalTitle').text('Reject Leave Application');
            $('#modalMessage').text('Are you sure you want to reject this leave application?');
            $('#submitBtn').removeClass('btn-success').addClass('btn-danger');
            $('#submitText').text('Reject');
            $('#approvalModal').modal('show');
        }
    </script>
</body>
</html> 