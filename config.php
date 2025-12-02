<?php
// Define the application root path
define('APP_ROOT', str_replace('\\', '/', dirname(__FILE__)));

// Define the base URL - this will work both locally and on web server
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_name);

// Define common paths
define('USER_PANEL', BASE_URL . 'user_panel/');
define('HR_PANEL', BASE_URL . 'hr_panel/');
define('ADMIN_PANEL', BASE_URL . 'admin_panel/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('IMG_URL', BASE_URL . 'img/');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'employees_management');
?> 