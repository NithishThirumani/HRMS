<?php
include '../connection.php';

// This script helps set up the leave hierarchy for testing
// It will assign the current HOD as a recommender for all employees in their department

if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$hod_eid = $_SESSION['eid'];

// Get HOD's numeric ID and department
$hod_query = "SELECT id, department_id FROM employees WHERE eid = ?";
$stmt = $con->prepare($hod_query);
$stmt->bind_param("s", $hod_eid);
$stmt->execute();
$hod_result = $stmt->get_result();
$hod_data = $hod_result->fetch_assoc();

if (!$hod_data) {
    echo "Error: Could not find HOD data";
    exit();
}

$hod_numeric_id = $hod_data['id'];
$hod_department_id = $hod_data['department_id'];

echo "HOD EID: " . $hod_eid . "<br>";
echo "HOD Numeric ID: " . $hod_numeric_id . "<br>";
echo "HOD Department ID: " . $hod_department_id . "<br><br>";

// Get all employees in HOD's department
$emp_query = "SELECT id, eid, full_name FROM employees WHERE department_id = ? AND id != ?";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("ii", $hod_department_id, $hod_numeric_id);
$stmt->execute();
$emp_result = $stmt->get_result();

echo "Setting up hierarchy for employees in department " . $hod_department_id . ":<br>";

$success_count = 0;
$error_count = 0;

while ($emp = $emp_result->fetch_assoc()) {
    // Check if hierarchy already exists
    $check_query = "SELECT id FROM leave_hierarchy WHERE employee_id = ? AND recommender_id = ? AND type = 'recommender'";
    $check_stmt = $con->prepare($check_query);
    $check_stmt->bind_param("ii", $emp['id'], $hod_numeric_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Insert hierarchy record
        $insert_query = "INSERT INTO leave_hierarchy (employee_id, recommender_id, department_id, type) VALUES (?, ?, ?, 'recommender')";
        $insert_stmt = $con->prepare($insert_query);
        $insert_stmt->bind_param("iii", $emp['id'], $hod_numeric_id, $hod_department_id);
        
        if ($insert_stmt->execute()) {
            echo "✓ Set up hierarchy for " . $emp['full_name'] . " (ID: " . $emp['id'] . ")<br>";
            $success_count++;
        } else {
            echo "✗ Error setting up hierarchy for " . $emp['full_name'] . ": " . $con->error . "<br>";
            $error_count++;
        }
    } else {
        echo "ℹ Hierarchy already exists for " . $emp['full_name'] . "<br>";
    }
}

echo "<br><strong>Summary:</strong><br>";
echo "Successfully set up: " . $success_count . " employees<br>";
echo "Errors: " . $error_count . "<br>";

echo "<br><a href='recommend_leave.php'>Go to Recommend Leave Page</a>";
?> 