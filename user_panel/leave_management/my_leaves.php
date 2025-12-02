<?php
// Include the main session file first
include('../session.php');
// Include other required files
include('../connection.php');
include('includes/leave_functions.php'); // For getRecentLeaves and getStatusBadgeClass

// --- ADD THE FUNCTION HERE ---
if (!function_exists('getEmployeeNameById')) {
    function getEmployeeNameById($con, $emp_id) {
        $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();
        return $name;
    }
}
// --- END FUNCTION ---

// Check if user is logged in
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

$emp_id = $_SESSION['eid'];
$errors = [];
$success_message = ''; // In case you want to add success messages later

// Only fetch leaves for the logged-in employee
$stmt = $con->prepare("SELECT * FROM leaves WHERE emp_id = ? ORDER BY applied_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$my_leaves = [];
while ($row = $result->fetch_assoc()) {
    $my_leaves[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Leave History</title>
    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/custom.css">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../topbar.php'); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">My Leave History</h1>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Leave Applications</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($my_leaves)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>   <th>Leave Id</th>
                                                <th>Applied On</th>
                                                <th>Leave Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Total Days</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                                <th>HOD Name</th>
                                                <th>Leave Slip</th>
                                                <th>Pending With</th>
                                                <!-- Add more columns if needed, e.g., remarks -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($my_leaves as $leave): ?>
                                                <tr>
                                                <td><?php echo htmlspecialchars($leave['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($leave['applied_at']))); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($leave['start_date']))); ?></td>
                                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($leave['end_date']))); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                                    <td><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo getStatusBadgeClass($leave['status']); ?>">
                                                            <?php echo htmlspecialchars($leave['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($leave['hod_name'] ?? 'N/A'); ?></td>
                                                    <td>
    <a href="leave_slip.php?id=<?php echo $leave['id']; ?>" target="_blank" class="btn btn-info btn-sm">Print/Download Slip</a>
</td>
                                                    <td>
                                                        <?php
                                                        if ($leave['status'] == 'Pending' && !empty($leave['recommender_id'])) {
                                                            echo htmlspecialchars(getEmployeeNameById($con, $leave['recommender_id']));
                                                        } elseif ($leave['status'] == 'Recommended' && !empty($leave['approver_id'])) {
                                                            echo htmlspecialchars(getEmployeeNameById($con, $leave['approver_id']));
                                                        } elseif ($leave['status'] == 'Approved') {
                                                            echo '<span class="text-success">No one (Approved)</span>';
                                                        } elseif ($leave['status'] == 'Rejected') {
                                                            echo '<span class="text-danger">No one (Rejected)</span>';
                                                        } else {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">You have no leave applications yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div> <!-- /.container-fluid -->
            </div> <!-- End of Main Content -->
            <?php include('../footer.php'); ?>
        </div> <!-- End of Content Wrapper -->
    </div> <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal (Optional, if you use it consistently) -->
  

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    <!-- Page level plugins (Optional, if you want datatables) -->
    <!-- <script src="../vendor/datatables/jquery.dataTables.min.js"></script> -->
    <!-- <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script> -->
    <!-- Page level custom scripts (Optional) -->
    <!-- <script src="../js/demo/datatables-demo.js"></script> -->

</body>
</html>

