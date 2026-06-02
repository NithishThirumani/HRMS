<?php
include('session.php');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Leave History</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Leave History</h1>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
                style="background: linear-gradient(to right, #4e73df, #224abe);">
                <h6 class="m-0 font-weight-bold text-white">Leave Applications History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="leaveHistory">
                        <thead class="thead-light">
                            <tr>
                                <th>Leave Type</th>
                                <th>Duration</th>

                                <th>Days</th>
                                <th>Reason</th>
                                <th>Applied On</th>

                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $emp_id = $_SESSION['user_id'];
                            $query = "SELECT * FROM leaves WHERE emp_id = ? ORDER BY applied_at DESC";
                            $stmt = $con->prepare($query);
                            $stmt->bind_param("i", $emp_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="font-weight-bold">
                                        <?php echo ucwords(strtolower($row['type_of_leave'])); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                        <?php echo date('d M Y', strtotime($row['start_date'])); ?> -
                                        <?php echo date('d M Y', strtotime($row['end_date'])); ?>
                                    </td>
                                    <td class="font-weight-bold"><?php echo $row['total_days']; ?></td>
                                    <td><?php echo $row['reason']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['applied_at'])); ?></td>
                                    <td>
                                        <div class="status-group">
                                            <div class="status-item">
                                                <small class="text-muted">HOD:</small>
                                                <span
                                                    class="badge badge-<?php echo getStatusColor($row['hod_status']); ?> px-2">
                                                    <?php echo ucfirst($row['hod_status']); ?>
                                                </span>
                                            </div>
                                            <div class="status-item">
                                                <small class="text-muted">HR:</small>
                                                <span
                                                    class="badge badge-<?php echo getStatusColor($row['hr_status']); ?> px-2">
                                                    <?php echo ucfirst($row['hr_status']); ?>
                                                </span>
                                            </div>
                                            <div class="status-item">
                                                <small class="text-muted">Admin:</small>
                                                <span
                                                    class="badge badge-<?php echo getStatusColor($row['admin_status']); ?> px-2">
                                                    <?php echo ucfirst($row['admin_status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['doctor_cert']) { ?>
                                            <a href="../uploads/certificates/<?php echo $row['doctor_cert']; ?>"
                                                class="btn btn-info btn-sm mr-2" title="View Medical Certificate"
                                                target="_blank">
                                                <i class="fas fa-file-medical"></i>
                                            </a>
                                        <?php } ?>
                                        <button class="btn btn-primary btn-sm"
                                            onclick="generateLeaveSlip(<?php echo $row['id']; ?>)"
                                            title="Generate Leave Slip">
                                            <i class="fas fa-file-pdf"></i>
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
    <style>
        .status-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .badge-success {
            background-color: #1cc88a;
        }

        .badge-danger {
            background-color: #e74a3b;
        }

        .badge-warning {
            background-color: #f6c23e;
        }

        .badge-secondary {
            background-color: #858796;
        }

        #leaveHistory tbody tr:hover {
            background-color: #f8f9fc;
            transition: all 0.2s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            transition: all 0.2s;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
    </style>

    <?php
    function getStatusColor($status)
    {
        switch (strtolower($status)) {
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            case 'pending':
                return 'warning';
            default:
                return 'secondary';
        }
    }
    ?>

    <script>
        function generateLeaveSlip(id) {
            window.open('../admin_panel/generate_leave_slip.php?leave_id=' + id, '_blank');
        }

        $(document).ready(function () {
            $('#leaveHistory').DataTable();
        });
    </script>

    <?php include('footer.php'); ?>
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
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="<?php echo htmlspecialchars(hrms_user_panel_url('logout.php')); ?>">Logout</a>
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
</body>

</html>