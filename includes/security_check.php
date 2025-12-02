<?php
// Include centralized session manager
require_once __DIR__ . '/session_manager.php';

// Function to check if user is logged in and not a trainee
function checkAccess() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        header("Location: /emps/login.php");
        exit();
    }

    // Check if user is a trainee
    if (isset($_SESSION['is_trainee']) && $_SESSION['is_trainee'] == 1) {
        // Log unauthorized access attempt
        error_log("Unauthorized access attempt by trainee: " . getCurrentUserEmail());
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Redirect to login with error message
        $_SESSION['login_error'] = "Trainee accounts do not have system access.";
        header("Location: /emps/login.php");
        exit();
    }
}

// Run the access check
checkAccess();
?> 