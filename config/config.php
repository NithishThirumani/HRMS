<?php
// Base configuration
if (!defined('BASE_URL')) define('BASE_URL', '/emps/');
if (!defined('ROOT_PATH')) define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/emps/');

// Define panels
if (!defined('ALLOWED_PANELS')) define('ALLOWED_PANELS', [
    'admin_panel',
    'user_panel',
    'hr_panel',
    'hod_panel'
]);

// Protected directories
if (!defined('PROTECTED_DIRS')) define('PROTECTED_DIRS', [
    'config',
    'includes',
    'PHPMailer',
    'vendor',
    'scss',
    'js',
    'css',
    'uploads'
]);

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'hrms_secret');
if (!defined('DB_NAME')) define('DB_NAME', 'EMPS');;
?>