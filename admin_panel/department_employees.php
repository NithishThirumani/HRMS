<?php
session_start();
include('connection.php');

$dept_id = isset($_GET['dept_id']) ? mysqli_real_escape_string($con, $_GET['dept_id']) : 0;

// Get department details
$dept_sql = "SELECT * FROM departments WHERE id = '$dept_id'";
$dept_result = mysqli_query($con, $dept_sql);
$department = mysqli_fetch_assoc($dept_result);

// Get employees in this department
$emp_sql = "SELECT * FROM employees WHERE department_id = '$dept_id'";
$emp_result = mysqli_query($con, $emp_sql);

// Get unassigned employees
$available_sql = "SELECT * FROM employees WHERE department_id IS NULL OR department_id = 0";
$available_result = mysqli_query($con, $available_sql);

// Handle assigning employee to department
if (isset($_POST['assign_employee'])) {
    $emp_id = mysqli_real_escape_string($con, $_POST['employee_id']);
    $update_sql = "UPDATE employees SET department_id = '$dept_id' WHERE id = '$emp_id'";
    if (mysqli_query($con, $update_sql)) {
        $_SESSION['message'] = "Employee assigned successfully!";
    } else {
        $_SESSION['error'] = "Error assigning employee: " . mysqli_error($con);
    }
    header("Location: department_employees.php?dept_id=" . $dept_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .department-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container mt-4">
        <?php if (!$department): ?>
            <div class="alert alert-danger">
                <h4>Department not found!</h4>
                <p>The requested department does not exist.</p>
                <a href="add_department.php" class="btn btn-primary">Back to Departments</a>
            </div>
        <?php else: ?>
            <div class="department-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Department: <?php echo $department['name']; ?></h2>
                    <a href="add_department.php" class="btn btn-light">Back to Departments</a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['message'];
                    unset($_SESSION['message']); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-primary">Assign New Employee</h4>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Select Employee</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">Choose Employee</option>
                                        <?php while ($emp = mysqli_fetch_assoc($available_result)): ?>
                                            <option value="<?php echo $emp['id']; ?>">
                                                <?php echo $emp['full_name']; ?> (<?php echo $emp['eid']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" name="assign_employee" class="btn btn-primary btn-block">
                                    Assign to Department
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-primary">Current Employees</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>EID</th>
                                            <th>Full Name</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($emp_result) > 0): ?>
                                            <?php while ($employee = mysqli_fetch_assoc($emp_result)): ?>
                                                <tr>
                                                    <td><?php echo $employee['eid']; ?></td>
                                                    <td><?php echo $employee['full_name']; ?></td>
                                                    <td><?php echo $employee['role']; ?></td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="confirmRemove(<?php echo $employee['id']; ?>)">
                                                            <i class="fas fa-user-minus"></i> Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No employees in this department</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?> <!-- Add this closing endif tag -->
    </div>
    <?php
    include_once('footer.php');
    ?>
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
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        function confirmRemove(empId) {
            if (confirm('Are you sure you want to remove this employee from the department?')) {
                window.location.href = `remove_from_department.php?emp_id=${empId}&dept_id=<?php echo $dept_id; ?>`;
            }
        }
    </script>
</body>

</html>