<?php
require_once dirname(__DIR__) . '/includes/hrms_session.php';
require_once dirname(__DIR__) . '/includes/hrms_paths.php';
hrms_start_session('super_admin');
include('../connection.php');

if (isset($_SESSION['email']) && isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    // Update admin status to inactive
    $email = $_SESSION['email'];
    $update_sql = "UPDATE admin SET status = 'Inactive' WHERE email = ? AND role = 'super_admin'";
    $stmt = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    // Log the logout action
    $log_sql = "INSERT INTO system_logs (action, description, user_email, user_role, ip_address) 
                VALUES ('Logout', 'Super admin logged out', ?, 'super_admin', ?)";
    $stmt = mysqli_prepare($con, $log_sql);
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_stmt_bind_param($stmt, "ss", $email, $ip);
    mysqli_stmt_execute($stmt);
}

hrms_destroy_session('super_admin');
hrms_redirect('login.php');
?> 