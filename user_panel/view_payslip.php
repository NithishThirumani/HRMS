<?php
include('session.php');
require_once dirname(__DIR__) . '/includes/salary_helpers.php';

$eid = $_SESSION['eid'] ?? '';
if ($eid === '') {
    header('Location: /emps/login.php');
    exit();
}

$stmt = $con->prepare(
    'SELECT s.*, ss.slip_no
     FROM sal s
     LEFT JOIN salary_slips ss ON ss.salary_id = s.id
     WHERE s.emp_id = ?
     ORDER BY s.salary_date DESC'
);
$stmt->bind_param('s', $eid);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    if (empty($row['slip_no'])) {
        $row['slip_no'] = hrms_ensure_salary_slip($con, (int) $row['id']);
    }
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Payslips</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid"><br>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">My Payslips</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="background-color: #2c3e50; color: white;">Slip No</th>
                                <th style="background-color: #2c3e50; color: white;">Month</th>
                                <th style="background-color: #2c3e50; color: white;">Base Salary</th>
                                <th style="background-color: #2c3e50; color: white;">Total Salary</th>
                                <th style="background-color: #2c3e50; color: white;">Present Days</th>
                                <th style="background-color: #2c3e50; color: white;">Leave Days</th>
                                <th style="background-color: #2c3e50; color: white;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rows) > 0): ?>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td>HRMS-<?php echo str_pad((string) $row['slip_no'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo date('F Y', strtotime($row['salary_date'])); ?></td>
                                        <td>AED <?php echo number_format((float) $row['base_salary'], 2); ?></td>
                                        <td>AED <?php echo number_format((float) $row['total_salary'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['present_days']); ?></td>
                                        <td><?php echo htmlspecialchars($row['leaves']); ?></td>
                                        <td>
                                            <a href="generate_payslip.php?id=<?php echo (int) $row['id']; ?>"
                                               class="btn btn-primary btn-sm" target="_blank">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No payslip records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('footer.php'); ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
</body>

</html>
