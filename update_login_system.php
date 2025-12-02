<?php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

try {
    // Start transaction
    mysqli_begin_transaction($con);

    // 1. Add email column to emp_login
    $sql1 = "ALTER TABLE emp_login ADD COLUMN email VARCHAR(255) AFTER user_name";
    if (!mysqli_query($con, $sql1)) {
        // If column already exists, ignore the error
        if (!strpos(mysqli_error($con), "Duplicate column name")) {
            throw new Exception("Error adding email column: " . mysqli_error($con));
        }
    }

    // 2. Update emp_login with email from employees table
    $sql2 = "UPDATE emp_login el 
             JOIN employees e ON el.emp_id = e.eid 
             SET el.email = e.email";
    if (!mysqli_query($con, $sql2)) {
        throw new Exception("Error updating email data: " . mysqli_error($con));
    }

    // 3. Make email column NOT NULL and add unique index
    $sql3 = "ALTER TABLE emp_login 
             MODIFY COLUMN email VARCHAR(255) NOT NULL,
             ADD UNIQUE INDEX email_idx (email)";
    if (!mysqli_query($con, $sql3)) {
        throw new Exception("Error modifying email column: " . mysqli_error($con));
    }

    // Commit transaction
    mysqli_commit($con);
    
    echo "<script>
        alert('Login system successfully updated to use email.');
        window.location.href = 'login.php';
    </script>";

} catch (Exception $e) {
    mysqli_rollback($con);
    echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
}
?> 