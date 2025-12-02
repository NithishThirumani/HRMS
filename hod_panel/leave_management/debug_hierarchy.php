<?php
session_start();
include '../connection.php';

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    die("Not logged in");
}

$eid = $_SESSION['eid'];
echo "<h2>Leave Hierarchy Debug for HOD: $eid</h2>";

// Get HOD's numeric ID
$stmt = $con->prepare("SELECT id, department_id, role, full_name FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
$hod_info = $result->fetch_assoc();

if ($hod_info) {
    echo "<p><strong>HOD Info:</strong></p>";
    echo "<ul>";
    echo "<li>EID: " . $eid . "</li>";
    echo "<li>Numeric ID: " . $hod_info['id'] . "</li>";
    echo "<li>Name: " . $hod_info['full_name'] . "</li>";
    echo "<li>Department ID: " . $hod_info['department_id'] . "</li>";
    echo "<li>Role: " . $hod_info['role'] . "</li>";
    echo "</ul>";
} else {
    die("HOD not found");
}

// Check leave hierarchy records where this HOD is recommender
echo "<h3>Leave Hierarchy Records Where HOD is Recommender:</h3>";
$hierarchy_query = "SELECT lh.*, e.full_name as employee_name, e.eid as employee_eid
                    FROM leave_hierarchy lh 
                    LEFT JOIN employees e ON lh.employee_id = e.id 
                    WHERE lh.recommender_id = ? AND lh.type = 'recommender'";
$hierarchy_stmt = $con->prepare($hierarchy_query);
$hierarchy_stmt->bind_param("i", $hod_info['id']);
$hierarchy_stmt->execute();
$hierarchy_result = $hierarchy_stmt->get_result();

if ($hierarchy_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Employee EID</th><th>Employee Numeric ID</th><th>Recommender ID</th><th>Type</th></tr>";
    
    while ($hierarchy = $hierarchy_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $hierarchy['id'] . "</td>";
        echo "<td>" . $hierarchy['employee_name'] . "</td>";
        echo "<td>" . $hierarchy['employee_eid'] . "</td>";
        echo "<td>" . $hierarchy['employee_id'] . "</td>";
        echo "<td>" . $hierarchy['recommender_id'] . "</td>";
        echo "<td>" . $hierarchy['type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hierarchy records found where this HOD is recommender.</p>";
}

// Check specific leave ID 42
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
    
    // Check if this employee has hierarchy records
    echo "<h4>Hierarchy Records for Employee " . $leave['employee_numeric_id'] . ":</h4>";
    $emp_hierarchy_query = "SELECT lh.*, e.full_name as employee_name, r.full_name as recommender_name
                           FROM leave_hierarchy lh 
                           LEFT JOIN employees e ON lh.employee_id = e.id 
                           LEFT JOIN employees r ON lh.recommender_id = r.id 
                           WHERE lh.employee_id = ?";
    $emp_hierarchy_stmt = $con->prepare($emp_hierarchy_query);
    $emp_hierarchy_stmt->bind_param("i", $leave['employee_numeric_id']);
    $emp_hierarchy_stmt->execute();
    $emp_hierarchy_result = $emp_hierarchy_stmt->get_result();
    
    if ($emp_hierarchy_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Employee</th><th>Recommender</th><th>Type</th></tr>";
        
        while ($hierarchy = $emp_hierarchy_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $hierarchy['id'] . "</td>";
            echo "<td>" . $hierarchy['employee_name'] . "</td>";
            echo "<td>" . $hierarchy['recommender_name'] . "</td>";
            echo "<td>" . $hierarchy['type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hierarchy records found for this employee.</p>";
    }
} else {
    echo "<p>Leave ID 42 not found.</p>";
}

// Check all pending leaves
echo "<h3>All Pending Leaves:</h3>";
$all_leaves_query = "SELECT l.*, e.full_name, e.eid as employee_eid, e.id as employee_numeric_id
                     FROM leaves l 
                     LEFT JOIN employees e ON l.emp_id = e.eid 
                     WHERE l.status = 'Pending' 
                     ORDER BY l.id";
$all_leaves_stmt = $con->prepare($all_leaves_query);
$all_leaves_stmt->execute();
$all_leaves_result = $all_leaves_stmt->get_result();

if ($all_leaves_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Leave ID</th><th>Employee</th><th>Employee Numeric ID</th><th>HOD ID</th><th>Recommender ID</th><th>Status</th></tr>";
    
    while ($leave = $all_leaves_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $leave['id'] . "</td>";
        echo "<td>" . $leave['full_name'] . " (" . $leave['employee_eid'] . ")</td>";
        echo "<td>" . $leave['employee_numeric_id'] . "</td>";
        echo "<td>" . $leave['hod_id'] . "</td>";
        echo "<td>" . $leave['recommender_id'] . "</td>";
        echo "<td>" . $leave['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending leaves found.</p>";
}

// Show what should be visible to this HOD
echo "<h3>Leaves That Should Be Visible to This HOD:</h3>";
echo "<p>Based on hierarchy records, this HOD should see leaves from employees:</p>";

$visible_employees_query = "SELECT DISTINCT lh.employee_id, e.full_name, e.eid
                           FROM leave_hierarchy lh 
                           LEFT JOIN employees e ON lh.employee_id = e.id 
                           WHERE lh.recommender_id = ? AND lh.type = 'recommender'";
$visible_employees_stmt = $con->prepare($visible_employees_query);
$visible_employees_stmt->bind_param("i", $hod_info['id']);
$visible_employees_stmt->execute();
$visible_employees_result = $visible_employees_stmt->get_result();

if ($visible_employees_result->num_rows > 0) {
    echo "<ul>";
    while ($employee = $visible_employees_result->fetch_assoc()) {
        echo "<li>" . $employee['full_name'] . " (ID: " . $employee['employee_id'] . ", EID: " . $employee['eid'] . ")</li>";
        
        // Check if this employee has pending leaves
        $emp_leaves_query = "SELECT COUNT(*) as leave_count FROM leaves WHERE emp_id = ? AND status = 'Pending'";
        $emp_leaves_stmt = $con->prepare($emp_leaves_query);
        $emp_leaves_stmt->bind_param("s", $employee['eid']);
        $emp_leaves_stmt->execute();
        $emp_leaves_result = $emp_leaves_stmt->get_result();
        $leave_count = $emp_leaves_result->fetch_assoc()['leave_count'];
        
        if ($leave_count > 0) {
            echo " - <strong>Has " . $leave_count . " pending leave(s)</strong>";
        } else {
            echo " - No pending leaves";
        }
    }
    echo "</ul>";
} else {
    echo "<p>No employees found in hierarchy where this HOD is recommender.</p>";
}
?> 