<?php
include('session.php');

$emp_id = $_SESSION['user_id'];

// Get employee details and leave balances
$query = "SELECT elb.*, lp.max_days, lp.is_paid 
          FROM employee_leave_balance elb
          JOIN leave_policies lp ON elb.leave_type = lp.leave_type
          WHERE elb.emp_id = '$emp_id' 
          AND elb.year = YEAR(CURRENT_DATE)";
$result = mysqli_query($con, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Employee Management System</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <link href="vendor/select2/select2.min.css" rel="stylesheet">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>


    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Leave Balance Overview</h1>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-white">Available Leave Balance</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="leaveBalanceTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Leave Type</th>
                                        <th class="text-center">Total Allowance</th>
                                        <th class="text-center">Used</th>
                                        <th class="text-center">Balance</th>
                                        <th class="text-center">Pay Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $used = $row['max_days'] - $row['balance'];
                                        $balanceClass = ($row['balance'] <= 2) ? 'text-danger' : 'text-success';
                                        ?>
                                        <tr>
                                            <td class="text-center font-weight-bold">
                                                <?php echo ucwords(strtolower($row['leave_type'])); ?>
                                            </td>
                                            <td class="text-center"><?php echo $row['max_days']; ?></td>
                                            <td class="text-center"><?php echo $used; ?></td>
                                            <td class="text-center font-weight-bold <?php echo $balanceClass; ?>">
                                                <?php echo $row['balance']; ?>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-<?php echo $row['is_paid'] ? 'success' : 'warning'; ?> px-3 py-2">
                                                    <?php echo $row['is_paid'] ? 'Paid Leave' : 'Unpaid Leave'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        $(document).ready(function () {
            $('#leaveBalanceTable').DataTable({
                "order": [[3, "desc"]],
                "pageLength": 10,
                "language": {
                    "emptyTable": "No leave balance records found"
                },
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ]
            });
        });
    </script>
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

</body>

</html>