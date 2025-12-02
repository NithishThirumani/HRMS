<?php
session_start();
require_once('../../connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: /emps/login.php");
    exit();
}

// Get employee details
$username = $_SESSION['user_name'];
$emp_id = $_SESSION['eid'];

// Get employee details from both tables
$query = "SELECT e.id, e.eid, e.full_name 
          FROM employees e 
          INNER JOIN emp_login el ON e.user_name = el.user_name 
          WHERE e.user_name = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /emps/login.php");
    exit();
}

$employee = $result->fetch_assoc();

// Fetch employee's goals
$goals_query = "SELECT 
                id,
                goal_description as title,
                target_date,
                status,
                progress,
                CASE 
                    WHEN status = 'completed' THEN 'Completed'
                    WHEN status = 'in_progress' THEN 'In Progress'
                    ELSE 'Pending'
                END as status_text
                FROM employee_goals
                WHERE emp_id = ?
                ORDER BY created_at DESC";

$stmt = $con->prepare($goals_query);
$stmt->bind_param("i", $emp_id); // Changed to integer binding since emp_id is INT in employee_goals
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Goals</title>
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Goals</h1>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addGoalModal">
                <i class="fas fa-plus"></i> Add New Goal
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">My Goals List</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Goal Description</th>
                                <th>Target Date</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($goals as $goal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($goal['title']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($goal['target_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $goal['status'] === 'completed' ? 'success' : 
                                                ($goal['status'] === 'in_progress' ? 'warning' : 'primary'); 
                                        ?>">
                                            <?php echo $goal['status_text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $goal['progress']; ?>%"
                                                 aria-valuenow="<?php echo $goal['progress']; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo $goal['progress']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="editGoal(<?php echo $goal['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($goal['status'] !== 'completed'): ?>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="markAsCompleted(<?php echo $goal['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Goal Modal -->
    <div class="modal fade" id="addGoalModal" tabindex="-1" role="dialog" aria-labelledby="addGoalModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGoalModalLabel">Add New Goal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="process/add_goal.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="goal_description">Goal Description</label>
                            <textarea class="form-control" id="goal_description" name="goal_description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="target_date">Target Date</label>
                            <input type="date" class="form-control" id="target_date" name="target_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../footer.php'); ?>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });

        function editGoal(goalId) {
            window.location.href = 'edit_goal.php?id=' + goalId;
        }

        function markAsCompleted(goalId) {
            if (confirm('Are you sure you want to mark this goal as completed?')) {
                window.location.href = 'process/update_goal_status.php?id=' + goalId + '&status=completed';
            }
        }
    </script>
</body>

</html>