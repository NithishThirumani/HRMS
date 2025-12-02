<?php
include('../connection.php');

// Get all employees with their current passwords
$sql = "SELECT id, eid, password FROM employees";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $eid = $row['eid'];
    $current_password = $row['password']; // Current non-hashed password
    
    // Hash the current password
    $hashed_password = password_hash($current_password, PASSWORD_DEFAULT);
    
    // Update employees table
    $update_emp = "UPDATE employees SET password = ? WHERE id = ?";
    $stmt = $con->prepare($update_emp);
    $stmt->bind_param("si", $hashed_password, $id);
    $stmt->execute();
    
    // Update emp_login table
    $update_login = "UPDATE emp_login SET password = ? WHERE emp_id = ?";
    $stmt = $con->prepare($update_login);
    $stmt->bind_param("ss", $hashed_password, $eid);
    $stmt->execute();
}

echo "All passwords have been updated successfully!";
?>