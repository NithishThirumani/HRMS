<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // For testing, let's create sample data
    $_SESSION['registration_details'] = [
        'employee_id' => 'EMP' . rand(1000, 9999),
        'full_name' => $_POST['fn'] . ' ' . $_POST['ln'],
        'username' => strtolower($_POST['fn'] . '.' . $_POST['ln']),
        'password' => $_POST['ps'],
        'email' => $_POST['em'],
        'registration_date' => date('Y-m-d H:i:s')
    ];

    // Redirect to the confirmation page
    header("Location: ../admin_panel/registration-complete.php");
    exit();
} else {
    // If not POST request, redirect back
    header("Location: ../admin_panel/add_emp.php");
    exit();
}
?>