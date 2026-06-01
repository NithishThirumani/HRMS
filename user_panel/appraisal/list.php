<?php
require_once('../session.php');

$empEid = $_SESSION['eid'] ?? '';
$empId = (int) ($_SESSION['user_id'] ?? 0);

if ($empEid === '' || $empId <= 0) {
    header('Location: /emps/login.php');
    exit();
}

$query = "SELECT ea.appraisal_id,
          ap.period_id,
          DATE_FORMAT(ap.start_date, '%b %Y') AS period_start,
          DATE_FORMAT(ap.end_date, '%b %Y') AS period_end,
          ap.start_date,
          ap.end_date,
          ap.status AS period_status,
          ea.status AS assignment_status
          FROM employee_appraisals ea
          JOIN appraisal_periods ap ON ea.period_id = ap.period_id
          WHERE ea.employee_id = ?
          ORDER BY ap.start_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param('i', $empId);
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
                                <?php foreach ($assignments as $assignment):
                                    $status = $assignment['assignment_status'];
                                    $badge = ($status === 'Completed') ? 'success' : (($status === 'Self_Submitted' || $status === 'HOD_Reviewed') ? 'warning' : 'primary');
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['period_start'] . ' - ' . $assignment['period_end']); ?></td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($assignment['start_date'])) . ' - ' .
                                                date('d M Y', strtotime($assignment['end_date'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $badge; ?>">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="current_appraisal.php" class="btn btn-primary btn-sm">
                                                <?php echo ($status === 'Pending') ? 'Start Assessment' : 'View / Continue'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No appraisal records yet. Ask HR/Admin to initiate an appraisal cycle for your department.
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