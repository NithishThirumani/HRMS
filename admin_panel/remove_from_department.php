<?php
session_start();
include('connection.php');

if (isset($_GET['emp_id']) && isset($_GET['dept_id'])) {
    $emp_id = mysqli_real_escape_string($con, $_GET['emp_id']);
    $dept_id = mysqli_real_escape_string($con, $_GET['dept_id']);

    $sql = "UPDATE employees SET department_id = NULL WHERE id = '$emp_id'";
    if (mysqli_query($con, $sql)) {
        $_SESSION['message'] = "Employee removed from department successfully!";
    } else {
        $_SESSION['error'] = "Error removing employee: " . mysqli_error($con);
    }
}

header("Location: department_employees.php?dept_id=" . $dept_id);
exit();
?>