<?php
include('session.php');
include('connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: /emps/admin_panel/access_control.php');
    exit();
}

// Handle update
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

    $stmt = $con->prepare("UPDATE access_rights SET role_name=?, department=?, can_view_employees=?, can_view_projects=?, can_view_leaves=?, can_view_salary=?, can_view_tours=?, can_edit_employees=?, can_edit_projects=?, can_edit_leaves=?, can_edit_salary=?, can_edit_tours=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('ssiiiiiiiiiii', $role_name, $department, $can_view_employees, $can_view_projects, $can_view_leaves, $can_view_salary, $can_view_tours, $can_edit_employees, $can_edit_projects, $can_edit_leaves, $can_edit_salary, $can_edit_tours, $id);
        $stmt->execute();
    }

    header('Location: /emps/admin_panel/access_control.php');
    exit();
}

// Fetch role
$stmt = $con->prepare("SELECT * FROM access_rights WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: /emps/admin_panel/access_control.php');
    exit();
}
$role = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Role - Access Control</title>
    <link href="../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Edit Role</h2>
    <form method="post">
        <div>
            <label>Role Name</label>
            <input type="text" name="role_name" value="<?php echo htmlspecialchars($role['role_name']); ?>" required>
        </div>
        <div>
            <label>Department</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($role['department']); ?>" required>
        </div>
        <div>
            <h4>View Permissions</h4>
            <label><input type="checkbox" name="view[]" value="employees" <?php if ($role['can_view_employees']) echo 'checked'; ?>> Employees</label>
            <label><input type="checkbox" name="view[]" value="projects" <?php if ($role['can_view_projects']) echo 'checked'; ?>> Projects</label>
            <label><input type="checkbox" name="view[]" value="leaves" <?php if ($role['can_view_leaves']) echo 'checked'; ?>> Leaves</label>
            <label><input type="checkbox" name="view[]" value="salary" <?php if ($role['can_view_salary']) echo 'checked'; ?>> Salary</label>
            <label><input type="checkbox" name="view[]" value="tours" <?php if ($role['can_view_tours']) echo 'checked'; ?>> Tours</label>
        </div>
        <div>
            <h4>Edit Permissions</h4>
            <label><input type="checkbox" name="edit[]" value="employees" <?php if ($role['can_edit_employees']) echo 'checked'; ?>> Employees</label>
            <label><input type="checkbox" name="edit[]" value="projects" <?php if ($role['can_edit_projects']) echo 'checked'; ?>> Projects</label>
            <label><input type="checkbox" name="edit[]" value="leaves" <?php if ($role['can_edit_leaves']) echo 'checked'; ?>> Leaves</label>
            <label><input type="checkbox" name="edit[]" value="salary" <?php if ($role['can_edit_salary']) echo 'checked'; ?>> Salary</label>
            <label><input type="checkbox" name="edit[]" value="tours" <?php if ($role['can_edit_tours']) echo 'checked'; ?>> Tours</label>
        </div>
        <button type="submit">Save</button>
    </form>
</div>
</body></html>
