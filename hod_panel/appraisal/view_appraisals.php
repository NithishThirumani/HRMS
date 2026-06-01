<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../session.php');
require_once(__DIR__ . '/../../connection.php');
require_once(__DIR__ . '/../../classes/EmployeeAppraisal.php');
require_once(__DIR__ . '/../../classes/AppraisalPeriod.php');

// Check if user is logged in and is HOD (session.php already handles this)
// The session.php file will redirect to login if not properly authenticated

$appraisal = new EmployeeAppraisal();
$period = new AppraisalPeriod();
$periods = $period->getAllPeriods();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>View Appraisals</title>
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
    <!-- Add this div for the datetime display -->
    <div id="datetime" class="datetime"></div>


    <div class="container-fluid">
        <!-- Remove this extra wrapper -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="text-seagreen">
                <i class="fas fa-file-alt mr-2"></i>
                View Appraisals
                <div class="h5 mb-0 font-weight-light text-gray-600">Manage your document templates efficiently</div>
            </h2>
        </div>


        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <select id="periodFilter" class="form-control">
                                    <option value="">Select Period</option>
                                    <?php foreach ($periods as $p): ?>
                                        <option value="<?php echo $p['period_id']; ?>">
                                            <?php echo date('M Y', strtotime($p['start_date'])) . ' - ' .
                                                date('M Y', strtotime($p['end_date'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="departmentFilter" class="form-control">
                                    <option value="">Select Department</option>
                                    <?php
                                    global $con;
                                    $query = "SELECT DISTINCT department FROM employees WHERE department IS NOT NULL ORDER BY department";
                                    $result = $con->query($query);
                                    while ($row = $result->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $row['department']; ?>">
                                            <?php echo $row['department']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="statusFilter" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Self_Submitted">Self Submitted</option>
                                    <option value="HOD_Reviewed">HOD Reviewed</option>
                                    <option value="HR_Reviewed">HR Reviewed</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="appraisalsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Final Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
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
    <script src="../js/view_appraisals.js"></script>
</body>

</html>