<?php
session_start();
include('connection.php');

// Verify database connection
if (!isset($con) || !$con) {
    die("Database connection failed");
}

// Verify admin session
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../login.php");
    exit();
}

// Handle password reset requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_selected'])) {
    $success_count = 0;
    $error_count = 0;
    
    foreach ($_POST['selected_employees'] as $emp_id) {
        $emp_id = mysqli_real_escape_string($con, $emp_id);
        $new_password = '123456'; // Simple default password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in emp_login table
        $update_query = "UPDATE emp_login SET password = ? WHERE emp_id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $emp_id);
            if (mysqli_stmt_execute($stmt)) {
                $success_count++;
            } else {
                $error_count++;
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    if ($success_count > 0) {
        echo "<script>alert('Passwords reset successfully to 123456');</script>";
    }
}

// Debug database connection
$debug = "SELECT COUNT(*) as count FROM employees";
$debug_result = mysqli_query($con, $debug);
$count = mysqli_fetch_assoc($debug_result)['count'];

// Fetch employees with login status
$query = "SELECT e.*, CASE WHEN el.emp_id IS NOT NULL THEN 'Yes' ELSE 'No' END as has_login 
          FROM employees e 
          LEFT JOIN emp_login el ON e.eid = el.emp_id 
          ORDER BY e.full_name";
$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reset Employee Passwords</title>
    
    <!-- Custom fonts -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        .btn-reset {
            min-width: 100px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Reset Employee Passwords</h1>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Employee List (Total: <?php echo $count; ?>)</h6>
                            <button type="button" class="btn btn-success" onclick="resetSelected()">
                                Reset Selected Passwords
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="resetForm" method="POST">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="employeeTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                                                <th>EID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Has Login</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) { 
                                            ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="selected_employees[]" value="<?php echo htmlspecialchars($row['eid']); ?>" class="employee-checkbox">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['eid']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['has_login']); ?></td>
                                                </tr>
                                            <?php 
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>No employees found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <input type="hidden" name="reset_selected" value="1">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('footer.php'); ?>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#employeeTable').DataTable({
            order: [[2, 'asc']], // Sort by name by default
            pageLength: 25
        });
    });

    function toggleAll(source) {
        const checkboxes = document.getElementsByClassName('employee-checkbox');
        for (let checkbox of checkboxes) {
            checkbox.checked = source.checked;
        }
    }

    function resetSelected() {
        const checkboxes = document.getElementsByClassName('employee-checkbox');
        let selected = false;
        for (let checkbox of checkboxes) {
            if (checkbox.checked) {
                selected = true;
                break;
            }
        }

        if (!selected) {
            alert('Please select at least one employee.');
            return;
        }

        if (confirm('Are you sure you want to reset passwords for the selected employees? The new password will be "123456".')) {
            document.getElementById('resetForm').submit();
        }
    }
    </script>
</body>
</html> 