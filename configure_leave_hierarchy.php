<?php
include_once 'config/config.php';
include_once 'connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Leave Hierarchy Configuration Tool</h2>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_hierarchy':
                $employee_id = $_POST['employee_id'];
                $department_id = $_POST['department_id'];
                $type = $_POST['type'];
                $recommender_id = ($type === 'recommender') ? $_POST['person_id'] : null;
                $approver_id = ($type === 'approver') ? $_POST['person_id'] : null;
                
                // Check if entry already exists
                $check_query = "SELECT id FROM leave_hierarchy 
                               WHERE employee_id = ? AND department_id = ? AND type = ?";
                $check_stmt = $con->prepare($check_query);
                $check_stmt->bind_param("iis", $employee_id, $department_id, $type);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing entry
                    $update_query = "UPDATE leave_hierarchy 
                                    SET recommender_id = ?, approver_id = ? 
                                    WHERE employee_id = ? AND department_id = ? AND type = ?";
                    $update_stmt = $con->prepare($update_query);
                    $update_stmt->bind_param("iiiss", $recommender_id, $approver_id, $employee_id, $department_id, $type);
                    $update_stmt->execute();
                    echo "<p style='color: green;'>Updated existing hierarchy entry.</p>";
                } else {
                    // Insert new entry
                    $insert_query = "INSERT INTO leave_hierarchy 
                                    (employee_id, department_id, type, recommender_id, approver_id) 
                                    VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $con->prepare($insert_query);
                    $insert_stmt->bind_param("iissi", $employee_id, $department_id, $type, $recommender_id, $approver_id);
                    $insert_stmt->execute();
                    echo "<p style='color: green;'>Added new hierarchy entry.</p>";
                }
                break;
                
            case 'delete_hierarchy':
                $hierarchy_id = $_POST['hierarchy_id'];
                $delete_query = "DELETE FROM leave_hierarchy WHERE id = ?";
                $delete_stmt = $con->prepare($delete_query);
                $delete_stmt->bind_param("i", $hierarchy_id);
                $delete_stmt->execute();
                echo "<p style='color: green;'>Deleted hierarchy entry.</p>";
                break;
        }
    }
}

// Fetch employees
$employees_query = "SELECT id, eid, full_name, email, department_id, role 
                   FROM employees 
                   WHERE status = 'active' 
                   ORDER BY full_name";
$employees_result = $con->query($employees_query);

// Fetch departments
$departments_query = "SELECT id, name FROM departments ORDER BY name";
$departments_result = $con->query($departments_query);

// Fetch current hierarchy
$hierarchy_query = "SELECT lh.*, 
                    e1.full_name as employee_name, e1.eid as employee_eid,
                    e2.full_name as recommender_name, e2.eid as recommender_eid,
                    e3.full_name as approver_name, e3.eid as approver_eid,
                    d.name as department_name
                    FROM leave_hierarchy lh
                    LEFT JOIN employees e1 ON lh.employee_id = e1.id
                    LEFT JOIN employees e2 ON lh.recommender_id = e2.id
                    LEFT JOIN employees e3 ON lh.approver_id = e3.id
                    LEFT JOIN departments d ON lh.department_id = d.id
                    ORDER BY e1.full_name, lh.type";
