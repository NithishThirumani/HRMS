<?php
// Include session validation first
include '../session.php';
include '../connection.php';
include '../../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Debug: Log the current user's EID
error_log("HOD Panel - Current EID: " . $eid);

// Get the numeric ID of the current HOD
$hod_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $hod_numeric_id = $result->fetch_assoc()['id'];
    error_log("HOD Panel - HOD Numeric ID: " . $hod_numeric_id);
} else {
    error_log("HOD Panel - Error: Could not find numeric ID for EID: " . $eid);
    $_SESSION['error'] = "Error: Could not find your employee record. Please contact HR.";
    header("Location: ../index.php");
    exit();
}

// Get HOD's department
$dept_query = "SELECT department_id FROM employees WHERE eid = ?";
$stmt_dept = $con->prepare($dept_query);
$stmt_dept->bind_param("s", $eid);
$stmt_dept->execute();
$dept_result = $stmt_dept->get_result();
$hod_dept = $dept_result->fetch_assoc();

if ($hod_dept && $hod_dept['department_id']) {
    error_log("HOD Panel - HOD Department ID: " . $hod_dept['department_id']);
}

// Handle recommendation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action']; // 'recommend' or 'not_recommend'
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
        $recommendation_status = ($action == 'recommend') ? 'Recommended' : 'Not Recommended';
        $action_date = date('Y-m-d H:i:s');
        
        // Update leave status
        $update_query = "UPDATE leaves SET 
                        recommender_status = ?, 
                        recommender_remarks = ?, 
                        recommender_action_date = ?, 
                        recommender_id = ?,
                        hod_status = ?,
                        hod_remarks = ?,
                        hod_action_date = ?,
                        hod_by = ?
                        WHERE id = ?";
        $stmt_update = $con->prepare($update_query);
        $stmt_update->bind_param("ssssssssi", $recommendation_status, $remarks, $action_date, $eid, 
                                $recommendation_status, $remarks, $action_date, $eid, $leave_id);
        
        if ($stmt_update->execute()) {
            // Prepare leave data for email
            $leave_data = array(
                'employee_name' => $leave['employee_name'],
                'leave_type' => $leave['type_of_leave'],
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'total_days' => $leave['total_days'],
                'reason' => $leave['reason'],
                'recommender_remarks' => $remarks
            );
            
            // Get recommender name and email
            $recommender_query = "SELECT full_name, email FROM employees WHERE eid = ?";
            $stmt_recommender = $con->prepare($recommender_query);
            $stmt_recommender->bind_param("s", $eid);
            $stmt_recommender->execute();
            $recommender_result = $stmt_recommender->get_result();
            $recommender = $recommender_result->fetch_assoc();
            $recommender_name = $recommender['full_name'] ?? 'HOD';
            $recommender_email = $recommender['email'] ?? null;
            
            // Get approver email if exists
            $approver_email = null;
            if (!empty($leave['approver_id'])) {
                $approver_email = getEmployeeEmail($con, $leave['approver_id']);
            }
            
            // Get applicant email
            $applicant_email = getEmployeeEmail($con, $leave['emp_id']);
            
            // Send email notification
            sendLeaveRecommendationEmail($leave_data, $recommender_name, $recommendation_status, $applicant_email, $approver_email);
            
            $_SESSION['success'] = "Leave application " . strtolower($recommendation_status) . " successfully!";
        } else {
            $_SESSION['error'] = "Error updating leave recommendation: " . $con->error;
        }
    } else {
        $_SESSION['error'] = "Leave application not found!";
    }
    
    header("Location: recommend_leave.php");
    exit();
}

// Corrected query to fetch pending leaves for HOD using leave_hierarchy
// This query will show leaves where:
// 1. HOD is assigned as recommender in leave_hierarchy (using numeric IDs)
// 2. HOD is directly assigned as hod_id in leaves table (using numeric IDs)
// 3. Employee is in HOD's department and HOD has HOD role
$query = "SELECT DISTINCT l.*, e.full_name as employee_name, e.email as employee_email,
          d.name as dept_name, e.department_id, l.approver_name
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.status = 'Pending' 
          AND (
              l.hod_id = ? 
              OR l.recommender_id = ?
              OR (e.department_id = ? AND ? IN (
                  SELECT id FROM employees WHERE role = 'HOD' AND department_id = e.department_id
              ))
              OR EXISTS (
                  SELECT 1 FROM leave_hierarchy lh 
                  WHERE lh.employee_id = e.id 
                  AND lh.recommender_id = ? 
                  AND lh.type = 'recommender'
              )
          )
          ORDER BY l.applied_at DESC";

