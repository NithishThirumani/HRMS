<?php
require_once 'connection.php';
require_once 'session.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is HOD (session.php already handles this)
// The session.php file will redirect to login if not properly authenticated

$role = $_SESSION['role'];  // Get role from session

// Debug connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get all feedback entries
$sql = "SELECT * FROM anonymous_feedback ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);

// Check for query errors
if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Check if there are any results
if (mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-info'>No feedback entries found.</div>";
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Update Feedback</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Department Feedback Management</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <table class="table">
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
                        <?php while ($feedback = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($feedback['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($feedback['department']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                                <td><?php echo ucfirst($feedback['status']); ?></td>
                                <td>
                                    <textarea name="admin_comment" class="form-control form-control-sm"
                                        rows="2"><?php echo htmlspecialchars($feedback['admin_comment'] ?? ''); ?></textarea>
                                </td>
                                <td>
                                    <form action="../process/feedback_process.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="feedback_id"
                                            value="<?php echo $feedback['feedback_id']; ?>">
                                        <select name="status" class="form-control form-control-sm d-inline w-50">
                                            <option value="pending" <?php echo $feedback['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_review" <?php echo $feedback['status'] == 'in_review' ? 'selected' : ''; ?>>In Review</option>
                                            <option value="resolved" <?php echo $feedback['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                        <input type="hidden" name="admin_comment" class="admin-comment-input">
                                        <button type="submit" class="btn btn-sm btn-primary update-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
</body>

</html>