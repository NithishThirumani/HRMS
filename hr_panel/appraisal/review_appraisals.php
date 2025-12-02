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

// Get active periods
$period = new AppraisalPeriod();
$activePeriods = $period->getActivePeriods();

// If no active periods, update the most recent draft period to active
if (empty($activePeriods)) {
    $query = "UPDATE appraisal_periods 
              SET status = 'Active' 
              WHERE status = 'Draft' 
              ORDER BY created_at DESC 
              LIMIT 1";
    $con->query($query);
    
    // Fetch the active periods again
    $activePeriods = $period->getActivePeriods();
}

// Get all departments
$deptQuery = "SELECT id, name FROM departments ORDER BY name ASC";
$departments = $con->query($deptQuery);

$appraisal = new EmployeeAppraisal();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Review Appraisals - HR Panel</title>
    
    <!-- Custom fonts and styles -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../header.php'); ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Review Employee Appraisals</h1>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <select id="periodFilter" class="form-control">
                                        <option value="">Select Period</option>
                                        <?php foreach ($activePeriods as $p): ?>
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
                                        <?php while($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="appraisalsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            <th>HOD Rating</th>
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
            <?php include('../footer.php'); ?>
        </div>
    </div>

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

    <!-- Page specific script -->
    <script src="../assets/js/hr_review.js"></script>
</body>
</html>