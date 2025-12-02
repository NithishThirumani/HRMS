<?php
include('session.php');
include('connection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hierarchy'])) {
    foreach ($_POST['employee_id'] as $index => $emp_id) {
        $department_id = $_POST['department_id'][$index];
        $recommenders = isset($_POST['recommender'][$emp_id]) ? $_POST['recommender'][$emp_id] : [];
        $approver = isset($_POST['approver'][$emp_id]) ? $_POST['approver'][$emp_id] : null;

        // Remove old recommenders
        $del = $con->prepare("DELETE FROM leave_hierarchy WHERE employee_id=? AND type='recommender'");
        $del->bind_param('i', $emp_id);
        $del->execute();
        // Insert new recommenders
        foreach ($recommenders as $rec_id) {
            $ins = $con->prepare("INSERT INTO leave_hierarchy (employee_id, recommender_id, department_id, type) VALUES (?, ?, ?, 'recommender')");
            $ins->bind_param('iii', $emp_id, $rec_id, $department_id);
            $ins->execute();
        }
        // Remove old approver
        $del2 = $con->prepare("DELETE FROM leave_hierarchy WHERE employee_id=? AND type='approver'");
        $del2->bind_param('i', $emp_id);
        $del2->execute();
        // Insert new approver
        if ($approver) {
            $ins2 = $con->prepare("INSERT INTO leave_hierarchy (employee_id, approver_id, department_id, type) VALUES (?, ?, ?, 'approver')");
            $ins2->bind_param('iii', $emp_id, $approver, $department_id);
            $ins2->execute();
        }
    }
    $_SESSION['success'] = 'Leave hierarchy updated!';
    header('Location: leave_hierarchy.php');
    exit();
}

// Fetch all departments
$departments = mysqli_query($con, "SELECT * FROM departments ORDER BY name");
// Fetch all employees
$employees = [];
$emp_res = mysqli_query($con, "SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.emp_left_org = 0 ORDER BY d.name, e.full_name");
while ($row = mysqli_fetch_assoc($emp_res)) {
    $employees[$row['department_id']][] = $row;
    $all_employees[] = $row;
}
// Fetch all current hierarchy assignments
$hierarchy = [];
$res = mysqli_query($con, "SELECT * FROM leave_hierarchy");
while ($row = mysqli_fetch_assoc($res)) {
    if ($row['type'] == 'recommender') {
        $hierarchy[$row['employee_id']]['recommender'][] = $row['recommender_id'];
    } else if ($row['type'] == 'approver') {
        $hierarchy[$row['employee_id']]['approver'] = $row['approver_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Hierarchy Assignment</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container { width: 100% !important; }
        .table th, .table td { vertical-align: middle !important; }
    </style>
</head>
<body>
<?php include('sidebar.php'); ?>
<?php include('header.php'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Leave Hierarchy Assignment</h1>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="save_hierarchy" value="1">
        <?php foreach ($departments as $dept): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <strong><?php echo htmlspecialchars($dept['name']); ?></strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th>Recommender(s)</th>
                                    <th>Approver</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($employees[$dept['id']])): foreach ($employees[$dept['id']] as $emp): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                        <input type="hidden" name="employee_id[]" value="<?php echo $emp['id']; ?>">
                                        <input type="hidden" name="department_id[]" value="<?php echo $dept['id']; ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['designation'] ?? ''); ?></td>
                                    <td>
                                        <select name="recommender[<?php echo $emp['id']; ?>][]" class="form-control select2" multiple>
                                            <?php foreach ($all_employees as $rec): ?>
                                                <?php if ($rec['id'] != $emp['id']): ?>
                                                <option value="<?php echo $rec['id']; ?>" <?php echo (isset($hierarchy[$emp['id']]['recommender']) && in_array($rec['id'], $hierarchy[$emp['id']]['recommender'])) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rec['full_name'] . ' (' . ($rec['department_name'] ?? 'No Department') . ')'); ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="approver[<?php echo $emp['id']; ?>]" class="form-control select2">
                                            <option value="">-- Select Approver --</option>
                                            <?php foreach ($all_employees as $app): ?>
                                                <?php if ($app['id'] != $emp['id']): ?>
                                                <option value="<?php echo $app['id']; ?>" <?php echo (isset($hierarchy[$emp['id']]['approver']) && $hierarchy[$emp['id']]['approver'] == $app['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($app['full_name'] . ' (' . ($app['department_name'] ?? 'No Department') . ')'); ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="text-center">No employees in this department.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success">Save Hierarchy</button>
    </form>
</div>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
</body>
</html> 