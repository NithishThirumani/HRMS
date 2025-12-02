<?php
// Error handling at the very top
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Add error logging for connection attempts
try {
$con = mysqli_connect("localhost", "root", "Nizam123$", "EMPS");
    
    if ($con->connect_error) {
        throw new Exception("Connection failed: " . $con->connect_error);
    }
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage());
    die("Critical system error. Administrators have been notified.");
}

// Add debug logging for session
error_log("[" . date('Y-m-d H:i:s') . "] Session started for IP: " . $_SERVER['REMOTE_ADDR']);
error_reporting(E_ALL);