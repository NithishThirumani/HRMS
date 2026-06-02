<?php
/**
 * Legacy URL — redirects to edit employee profile.
 */
require_once dirname(__DIR__) . '/includes/hrms_paths.php';
if (!isset($_GET['id']) && isset($_GET['edit'])) {
    $_GET['id'] = $_GET['edit'];
}
if (empty($_GET['id'])) {
    header('Location: ' . hrms_admin_panel_url('view_emp1.php'), true, 302);
    exit;
}
header('Location: ' . hrms_admin_panel_url('edit_employee.php?id=' . (int) $_GET['id']), true, 302);
exit;
