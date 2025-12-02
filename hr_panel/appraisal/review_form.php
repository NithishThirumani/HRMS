<?php
session_start();
require_once '../../connection.php';
require_once '../../classes/EmployeeAppraisal.php';
require_once '../../classes/AppraisalRating.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: review_appraisals.php');
    exit();
}

$appraisal_id = $_GET['id'];
$appraisal = new EmployeeAppraisal();
$rating = new AppraisalRating();

$details = $appraisal->getAppraisalDetails($appraisal_id);
$ratings = $rating->getAppraisalRatings($appraisal_id);

if (!$details || $details['status'] !== 'HOD_Reviewed') {
    header('Location: review_appraisals.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Review Form</title>
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
                            <h1>HR Review Form</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="review_appraisals.php">Back to List</a>
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
                                    <p><strong>Name:</strong> <?php echo $details['first_name'] . ' ' . $details['last_name']; ?></p>
                                    <p><strong>Department:</strong> <?php echo $details['department_name']; ?></p>
                                    <p><strong>Position:</strong> <?php echo $details['position']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Period:</strong> 
                                        <?php echo date('M Y', strtotime($details['start_date'])) . ' - ' . 
                                                date('M Y', strtotime($details['end_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Form -->
                    <form id="hrReviewForm">
                        <input type="hidden" name="appraisal_id" value="<?php echo $appraisal_id; ?>">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Performance Review</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Criteria</th>
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
                                            <td><?php echo $rating['self_rating']; ?></td>
                                            <td><?php echo $rating['hod_rating']; ?></td>
                                            <td>
                                                <input type="number" class="form-control" 
                                                       name="ratings[<?php echo $rating['criteria_id']; ?>]" 
                                                       min="1" max="5" step="0.5" required>
                                            </td>
                                            <td>
                                                <textarea class="form-control" 
                                                          name="comments[<?php echo $rating['criteria_id']; ?>]" 
                                                          rows="2"></textarea>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?php include('../includes/footer.php') ?>
    <script src="../assets/js/hr_review_form.js"></script>
</body>
</html>