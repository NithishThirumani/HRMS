<?php
ob_start(); // Start output buffering

// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// Base URL Configuration
$base_url = "/emps";
$admin_url = $base_url . "/admin_panel";
$user_url = $base_url . "/user_panel";
$hod_url = $base_url . "/hod_panel";

// Error handling
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Database Connection with error handling
try {
    
    $con = mysqli_connect("localhost", "root", "Nizam123$", "EMPS");
    
    if (!$con) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Set charset to ensure proper encoding
    if (!mysqli_set_charset($con, "utf8mb4")) {
        throw new Exception("Error setting charset: " . mysqli_error($con));
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// URL helper functions
if (!function_exists('getUrl')) {
    function getUrl($path = '') {
        global $base_url;
        return $base_url . '/' . ltrim($path, '/');
    }
}

if (!function_exists('getPanelUrl')) {
    function getPanelUrl($panel = 'user') {
        global $admin_url, $user_url, $hod_url;
        switch ($panel) {
            case 'admin':
                return $admin_url;
            case 'hod':
                return $hod_url;
            default:
                return $user_url;
        }
    }
}