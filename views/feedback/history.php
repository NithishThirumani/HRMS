<?php
// Use absolute paths for consistency
require_once $_SERVER['DOCUMENT_ROOT'] . '/emps/user_panel/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/emps/user_panel/session.php';

// Check connection first
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch feedback directly using mysqli
$sql = "SELECT * FROM anonymous_feedback ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Store feedback in array
$feedbacks = array();
while ($row = mysqli_fetch_assoc($result)) {
    $feedbacks[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Feedback History</title>
    <link href="../../user_panel/img/favicon.png" rel="icon">
    <link href="../../user_panel/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../../user_panel/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../user_panel/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../../user_panel/css/sb-admin-2.css" rel="stylesheet">
    <script src="../../user_panel/js/jquery-3.6.4.min.js"></script>
    <script src="../../user_panel/js/search.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<body>
    <?php include('../../user_panel/sidebar.php'); ?>
    <?php include('../../user_panel/topbar.php'); ?>


    <div class="container mt-4">
        <h2>Feedback History</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Admin Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?= $feedback['feedback_id'] ?></td>
                            <td><?= htmlspecialchars($feedback['department']) ?></td>
                            <td><?= htmlspecialchars($feedback['message']) ?></td>
                            <td>
                                <span class="badge badge-<?=
                                    $feedback['status'] === 'Resolved' ? 'success' :
                                    ($feedback['status'] === 'Pending' ? 'warning' : 'secondary') ?>">
                                    <?= $feedback['status'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($feedback['created_at'])) ?></td>
                            <td><?= htmlspecialchars($feedback['admin_comment']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <?php
    include_once('../../user_panel/footer.php');
    ?>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
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
                    <a class="btn btn-success" href="/emps/user_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../../user_panel/vendor/jquery/jquery.min.js"></script>
    <script src="../../user_panel/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../user_panel/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../user_panel/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../../user_panel/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../user_panel/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../user_panel/js/demo/datatables-demo.js"></script>

    <script src="../../user_panel/js/datetime.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>