$hierarchy_result = $con->query($hierarchy_query);

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .form-group { margin: 10px 0; }
    label { display: inline-block; width: 150px; font-weight: bold; }
    select, input { padding: 5px; width: 200px; }
    button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
    button:hover { background: #0056b3; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f8f9fa; }
    .delete-btn { background: #dc3545; padding: 3px 8px; font-size: 12px; }
    .delete-btn:hover { background: #c82333; }
</style>

<div class="container">
    <!-- Add New Hierarchy Entry -->
    <div class="section">
        <h3>Add/Update Leave Hierarchy</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_hierarchy">
            
            <div class="form-group">
                <label>Employee:</label>
                <select name="employee_id" required>
                    <option value="">Select Employee</option>
                    <?php while ($emp = $employees_result->fetch_assoc()): ?>
                        <option value="<?= $emp['id'] ?>">
                            <?= htmlspecialchars($emp['full_name']) ?> (<?= $emp['eid'] ?>) - <?= $emp['role'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Department:</label>
                <select name="department_id" required>
                    <option value="">Select Department</option>
                    <?php while ($dept = $departments_result->fetch_assoc()): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Type:</label>
                <select name="type" required onchange="togglePersonField()">
                    <option value="">Select Type</option>
                    <option value="recommender">Recommender</option>
                    <option value="approver">Approver</option>
                </select>
            </div>
            
            <div class="form-group" id="person_field" style="display: none;">
                <label id="person_label">Person:</label>
                <select name="person_id" required>
                    <option value="">Select Person</option>
                    <?php 
                    $employees_result->data_seek(0); // Reset result pointer
                    while ($emp = $employees_result->fetch_assoc()): 
                    ?>
                        <option value="<?= $emp['id'] ?>">
                            <?= htmlspecialchars($emp['full_name']) ?> (<?= $emp['eid'] ?>) - <?= $emp['role'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit">Add/Update Hierarchy</button>
        </form>
    </div>

    <!-- Current Hierarchy -->
    <div class="section">
        <h3>Current Leave Hierarchy</h3>
        <?php if ($hierarchy_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Recommender</th>
                        <th>Approver</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hierarchy = $hierarchy_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($hierarchy['employee_name']) ?> (<?= $hierarchy['employee_eid'] ?>)</td>
                            <td><?= htmlspecialchars($hierarchy['department_name']) ?></td>
                            <td><?= ucfirst($hierarchy['type']) ?></td>
                            <td>
                                <?php if ($hierarchy['recommender_name']): ?>
                                    <?= htmlspecialchars($hierarchy['recommender_name']) ?> (<?= $hierarchy['recommender_eid'] ?>)
                                <?php else: ?>
                                    <em>None</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hierarchy['approver_name']): ?>
                                    <?= htmlspecialchars($hierarchy['approver_name']) ?> (<?= $hierarchy['approver_eid'] ?>)
                                <?php else: ?>
                                    <em>None</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_hierarchy">
                                    <input type="hidden" name="hierarchy_id" value="<?= $hierarchy['id'] ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hierarchy entries found.</p>
        <?php endif; ?>
    </div>

    <!-- Quick Setup for Bashid Khan as Approver -->
    <div class="section">
        <h3>Quick Setup - Make Bashid Khan Approver for All</h3>
        <p>This will set Bashid Khan as the approver for all employees in all departments.</p>
        <form method="POST">
            <input type="hidden" name="action" value="bulk_setup">
            <button type="submit" onclick="return confirm('This will set Bashid Khan as approver for ALL employees. Continue?')">
                Set Bashid Khan as Approver for All
            </button>
        </form>
    </div>
</div>

<script>
function togglePersonField() {
    const typeSelect = document.querySelector('select[name="type"]');
    const personField = document.getElementById('person_field');
    const personLabel = document.getElementById('person_label');
    
    if (typeSelect.value) {
        personField.style.display = 'block';
        personLabel.textContent = typeSelect.value === 'recommender' ? 'Recommender:' : 'Approver:';
    } else {
        personField.style.display = 'none';
    }
}
</script>

<?php
// Handle bulk setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_setup') {
    // Find Bashid Khan's ID
    $bashid_query = "SELECT id FROM employees WHERE full_name LIKE '%Bashid Khan%' OR eid = 'CME0001'";
    $bashid_result = $con->query($bashid_query);
    
    if ($bashid_result->num_rows > 0) {
        $bashid = $bashid_result->fetch_assoc();
        $bashid_id = $bashid['id'];
        
        // Get all employees
        $all_employees_query = "SELECT id, department_id FROM employees WHERE status = 'active'";
        $all_employees_result = $con->query($all_employees_query);
        
        $success_count = 0;
        while ($emp = $all_employees_result->fetch_assoc()) {
            // Check if approver entry exists
            $check_query = "SELECT id FROM leave_hierarchy 
                           WHERE employee_id = ? AND department_id = ? AND type = 'approver'";
            $check_stmt = $con->prepare($check_query);
            $check_stmt->bind_param("ii", $emp['id'], $emp['department_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing
                $update_query = "UPDATE leave_hierarchy 
                                SET approver_id = ? 
                                WHERE employee_id = ? AND department_id = ? AND type = 'approver'";
                $update_stmt = $con->prepare($update_query);
                $update_stmt->bind_param("iii", $bashid_id, $emp['id'], $emp['department_id']);
                $update_stmt->execute();
            } else {
                // Insert new
                $insert_query = "INSERT INTO leave_hierarchy 
                                (employee_id, department_id, type, approver_id) 
                                VALUES (?, ?, 'approver', ?)";
                $insert_stmt = $con->prepare($insert_query);
                $insert_stmt->bind_param("iii", $emp['id'], $emp['department_id'], $bashid_id);
                $insert_stmt->execute();
            }
            $success_count++;
        }
        
        echo "<script>alert('Successfully set Bashid Khan as approver for $success_count employees.');</script>";
    } else {
        echo "<script>alert('Bashid Khan not found in employees table!');</script>";
    }
}
?> 