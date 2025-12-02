<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
include('connection.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Department
if (isset($_POST['add_department'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $department_head_role = mysqli_real_escape_string($con, $_POST['department_head_role']);
    $parent_department_id = !empty($_POST['parent_department_id']) ? mysqli_real_escape_string($con, $_POST['parent_department_id']) : 'NULL';
    $annual_leave_days = !empty($_POST['annual_leave_days']) ? mysqli_real_escape_string($con, $_POST['annual_leave_days']) : '30';

    $sql = "INSERT INTO departments (name, department_head_role, parent_department_id, annual_leave_days) 
            VALUES ('$name', '$department_head_role', $parent_department_id, $annual_leave_days)";

    if (mysqli_query($con, $sql)) {
        $_SESSION['message'] = "Department added successfully!";
    } else {
        $_SESSION['error'] = "Error adding department: " . mysqli_error($con);
    }
    header("Location: add_department.php");
    exit();
}

// Handle Edit Department
if (isset($_POST['edit_department'])) {
    $id = mysqli_real_escape_string($con, $_POST['department_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $department_head_role = mysqli_real_escape_string($con, $_POST['department_head_role']);
    $parent_department_id = !empty($_POST['parent_department_id']) ? mysqli_real_escape_string($con, $_POST['parent_department_id']) : 'NULL';
    $annual_leave_days = !empty($_POST['annual_leave_days']) ? mysqli_real_escape_string($con, $_POST['annual_leave_days']) : '30';

    $update_sql = "UPDATE departments 
                   SET name = '$name', 
                       department_head_role = '$department_head_role',
                       parent_department_id = $parent_department_id,
                       annual_leave_days = $annual_leave_days 
                   WHERE id = '$id'";
    
    if (mysqli_query($con, $update_sql)) {
        $_SESSION['message'] = "Department updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating department: " . mysqli_error($con);
    }
    header("Location: add_department.php");
    exit();
}

// Handle Delete Department
if (isset($_POST['delete_department'])) {
    $id = mysqli_real_escape_string($con, $_POST['department_id']);
    
    // Check if department has employees
    $check_sql = "SELECT COUNT(*) as count FROM employees WHERE department_id = '$id'";
    $check_result = mysqli_query($con, $check_sql);
    $count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete department: It has " . $count . " employee(s) assigned.";
    } else {
        $delete_sql = "DELETE FROM departments WHERE id = '$id'";
        if (mysqli_query($con, $delete_sql)) {
            $_SESSION['message'] = "Department deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting department: " . mysqli_error($con);
        }
    }
    header("Location: add_department.php");
    exit();
}

// Fetch departments with employee count
$sql = "SELECT 
        d.id,
        d.name,
        d.department_head_role,
        d.parent_department_id,
        d.annual_leave_days,
        pd.name as parent_name,
        COUNT(DISTINCT e.id) as employee_count 
        FROM departments d 
        LEFT JOIN departments pd ON d.parent_department_id = pd.id
        LEFT JOIN employees e ON d.id = e.department_id
        GROUP BY d.id, d.name, d.department_head_role, d.parent_department_id, d.annual_leave_days
        ORDER BY d.name ASC";

$result = mysqli_query($con, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

$departments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $departments[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management - HR Matrix</title>
    
    <!-- Include CSS files -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        .department-card {
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border-radius: 0.35rem;
        }
        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .action-buttons .btn {
            margin: 0 5px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #fff;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #4e73df;
        }
        .add-dept-btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .department-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        .department-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .stat-card {
            flex: 1;
            margin: 0 0.5rem;
            padding: 1rem;
            background: #fff;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: #4e73df;
        }
        .stat-card p {
            margin: 0.5rem 0 0;
            color: #858796;
            font-size: 0.875rem;
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include('header.php'); ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1>Department Management</h1>
                        <button class="btn btn-primary add-dept-btn" data-toggle="modal" data-target="#addDepartmentModal">
                            <i class="fas fa-plus"></i> Add New Department
                        </button>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']); 
                            ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']); 
                            ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <!-- Department Statistics -->
                    <div class="department-stats">
                        <div class="stat-card">
                            <h3><?php echo count($departments); ?></h3>
                            <p>Total Departments</p>
                        </div>
                        <?php
                        $total_employees = 0;
                        foreach ($departments as $dept) {
                            $total_employees += isset($dept['employee_count']) ? (int)$dept['employee_count'] : 0;
                        }
                        ?>
                        <div class="stat-card">
                            <h3><?php echo $total_employees; ?></h3>
                            <p>Total Employees</p>
                        </div>
                    </div>

                    <!-- Department Cards -->
                    <div class="department-grid">
                        <?php 
                        if (!empty($departments)):
                            foreach ($departments as $row): 
                        ?>
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo htmlspecialchars($row['name'] ?? 'Unnamed Department'); ?>
                                    </h6>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-info" data-toggle="modal" 
                                                data-target="#editDepartmentModal<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" 
                                                data-target="#deleteDepartmentModal<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Department Head
                                    </div>
                                    <div class="h6 mb-3 font-weight-bold text-gray-800">
                                        <?php echo htmlspecialchars($row['department_head_role'] ?? 'Not Assigned'); ?>
                                    </div>
                                    
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Parent Department
                                    </div>
                                    <div class="h6 mb-3 font-weight-bold text-gray-800">
                                        <?php echo htmlspecialchars($row['parent_name'] ?? 'None'); ?>
                                    </div>

                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Employees
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h6 mb-0 mr-3 font-weight-bold text-gray-800">
                                                        <?php echo isset($row['employee_count']) ? (int)$row['employee_count'] : 0; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Annual Leave Days
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?php echo htmlspecialchars($row['annual_leave_days'] ?? '30'); ?> days
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Department Modal -->
                            <div class="modal fade" id="editDepartmentModal<?php echo htmlspecialchars($row['id'] ?? ''); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Department</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                                
                                                <div class="form-group">
                                                    <label>Department Name</label>
                                                    <input type="text" class="form-control" name="name" 
                                                           value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Department Head Role</label>
                                                    <input type="text" class="form-control" name="department_head_role" 
                                                           value="<?php echo htmlspecialchars($row['department_head_role'] ?? ''); ?>">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Parent Department</label>
                                                    <select class="form-control" name="parent_department_id">
                                                        <option value="">None</option>
                                                        <?php 
                                                        foreach ($departments as $dept) {
                                                            if ($dept['id'] != $row['id']) {
                                                                $selected = ($dept['id'] == $row['parent_department_id']) ? 'selected' : '';
                                                                echo "<option value='" . htmlspecialchars($dept['id']) . "' $selected>" . 
                                                                     htmlspecialchars($dept['name']) . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Annual Leave Days</label>
                                                    <input type="number" class="form-control" name="annual_leave_days" 
                                                           value="<?php echo htmlspecialchars($row['annual_leave_days'] ?? '30'); ?>" 
                                                           min="0" max="365">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_department" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Department Modal -->
                            <div class="modal fade" id="deleteDepartmentModal<?php echo htmlspecialchars($row['id'] ?? ''); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Department</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($row['name'] ?? 'this department'); ?></strong>?</p>
                                            <?php if (isset($row['employee_count']) && $row['employee_count'] > 0): ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    This department has <?php echo (int)$row['employee_count']; ?> employees. 
                                                    Please reassign them before deleting.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <?php if (!isset($row['employee_count']) || $row['employee_count'] == 0): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($row['id'] ?? ''); ?>">
                                                    <button type="submit" name="delete_department" class="btn btn-danger">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No departments found. Use the "Add New Department" button to create one.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright © HR Matrix 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Department Head Role</label>
                            <input type="text" class="form-control" name="department_head_role">
                        </div>
                        
                        <div class="form-group">
                            <label>Parent Department</label>
                            <select class="form-control" name="parent_department_id">
                                <option value="">None</option>
                                <?php 
                                mysqli_data_seek($result, 0);
                                while ($dept = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $dept['id'] . "'>" . 
                                         htmlspecialchars($dept['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Annual Leave Days</label>
                            <input type="number" class="form-control" name="annual_leave_days" 
                                   value="30" min="0" max="365">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Custom scripts -->
    <script>
        $(document).ready(function() {
            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Prevent nested modal issues
            $('.modal').on('show.bs.modal', function () {
                var zIndex = 1040 + (10 * $('.modal:visible').length);
                $(this).css('z-index', zIndex);
                setTimeout(function() {
                    $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                }, 0);
            });
        });
    </script>
</body>
</html>