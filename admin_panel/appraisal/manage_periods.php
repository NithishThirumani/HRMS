<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/bootstrap_session.php';
require_once __DIR__ . '/../../connection.php';
require_once __DIR__ . '/../../classes/AppraisalPeriod.php';

// Check if database connection exists
global $con;
if (!isset($con)) {
    die("Database connection failed");
}

$period = new AppraisalPeriod();
$periods = $period->getAllPeriods();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HRM">
    <meta name="author" content="san-solutions">
    <title>Manage Appraisal Periods - Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/reg_emp.js"></script>
    <script src="../js/jquery-3.6.4.min.js"></script>
    <script src="../js/search.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-seagreen {
            color: #20B2AA !important;
            background: linear-gradient(to bottom, #2cdad5, #20B2AA, #187f7b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .text-seagreen::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 45%, rgba(255, 255, 255, 0.1) 50%, transparent 55%);
            background-size: 200% 200%;
            animation: shine 3s infinite;
        }

        .form-control {
            background: linear-gradient(145deg, #f0f0f0, #e6e6e6);
            border: 1px solid rgba(32, 178, 170, 0.3);
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-color: #20B2AA;
            box-shadow: 0 0 15px rgba(32, 178, 170, 0.2);
            transform: translateY(-1px);
        }

        .form-control:hover {
            background: linear-gradient(145deg, #f5f5f5, #ebebeb);
        }

        @keyframes shine {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }
    </style>

</head>

<body id="page-top">
    <?php include(__DIR__ . '/../sidebar.php') ?>
    <?php include(__DIR__ . '/../header.php') ?>



    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="text-seagreen">
                <i class="fas fa-file-alt mr-2"></i>
                Manage Appraisal Periods
                <div class="h5 mb-0 font-weight-light text-gray-600">Manage your document templates efficiently</div>
            </h2>
        </div>


        <section class="content">
            <div class="container-fluid">
                <!-- Add New Period -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Period</h3>
                    </div>
                    <div class="card-body">
                        <form id="addPeriodForm" class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Add Period</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Periods List -->
                <div class="card-body">
                    <table id="periodsTable" class="table table-bordered table-striped"
                        style="background-color:rgb(57, 161, 189);">
                        <thead>
                            <tr>
                                <th style="color: white;">Period</th>
                                <th style="color: white;">Status</th>
                                <th style="color: white;">Total Employees</th>
                                <th style="color: white;">Completed</th>
                                <th style="color: white;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periods as $p): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M Y', strtotime($p['start_date'])) . ' - ' .
                                            date('M Y', strtotime($p['end_date'])); ?>
                                    </td>
                                    <td><?php echo $p['status']; ?></td>
                                    <td><?php echo $p['total_employees']; ?></td>
                                    <td><?php echo $p['completed_count']; ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm edit-period"
                                            data-id="<?php echo $p['period_id']; ?>"
                                            data-start="<?php echo $p['start_date']; ?>"
                                            data-end="<?php echo $p['end_date']; ?>">
                                            Edit
                                        </button>
                                        <?php if ($p['status'] === 'Draft'): ?>
                                            <button class="btn btn-danger btn-sm delete-period"
                                                data-id="<?php echo $p['period_id']; ?>">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    </section>
    </div>
    </div>

    <!-- Edit Period Modal -->
    <div class="modal fade" id="editPeriodModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Period</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="editPeriodForm">
                    <div class="modal-body">
                        <input type="hidden" name="period_id" id="editPeriodId">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="editStartDate" required>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date" id="editEndDate" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include(__DIR__ . '/../footer.php') ?>

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

    <!-- Bootstrap core JavaScript -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages -->
    <script src="../js/sb-admin-2.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/datetime.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/manage_periods.js"></script>
</body>

</html>