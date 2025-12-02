<?php
include('../session.php');

include('includes/leave_functions.php');

// Get recommender's numeric ID
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$result = $stmt->get_result();
$recommender = $result->fetch_assoc();
$recommender_id = $recommender['id'];

// DEBUG: Fetch pending leaves directly without joins
$query = "SELECT * FROM leaves WHERE recommender_id = ? AND status = 'Pending' ORDER BY applied_at DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $recommender_id);
$stmt->execute();
$pending_leaves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// DEBUG: Output raw data for troubleshooting
if (empty($pending_leaves)) {
    echo '<div class="alert alert-warning">No pending leaves found for you as recommender (raw fetch).</div>';
} else {
    echo '<pre style="background:#fff;color:#000;">';
    print_r($pending_leaves);
    echo '</pre>';
}

// For debugging
error_log("Recommender ID: " . $recommender_id);
error_log("Number of pending leaves: " . count($pending_leaves));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Leaves</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
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
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Pending Leaves for Recommendation</h1>
                        <a href="recommender_dashboard.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
                        </a>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Pending Leaves Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Leaves Pending Your Recommendation</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="pendingLeavesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Total Days</th>
                                            <th>Reason</th>
                                            <th>Applied On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pending_leaves)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No leaves pending your recommendation</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pending_leaves as $leave): ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        $emp_eid = $leave['emp_id'];
                                                        $emp_stmt = $con->prepare("SELECT full_name FROM employees WHERE eid = ?");
                                                        $emp_stmt->bind_param("s", $emp_eid);
                                                        $emp_stmt->execute();
                                                        $emp_stmt->bind_result($emp_name);
                                                        $emp_stmt->fetch();
                                                        $emp_stmt->close();
                                                        echo htmlspecialchars($emp_name ?? $emp_eid);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $dept_stmt = $con->prepare("SELECT d.name FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.eid = ?");
                                                        $dept_stmt->bind_param("s", $emp_eid);
                                                        $dept_stmt->execute();
                                                        $dept_stmt->bind_result($dept_name);
                                                        $dept_stmt->fetch();
                                                        $dept_stmt->close();
                                                        echo htmlspecialchars($dept_name ?? '');
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $leave_type = $leave['type_of_leave'];
                                                        echo htmlspecialchars($leave_type);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['applied_at']); ?></td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-info btn-sm viewBtn" 
                                                                onclick="viewLeaveDetails(<?php echo $leave['id']; ?>)">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm recommendBtn" 
                                                                onclick="showRecommendModal(<?php echo $leave['id']; ?>)">
                                                            <i class="fas fa-check"></i> Recommend
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm rejectBtn" 
                                                                onclick="showRejectModal(<?php echo $leave['id']; ?>)">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include('../footer.php'); ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- View Leave Details Modal -->
    <div class="modal fade" id="viewLeaveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="leaveDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Recommend Modal -->
    <div class="modal fade" id="recommendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recommend Leave</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="recommendForm" action="process/process_recommendation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="recommend_leave_id">
                        <input type="hidden" name="action" value="recommend">
                        <div class="form-group">
                            <label>Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Recommend</button>
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
                    <h5 class="modal-title">Reject Leave</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectForm" action="process/process_recommendation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="reject_leave_id">
                        <input type="hidden" name="action" value="reject">
                        <div class="form-group">
                            <label>Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="remarks" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
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

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        function viewLeaveDetails(leaveId) {
            $.get('ajax/get_leave_details.php', { id: leaveId }, function(response) {
                $('#leaveDetailsContent').html(response);
                $('#viewLeaveModal').modal('show');
            });
        }

        function showRecommendModal(leaveId) {
            $('#recommend_leave_id').val(leaveId);
            $('#recommendModal').modal('show');
        }

        function showRejectModal(leaveId) {
            $('#reject_leave_id').val(leaveId);
            $('#rejectModal').modal('show');
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#pendingLeavesTable').DataTable({
                "order": [[7, "desc"]], // Sort by applied date by default
                "pageLength": 10,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries to show",
                    "infoFiltered": "(filtered from _MAX_ total entries)"
                }
            });
        });
    </script>
</body>
</html> 