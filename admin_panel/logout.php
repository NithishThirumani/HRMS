<?php
session_start();

// Include your database connection
include('connection.php');

// Check if user is logged in
if (isset($_SESSION['email'])) {
    // Only update status to inactive for non-admin users
    if (!isset($_SESSION['admin_type']) || ($_SESSION['admin_type'] !== 'admin' && $_SESSION['admin_type'] !== 'super_admin')) {
        $stmt = mysqli_prepare($con, "UPDATE admin SET status = 'Inactive' WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['email']);
        mysqli_stmt_execute($stmt);
    }
    
    // Log the logout action
    $log_stmt = mysqli_prepare($con, "INSERT INTO system_logs (action, description, user_email, user_role, ip_address) 
                VALUES ('Logout', 'User logged out', ?, ?, ?)");
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($log_stmt, "sss", $_SESSION['email'], $role, $ip);
    mysqli_stmt_execute($log_stmt);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../login.php");
exit();
?>