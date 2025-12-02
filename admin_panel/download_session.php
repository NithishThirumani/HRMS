<?php
session_start();
require_once('../connection.php');

// Allow if admin or employee is logged in
if (
    (isset($_SESSION['email']) && isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin')) ||
    (isset($_SESSION['user_name']) && isset($_SESSION['eid']))
) {
    $_SESSION['last_activity'] = time();
} else {
    header("Location: ../login.php");
    exit();
}
?> 