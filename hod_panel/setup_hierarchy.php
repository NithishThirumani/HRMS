<?php
include '../session.php';
include '../connection.php';

$eid = $_SESSION['eid'];

// Get HOD's numeric ID and department
$hod_query = "SELECT e.id, e.eid, e.full_name, e.department_id, d.name as dept_name 
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.id 
              WHERE e.eid = ?";
$stmt = $con->prepare($hod_query);
$stmt->bind_param("s", $eid);
$stmt->execute();
$hod_data = $stmt->get_result()->fetch_assoc();

if (!$hod_data) {
    echo "Error: HOD not found";
    exit();
}

$hod_id = $hod_data['id'];
$hod_dept_id = $hod_data['department_id'];

echo "<h2>Setup Leave Hierarchy for HOD</h2>";
echo "<p><strong>HOD:</strong> " . $hod_data['full_name'] . " (ID: " . $hod_id . ")</p>";
echo "<p><strong>Department:</strong> " . $hod_data['dept_name'] . " (ID: " . $hod_dept_id . ")</p>";

// Get all employees in HOD's department
$emp_query = "SELECT e.id, e.eid, e.full_name, e.role 
              FROM employees e 
              WHERE e.department_id = ? AND e.id != ?";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("ii", $hod_dept_id, $hod_id);
$stmt->execute();
$employees = $stmt->get_result();

echo "<h3>Employees in Department</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>EID</th><th>Name</th><th>Role</th><th>Hierarchy Status</th></tr>";

while ($emp = $employees->fetch_assoc()) {
    // Check if hierarchy exists for this employee
    $hierarchy_check = "SELECT COUNT(*) as count FROM leave_hierarchy WHERE employee_id = ? AND recommender_id = ?";
    $hier_stmt = $con->prepare($hierarchy_check);
    $hier_stmt->bind_param("ii", $emp['id'], $hod_id);
    $hier_stmt->execute();
    $hier_result = $hier_stmt->get_result();
    $hier_count = $hier_result->fetch_assoc()['count'];
    
    $status = $hier_count > 0 ? "✅ Set up" : "❌ Not set up";
    
    echo "<tr>";
    echo "<td>" . $emp['id'] . "</td>";
    echo "<td>" . $emp['eid'] . "</td>";
    echo "<td>" . $emp['full_name'] . "</td>";
    echo "<td>" . $emp['role'] . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

// Handle form submission to set up hierarchy
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_hierarchy'])) {
    // Get all employees in department
    $emp_query2 = "SELECT e.id FROM employees e WHERE e.department_id = ? AND e.id != ?";
    $stmt2 = $con->prepare($emp_query2);
    $stmt2->bind_param("ii", $hod_dept_id, $hod_id);
    $stmt2->execute();
    $emp_list = $stmt2->get_result();
    
    $success_count = 0;
    $error_count = 0;
    
    while ($emp = $emp_list->fetch_assoc()) {
        // Check if hierarchy already exists
        $check_query = "SELECT COUNT(*) as count FROM leave_hierarchy WHERE employee_id = ? AND recommender_id = ?";
        $check_stmt = $con->prepare($check_query);
        $check_stmt->bind_param("ii", $emp['id'], $hod_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        if (!$exists) {
            // Insert hierarchy record
            $insert_query = "INSERT INTO leave_hierarchy (employee_id, recommender_id, department_id, type) VALUES (?, ?, ?, 'recommender')";
            $insert_stmt = $con->prepare($insert_query);
            $insert_stmt->bind_param("iii", $emp['id'], $hod_id, $hod_dept_id);
            
            if ($insert_stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Hierarchy Setup Complete:</strong><br>";
    echo "Successfully set up: " . $success_count . " employees<br>";
    if ($error_count > 0) {
        echo "Errors: " . $error_count . " employees<br>";
    }
    echo "</div>";
}

echo "<form method='POST'>";
echo "<input type='submit' name='setup_hierarchy' value='Setup Hierarchy for All Department Employees' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

echo "<h3>Current Leave Hierarchy Records</h3>";
$hierarchy_query = "SELECT lh.*, e.full_name as employee_name, r.full_name as recommender_name 
                   FROM leave_hierarchy lh 
                   LEFT JOIN employees e ON lh.employee_id = e.id 
                   LEFT JOIN employees r ON lh.recommender_id = r.id 
                   WHERE lh.recommender_id = ?";
$hier_stmt = $con->prepare($hierarchy_query);
$hier_stmt->bind_param("i", $hod_id);
$hier_stmt->execute();
$hierarchy_result = $hier_stmt->get_result();

if ($hierarchy_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Employee</th><th>Recommender</th><th>Type</th><th>Department ID</th></tr>";
    
    while ($hier = $hierarchy_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $hier['employee_name'] . "</td>";
        echo "<td>" . $hier['recommender_name'] . "</td>";
        echo "<td>" . $hier['type'] . "</td>";
        echo "<td>" . $hier['department_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hierarchy records found for this HOD.</p>";
}

echo "<p><a href='recommend_leave.php'>Go to Recommend Leave Page</a></p>";
echo "<p><a href='../index.php'>Back to HOD Dashboard</a></p>";
?> 