<?php
session_start();
include '../../includes/config.php';

if (!isset($_SESSION['eid'])) {
    header('Location: ../login.php');
    exit();
}

$emp_id = $_SESSION['eid'];

// Fetch all pending leaves for the employee
$stmt = $con->prepare("SELECT * FROM leaves WHERE emp_id = ? AND status = 'Pending' ORDER BY applied_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_leaves = [];
while ($row = $result->fetch_assoc()) {
    $pending_leaves[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pending Leave Applications</title>
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../topbar.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">My Pending Leave Applications</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Pending Leaves</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Current Approver</th>
                                            <th>Applied At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($pending_leaves) > 0): ?>
                                            <?php foreach ($pending_leaves as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($leave['start_date'])); ?></td>
                                                <td><?php echo date('d M Y', strtotime($leave['end_date'])); ?></td>
                                                <td><?php echo $leave['total_days']; ?></td>
                                                <td><span class="badge badge-warning"><?php echo $leave['status']; ?></span></td>
                                                <td><?php echo htmlspecialchars($leave['current_approver_role']); ?></td>
                                                <td><?php echo date('d M Y H:i', strtotime($leave['applied_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center">No pending leave applications found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html> 