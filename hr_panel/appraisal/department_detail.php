<?php
session_start();
require_once '../../connection.php';
require_once '../../classes/EmployeeAppraisal.php';
require_once '../../classes/AppraisalPeriod.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['period'])) {
    header('Location: department_reports.php');
    exit();
}

$department_id = $_GET['id'];
$period_id = $_GET['period'];

// Get department info
$query = "SELECT * FROM department WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $department_id);
$stmt->execute();
$department = $stmt->get_result()->fetch_assoc();

if (!$department) {
    header('Location: department_reports.php');
    exit();
}

// Get period info
$period = new AppraisalPeriod();
$periodInfo = $period->getPeriodById($period_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Detail - <?php echo $department['name']; ?></title>
    <link rel="stylesheet" href="../assets/css/Chart.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <?php include('../includes/sidebar.php') ?>
    <?php include('../includes/header.php') ?>
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?php echo $department['name']; ?> - Performance Detail</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="department_reports.php">Back to Reports</a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Period Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Appraisal Period: 
                                <?php echo date('M Y', strtotime($periodInfo['start_date'])) . ' - ' . 
                                        date('M Y', strtotime($periodInfo['end_date'])); ?>
                            </h3>
                        </div>
                    </div>

                    <!-- Performance Charts -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Rating Distribution</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="ratingDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Criteria Performance</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="criteriaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Performance</h3>
                        </div>
                        <div class="card-body">
                            <table id="employeeTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Self Rating</th>
                                        <th>HOD Rating</th>
                                        <th>HR Rating</th>
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

    <?php include('../includes/footer.php') ?>
    <script src="../assets/js/Chart.min.js"></script>
    <script>
        // Pass PHP variables to JavaScript
        const departmentId = <?php echo $department_id; ?>;
        const periodId = <?php echo $period_id; ?>;
    </script>
    <script src="../assets/js/department_detail.js"></script>
</body>
</html>