
<?php
session_start();
include('connection.php');

// Check if user is logged in
if (isset($_SESSION['username'])) {
    unset($_SESSION['username']);
    unset($_SESSION['role']);
}

// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: /login.php");
exit();
?>
