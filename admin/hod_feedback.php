<?php
require_once 'connection.php';
require_once 'session.php';

// Check if user is HOD
if (!isset($role) || $role != 'admin') {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM anonymous_feedback WHERE department = '$department' ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);
?>

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
                        <th>Feedback</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feedback = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($feedback['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                            <td><?php echo ucfirst($feedback['status']); ?></td>
                            <td>
                                <form action="../process/feedback_process.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback['feedback_id']; ?>">
                                    <select name="status" class="form-control form-control-sm d-inline w-50">
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