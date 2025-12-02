<?php
// Include centralized session manager
require_once __DIR__ . '/../includes/session_manager.php';

include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || strtoupper($_SESSION['role']) != 'HR') {
    header("Location: ../login.php");
    exit();
}

// Additional session timeout check
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
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
    header("Location: ../login.php?error=invalid_user");
    exit();
}

$user_data = $result->fetch_assoc();
?>