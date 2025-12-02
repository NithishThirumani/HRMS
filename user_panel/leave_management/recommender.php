<?php
// Include the main session file first
include('../session.php');

// Include other required files
include('../connection.php');
include('includes/leave_functions.php');
include('../../includes/email_functions.php');

// Check if user is logged in
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

// Get employee details
$stmt = $con->prepare("SELECT id, eid, full_name, department_id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "<div class='alert alert-danger'>Employee record not found. Please contact HR.</div>";
    exit;
}

// Debug information
echo "<div class='alert alert-info'>";
echo "Your Employee ID (eid): " . htmlspecialchars($_SESSION['eid']) . "<br>";
echo "Your Employee Database ID: " . htmlspecialchars($employee['id']) . "<br>";
echo "Your Name: " . htmlspecialchars($employee['full_name']) . "<br>";

// Check recommender assignments
$debug_stmt = $con->prepare("SELECT COUNT(*) as count FROM leaves WHERE recommender_id = ?");
$debug_stmt->bind_param("i", $employee['id']);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result()->fetch_assoc();
echo "Total leaves where you are recommender: " . $debug_result['count'] . "<br>";

// Check pending leaves
$debug_stmt = $con->prepare("SELECT COUNT(*) as count FROM leaves WHERE recommender_id = ? AND status = 'Pending' AND (recommender_status IS NULL OR recommender_status = '')");
$debug_stmt->bind_param("i", $employee['id']);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result()->fetch_assoc();
echo "Pending leaves requiring your recommendation: " . $debug_result['count'] . "<br>";
echo "</div>";

$employee_id = $employee['id'];

// Check if this employee is a recommender
$is_recommender = false;
$stmt = $con->prepare("SELECT 1 FROM leave_hierarchy WHERE recommender_id = ? LIMIT 1");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$is_recommender = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$is_recommender) {
    echo "<div class='alert alert-warning'>You are not assigned as a recommender for any employees.</div>";
    exit;
}

// Handle actions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'recommend':
                $leave_id = $_POST['leave_id'];
                $recommendation = $_POST['recommendation'];
                $remarks = $_POST['remarks'];
                
                                 // Update leave with recommendation
                 $update_query = "UPDATE leaves SET 
                                 recommender_status = ?, 
                                 recommender_remarks = ?, 
                                 recommender_action_date = NOW(),
                                 status = ? 
                                 WHERE id = ? AND recommender_id = ?";
                
                                 $new_status = ($recommendation === 'Approved') ? 'Pending' : 'Rejected';
                 
                 // Change the recommendation value to be more descriptive
                 $recommendation_display = ($recommendation === 'Approved') ? 'Recommended' : 'Rejected';
                                 $stmt = $con->prepare($update_query);
                 $stmt->bind_param("sssii", $recommendation_display, $remarks, $new_status, $leave_id, $employee_id);
                
                                 if ($stmt->execute()) {
                     $success_message = "Recommendation submitted successfully! The approver will review your recommendation.";
                    
                    // Send email notification
                    $leave_data = getLeaveDetails($con, $leave_id);
                    if ($leave_data) {
                        $applicant_email = getEmployeeEmail($con, $leave_data['emp_id']);
                        $approver_email = getEmployeeEmail($con, $leave_data['approver_id']);
                        
                                                 // Send notification to applicant and approver
                         sendLeaveRecommendationEmail($con, $leave_data, $employee['full_name'], $recommendation_display, $applicant_email, $approver_email);
                    }
                } else {
                    $error_message = "Error submitting recommendation: " . $con->error;
                }
                break;
                
            case 'reassign':
                $leave_id = $_POST['leave_id'];
                $new_recommender_id = $_POST['new_recommender_id'];
                $reassignment_reason = $_POST['reassignment_reason'];
                
                // Get new recommender name
                $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
                $stmt->bind_param("i", $new_recommender_id);
                $stmt->execute();
                $new_recommender = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if ($new_recommender) {
                    // Update leave with new recommender
                    $update_query = "UPDATE leaves SET 
                                    recommender_id = ?, 
                                    recommender_name = ?,
                                    reassignment_reason = ?,
                                    reassigned_by = ?,
                                    reassigned_at = NOW()
                                    WHERE id = ? AND recommender_id = ?";
                    
                    $stmt = $con->prepare($update_query);
                    $stmt->bind_param("issisi", $new_recommender_id, $new_recommender['full_name'], $reassignment_reason, $employee_id, $leave_id, $employee_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Leave application reassigned to " . $new_recommender['full_name'];
                        
                        // Send notification to new recommender
                        $leave_data = getLeaveDetails($con, $leave_id);
                        if ($leave_data) {
                            $new_recommender_email = getEmployeeEmail($con, $new_recommender_id);
                            $applicant_email = getEmployeeEmail($con, $leave_data['emp_id']);
                            
                            // Send reassignment notification
                            sendLeaveReassignmentEmail($con, $leave_data, $employee['full_name'], $new_recommender['full_name'], $reassignment_reason, $new_recommender_email, $applicant_email);
                        }
                    } else {
                        $error_message = "Error reassigning leave: " . $con->error;
                    }
                } else {
                    $error_message = "Invalid recommender selected.";
                }
                break;
        }
    }
}

