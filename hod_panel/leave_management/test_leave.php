<?php
include '../connection.php';

// This script creates a test leave application for testing the HOD panel
// Only run this if you want to test the system

if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$hod_eid = $_SESSION['eid'];

// Get HOD's department
$hod_query = "SELECT department_id FROM employees WHERE eid = ?";
$stmt = $con->prepare($hod_query);
$stmt->bind_param("s", $hod_eid);
$stmt->execute();
$hod_result = $stmt->get_result();
$hod_data = $hod_result->fetch_assoc();

if (!$hod_data) {
    echo "Error: Could not find HOD data";
    exit();
}

$hod_department_id = $hod_data['department_id'];

// Get a random employee from HOD's department (not the HOD themselves)
$emp_query = "SELECT id, eid, full_name FROM employees WHERE department_id = ? AND id != (SELECT id FROM employees WHERE eid = ?) LIMIT 1";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("is", $hod_department_id, $hod_eid);
$stmt->execute();
$emp_result = $stmt->get_result();
$employee = $emp_result->fetch_assoc();

if (!$employee) {
    echo "Error: No employees found in your department to create test leave for";
    exit();
}

// Get HOD's numeric ID
$hod_id_query = "SELECT id FROM employees WHERE eid = ?";
$stmt = $con->prepare($hod_id_query);
$stmt->bind_param("s", $hod_eid);
$stmt->execute();
$hod_id_result = $stmt->get_result();
$hod_numeric_id = $hod_id_result->fetch_assoc()['id'];

// Create a test leave application
$test_leave_query = "INSERT INTO leaves (
    emp_id, 
    user_name, 
    type_of_leave, 
    start_date, 
    end_date, 
    total_days, 
    reason, 
    status, 
    hod_id, 
    hod_name, 
    applied_at
) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())";

$stmt = $con->prepare($test_leave_query);
$stmt->bind_param("sssssssss", 
    $employee['eid'],
    $employee['full_name'],
    'Annual Leave',
    date('Y-m-d', strtotime('+1 week')),
    date('Y-m-d', strtotime('+1 week +2 days')),
    3,
    'Test leave application for HOD panel testing',
    $hod_numeric_id,
    'Test HOD'
);

if ($stmt->execute()) {
    echo "✓ Test leave application created successfully!<br>";
    echo "Employee: " . $employee['full_name'] . "<br>";
    echo "Leave Type: Annual Leave<br>";
    echo "Start Date: " . date('Y-m-d', strtotime('+1 week')) . "<br>";
    echo "End Date: " . date('Y-m-d', strtotime('+1 week +2 days')) . "<br>";
    echo "Days: 3<br>";
    echo "Status: Pending<br><br>";
    
    echo "<a href='recommend_leave.php' class='btn btn-primary'>Go to Recommend Leave Page</a>";
} else {
    echo "✗ Error creating test leave: " . $con->error;
}
?> 