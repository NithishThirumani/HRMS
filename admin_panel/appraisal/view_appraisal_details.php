<?php
require_once __DIR__ . '/bootstrap_session.php';
require_once __DIR__ . '/../../connection.php';

if (!isset($_GET['appraisal_id'])) {
    header('Location: view_appraisals.php');
    exit();
}

$appraisal_id = (int) $_GET['appraisal_id'];

$sql = "SELECT 
            ea.*,
            e.full_name,
            COALESCE(d.name, 'N/A') AS department,
            ap.start_date,
            ap.end_date
        FROM employee_appraisals ea
        INNER JOIN employees e ON ea.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        INNER JOIN appraisal_periods ap ON ea.period_id = ap.period_id
        WHERE ea.appraisal_id = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param('i', $appraisal_id);
$stmt->execute();
$appraisal = $stmt->get_result()->fetch_assoc();

if (!$appraisal) {
    header('Location: view_appraisals.php');
    exit();
}

// Get ratings
$ratings_sql = "SELECT * FROM appraisal_ratings WHERE appraisal_id = '$appraisal_id'";
$ratings_result = mysqli_query($con, $ratings_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Appraisal Details</title>
    
    <!-- Include your CSS files -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include(__DIR__ . '/../sidebar.php') ?>
    <?php include(__DIR__ . '/../header.php') ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Appraisal Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Employee:</strong> <?php echo htmlspecialchars($appraisal['full_name']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($appraisal['department']); ?></p>
                        <p><strong>Period:</strong> <?php echo date('M Y', strtotime($appraisal['start_date'])) . ' - ' . date('M Y', strtotime($appraisal['end_date'])); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($appraisal['status']); ?></p>
                        <p><strong>Final Rating:</strong> <?php echo $appraisal['final_rating'] ?: 'N/A'; ?></p>
                    </div>
                </div>

                <?php if (mysqli_num_rows($ratings_result) > 0): ?>
                    <div class="table-responsive mt-4">
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
                                <?php while ($rating = mysqli_fetch_assoc($ratings_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rating['criteria_id']); ?></td>
                                        <td><?php echo htmlspecialchars($rating['self_rating']); ?></td>
                                        <td><?php echo htmlspecialchars($rating['hod_rating']); ?></td>
                                        <td><?php echo htmlspecialchars($rating['hr_rating']); ?></td>
                                        <td><?php echo htmlspecialchars($rating['comments']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="view_appraisals.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/../footer.php') ?>

    <!-- Include your JavaScript files -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html>