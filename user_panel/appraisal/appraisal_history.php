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
$query = "SELECT e.id, e.eid, e.full_name 
          FROM employees e 
          INNER JOIN emp_login el ON e.user_name = el.user_name
          WHERE e.user_name = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header("Location: /emps/login.php");
    exit();
}

$query = "SELECT 
            aa.id as assignment_id,
            ap.period_id,
            ap.start_date,
            ap.end_date,
            aa.status,
            CASE 
                WHEN ar.hod_rating IS NOT NULL THEN 'Completed'
                WHEN ar.self_rating IS NOT NULL THEN 'Under Review'
                WHEN aa.status = 'active' THEN 'Open'
                ELSE 'Pending'
            END as assessment_status,
            COALESCE(ar.self_rating, NULL) as self_rating,
            COALESCE(ar.hod_rating, NULL) as hod_rating
          FROM appraisal_assignments aa
          INNER JOIN appraisal_periods ap ON aa.period_id = ap.period_id
          LEFT JOIN employee_appraisals ea ON ea.employee_id = aa.employee_id 
          LEFT JOIN appraisal_ratings ar ON ar.appraisal_id = ea.appraisal_id
          WHERE aa.employee_id = ?
          ORDER BY ap.start_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("s", $employee['eid']);
$stmt->execute();
$appraisals = $stmt->get_result();
// Store results in array
$all_appraisals = [];
while ($row = $appraisals->fetch_assoc()) {
    $all_appraisals[] = $row;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Appraisal History</title>
    <link href="../img/favicon.png" rel="icon">
    <!-- Load CSS first -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Move jQuery to header -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
</head>



<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Appraisal History</h1>

        <div class="card shadow mb-4">
            <div class="card-body">
                <?php if (count($all_appraisals) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="appraisalHistory">

                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Self Rating</th>
                                    <th>HOD Rating</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_appraisals as $row) { ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($row['start_date'])) . ' - ' . date('M Y', strtotime($row['end_date'])); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'secondary';
                                            if ($row['assessment_status'] == 'Completed')
                                                $statusClass = 'success';
                                            if ($row['assessment_status'] == 'Under Review')
                                                $statusClass = 'warning';
                                            if ($row['assessment_status'] == 'Open')
                                                $statusClass = 'primary';
                                            ?>
                                            <span
                                                class="badge badge-<?php echo $statusClass; ?>"><?php echo $row['assessment_status']; ?></span>
                                        </td>
                                        <td><?php echo ($row['self_rating'] > 0) ? number_format($row['self_rating'], 1) : 'N/A'; ?>
                                        </td>
                                        <td><?php echo ($row['hod_rating'] > 0) ? number_format($row['hod_rating'], 1) : 'Pending'; ?>
                                        </td>
                                        <td>
                                            <a href="self_assessment.php?assignment_id=<?php echo $row['assignment_id']; ?>"
                                                class="btn btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info">No appraisal records found.</div>
                <?php } ?>
            </div>
        </div>
        <?php include('../footer.php'); ?>
        <!-- Core plugin JavaScript-->
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
        <!-- Custom scripts -->
        <script src="../js/sb-admin-2.min.js"></script>
        <script>
            $(document).ready(function () {
                if ($.fn.DataTable.isDataTable('#appraisalHistory')) {
                    $('#appraisalHistory').DataTable().destroy();
                }
                $('#appraisalHistory').DataTable({
                    "order": [[0, "desc"]],
                    "pageLength": 10,
                    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
                });
            });
        </script>
</body>

</html>