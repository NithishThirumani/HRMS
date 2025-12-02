<?php
include('../session.php');
include('../header.php');
include('includes/leave_functions.php');

// Get pending leaves for current user's role
$pending_leaves = getPendingLeaves($_SESSION['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Leave Actions</title>
</head>
<body>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Pending Leave Actions</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Leave Requests Requiring Your Action</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="pendingLeavesTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_leaves as $leave): ?>
                            <tr>
                                <td><?php echo $leave['employee_name']; ?></td>
                                <td><?php echo $leave['type_of_leave']; ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($leave['start_date'])); ?> - 
                                    <?php echo date('M d, Y', strtotime($leave['end_date'])); ?>
                                </td>
                                <td><?php echo $leave['total_days']; ?></td>
                                <td><?php echo $leave['status']; ?></td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-info btn-sm viewBtn" 
                                            data-id="<?php echo $leave['id']; ?>"
                                            data-toggle="modal" 
                                            data-target="#leaveDetailsModal">
                                        View
                                    </button>
                                    <button type="button" 
                                            class="btn btn-success btn-sm approveBtn" 
                                            data-id="<?php echo $leave['id']; ?>">
                                        Approve
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm rejectBtn" 
                                            data-id="<?php echo $leave['id']; ?>">
                                        Reject
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

    <!-- Leave Details Modal -->
    <?php include('includes/leave_details_modal.php'); ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle approve button click
            $('.approveBtn').click(function() {
                var leaveId = $(this).data('id');
                if (confirm('Are you sure you want to approve this leave request?')) {
                    window.location.href = 'process/process_leave_action.php?action=approve&id=' + leaveId;
                }
            });

            // Handle reject button click
            $('.rejectBtn').click(function() {
                var leaveId = $(this).data('id');
                if (confirm('Are you sure you want to reject this leave request?')) {
                    window.location.href = 'process/process_leave_action.php?action=reject&id=' + leaveId;
                }
            });
        });
    </script>
</body>
</html>