<?php
session_start();
require_once('../connection.php');

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    // Log the error for debugging
    error_log("Session validation failed: " . json_encode($_SESSION));
    
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Get admin details
$email = $_SESSION['email'];
$role = $_SESSION['role'];

try {
    // Verify admin exists and is active
    $sql = "SELECT * FROM admin WHERE email = ? AND status = 'Active'";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        // Log the error
        error_log("Admin not found or inactive: " . $email);
        
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
        error_log("Session timeout for user: " . $email);
        
        // Update admin status to inactive
        $update_sql = "UPDATE admin SET status = 'Inactive' WHERE email = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        
        // Clear session and redirect
        session_destroy();
        header("Location: ../login.php");
        exit();
    }

} catch (Exception $e) {
    error_log("Session Error: " . $e->getMessage());
    
    // Clear session and redirect
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>
