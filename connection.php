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

require_once __DIR__ . '/includes/db_connection.php';

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