<?php
session_start();
include('../connection.php');

// Check if user is logged in and has super admin privileges
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    // Log the error for debugging
    error_log("Super admin session validation failed: " . json_encode($_SESSION));
    
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Get super admin details
$email = $_SESSION['email'];

// Verify super admin exists and is active
$sql = "SELECT * FROM admin WHERE email = ? AND role = 'super_admin' AND status = 'Active'";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Log the error
    error_log("Super admin not found or inactive: " . $email);
    
    // Clear session and redirect
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check for session timeout (60 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // Log the timeout
    error_log("Session timeout for super admin: " . $email);
    
    // Update admin status to inactive
    $update_sql = "UPDATE admin SET status = 'Inactive' WHERE email = ? AND role = 'super_admin'";
    $stmt = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    // Clear session and redirect
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?> 