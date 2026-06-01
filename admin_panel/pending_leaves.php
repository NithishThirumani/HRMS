<?php
include('session.php');
require_once dirname(__DIR__) . '/includes/hrms_employees.php';
$leaveEmpJoin = hrms_leave_employee_join('l', 'e');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pending Leaves</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: none;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.2rem 1.5rem;
            border: none;
        }

        .card-header h6 {
            color: white !important;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            color: #4e73df;
            font-weight: 600;
            padding: 1rem 0.75rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            color: #5a5c69;
            font-weight: 500;
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #eaecf4;
        }

        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .btn i {
            margin-right: 5px;
        }

        .btn {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            margin: 3px;
            font-weight: 500;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background: linear-gradient(135deg, #1cc88a, #169a6f);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74a3b, #c0392b);
            border: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #36b9cc, #2a8aad);
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4e73df, #3a56b7);
            border: none;
        }

        .table-responsive {
            padding: 20px;
            border-radius: 0 0 15px 15px;
        }

        .container-fluid {
            padding: 30px;
        }

        .h3 {
            color: #4e73df;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #4e73df, #224abe);
            border-radius: 3px;
        }

        .hr-status-pending {
            color: #f6c23e;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .hr-status-approved {
            color: #1cc88a;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .hr-status-rejected {
            color: #e74a3b;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .hr-status-pending i,
        .hr-status-approved i,
        .hr-status-rejected i {
            margin-right: 5px;
            font-size: 10px;
        }

        .dataTables_length select {
            min-width: 80px !important;
            padding: 8px 12px !important;
            margin: 0 8px !important;
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .dataTables_length {
            margin-bottom: 20px !important;
            margin-left: 5px !important;
        }

        /* Search box styling */
        .dataTables_filter input {
            min-width: 300px !important;
            padding: 10px 15px !important;
            border-radius: 50px;
            border: 1px solid #d1d3e2;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .dataTables_filter input:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        .dataTables_filter {
            margin-right: 5px !important;
            margin-bottom: 20px !important;
        }

        /* Pagination styling */
        .dataTables_paginate .paginate_button {
            border-radius: 50px !important;
            margin: 0 3px;
        }

        .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #4e73df, #224abe) !important;
            border: none !important;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #eaecf4 !important;
            border: 1px solid #eaecf4 !important;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>


<body id="page-top">
    <?php include('sidebar.php'); ?>

    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Pending Leave Requests</h1>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Leave Requests Awaiting Approval</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="pendingLeaves">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>HR Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_role = strtolower($_SESSION['role'] ?? 'admin');

                            if ($user_role === 'hod') {
                                $query = "SELECT l.*, e.full_name, d.name AS department,
                                        COALESCE(l.hod_status, 'pending') AS hod_status,
                                        COALESCE(l.hr_status, 'pending') AS hr_status
                                    FROM leaves l
                                    JOIN employees e ON {$leaveEmpJoin}
                                    JOIN departments d ON e.department_id = d.id
                                    WHERE l.hod_status = 'pending'
                                      AND LOWER(l.status) NOT IN ('rejected', 'approved')
                                    ORDER BY l.applied_at DESC";
                            } elseif ($user_role === 'hr') {
                                $query = "SELECT l.*, e.full_name, d.name AS department,
                                        COALESCE(l.hod_status, 'pending') AS hod_status,
                                        COALESCE(l.hr_status, 'pending') AS hr_status
                                    FROM leaves l
                                    JOIN employees e ON {$leaveEmpJoin}
                                    JOIN departments d ON e.department_id = d.id
                                    WHERE l.hr_status = 'pending'
                                      AND LOWER(l.status) NOT IN ('rejected', 'approved')
                                    ORDER BY l.applied_at DESC";
                            } else {
                                // Admin / super_admin: show all pending leave requests
                                $query = "SELECT l.*, e.full_name, d.name AS department,
                                        COALESCE(l.hod_status, 'pending') AS hod_status,
                                        COALESCE(l.hr_status, 'pending') AS hr_status
                                    FROM leaves l
                                    JOIN employees e ON {$leaveEmpJoin}
                                    JOIN departments d ON e.department_id = d.id
                                    WHERE LOWER(l.status) IN ('pending', 'recommended')
                                      AND (l.hod_status = 'pending' OR l.hr_status = 'pending')
                                    ORDER BY l.applied_at DESC";
                            }

                            $result = mysqli_query($con, $query);
                            if (!$result) {
                                echo '<tr><td colspan="9" class="text-danger">Query error: ' . htmlspecialchars(mysqli_error($con)) . '</td></tr>';
                            }
                            while ($result && ($row = mysqli_fetch_assoc($result))) {
                                ?>
                                <tr>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td><?php echo $row['type_of_leave']; ?></td>
                                    <td><?php echo $row['start_date']; ?></td>
                                    <td><?php echo $row['end_date']; ?></td>
                                    <td><?php echo $row['total_days']; ?></td>
                                    <td><?php echo $row['reason']; ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($row['status']); ?></span>
                                        <div class="small mt-1">
                                            HOD: <?php echo htmlspecialchars($row['hod_status']); ?> |
                                            HR: <?php echo htmlspecialchars($row['hr_status']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm"
                                            onclick="processLeave(<?php echo $row['id']; ?>, 'approved')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="processLeave(<?php echo $row['id']; ?>, 'rejected')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <?php if ($row['doctor_cert']) { ?>
                                            <a href="../uploads/certificates/<?php echo $row['doctor_cert']; ?>"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-file-medical"></i> View Certificate
                                            </a>
                                        <?php } ?>
                                        <button class="btn btn-primary btn-sm"
                                            onclick="generateLeaveSlip(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-file-pdf"></i> Leave Slip
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function processLeave(id, action) {
            let remarks = prompt("Enter remarks (optional):");

            $.ajax({
                url: 'approve_leave.php',
                type: 'POST',
                data: {
                    leave_id: id,
                    action: action,
                    remarks: remarks
                },
                success: function (response) {
                    console.log("Response:", response);
                    if (response.trim() === 'success') {
                        alert('Leave request ' + action + ' successfully');
                        location.reload();
                    } else {
                        alert('Error processing request: ' + response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("Error connecting to server. Please try again.");
                }
            });
        }

        function generateLeaveSlip(id) {
            window.open('generate_leave_slip.php?leave_id=' + id, '_blank');
        }

        $(document).ready(function () {
            var table = $('#pendingLeaves').DataTable({
                stateSave: false,
                order: [[3, 'desc']],
                pageLength: 25
            });
            table.search('').draw();
        });
    </script>

    <?php
    include_once('footer.php');
    ?>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
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

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>