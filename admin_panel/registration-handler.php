<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Just store the necessary data for the receipt
    $_SESSION['registration_details'] = [
        'employee_id' => $_POST['employee_id'],
        'full_name' => $_POST['fn'] . ' ' . $_POST['ln'],
        'username' => $_POST['username'],
        'password' => $_POST['ps'],
        'email' => $_POST['em'],
        'registration_date' => date('Y-m-d H:i:s')
    ];

    header("Location: registration-complete.php");
    exit();
} else {
    header("Location: add_emp.php");
    exit();
}
?>