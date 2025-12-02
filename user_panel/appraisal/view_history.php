<?php
session_start();
require_once '../../connection.php';
require_once '../../classes/EmployeeAppraisal.php';
require_once '../../classes/AppraisalPeriod.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../../login.php');
    exit();
}

$employee_id = $_SESSION['user_id'];
$appraisal = new EmployeeAppraisal();
$period = new AppraisalPeriod();
$periods = $period->getAllPeriods();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appraisal History</title>
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
                            <h1>Appraisal History</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Period Filter -->
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-4">
                                    <select id="periodFilter" class="form-control">
                                        <option value="">All Periods</option>
                                        <?php foreach ($periods as $p): ?>
                                        <option value="<?php echo $p['period_id']; ?>">
                                            <?php echo date('M Y', strtotime($p['start_date'])) . ' - ' . 
                                                    date('M Y', strtotime($p['end_date'])); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Summary -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Rating Trends</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="ratingTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Performance by Criteria</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="criteriaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appraisal History Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Past Appraisals</h3>
                        </div>
                        <div class="card-body">
                            <table id="appraisalTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Period</th>
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
    <script>
        const employeeId = <?php echo $employee_id; ?>;
    </script>
    <script src="../assets/js/Chart.min.js"></script>
    <script src="../assets/js/view_history.js"></script>
</body>
</html>