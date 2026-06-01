<?php
include('session.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leave History</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(45deg, #4e73df, #36b9cc);
            border-radius: 15px 15px 0 0 !important;
        }

        .card-header h6 {
            color: white !important;
        }

        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-sm {
            padding: 0.15rem 0.4rem;
            /* Smaller padding */
            margin: 0 1px;
            /* Reduced margin between buttons */
            font-size: 0.7rem;
            /* Smaller font size for icons */
            line-height: 1;
            display: inline-block;
        }

        .btn-sm i {
            font-size: 0.7rem;
            /* Smaller icon size */
        }

        td .btn-sm {
            vertical-align: middle;
            white-space: nowrap;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
        }

        .modal-header {
            background: linear-gradient(45deg, #4e73df, #36b9cc);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-header .close {
            color: white;
            opacity: 1;
        }

        #leaveHistory_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 6px 12px;
            border: 1px solid #d1d3e2;
        }

        .dataTables_length select {
            border-radius: 20px;
            padding: 6px 12px;
            border: 1px solid #d1d3e2;
        }

        .page-item.active .page-link {
            background: linear-gradient(45deg, #4e73df, #36b9cc);
            border-color: #4e73df;
        }

        .table td,
        .table th {

            white-space: nowrap;
            padding: 0.5rem;
            /* Reduced from 0.75rem */
            font-size: 0.8rem;
            /* Smaller font size */
            line-height: 1.2;
            /* Tighter line height */
        }

        /* Adjust specific column widths */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 10%;
            /* Reduced from 12% */
        }

        .table th:nth-child(2),
        .table td:nth-child(2),
        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 8%;
            /* Reduced from 10% */
        }

        .table th:nth-child(4),
        .table td:nth-child(4),
        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 7%;
            /* Reduced from 8% */
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            width: 4%;
            /* Reduced from 5% */
        }

        .table td:nth-child(11) {
            white-space: nowrap;
            width: auto;
            min-width: 80px;
        }

        .table-responsive {
            padding: 0.25rem;
            /* Reduced from 0.5rem */
        }

        #leaveHistory_wrapper {
            padding: 0.5rem 0;
            /* Reduced from 1rem */
        }

        #leaveHistory_length,
        #leaveHistory_filter {
            margin-bottom: 0.5rem;
            /* Reduced from 1rem */
        }

        #leaveHistory_info,
        #leaveHistory_paginate {
            margin-top: 0.5rem;
            /* Reduced from 1rem */
            padding: 0.25rem;
            /* Reduced from 0.5rem */
        }

        .dataTables_wrapper .row {
            margin: 0.5rem 0;
            /* Reduced from 1rem */
        }

        .table-responsive {
            padding: 0.5rem;
            margin: 0;
            width: 100%;

        }

        .card-body {
            padding: 0.5rem;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
        }

        /* Adjust column widths for better fit */
        .table th,
        .table td {
            padding: 0.75rem;
            white-space: normal;
            /* Allow text wrapping */
        }

        /* Add smooth scrollbar for better UX */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Actions */
    </style>
    <style>
        /* Existing styles remain the same */

        /* Add status color styles */
        td:contains('Approved') {
            color: #28a745;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-approved {
            background-color: #d4edda;
            color: #28a745;
            border: 1px solid #c3e6cb;
        }
    </style>

</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Leave History</h1>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Leave Requests</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="leaveHistory">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Dept</th>
                                <th>Reason</th>
                                <th>Type of Leave</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Current Month LDays</th>
                                <th>Total Days</th>
                                <th>Applied At</th>
                                <th>Status</th>
                                <th>Recommender ID</th>
                                <th>Approver ID</th>
                                <th>Recommender Remarks</th>
                                <th>Recommender Action Date</th>
                                <th>Actions</th>
                                <th>Leave Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "
                            SELECT 
                                l.*, 
                                COALESCE(e.full_name, l.user_name) as employee_name, 
                                d.name as department 
                            FROM leaves l
                            LEFT JOIN employees e 
                                ON l.emp_id = e.eid OR l.emp_id = e.id
                            LEFT JOIN departments d 
                                ON e.department_id = d.id
                            ORDER BY l.applied_at DESC
                            ";
                            $result = mysqli_query($con, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['employee_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['department'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['reason'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['type_of_leave'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['start_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['end_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['current_month_ldays'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['total_days'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['applied_at'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(getUserName($con, $row['recommender_id'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(getUserName($con, $row['approver_id'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($row['recommender_remarks'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['recommender_action_date'] ?? '') ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="viewDetails(<?= $row['id'] ?>)"><i class="fas fa-eye"></i></button>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="generateLeaveSlip(<?= $row['id'] ?>)"><i class="fas fa-file-pdf"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Details Modal -->
    <div class="modal fade" id="leaveDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Request Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="leaveDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update the modal script -->
    <script>
        $(document).ready(function () {
            $('#leaveHistory').DataTable({
                order: [[9, 'desc']]
            });
        });

        function viewDetails(leaveId) {
            $.ajax({
                url: 'get_leave_details.php',
                type: 'POST',
                data: { leave_id: leaveId },
                success: function (response) {
                    $('#leaveDetails').html(response);
                    $('#leaveDetailsModal').modal('show');
                },
                error: function () {
                    alert('Error loading leave details');
                }
            });
        }
    </script>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Make sure these scripts are loaded in the correct order -->



    <script>
        function generateLeaveSlip(id) {
            window.open('generate_leave_slip.php?leave_id=' + id, '_blank');
        }
    </script>

    <?php include('footer.php'); ?>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>

</html>

<?php
// Helper function to get user name by id
function getUserName($con, $id) {
    if (!$id || $id == 0) return '-';
    // Try employee
    $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();
    if ($emp) return $emp['full_name'];
    // Try admin
    $stmt = $con->prepare("SELECT user_name FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $adm = $stmt->get_result()->fetch_assoc();
    if ($adm) return $adm['user_name'];
    // Try department head
    $stmt = $con->prepare("SELECT head_name FROM department_heads WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $dh = $stmt->get_result()->fetch_assoc();
    if ($dh) return $dh['head_name'];
    return '-';
}