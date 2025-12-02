<?php
session_start();
include('connection.php');

// Check if user is logged in with all required session variables
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: /emps/login.php");
    exit();
}

// Set session timeout to 1 hours
$session_timeout = 3600; // 1 hours in seconds

// Check if last activity was set
if (isset($_SESSION['last_activity'])) {
    // Calculate time difference
    $inactive_time = time() - $_SESSION['last_activity'];
    
    // If user has been inactive longer than timeout period
    if ($inactive_time >= $session_timeout) {
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header("Location: /emps/login.php?timeout=1");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Keep user data in variables for easy access
$email = $_SESSION['email'];
$role = $_SESSION['role'];
$department = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$department_name = isset($_SESSION['department_name']) ? $_SESSION['department_name'] : '';
$eid = $_SESSION['eid'];

// Get employee details
$emp_query = "SELECT e.*, el.status as login_status 
             FROM employees e 
             JOIN emp_login el ON e.eid = el.emp_id 
             WHERE e.eid = ? AND el.status = 'Active'";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // If employee not found or inactive, destroy session and redirect
    session_unset();
    session_destroy();
    header("Location: /emps/login.php?error=invalid_user");
    exit();
}

$user_data = $result->fetch_assoc();
?>

