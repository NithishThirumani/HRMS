<?php
session_start();
include '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    die("Not logged in");
}

$eid = $_SESSION['eid'];
echo "<h2>Assigning Leaves to HOD: $eid</h2>";

// Get HOD's department
$dept_query = "SELECT department_id FROM employees WHERE eid = ?";
$stmt_dept = $con->prepare($dept_query);
$stmt_dept->bind_param("s", $eid);
$stmt_dept->execute();
$dept_result = $stmt_dept->get_result();
$hod_dept = $dept_result->fetch_assoc();

if (!$hod_dept || !$hod_dept['department_id']) {
    die("HOD department not found");
}

echo "<p><strong>HOD Department ID:</strong> " . $hod_dept['department_id'] . "</p>";

// Find pending leaves in HOD's department that don't have hod_id assigned
$find_leaves_query = "SELECT l.*, e.full_name 
                      FROM leaves l 
                      JOIN employees e ON l.emp_id = e.eid 
                      WHERE l.status = 'Pending' 
                      AND e.department_id = ? 
                      AND (l.hod_id IS NULL OR l.hod_id = '')";
$find_stmt = $con->prepare($find_leaves_query);
$find_stmt->bind_param("i", $hod_dept['department_id']);
$find_stmt->execute();
$find_result = $find_stmt->get_result();

$leaves_to_assign = [];
while ($leave = $find_result->fetch_assoc()) {
    $leaves_to_assign[] = $leave;
}

echo "<p><strong>Found " . count($leaves_to_assign) . " leaves to assign to HOD</strong></p>";

if (count($leaves_to_assign) > 0) {
    echo "<h3>Leaves to be assigned:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Leave Type</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    
    foreach ($leaves_to_assign as $leave) {
        echo "<tr>";
        echo "<td>" . $leave['id'] . "</td>";
        echo "<td>" . $leave['full_name'] . "</td>";
        echo "<td>" . $leave['type_of_leave'] . "</td>";
        echo "<td>" . $leave['start_date'] . "</td>";
        echo "<td>" . $leave['end_date'] . "</td>";
        echo "<td>" . $leave['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Assign leaves to HOD
    if (isset($_POST['assign_leaves'])) {
        $update_query = "UPDATE leaves SET hod_id = ?, hod_name = ? 
                        WHERE id = ? AND status = 'Pending'";
        $update_stmt = $con->prepare($update_query);
        
        $hod_name = $_SESSION['username'] ?? 'HOD';
        
        $updated_count = 0;
        foreach ($leaves_to_assign as $leave) {
            $update_stmt->bind_param("ssi", $eid, $hod_name, $leave['id']);
            if ($update_stmt->execute()) {
                $updated_count++;
            }
        }
        
        echo "<p><strong>Successfully assigned $updated_count leaves to HOD</strong></p>";
        echo "<p><a href='recommend_leave.php'>Go to Recommend Leave Page</a></p>";
    } else {
        echo "<form method='post'>";
        echo "<input type='submit' name='assign_leaves' value='Assign These Leaves to HOD' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
        echo "</form>";
    }
} else {
    echo "<p><strong>No leaves found to assign.</strong></p>";
    echo "<p><a href='recommend_leave.php'>Go to Recommend Leave Page</a></p>";
}

// Show current leaves assigned to this HOD
echo "<h3>Current Leaves Assigned to This HOD:</h3>";
$current_leaves_query = "SELECT l.*, e.full_name 
                        FROM leaves l 
                        JOIN employees e ON l.emp_id = e.eid 
                        WHERE l.hod_id = ? AND l.status = 'Pending'";
$current_stmt = $con->prepare($current_leaves_query);
$current_stmt->bind_param("s", $eid);
$current_stmt->execute();
$current_result = $current_stmt->get_result();

if ($current_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Leave Type</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    
    while ($leave = $current_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $leave['id'] . "</td>";
        echo "<td>" . $leave['full_name'] . "</td>";
        echo "<td>" . $leave['type_of_leave'] . "</td>";
        echo "<td>" . $leave['start_date'] . "</td>";
        echo "<td>" . $leave['end_date'] . "</td>";
        echo "<td>" . $leave['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No leaves currently assigned to this HOD.</p>";
}
?> 