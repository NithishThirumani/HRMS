<?php
include('../session.php');

include('includes/leave_functions.php');

function hasApproverRights() {
    // TODO: Implement actual logic
    return true;
}

function getPendingApprovals() {
    // TODO: Implement actual logic
    return [];
}

function getApprovalsCount() {
    // TODO: Implement actual logic
    return 0;
}

// Verify user has approver rights
if (!hasApproverRights($_SESSION['role'])) {
    header("Location: dashboard.php");
    exit();
}

// Get pending approvals
$pending_approvals = getPendingApprovals($_SESSION['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Approver Dashboard</title>
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include('../header.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Approver Dashboard</h1>
                    </div>
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Pending Approvals</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo count($pending_approvals); ?>
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
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Approved Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo getApprovalsCount('approved', 'today'); ?>
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
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Rejected Today</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo getApprovalsCount('rejected', 'today'); ?>
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
                            <h6 class="m-0 font-weight-bold text-primary">Pending Approvals</h6>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="approvalsTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Leave Type</th>
                                            <th>Duration</th>
                                            <th>Total Days</th>
                                            <th>Recommender</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_approvals as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
                                                <td><?php echo htmlspecialchars($leave['department_name']); ?></td>
                                                <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($leave['start_date'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($leave['end_date'])); ?>
                                                </td>
                                                <td><?php echo $leave['total_days']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($leave['recommender_name']); ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($leave['recommended_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">Pending Approval</span>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-info btn-sm viewBtn" 
                                                            onclick="viewLeaveDetails(<?php echo $leave['id']; ?>)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-success btn-sm approveBtn" 
                                                            onclick="showApproveModal(<?php echo $leave['id']; ?>)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm rejectBtn" 
                                                            onclick="showRejectModal(<?php echo $leave['id']; ?>)">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Leave</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm" action="process/process_approval.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="approve_leave_id">
                        <input type="hidden" name="action" value="approve">
                        <div class="form-group">
                            <label>Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Leave</button>
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
                <form id="rejectForm" action="process/process_approval.php" method="POST">
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
                        <button type="submit" class="btn btn-danger">Reject Leave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function viewLeaveDetails(leaveId) {
            $.get('ajax/get_leave_details.php', { id: leaveId }, function(response) {
                $('#leaveDetailsModal').modal('show');
                populateLeaveModal(response);
            });
        }

        function showApproveModal(leaveId) {
            $('#approve_leave_id').val(leaveId);
            $('#approveModal').modal('show');
        }

        function showRejectModal(leaveId) {
            $('#reject_leave_id').val(leaveId);
            $('#rejectModal').modal('show');
        }
    </script>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html>