$stmt = $con->prepare($query);
$hod_dept_id = $hod_dept['department_id'] ?? 0;
$stmt->bind_param("iiiii", $hod_numeric_id, $hod_numeric_id, $hod_dept_id, $hod_numeric_id, $hod_numeric_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Log the number of results
error_log("HOD Panel - Number of pending leaves found: " . $result->num_rows);

// Test query: Check if there are any pending leaves at all
$test_query = "SELECT COUNT(*) as total_pending FROM leaves WHERE status = 'Pending'";
$test_stmt = $con->prepare($test_query);
$test_stmt->execute();
$test_result = $test_stmt->get_result();
$total_pending = $test_result->fetch_assoc()['total_pending'];
error_log("HOD Panel - Total pending leaves in system: " . $total_pending);

// Test query: Check if there are any leaves in the hierarchy
$hierarchy_test_query = "SELECT COUNT(*) as total_hierarchy FROM leave_hierarchy";
$hierarchy_test_stmt = $con->prepare($hierarchy_test_query);
$hierarchy_test_stmt->execute();
$hierarchy_test_result = $hierarchy_test_stmt->get_result();
$total_hierarchy = $hierarchy_test_result->fetch_assoc()['total_hierarchy'];
error_log("HOD Panel - Total records in leave_hierarchy: " . $total_hierarchy);

// Additional debug: Check leaves assigned to this HOD directly (by numeric ID)
$hod_direct_query = "SELECT COUNT(*) as hod_direct FROM leaves WHERE hod_id = ? AND status = 'Pending'";
$hod_direct_stmt = $con->prepare($hod_direct_query);
$hod_direct_stmt->bind_param("i", $hod_numeric_id);
$hod_direct_stmt->execute();
$hod_direct_result = $hod_direct_stmt->get_result();
$hod_direct_count = $hod_direct_result->fetch_assoc()['hod_direct'];
error_log("HOD Panel - Leaves directly assigned to HOD (by numeric ID): " . $hod_direct_count);

// Additional debug: Check leaves in HOD's department
$dept_leaves_query = "SELECT COUNT(*) as dept_leaves 
                      FROM leaves l 
                      JOIN employees e ON l.emp_id = e.eid 
                      WHERE e.department_id = ? AND l.status = 'Pending'";
$dept_leaves_stmt = $con->prepare($dept_leaves_query);
$dept_leaves_stmt->bind_param("i", $hod_dept_id);
$dept_leaves_stmt->execute();
$dept_leaves_result = $dept_leaves_stmt->get_result();
$dept_leaves_count = $dept_leaves_result->fetch_assoc()['dept_leaves'];
error_log("HOD Panel - Pending leaves in HOD's department: " . $dept_leaves_count);

// Additional debug: Check leave_hierarchy records for this HOD
$hierarchy_check_query = "SELECT COUNT(*) as hierarchy_count 
                         FROM leave_hierarchy 
                         WHERE recommender_id = ? AND type = 'recommender'";
$hierarchy_check_stmt = $con->prepare($hierarchy_check_query);
$hierarchy_check_stmt->bind_param("i", $hod_numeric_id);
$hierarchy_check_stmt->execute();
$hierarchy_check_result = $hierarchy_check_stmt->get_result();
$hierarchy_count = $hierarchy_check_result->fetch_assoc()['hierarchy_count'];
error_log("HOD Panel - Leave hierarchy records where HOD is recommender: " . $hierarchy_count);

// Additional debug: Show specific leave with ID 42
$specific_leave_query = "SELECT l.*, e.full_name, e.eid as employee_eid, e.id as employee_numeric_id
                        FROM leaves l 
                        LEFT JOIN employees e ON l.emp_id = e.eid 
                        WHERE l.id = 42";
$specific_leave_stmt = $con->prepare($specific_leave_query);
$specific_leave_stmt->execute();
$specific_leave_result = $specific_leave_stmt->get_result();
$specific_leave = $specific_leave_result->fetch_assoc();

if ($specific_leave) {
    error_log("HOD Panel - Leave ID 42: emp_id=" . $specific_leave['emp_id'] . ", employee_numeric_id=" . $specific_leave['employee_numeric_id'] . ", hod_id=" . $specific_leave['hod_id'] . ", recommender_id=" . $specific_leave['recommender_id']);
    
    // Check if this employee has hierarchy records
    $emp_hierarchy_query = "SELECT * FROM leave_hierarchy WHERE employee_id = ?";
    $emp_hierarchy_stmt = $con->prepare($emp_hierarchy_query);
    $emp_hierarchy_stmt->bind_param("i", $specific_leave['employee_numeric_id']);
    $emp_hierarchy_stmt->execute();
    $emp_hierarchy_result = $emp_hierarchy_stmt->get_result();
    
    while ($hierarchy = $emp_hierarchy_result->fetch_assoc()) {
        error_log("HOD Panel - Hierarchy record: employee_id=" . $hierarchy['employee_id'] . ", recommender_id=" . $hierarchy['recommender_id'] . ", type=" . $hierarchy['type']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommend Leave Applications - HOD Panel</title>
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
                    <h1 class="h2">Recommend Leave Applications</h1>
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
                        <h5 class="card-title mb-0">Pending Leave Applications for Your Department</h5>
                        <small class="text-muted">
                            <a href="setup_hierarchy.php" class="btn btn-sm btn-outline-info">Setup Hierarchy</a>
                            <a href="test_leave.php" class="btn btn-sm btn-outline-warning">Create Test Leave</a>
                            | Debug Info: HOD ID: <?php echo $hod_numeric_id ?? 'N/A'; ?>, 
                            Total Pending: <?php echo $total_pending ?? 'N/A'; ?>, 
                            Hierarchy Records: <?php echo $total_hierarchy ?? 'N/A'; ?>
                        </small>
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
                                            <th>Approver</th>
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
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['approver_name'] ?? 'Bashid Khan'); ?></span>
                                                </td>
                                                <td><?php echo date('d M Y H:i', strtotime($row['applied_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm" 
                                                                onclick="recommendLeave(<?php echo $row['id']; ?>)">
                                                            <i class="fas fa-thumbs-up"></i> Recommend
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                onclick="notRecommendLeave(<?php echo $row['id']; ?>)">
                                                            <i class="fas fa-thumbs-down"></i> Not Recommend
                                                        </button>
                                                    </div>
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
                                <p class="text-muted">
                                    All leave applications in your department have been processed or no leaves have been applied yet.
                                    <?php if ($total_pending > 0): ?>
                                        <br><strong>Note:</strong> There are <?php echo $total_pending; ?> pending leaves in the system, but none are assigned to you as a recommender.
                                        <br>You may need to set up the leave hierarchy properly.
                                    <?php endif; ?>
                                </p>
                                <a href="setup_hierarchy.php" class="btn btn-primary">Setup Leave Hierarchy</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Recommendation Modal -->
    <div class="modal fade" id="recommendationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Recommend Leave Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="recommendationForm" method="POST">
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
                            <span id="modalMessage">Are you sure you want to recommend this leave application?</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-thumbs-up"></i> <span id="submitText">Recommend</span>
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
                order: [[7, 'desc']], // Sort by applied date descending
                pageLength: 25,
                responsive: true
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        function recommendLeave(leaveId) {
            $('#leaveId').val(leaveId);
            $('#actionType').val('recommend');
            $('#modalTitle').text('Recommend Leave Application');
            $('#modalMessage').text('Are you sure you want to recommend this leave application?');
            $('#submitBtn').removeClass('btn-warning').addClass('btn-success');
            $('#submitText').text('Recommend');
            $('#recommendationModal').modal('show');
        }
        
        function notRecommendLeave(leaveId) {
            $('#leaveId').val(leaveId);
            $('#actionType').val('not_recommend');
            $('#modalTitle').text('Not Recommend Leave Application');
            $('#modalMessage').text('Are you sure you want to not recommend this leave application?');
            $('#submitBtn').removeClass('btn-success').addClass('btn-warning');
            $('#submitText').text('Not Recommend');
            $('#recommendationModal').modal('show');
        }
    </script>
</body>
</html> 