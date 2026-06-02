<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Leaves</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>

    <style>
        .leave-summary {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 15px;
            max-width: 350px;
            position: fixed;
            right: 20px;
            top: 100px;
            z-index: 100;
        }

        .card-header {
            background: #1cc88a !important;
            padding: 0.75rem !important;
        }

        .card-header h6 {
            color: white !important;
        }

        .table {
            font-size: 0.85rem;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 2px;
            font-size: 0.75rem;
        }

        .summary-table th {
            background: #f8f9fc;
            padding: 6px;
            color: #4e73df;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .summary-table td {
            padding: 6px;
            background: #ffffff;
        }

        .leave-count {
            font-size: 0.75rem;
            font-weight: bold;
            color: #1cc88a;
        }

        .summary-table tr {
            line-height: 1;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .card.shadow.mb-4 {
            min-width: 800px;
        }

        .main-content {
            margin-right: 330px;
            margin-left: 10px;
            width: calc(100% - 340px);
        }

        .table td,
        .table th {
            padding: 0.5rem;
            vertical-align: middle;
        }

        .card.shadow.mb-4 {
            border-radius: 8px;
            border: none;
        }

        .btn-success {
            padding: 0.375rem 1rem;
            font-size: 0.9rem;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #e3e6f0;
        }

        .card-body {
            padding: 1rem;
        }

        .table thead th {
            background-color: #4e73df;
            color: #ffffff;
            border-bottom: 2px solid #3a5ad1;
            font-weight: 600;
        }
    </style>

</head>

<body id="page-top">

    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <!-- Begin Page Content -->
    <div class="container-fluid"><br>
        <div class="row">
            <div class="col-lg-8"></div>
            <div class="leave-summary">
                <h5 class="font-weight-bold text-primary mb-4">Leave Balance</h5>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Total Allocated</th>
                            <th>Used</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $id = $_SESSION['eid'];
                        $leave_types = array(
                            'Casual Leave' => 12,
                            'Sick Leave' => 15,
                            'Annual Leave' => 30
                        );

                        foreach ($leave_types as $type => $allocated) {
                            $stmt = $con->prepare("SELECT COALESCE(SUM(total_days), 0) as used_days 
                                     FROM leaves 
                                     WHERE emp_id = ? 
                                     AND type_of_leave = ? 
                                     AND status = 'Approved'");
                            $stmt->bind_param("ss", $id, $type);
                            $stmt->execute();
                            $used_result = $stmt->get_result();
                            $used_days = mysqli_fetch_assoc($used_result)['used_days'];
                            $balance = $allocated - $used_days;
                            ?>
                            <tr>
                                <td><?php echo $type; ?></td>
                                <td class="leave-count"><?php echo $allocated; ?></td>
                                <td class="leave-count"><?php echo $used_days; ?></td>
                                <td class="leave-count"><?php echo $balance; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="main-content">
        <a href="request_leave.php" class="btn btn-success btn-icon-split mb-4">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Apply for Leave</span>
        </a>
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Leaves</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Employee Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Days</th>
                                <th>Reason</th>
                                <th>Leave Type</th>
                                <th>Status</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id = $_SESSION['eid'];
                            $stmt = $con->prepare("SELECT * FROM leaves WHERE emp_id = ?");
                            $stmt->bind_param("s", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $count = mysqli_num_rows($result);
                            while ($a = mysqli_fetch_array($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $a[1]; ?></td>
                                    <td><?php echo $a[2]; ?></td>
                                    <td><?php echo $a[4]; ?></td>
                                    <td><?php echo $a[5]; ?></td>
                                    <td><?php echo $a[6]; ?></td>
                                    <td><?php echo $a['reason']; ?></td>
                                    <td><?php echo $a['type_of_leave']; ?></td>
                                    <td><?php echo $a['status']; ?></td>
                                <?php } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    </div>
    <!-- /.container-fluid -->
    <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->



    </div>
    <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->
    <?php
    include_once('footer.php');
    ?>
    <!-- Scroll to Top Button-->
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

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('dataTable');
            const cells = table.getElementsByTagName('td');

            for (let cell of cells) {
                if (cell.textContent) {
                    let text = cell.textContent.toLowerCase();
                    cell.textContent = text.charAt(0).toUpperCase() + text.slice(1);
                }
            }
        });
    </script>

</body>

</html>