<?php
require_once 'connection.php';
require_once 'session.php';

// Get unique departments from employees table
$sql = "SELECT DISTINCT department FROM employees ORDER BY department";
$result = mysqli_query($con, $sql);
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Anonymous Feedback</h4>
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

            <form action="../process/feedback_process.php" method="POST">
                <input type="hidden" name="action" value="submit_feedback">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" class="form-control" required>
                        <?php while ($dept = mysqli_fetch_assoc($result)): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>">
                                <?php echo htmlspecialchars($dept['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label>Your Feedback</label>
                    <textarea name="message" class="form-control" rows="5" required
                        placeholder="Share your concerns or suggestions..."></textarea>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>