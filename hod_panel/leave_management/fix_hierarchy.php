<?php
session_start();
include '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    die("Not logged in");
}

$eid = $_SESSION['eid'];
echo "<h2>Fix Leave Hierarchy for HOD: $eid</h2>";

// Get HOD's numeric ID
$stmt = $con->prepare("SELECT id, department_id, role, full_name FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
$hod_info = $result->fetch_assoc();

if (!$hod_info) {
    die("HOD not found");
}

echo "<p><strong>HOD Info:</strong></p>";
echo "<ul>";
echo "<li>EID: " . $eid . "</li>";
echo "<li>Numeric ID: " . $hod_info['id'] . "</li>";
echo "<li>Name: " . $hod_info['full_name'] . "</li>";
echo "<li>Department ID: " . $hod_info['department_id'] . "</li>";
echo "<li>Role: " . $hod_info['role'] . "</li>";
echo "</ul>";

// Check current hierarchy for employee 67
echo "<h3>Current Hierarchy for Employee 67 (Manobala):</h3>";
$current_hierarchy_query = "SELECT lh.*, e.full_name as employee_name, r.full_name as recommender_name, a.full_name as approver_name
                           FROM leave_hierarchy lh 
                           LEFT JOIN employees e ON lh.employee_id = e.id 
                           LEFT JOIN employees r ON lh.recommender_id = r.id 
                           LEFT JOIN employees a ON lh.approver_id = a.id 
                           WHERE lh.employee_id = 67";
$current_hierarchy_stmt = $con->prepare($current_hierarchy_query);
$current_hierarchy_stmt->execute();
$current_hierarchy_result = $current_hierarchy_stmt->get_result();

if ($current_hierarchy_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Recommender</th><th>Approver</th><th>Type</th></tr>";
    
    while ($hierarchy = $current_hierarchy_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $hierarchy['id'] . "</td>";
        echo "<td>" . $hierarchy['employee_name'] . "</td>";
        echo "<td>" . $hierarchy['recommender_name'] . "</td>";
        echo "<td>" . $hierarchy['approver_name'] . "</td>";
        echo "<td>" . $hierarchy['type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hierarchy records found for employee 67.</p>";
}

// Check leave 42
echo "<h3>Leave ID 42 Details:</h3>";
$leave_query = "SELECT l.*, e.full_name, e.eid as employee_eid, e.id as employee_numeric_id
                FROM leaves l 
                LEFT JOIN employees e ON l.emp_id = e.eid 
                WHERE l.id = 42";
$leave_stmt = $con->prepare($leave_query);
$leave_stmt->execute();
$leave_result = $leave_stmt->get_result();
$leave = $leave_result->fetch_assoc();

if ($leave) {
    echo "<p><strong>Leave Details:</strong></p>";
    echo "<ul>";
    echo "<li>Leave ID: " . $leave['id'] . "</li>";
    echo "<li>Employee: " . $leave['full_name'] . " (" . $leave['employee_eid'] . ")</li>";
    echo "<li>Employee Numeric ID: " . $leave['employee_numeric_id'] . "</li>";
    echo "<li>HOD ID: " . $leave['hod_id'] . "</li>";
    echo "<li>Recommender ID: " . $leave['recommender_id'] . "</li>";
    echo "<li>Status: " . $leave['status'] . "</li>";
    echo "</ul>";
}

// Show the problem
echo "<h3>The Problem:</h3>";
echo "<p style='color: red;'>Leave 42 has recommender_id = " . $hod_info['id'] . ", but there's no hierarchy record where HOD " . $hod_info['id'] . " is a recommender for employee 67.</p>";

// Show solutions
echo "<h3>Solutions:</h3>";
echo "<p>Choose one of the following fixes:</p>";

echo "<h4>Option 1: Add HOD as Recommender in Hierarchy</h4>";
echo "<p>This will add a hierarchy record making HOD " . $hod_info['id'] . " a recommender for employee 67.</p>";
echo "<form method='post'>";
echo "<input type='submit' name='add_recommender' value='Add HOD as Recommender' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

echo "<h4>Option 2: Update Leave Assignment</h4>";
echo "<p>This will update leave 42 to use the existing approver assignment instead of recommender.</p>";
echo "<form method='post'>";
echo "<input type='submit' name='update_leave' value='Update Leave to Use Approver' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

// Handle form submissions
if (isset($_POST['add_recommender'])) {
    // Add HOD as recommender for employee 67
    $insert_query = "INSERT INTO leave_hierarchy (employee_id, recommender_id, department_id, type) VALUES (67, ?, 2, 'recommender')";
    $insert_stmt = $con->prepare($insert_query);
    $insert_stmt->bind_param("i", $hod_info['id']);
    
    if ($insert_stmt->execute()) {
        echo "<p style='color: green;'><strong>Success!</strong> Added HOD " . $hod_info['id'] . " as recommender for employee 67.</p>";
        echo "<p><a href='recommend_leave.php'>Go to Recommend Leave Page</a></p>";
    } else {
        echo "<p style='color: red;'><strong>Error:</strong> " . $insert_stmt->error . "</p>";
    }
}

if (isset($_POST['update_leave'])) {
    // Update leave to use approver instead of recommender
    $update_query = "UPDATE leaves SET recommender_id = NULL, hod_id = ? WHERE id = 42";
    $update_stmt = $con->prepare($update_query);
    $update_stmt->bind_param("i", $hod_info['id']);
    
    if ($update_stmt->execute()) {
        echo "<p style='color: green;'><strong>Success!</strong> Updated leave 42 to use HOD " . $hod_info['id'] . " as direct HOD assignment.</p>";
        echo "<p><a href='recommend_leave.php'>Go to Recommend Leave Page</a></p>";
    } else {
        echo "<p style='color: red;'><strong>Error:</strong> " . $update_stmt->error . "</p>";
    }
}

// Show what should happen after fix
echo "<h3>After Fix:</h3>";
echo "<p>Once you apply one of the fixes above, the leave should appear in your HOD panel because:</p>";
echo "<ul>";
echo "<li><strong>Option 1:</strong> HOD will be a recommender in hierarchy, so the EXISTS clause will match</li>";
echo "<li><strong>Option 2:</strong> Leave will have hod_id = " . $hod_info['id'] . ", so the direct assignment will match</li>";
echo "</ul>";
?> 