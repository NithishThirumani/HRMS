<?php
require_once __DIR__ . '/../includes/hrms_session.php';
require_once __DIR__ . '/../includes/hrms_paths.php';
hrms_start_session('employee');
include('connection.php');

// Debug: Log session variables
error_log("HOD Session Debug - Email: " . ($_SESSION['email'] ?? 'NOT SET'));
error_log("HOD Session Debug - EID: " . ($_SESSION['eid'] ?? 'NOT SET'));
error_log("HOD Session Debug - Role: " . ($_SESSION['role'] ?? 'NOT SET'));
error_log("HOD Session Debug - Username: " . ($_SESSION['username'] ?? 'NOT SET'));

// Simple security check - check if user is logged in
if (!isset($_SESSION['email']) && !isset($_SESSION['eid']) && !isset($_SESSION['admin_id'])) {
    error_log("HOD Session Debug - No session variables found, redirecting to login");
    hrms_redirect('login.php');
    exit();
}

// Check if user is a trainee (if trainee flag is set)
if (isset($_SESSION['is_trainee']) && $_SESSION['is_trainee'] == 1) {
    session_unset();
    session_destroy();
    hrms_redirect('login.php');
    exit();
}

include('connection.php');

// Check if user is logged in and is HOD (more flexible role checking)
$is_hod = false;
if (isset($_SESSION['role'])) {
    $role = strtoupper($_SESSION['role']);
    $is_hod = ($role == 'HOD' || $role == 'HEAD OF DEPARTMENT' || $role == 'DEPARTMENT HEAD');
}

// If role check fails, try to verify HOD status from database
if (!$is_hod && isset($_SESSION['eid'])) {
    $hod_check_query = "SELECT role FROM employees WHERE eid = ?";
    $hod_check_stmt = $con->prepare($hod_check_query);
    $hod_check_stmt->bind_param("s", $_SESSION['eid']);
    $hod_check_stmt->execute();
    $hod_check_result = $hod_check_stmt->get_result();
    
    if ($hod_check_result->num_rows > 0) {
        $emp_role = $hod_check_result->fetch_assoc()['role'];
        $role = strtoupper($emp_role);
        $is_hod = ($role == 'HOD' || $role == 'HEAD OF DEPARTMENT' || $role == 'DEPARTMENT HEAD');
        error_log("HOD Session Debug - Database role check: " . $emp_role . ", is_hod: " . ($is_hod ? 'true' : 'false'));
    }
}

// Additional check for lowercase 'hod' role
if (!$is_hod && isset($_SESSION['role'])) {
    $role_lower = strtolower($_SESSION['role']);
    if ($role_lower == 'hod') {
        $is_hod = true;
        error_log("HOD Session Debug - Found lowercase 'hod' role, setting is_hod to true");
    }
}

// Additional check for lowercase 'hod' role in database
if (!$is_hod && isset($_SESSION['eid'])) {
    $hod_check_query2 = "SELECT role FROM employees WHERE eid = ?";
    $hod_check_stmt2 = $con->prepare($hod_check_query2);
    $hod_check_stmt2->bind_param("s", $_SESSION['eid']);
    $hod_check_stmt2->execute();
    $hod_check_result2 = $hod_check_stmt2->get_result();
    
    if ($hod_check_result2->num_rows > 0) {
        $emp_role2 = $hod_check_result2->fetch_assoc()['role'];
        $role_lower2 = strtolower($emp_role2);
        if ($role_lower2 == 'hod') {
            $is_hod = true;
            error_log("HOD Session Debug - Found lowercase 'hod' role in database, setting is_hod to true");
        }
    }
}

    if (!$is_hod) {
    error_log("HOD Session Debug - User is not HOD, redirecting to login");
    hrms_redirect('login.php');
    exit();
}

// Additional session timeout check
$timeout = 1800; // 30 minutes in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    hrms_redirect('login.php');
    exit();
}
$_SESSION['last_activity'] = time();

// Verify HOD access for specific department (simplified check)
if (isset($_SESSION['eid'])) {
    $username = $_SESSION['username'] ?? $_SESSION['eid'];
    $department_id = $_SESSION['department_id'] ?? 0;

    $sql = "SELECT e.role, e.eid, e.full_name, e.department_id, d.name as department_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.eid = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $_SESSION['eid']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update session with verified data
        $hod_data = $result->fetch_assoc();
        $_SESSION['role'] = $hod_data['role'];
        $_SESSION['department_id'] = $hod_data['department_id'];
        $_SESSION['username'] = $hod_data['full_name'];
        
        error_log("HOD Session Debug - Employee verified: " . $hod_data['full_name'] . ", Role: " . $hod_data['role'] . ", Department: " . $hod_data['department_name']);
        } else {
        error_log("HOD Session Debug - Employee not found for EID: " . $_SESSION['eid']);
        hrms_redirect('login.php');
        exit();
    }
}
?>