// Get pending leave applications for this recommender
$pending_leaves = [];
$query = "SELECT l.*, e.full_name as applicant_name, e.department_id,
          d.name as department_name
          FROM leaves l 
          JOIN employees e ON l.emp_id = e.id 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.recommender_id = ?
          AND l.status = 'Pending' 
          AND (l.recommender_status IS NULL OR l.recommender_status = '')
          ORDER BY l.applied_at ASC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_leaves[] = $row;
}
$stmt->close();

// Get recommended leaves (already processed)
$recommended_leaves = [];
$query = "SELECT l.*, e.full_name as applicant_name, e.department_id,
          d.name as department_name
          FROM leaves l 
          JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.recommender_id = ?
          AND l.recommender_status IS NOT NULL 
          AND l.recommender_status != ''
          ORDER BY l.recommender_action_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recommended_leaves[] = $row;
}
$stmt->close();

// Get available senior employees for reassignment
$senior_employees = [];
$query = "SELECT e.id, e.full_name, e.department_id, d.name as department_name
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE e.id != ? 
          AND e.status = 'Active'
          ORDER BY e.department_id, e.full_name";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $senior_employees[] = $row;
}
$stmt->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Recommender Dashboard</title>
    
    <!-- Custom fonts -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include('../topbar.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                                         <h1 class="h3 mb-4 text-gray-800">Recommender Dashboard</h1>
                     <p class="text-muted">As a recommender, you can provide recommendations on leave applications. Final approval is handled by the approver.</p>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                                         <?php if (!empty($error_message)): ?>
                         <div class="alert alert-danger alert-dismissible fade show" role="alert">
                             <?php echo htmlspecialchars($error_message); ?>
                             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                             </button>
                         </div>
                     <?php endif; ?>
                     


                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Pending Recommendations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($pending_leaves); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Processed Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $today_count = 0;
                                                                                                 foreach ($recommended_leaves as $leave) {
                                                     if (date('Y-m-d', strtotime($leave['recommender_action_date'])) === date('Y-m-d')) {
                                                         $today_count++;
                                                     }
                                                 }
                                                echo $today_count;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Applications -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Pending Leave Applications</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pending_leaves)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5>No pending leave applications</h5>
                                    <p class="text-muted">All leave applications have been processed.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Leave Type</th>
                                                <th>Dates</th>
                                                <th>Days</th>
                                                <th>Reason</th>
                                                <th>Applied</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_leaves as $leave): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($leave['applicant_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($leave['user_name']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($leave['department_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($leave['start_date'])); ?> - 
                                                    <?php echo date('d M Y', strtotime($leave['end_date'])); ?>
                                                </td>
                                                <td><?php echo $leave['total_days']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewReason('<?php echo htmlspecialchars(addslashes($leave['reason'])); ?>')">
                                                        View Reason
                                                    </button>
                                                </td>
                                                <td><?php echo date('d M Y H:i', strtotime($leave['applied_at'])); ?></td>
                                                <td>
                                                                                                         <div class="btn-group" role="group">
                                                         <button class="btn btn-sm btn-success" onclick="recommendLeave(<?php echo $leave['id']; ?>, 'Approved')">
                                                             <i class="fas fa-check"></i> Recommend Approve
                                                         </button>
                                                         <button class="btn btn-sm btn-danger" onclick="recommendLeave(<?php echo $leave['id']; ?>, 'Rejected')">
                                                             <i class="fas fa-times"></i> Reject
                                                         </button>
                                                         <button class="btn btn-sm btn-warning" onclick="reassignLeave(<?php echo $leave['id']; ?>)">
                                                             <i class="fas fa-exchange-alt"></i> Reassign
                                                         </button>
                                                     </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Processed Leave Applications -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recently Processed Recommendations</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recommended_leaves)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h5>No processed applications</h5>
                                    <p class="text-muted">You haven't processed any leave applications yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Leave Type</th>
                                                <th>Dates</th>
                                                <th>Your Recommendation</th>
                                                <th>Remarks</th>
                                                <th>Processed Date</th>
                                                <th>Current Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($recommended_leaves, 0, 10) as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['applicant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($leave['start_date'])); ?> - 
                                                    <?php echo date('d M Y', strtotime($leave['end_date'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $leave['recommender_status'] === 'Recommended' ? 'success' : 'danger'; ?>">
                                                        <?php echo $leave['recommender_status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($leave['recommender_remarks'])): ?>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewRemarks('<?php echo htmlspecialchars(addslashes($leave['recommender_remarks'])); ?>')">
                                                            View Remarks
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">No remarks</span>
                                                    <?php endif; ?>
                                                </td>
                                                                                                 <td><?php echo date('d M Y H:i', strtotime($leave['recommender_action_date'])); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo getStatusBadgeClass($leave['status']); ?>">
                                                        <?php echo $leave['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- End of Main Content -->
            </div>
        </div>
    </div>

    <!-- Recommendation Modal -->
    <div class="modal fade" id="recommendationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Recommendation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="recommend">
                        <input type="hidden" name="leave_id" id="recommendLeaveId">
                        <input type="hidden" name="recommendation" id="recommendationType">
                        
                        <div class="form-group">
                            <label for="remarks">Remarks (Optional)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" 
                                      placeholder="Add any comments or remarks about your recommendation..."></textarea>
                        </div>
                        
                                                 <div class="alert alert-info">
                             <i class="fas fa-info-circle"></i>
                             <strong>Note:</strong> This is your recommendation. The final approval/rejection will be made by the approver.
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Recommendation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reassignment Modal -->
    <div class="modal fade" id="reassignmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reassign Leave Application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reassign">
                        <input type="hidden" name="leave_id" id="reassignLeaveId">
                        
                        <div class="form-group">
                            <label for="new_recommender_id">Select New Recommender</label>
                            <select class="form-control" id="new_recommender_id" name="new_recommender_id" required>
                                <option value="">Choose a senior employee...</option>
                                <?php foreach ($senior_employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name']); ?> 
                                        (<?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="reassignment_reason">Reason for Reassignment</label>
                            <textarea class="form-control" id="reassignment_reason" name="reassignment_reason" rows="3" 
                                      placeholder="Please provide a reason for reassigning this leave application..." required></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> The selected employee will be notified and asked to provide a recommendation.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reassign Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reason/Remarks Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalTitle">Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="reasonModalBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <script>
                 function recommendLeave(leaveId, recommendation) {
             document.getElementById('recommendLeaveId').value = leaveId;
             document.getElementById('recommendationType').value = recommendation;
             
             // Set appropriate modal title based on recommendation type
             let modalTitle = '';
             if (recommendation === 'Approved') {
                 modalTitle = 'Submit Recommend Approve';
             } else if (recommendation === 'Rejected') {
                 modalTitle = 'Submit Recommend Reject';
             } else {
                 modalTitle = 'Submit ' + recommendation + ' Recommendation';
             }
             
             document.getElementById('recommendationModal').querySelector('.modal-title').textContent = modalTitle;
             $('#recommendationModal').modal('show');
         }

        function reassignLeave(leaveId) {
            document.getElementById('reassignLeaveId').value = leaveId;
            $('#reassignmentModal').modal('show');
        }

        function viewReason(reason) {
            document.getElementById('reasonModalTitle').textContent = 'Leave Reason';
            document.getElementById('reasonModalBody').textContent = reason;
            $('#reasonModal').modal('show');
        }

        function viewRemarks(remarks) {
            document.getElementById('reasonModalTitle').textContent = 'Your Remarks';
            document.getElementById('reasonModalBody').textContent = remarks;
            $('#reasonModal').modal('show');
        }
    </script>
</body>
</html> 