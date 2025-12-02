<?php
require_once 'connection.php';
require_once 'session.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all feedback entries with status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql = "SELECT * FROM anonymous_feedback";
if ($status_filter != 'all') {
    $sql .= " WHERE status = '" . mysqli_real_escape_string($con, $status_filter) . "'";
}
$sql .= " ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);
?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Feedback History</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Feedback History</h6>
                <div class="btn-group">
                    <a href="?status=all"
                        class="btn btn-sm btn-<?php echo $status_filter == 'all' ? 'primary' : 'outline-primary'; ?>">All</a>
                    <a href="?status=pending"
                        class="btn btn-sm btn-<?php echo $status_filter == 'pending' ? 'warning' : 'outline-warning'; ?>">Pending</a>
                    <a href="?status=in_review"
                        class="btn btn-sm btn-<?php echo $status_filter == 'in_review' ? 'info' : 'outline-info'; ?>">In
                        Review</a>
                    <a href="?status=resolved"
                        class="btn btn-sm btn-<?php echo $status_filter == 'resolved' ? 'success' : 'outline-success'; ?>">Resolved</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="feedbackTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Department</th>
                                <th>Feedback</th>
                                <th>Status</th>
                                <th>Admin Comments</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($feedback = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($feedback['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['department']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                        echo $feedback['status'] == 'pending' ? 'warning' :
                                            ($feedback['status'] == 'in_review' ? 'info' : 'success');
                                        ?>">
                                            <?php echo ucfirst($feedback['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($feedback['admin_comment'] ?? ''); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($feedback['updated_at'] ?? $feedback['created_at'])); ?>
                                    </td>
                                    <td>
                                        <form action="../process/feedback_process.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_feedback">
                                            <input type="hidden" name="feedback_id"
                                                value="<?php echo $feedback['feedback_id']; ?>">
                                            <textarea name="admin_comment"
                                                class="form-control form-control-sm mb-2"><?php echo htmlspecialchars($feedback['admin_comment'] ?? ''); ?></textarea>
                                            <select name="status" class="form-control form-control-sm mb-2">
                                                <option value="pending" <?php echo $feedback['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_review" <?php echo $feedback['status'] == 'in_review' ? 'selected' : ''; ?>>In Review</option>
                                                <option value="resolved" <?php echo $feedback['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#feedbackTable').DataTable({
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>

</html>