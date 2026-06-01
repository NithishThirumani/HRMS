<?php
require_once 'connection.php';
require_once 'session.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: /emps/login.php');
    exit();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $_SESSION['admin_id'];
}
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
    header('Location: /emps/login.php');
    exit();
}

$sql = 'SELECT * FROM anonymous_feedback ORDER BY created_at DESC';
$feedbackResult = mysqli_query($con, $sql);

if (!$feedbackResult) {
    die('Query failed: ' . mysqli_error($con));
}

$feedbacks = [];
while ($row = mysqli_fetch_assoc($feedbackResult)) {
    $feedbacks[] = $row;
}
mysqli_free_result($feedbackResult);

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Department Feedback Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid" style="padding-left: 250px;">
        <div class="card shadow mb-4 mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Department Feedback Management</h6>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (count($feedbacks) === 0): ?>
                    <div class="alert alert-info">No feedback entries found.</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered" id="feedbackTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Department</th>
                                <th>Feedback</th>
                                <th>Status</th>
                                <th>Admin Comments</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($feedback['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['department']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($feedback['status'])); ?></td>
                                    <td>
                                        <textarea form="feedback-form-<?php echo (int) $feedback['feedback_id']; ?>"
                                            name="admin_comment" class="form-control form-control-sm"
                                            rows="2"><?php echo htmlspecialchars($feedback['admin_comment'] ?? ''); ?></textarea>
                                    </td>
                                    <td>
                                        <form id="feedback-form-<?php echo (int) $feedback['feedback_id']; ?>"
                                            action="../process/feedback_process.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="feedback_id"
                                                value="<?php echo (int) $feedback['feedback_id']; ?>">
                                            <select name="status" class="form-control form-control-sm mb-2">
                                                <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_review" <?php echo $feedback['status'] === 'in_review' ? 'selected' : ''; ?>>In Review</option>
                                                <option value="resolved" <?php echo $feedback['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#feedbackTable').DataTable({ order: [[0, 'desc']] });
        });
    </script>
</body>

</html>
