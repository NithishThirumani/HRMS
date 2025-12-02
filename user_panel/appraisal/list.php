<?php
session_start();
require_once('../../connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: /emps/login.php");
    exit();
}

// Get employee details
$username = $_SESSION['user_name'];
$query = "SELECT e.id as emp_id, e.eid 
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
$emp_id = $employee['eid'];

// Fetch active appraisal assignments
$query = "SELECT aa.id as assignment_id, 
          ap.period_id,
          DATE_FORMAT(ap.start_date, '%b %Y') as period_start,
          DATE_FORMAT(ap.end_date, '%b %Y') as period_end,
          ap.start_date, 
          ap.end_date,
          CONCAT('Appraisal Period ', YEAR(ap.start_date)) as period_name,
          ap.status as period_status,
          aa.status as assignment_status,
          CASE 
              WHEN aa.status = 'completed' THEN 'Completed'
              WHEN aa.status = 'in_progress' THEN 'In Progress'
              ELSE 'Pending'
          END as status_text
          FROM appraisal_assignments aa
          JOIN appraisal_periods ap ON aa.period_id = ap.period_id
          WHERE aa.employee_id = ?
          AND ap.status = 'active'
          AND ap.end_date >= CURDATE()
          ORDER BY ap.start_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Appraisal Assignments</title>
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Appraisal Assignments</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Appraisal Periods</h6>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (count($assignments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['period_name']); ?></td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($assignment['start_date'])) . ' - ' .
                                                date('d M Y', strtotime($assignment['end_date'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $assignment['assignment_status'] === 'completed' ? 'success' : 
                                                    ($assignment['assignment_status'] === 'in_progress' ? 'warning' : 'primary'); 
                                            ?>">
                                                <?php echo $assignment['status_text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($assignment['assignment_status'] === 'pending'): ?>
                                                <a href="self_assessment.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                   Start Assessment
                                                </a>
                                            <?php elseif ($assignment['assignment_status'] === 'in_progress'): ?>
                                                <a href="self_assessment.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" 
                                                   class="btn btn-warning btn-sm">
                                                   Continue Assessment
                                                </a>
                                            <?php else: ?>
                                                <a href="view_form.php?id=<?php echo $assignment['assignment_id']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                   View Details
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No active appraisal assignments available at this time.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../footer.php'); ?>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>
</body>
</html>