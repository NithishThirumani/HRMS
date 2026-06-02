<?php
session_start();
require_once('../../connection.php');
include('classes/EmployeeAppraisal.php');
include('classes/AppraisalPeriod.php');

// Check session and redirect if not logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: /emps/login.php");
    exit();
}

if (!isset($_GET['assignment_id'])) {
    // Add proper error handling and redirect back to appraisal list
    $_SESSION['error'] = "Invalid appraisal assignment selected";
    header('Location: ../appraisal/list.php');  // Redirect to appraisal list instead of index
    exit;
}

// Get employee details - consolidated employee ID fetching
$username = $_SESSION['user_name'];
$query = "SELECT e.id as emp_id, e.eid, e.full_name 
          FROM employees e 
          INNER JOIN emp_login el ON e.user_name = el.user_name 
          WHERE e.user_name = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /emps/login.php");
    exit();
}

$employee = $result->fetch_assoc();
$emp_id = $employee['eid']; // Use this single emp_id throughout

$assignment_id = $_GET['assignment_id'];

// Fetch appraisal details with all necessary information
// Fetch appraisal details
// Modify the appraisal details query to include ratings
$query = "SELECT aa.id, aa.period_id, aa.status, aa.employee_id,
          ap.start_date, ap.end_date, ap.status as period_status,
          ac.criteria_id, ac.criteria_name, ac.description, ac.weightage,
          ar.self_rating, ar.comments as self_comments
          FROM appraisal_assignments aa 
          JOIN appraisal_periods ap ON aa.period_id = ap.period_id 
          JOIN appraisal_criteria ac ON ac.criteria_id IN (
              SELECT criteria_id FROM appraisal_criteria WHERE criteria_id = ac.criteria_id
          )
          LEFT JOIN appraisal_ratings ar ON ar.criteria_id = ac.criteria_id 
            AND ar.appraisal_id = (
                SELECT appraisal_id FROM employee_appraisals 
                WHERE period_id = aa.period_id AND employee_id = ?
            )
          WHERE aa.id = ? 
          AND ac.is_active = 1";

// Fix the employee_id parameter in the query binding
$employee_id = $employee['emp_id']; // Use emp_id from the earlier query result
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $employee_id, $assignment_id);
$stmt->execute();
$result = $stmt->get_result();

// Organize the results
$appraisal = null;
$criteria = [];
// Update the criteria array construction
// Fix the while loop structure and criteria array
while ($row = $result->fetch_assoc()) {
    if (!$appraisal) {
        $appraisal = [
            'id' => $row['id'],
            'period_id' => $row['period_id'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'status' => $row['status'],
            'criteria' => []
        ];
    }

    // Include self_rating and self_comments in criteria array
    $criteria[] = [
        'id' => $row['criteria_id'],
        'name' => $row['criteria_name'],
        'description' => $row['description'],
        'weightage' => $row['weightage'],
        'self_rating' => $row['self_rating'],
        'self_comments' => $row['self_comments']
    ];
}

if ($appraisal) {
    $appraisal['criteria'] = $criteria;
}

// Check if form already exists
$checkQuery = "SELECT form_id, status FROM appraisal_forms 
              WHERE period_id = ? AND employee_id = ?";
$stmt = $con->prepare($checkQuery);
$stmt->bind_param("ii", $appraisal['period_id'], $emp_id);
$stmt->execute();
$formResult = $stmt->get_result();

if ($formResult->num_rows === 0) {
    // Create new form entry when user starts
    $insertQuery = "INSERT INTO appraisal_forms (period_id, employee_id, status) 
                   VALUES (?, ?, 'draft')";
    $stmt = $con->prepare($insertQuery);
    $stmt->bind_param("ii", $appraisal['period_id'], $emp_id);
    $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update form status when assessment is submitted
    $updateQuery = "UPDATE appraisal_forms 
                   SET status = 'submitted' 
                   WHERE period_id = ? AND employee_id = ?";
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("ii", $appraisal['period_id'], $emp_id);
    $stmt->execute();

    // Your existing form submission logic here
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Self Assesment</title>
    <!-- Fix asset paths to point to parent directory -->
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">

    <script src="../vendor/jquery/jquery.min.js"></script>
</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <?php if ($appraisal): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Appraisal Period:
                    <?php echo date('M Y', strtotime($appraisal['start_date'])) . ' - ' .
                        date('M Y', strtotime($appraisal['end_date'])); ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($appraisal['status'] === 'Pending'): ?>
                    <!-- Remove the Fill Form button and keep only the form -->
                    <form id="selfAssessmentForm" method="post">
                        <input type="hidden" name="assignment_id" value="<?php echo $appraisal['id']; ?>">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Criteria</th>
                                    <th>Description</th>
                                    <th>Weightage</th>
                                    <th>Self Rating (1-5)</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appraisal['criteria'] as $criterion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($criterion['name']); ?></td>
                                        <td><?php echo htmlspecialchars($criterion['description']); ?></td>
                                        <td><?php echo htmlspecialchars($criterion['weightage']); ?>%</td>
                                        <td>
                                            <input type="number" class="form-control"
                                                name="ratings[<?php echo $criterion['id']; ?>]" min="1" max="5" step="0.5" required>
                                        </td>
                                        <td>
                                            <textarea class="form-control" name="comments[<?php echo $criterion['id']; ?>]" rows="2"
                                                required></textarea>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="submitAssessment">Submit Assessment</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Your assessment has been submitted and is currently under review.
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Criteria</th>
                                <th>Self Rating</th>
                                <th>Self Comments</th>
                                <th>HOD Rating</th>
                                <th>HOD Comments</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($appraisal['criteria'] as $criterion): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($criterion['name']); ?></td>
                                    <td><?php echo isset($criterion['self_rating']) ? htmlspecialchars($criterion['self_rating']) : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($criterion['self_comments']) ? htmlspecialchars($criterion['self_comments']) : 'N/A'; ?>
                                    </td>
                                    <td><?php echo isset($criterion['hod_rating']) ? htmlspecialchars($criterion['hod_rating']) : 'Pending'; ?>
                                    </td>
                                    <td><?php echo isset($criterion['hod_comments']) ? htmlspecialchars($criterion['hod_comments']) : 'Pending'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No active appraisal period at the moment.
        </div>
    <?php endif; ?>
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
                    <a class="btn btn-success" href="<?php echo htmlspecialchars(hrms_user_panel_url('logout.php')); ?>">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <?php include('../footer.php'); ?>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- Scripts -->
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/self_assessment.js"></script>
</body>

</html>