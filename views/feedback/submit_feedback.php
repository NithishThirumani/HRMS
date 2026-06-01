<?php

require_once '../../user_panel/connection.php';
require_once '../../user_panel/session.php';

// Check database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch departments
$sql = "SELECT DISTINCT d.id, d.name 
        FROM departments d 
        ORDER BY d.name ASC";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Store departments in array
$departments = array();
while ($row = mysqli_fetch_assoc($result)) {
    $departments[] = $row['name'];
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
    <title>Submit Anonymous Feedback</title>
    <link href="../../user_panel/img/favicon.png" rel="icon">
    <link href="../../user_panel/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../../user_panel/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../user_panel/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../../user_panel/css/sb-admin-2.css" rel="stylesheet">
    <script src="../../user_panel/js/jquery-3.6.4.min.js"></script>
    <script src="../../user_panel/js/search.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('../../user_panel/sidebar.php'); ?>
    <?php include('../../user_panel/topbar.php'); ?>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Submit Anonymous Feedback</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="../../process/feedback_process.php" method="POST">
                    <input type="hidden" name="action" value="submit_feedback">
                    <div class="mb-3">
                        <label class="form-label">Select Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">Choose Department...</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>">
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Feedback</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                        <small class="form-text text-muted">
                            Your feedback will be submitted anonymously. No personal information will be stored.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </form>
            </div>
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