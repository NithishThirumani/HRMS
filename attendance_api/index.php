<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>