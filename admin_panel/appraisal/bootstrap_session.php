<?php
require_once dirname(__DIR__, 2) . '/includes/hrms_paths.php';
require_once dirname(__DIR__) . '/session.php';

if (!isset($_SESSION['user_id']) && isset($_SESSION['admin_id'])) {
    $_SESSION['user_id'] = $_SESSION['admin_id'];
}

$role = strtolower((string) ($_SESSION['role'] ?? ''));
$allowed = ['admin', 'super_admin', 'hr', 'hod'];

if (!isset($_SESSION['user_id']) || !in_array($role, $allowed, true)) {
    hrms_redirect('login.php');
    exit();
}
