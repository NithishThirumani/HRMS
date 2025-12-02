<?php
session_start();
require_once('../../connection.php');

// Debug session
error_log("Session user_name: " . ($_SESSION['user_name'] ?? 'not set'));
error_log("Assignment ID: " . ($_GET['assignment_id'] ?? 'not set'));

// Check session and redirect if not logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: /emps/login.php");
    exit();
}

if (!isset($_GET['assignment_id'])) {
    header('Location: ../index.php');
    exit;
}

$assignment_id = $_GET['assignment_id'];
$username = $_SESSION['user_name'];

// Get employee details with proper join
$query = "SELECT e.id, e.eid, e.full_name, e.user_name 
          FROM employees e 
          INNER JOIN emp_login el ON e.user_name = el.user_name 
          WHERE e.user_name = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    error_log("Employee not found for username: " . $username);
    header("Location: /emps/login.php");
    exit();
}

// Fetch appraisal details with proper joins
$query = "SELECT DISTINCT a.*, ap.start_date, ap.end_date, ac.criteria_id, ac.criteria_name, 
          ac.description, ac.weightage
          FROM appraisal_assignments a 
          JOIN appraisal_periods ap ON a.period_id = ap.period_id
          CROSS JOIN appraisal_criteria ac
          WHERE a.id = ? AND a.employee_id = ? AND ac.is_active = 1";

$stmt = $con->prepare($query);
$stmt->bind_param("is", $assignment_id, $employee['eid']);
$stmt->execute();
$result = $stmt->get_result();

// Debug query results
error_log("Query results count: " . $result->num_rows);

$appraisal = null;
$criteria = [];
while ($row = $result->fetch_assoc()) {
    if (!$appraisal) {
        $appraisal = [
            'id' => $row['id'],
            'period_id' => $row['period_id'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'status' => $row['status']
        ];
    }
    $criteria[] = [
        'id' => $row['criteria_id'],
        'name' => $row['criteria_name'],
        'description' => $row['description'],
        'weightage' => $row['weightage']
    ];
}

if (!$appraisal) {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Self Appraisal Form</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="../vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Appraisal Period: <?php echo date('d M Y', strtotime($appraisal['start_date'])) . ' - ' .
                        date('d M Y', strtotime($appraisal['end_date'])); ?>
                </h6>
            </div>
            <div class="card-body">
                <form id="appraisalForm" method="POST" action="submit_assessment.php">
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">

                    <?php foreach ($criteria as $criterion): ?>
                        <div class="form-group">
                            <h5><?php echo htmlspecialchars($criterion['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($criterion['description']); ?></p>
                            <p class="text-muted">Weightage: <?php echo htmlspecialchars($criterion['weightage']); ?>%</p>

                            <label>Rating (1-5)</label>
                            <input type="number" class="form-control" name="ratings[<?php echo $criterion['id']; ?>]"
                                min="1" max="5" step="0.5" required>

                            <label>Comments</label>
                            <textarea class="form-control" name="comments[<?php echo $criterion['id']; ?>]" rows="3"
                                required></textarea>
                        </div>
                        <hr>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary">Submit Assessment</button>
                    <a href="self_assessment.php?assignment_id=<?php echo $assignment_id; ?>"
                        class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <?php include('../footer.php'); ?>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>

</html>