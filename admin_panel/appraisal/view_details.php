<?php
session_start();
require_once '../../connection.php';
require_once '../../classes/EmployeeAppraisal.php';
require_once '../../classes/AppraisalRating.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'hr'])) {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: view_appraisals.php');
    exit();
}

$appraisal_id = $_GET['id'];
$employeeAppraisal = new EmployeeAppraisal();
$appraisalRating = new AppraisalRating();

$appraisalDetails = $employeeAppraisal->getAppraisalById($appraisal_id);
$ratings = $appraisalRating->getAppraisalRatings($appraisal_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appraisal Details</title>
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
                            <h1>Appraisal Details</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="view_appraisals.php">Back to List</a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Employee Info Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo $appraisalDetails['first_name'] . ' ' . $appraisalDetails['last_name']; ?></p>
                                    <p><strong>Department:</strong> <?php echo $appraisalDetails['department_name']; ?></p>
                                    <p><strong>Position:</strong> <?php echo $appraisalDetails['position']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge badge-<?php echo getStatusBadgeClass($appraisalDetails['status']); ?>">
                                            <?php echo $appraisalDetails['status']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Period:</strong> 
                                        <?php echo date('M Y', strtotime($appraisalDetails['start_date'])) . ' - ' . 
                                                date('M Y', strtotime($appraisalDetails['end_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ratings Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Performance Ratings</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Criteria</th>
                                        <th>Weightage</th>
                                        <th>Self Rating</th>
                                        <th>HOD Rating</th>
                                        <th>HR Rating</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ratings as $rating): ?>
                                    <tr>
                                        <td><?php echo $rating['criteria_name']; ?></td>
                                        <td><?php echo $rating['weightage']; ?>%</td>
                                        <td><?php echo $rating['self_rating'] ?? 'Pending'; ?></td>
                                        <td><?php echo $rating['hod_rating'] ?? 'Pending'; ?></td>
                                        <td><?php echo $rating['hr_rating'] ?? 'Pending'; ?></td>
                                        <td><?php echo nl2br($rating['comments']); ?></td>
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

    <?php include('../includes/footer.php') ?>
</body>
</html>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Pending':
            return 'warning';
        case 'Self_Submitted':
            return 'info';
        case 'HOD_Reviewed':
            return 'primary';
        case 'HR_Reviewed':
            return 'success';
        case 'Completed':
            return 'secondary';
        default:
            return 'light';
    }
}
?>