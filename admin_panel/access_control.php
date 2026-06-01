<?php
include('session.php');
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_name = mysqli_real_escape_string($con, $_POST['role_name']);
    $department = mysqli_real_escape_string($con, $_POST['department']);

    $view_permissions = isset($_POST['view']) ? $_POST['view'] : array();
    $edit_permissions = isset($_POST['edit']) ? $_POST['edit'] : array();

    $can_view_employees = in_array('employees', $view_permissions) ? 1 : 0;
    $can_view_projects = in_array('projects', $view_permissions) ? 1 : 0;
    $can_view_leaves = in_array('leaves', $view_permissions) ? 1 : 0;
    $can_view_salary = in_array('salary', $view_permissions) ? 1 : 0;
    $can_view_tours = in_array('tours', $view_permissions) ? 1 : 0;

    $can_edit_employees = in_array('employees', $edit_permissions) ? 1 : 0;
    $can_edit_projects = in_array('projects', $edit_permissions) ? 1 : 0;
    $can_edit_leaves = in_array('leaves', $edit_permissions) ? 1 : 0;
    $can_edit_salary = in_array('salary', $edit_permissions) ? 1 : 0;
    $can_edit_tours = in_array('tours', $edit_permissions) ? 1 : 0;

    $query = "INSERT INTO access_rights (role_name, department, 
        can_view_employees, can_view_projects, can_view_leaves, can_view_salary, can_view_tours,
        can_edit_employees, can_edit_projects, can_edit_leaves, can_edit_salary, can_edit_tours,
        created_by) VALUES (
        '$role_name', '$department',
        $can_view_employees, $can_view_projects, $can_view_leaves, $can_view_salary, $can_view_tours,
        $can_edit_employees, $can_edit_projects, $can_edit_leaves, $can_edit_salary, $can_edit_tours,
        {$_SESSION['admin_id']})";

    mysqli_query($con, $query);
}

// Fetch existing roles
$roles = mysqli_query($con, "SELECT * FROM access_rights ORDER BY department, role_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Access Control - HR Matrix</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicons -->
    <link href="../img/favicon.png" rel="icon">
    <link href="../img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Montserrat:300,400,500,700" rel="stylesheet">

    <!-- Libraries CSS Files -->
    <link href="../lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="../lib/animate/animate.min.css" rel="stylesheet">
    <link href="../lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Main Stylesheet File -->
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">

    <style>
        .access-control-container {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .card-header {
            background: #1bb1dc;
            color: white;
            font-weight: bold;
            padding: 15px 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #444;
        }
        .form-check-label {
            color: #666;
        }
        .btn-primary {
            background: #1bb1dc;
            border-color: #1bb1dc;
        }
        .btn-primary:hover {
            background: #1696bd;
            border-color: #1696bd;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header id="header" class="header-scrolled">
        <div class="container-fluid">
            <div id="logo" class="pull-left">
                <h1><a href="../index.php" class="scrollto">HR Matrix</a></h1>
            </div>
            <nav id="nav-menu-container">
                <ul class="nav-menu">
                    <li><a href="index.php">Dashboard</a></li>
                    <li class="menu-active"><a href="access_control.php">Access Control</a></li>
                    <li><a href="/emps/admin_panel/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="access-control-container">
        <div class="container">
            <h2 class="text-center mb-4">Access Control Management</h2>

            <!-- Create New Role Form -->
            <div class="card mb-4">
                <div class="card-header">Create New Role</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Role Name</label>
                            <input type="text" name="role_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" class="form-control" required>
                                <option value="">Select Department</option>
                                <option value="HR">HR</option>
                                <option value="IT">IT</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>View Permissions</h5>
                                <div class="form-check">
                                    <input type="checkbox" name="view[]" value="employees" class="form-check-input">
                                    <label class="form-check-label">Employees</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="view[]" value="projects" class="form-check-input">
                                    <label class="form-check-label">Projects</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="view[]" value="leaves" class="form-check-input">
                                    <label class="form-check-label">Leaves</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="view[]" value="salary" class="form-check-input">
                                    <label class="form-check-label">Salary</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="view[]" value="tours" class="form-check-input">
                                    <label class="form-check-label">Tours</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Edit Permissions</h5>
                                <div class="form-check">
                                    <input type="checkbox" name="edit[]" value="employees" class="form-check-input">
                                    <label class="form-check-label">Employees</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="edit[]" value="projects" class="form-check-input">
                                    <label class="form-check-label">Projects</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="edit[]" value="leaves" class="form-check-input">
                                    <label class="form-check-label">Leaves</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="edit[]" value="salary" class="form-check-input">
                                    <label class="form-check-label">Salary</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="edit[]" value="tours" class="form-check-input">
                                    <label class="form-check-label">Tours</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Create Role</button>
                    </form>
                </div>
            </div>

            <!-- Existing Roles Table -->
            <div class="card">
                <div class="card-header">Existing Roles</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Role Name</th>
                                    <th>Department</th>
                                    <th>View Permissions</th>
                                    <th>Edit Permissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($role = mysqli_fetch_assoc($roles)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                                        <td><?php echo htmlspecialchars($role['department']); ?></td>
                                        <td>
                                            <?php
                                            $views = array();
                                            if ($role['can_view_employees']) $views[] = 'Employees';
                                            if ($role['can_view_projects']) $views[] = 'Projects';
                                            if ($role['can_view_leaves']) $views[] = 'Leaves';
                                            if ($role['can_view_salary']) $views[] = 'Salary';
                                            if ($role['can_view_tours']) $views[] = 'Tours';
                                            echo implode(', ', $views);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $edits = array();
                                            if ($role['can_edit_employees']) $edits[] = 'Employees';
                                            if ($role['can_edit_projects']) $edits[] = 'Projects';
                                            if ($role['can_edit_leaves']) $edits[] = 'Leaves';
                                            if ($role['can_edit_salary']) $edits[] = 'Salary';
                                            if ($role['can_edit_tours']) $edits[] = 'Tours';
                                            echo implode(', ', $edits);
                                            ?>
                                        </td>
                                        <td>
                                            <a href="/emps/admin_panel/edit_role.php?id=<?php echo (int)$role['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="/emps/admin_panel/delete_role.php?id=<?php echo (int)$role['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="../lib/jquery/jquery.min.js"></script>
    <script src="../lib/jquery/jquery-migrate.min.js"></script>
    <script src="../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/superfish/hoverIntent.js"></script>
    <script src="../lib/superfish/superfish.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="../lib/isotope/isotope.pkgd.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>
    <script src="../lib/touchSwipe/jquery.touchSwipe.min.js"></script>
    <script src="../contactform/contactform.js"></script>

    <!-- Template Main Javascript File -->
    <script src="../js/main.js"></script>
</body>
</html>