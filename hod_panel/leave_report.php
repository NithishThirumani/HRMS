<?php
include('session.php');


$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$department = isset($_GET['department']) ? $_GET['department'] : '';

$query = "SELECT l.*, e.department, e.full_name, 
          elb.balance as current_balance
          FROM leaves l
          JOIN employees e ON l.emp_id = e.id
          LEFT JOIN employee_leave_balance elb ON 
            elb.emp_id = l.emp_id AND 
            elb.leave_type = l.type_of_leave AND 
            elb.year = YEAR(l.start_date)
          WHERE YEAR(l.start_date) = '$year'";

if ($month) {
    $query .= " AND MONTH(l.start_date) = '$month'";
}
if ($department) {
    $query .= " AND e.department = '$department'";
}

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Leave Reports</title>
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
        /* Enhanced Card Styling */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Filter Form Styling */
        .form-control {
            border-radius: 6px;
            padding: 8px 12px;
            border: 1px solid #e3e6f0;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Table Enhancements */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #4e73df;
            color: white;
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
        }

        /* Button Styling */
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }

        .btn-success:hover {
            background-color: #17a673;
            border-color: #169b6b;
            transform: translateY(-1px);
        }

        /* Modal Enhancements */
        .modal-content {
            border-radius: 8px;
            border: none;
        }

        .modal-header {
            background-color: #4e73df;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .modal-header .close {
            color: white;
        }
    </style>



</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>

    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Leave Reports</h1>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <select name="year" class="form-control mr-2">
                        <?php for ($y = 2020; $y <= date('Y'); $y++) { ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="month" class="form-control mr-2">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="department" class="form-control mr-2">
                        <option value="">All Departments</option>
                        <?php
                        $dept_query = "SELECT DISTINCT department FROM employees";
                        $dept_result = mysqli_query($con, $dept_query);
                        while ($dept = mysqli_fetch_assoc($dept_result)) { ?>
                            <option value="<?php echo $dept['department']; ?>" <?php echo $dept['department'] == $department ? 'selected' : ''; ?>>
                                <?php echo $dept['department']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Reports</h6>
                    <div>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportModal">
                            <i class="fas fa-file-excel"></i> Export Leave Data
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="leaveReport">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['department']; ?></td>
                                        <td><?php echo $row['type_of_leave']; ?></td>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td><?php echo $row['total_days']; ?></td>
                                        <td><?php echo $row['current_balance']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Modal -->
        <div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Leave Data</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="export_leave_data.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Report Type</label>
                                <select class="form-control" name="report_type" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="halfyearly">Half Yearly</option>
                                    <option value="annually">Annually</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <select class="form-control" name="year" required>
                                    <?php
                                    $current_year = date('Y');
                                    for ($i = $current_year; $i >= $current_year - 2; $i--) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group" id="periodDiv">
                                <label>Period</label>
                                <select class="form-control" name="period" required>
                                    <!-- Will be populated by JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Export</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $('select[name="report_type"]').change(function () {
                    updatePeriodOptions();
                });

                function updatePeriodOptions() {
                    var reportType = $('select[name="report_type"]').val();
                    var periodSelect = $('select[name="period"]');
                    periodSelect.empty();

                    switch (reportType) {
                        case 'monthly':
                            for (let i = 1; i <= 12; i++) {
                                let monthName = new Date(2000, i - 1, 1).toLocaleString('default', { month: 'long' });
                                periodSelect.append(`<option value="${i}">${monthName}</option>`);
                            }
                            break;
                        case 'quarterly':
                            for (let i = 1; i <= 4; i++) {
                                periodSelect.append(`<option value="${i}">Quarter ${i}</option>`);
                            }
                            break;
                        case 'halfyearly':
                            periodSelect.append('<option value="1">First Half (Jan-Jun)</option>');
                            periodSelect.append('<option value="2">Second Half (Jul-Dec)</option>');
                            break;
                        case 'annually':
                            periodSelect.append('<option value="1">Full Year</option>');
                            break;
                    }
                }

                updatePeriodOptions();
            });
        </script>
    </div>

    <script>
        $(document).ready(function () {
            $('#leaveReport').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
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
                    <a class="btn btn-success" href="https:/emps/admin_panel/logout.php">Logout</a>
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