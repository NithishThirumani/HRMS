<?php
include('session.php');
include('connection.php');

// Handle HOD assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_hod'])) {
    $department_id = $_POST['department_id'];
    $employee_id = $_POST['employee_id'];
    
    // Get department name
    $dept_query = "SELECT name FROM departments WHERE id = ?";
    $stmt = $con->prepare($dept_query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $dept_result = $stmt->get_result();
    $department = $dept_result->fetch_assoc();
    
    // Get employee details
    $emp_query = "SELECT eid, full_name, email FROM employees WHERE id = ?";
    $stmt = $con->prepare($emp_query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $emp_result = $stmt->get_result();
    $employee = $emp_result->fetch_assoc();
    
    if ($department && $employee) {
        // Check if department already has an HOD
        $check_query = "SELECT id FROM department_heads WHERE dept_name = ?";
        $stmt = $con->prepare($check_query);
        $stmt->bind_param("s", $department['name']);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing HOD
            $update_query = "UPDATE department_heads SET 
                           head_id = ?, 
                           user_name = ?, 
                           head_name = ?, 
                           head_email = ? 
                           WHERE dept_name = ?";
            $stmt = $con->prepare($update_query);
            $stmt->bind_param("sssss", 
                $employee['eid'],
                $employee['eid'],
                $employee['full_name'],
                $employee['email'],
                $department['name']
            );
        } else {
            // Insert new HOD
            $insert_query = "INSERT INTO department_heads 
                           (dept_name, head_id, user_name, password, head_name, head_email) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($insert_query);
            $default_password = password_hash('123456', PASSWORD_DEFAULT); // Default password
            $stmt->bind_param("ssssss", 
                $department['name'],
                $employee['eid'],
                $employee['eid'],
                $default_password,
                $employee['full_name'],
                $employee['email']
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "HOD assigned successfully!";
        } else {
            $_SESSION['error'] = "Error assigning HOD: " . $stmt->error;
        }
    }
    
    header("Location: manage_hods.php");
    exit();
}

// Get all departments
$dept_query = "SELECT * FROM departments ORDER BY name";
$dept_result = mysqli_query($con, $dept_query);

// Get all potential HODs (employees with HOD role or designation)
// Get all potential HODs (employees with HOD role or designation)
$hod_query = "SELECT id, eid, full_name, email, designation 
              FROM employees 
             WHERE role = 'HOD' OR designation LIKE '%Director%' OR designation LIKE '%Manager%'
              ORDER BY full_name";
$hod_result = mysqli_query($con, $hod_query);

// Fetch all HODs into an array
$all_hods = [];
while ($row = mysqli_fetch_assoc($hod_result)) {
    $all_hods[] = $row;
}

// Get current HOD assignments
$current_hods_query = "SELECT dh.*, d.id as department_id 
                      FROM department_heads dh 
                      JOIN departments d ON dh.dept_name = d.name";
$current_hods_result = mysqli_query($con, $current_hods_query);
$current_hods = [];
while ($row = mysqli_fetch_assoc($current_hods_result)) {
    $current_hods[$row['department_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage HODs</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Department Heads</h1>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Department Heads</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Current HOD</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                    <td>
                                        <?php 
                                        if (isset($current_hods[$dept['id']])) {
                                            echo htmlspecialchars($current_hods[$dept['id']]['head_name']);
                                        } else {
                                            echo '<span class="text-danger">Not Assigned</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (isset($current_hods[$dept['id']])) {
                                            echo htmlspecialchars($current_hods[$dept['id']]['head_email']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#assignHodModal<?php echo $dept['id']; ?>">
                                            <i class="fas fa-user-plus"></i> Assign HOD
                                        </button>
                                    </td>
                                </tr>

                                <!-- Assign HOD Modal -->
                                <div class="modal fade" id="assignHodModal<?php echo $dept['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Assign HOD - <?php echo htmlspecialchars($dept['name']); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="department_id" value="<?php echo $dept['id']; ?>">
                                                    <div class="form-group">
                                                        <label>Select HOD</label>
                                                        <select name="employee_id" class="form-control" required>
    <option value="">Select Employee</option>
    <?php foreach ($all_hods as $hod): ?>
        <option value="<?php echo $hod['id']; ?>">
            <?php echo htmlspecialchars($hod['full_name'] . ' (' . $hod['designation'] . ')'); ?>
        </option>
    <?php endforeach; ?>
</select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="assign_hod" class="btn btn-primary">Assign HOD</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
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
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>
</body>
</html> 