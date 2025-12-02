<?php
session_start();
require_once '../../connection.php';
require_once '../../classes/EmployeeAppraisal.php';
require_once '../../classes/AppraisalPeriod.php';

// Check if user is logged in and has HR role
if (!isset($_SESSION['eid']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'hr') {
    header('Location: ../../login.php');
    exit();
}

$period = new AppraisalPeriod();
$periods = $period->getAllPeriods();

// Get all departments
$deptQuery = "SELECT id, name FROM departments ORDER BY name ASC";
$departments = $con->query($deptQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Department Reports - HR Panel</title>
    
    <!-- Custom fonts and styles -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../vendor/chart.js/Chart.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../header.php'); ?>
                
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Department Performance Reports</h1>
                    </div>

                    <!-- Filters -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
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
                                        <option value="">All Departments</option>
                                        <?php while($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Overview -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Department Average Ratings</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="departmentRatingsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Completion Status</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="completionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Statistics -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Detailed Department Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="statsTable" class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Total Employees</th>
                                            <th>Completed</th>
                                            <th>Pending</th>
                                            <th>Average Rating</th>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../vendor/chart.js/Chart.min.js"></script>

    <!-- Page specific script -->
    <script src="../js/appraisal/department_reports.js"></script>
</body>
</html>