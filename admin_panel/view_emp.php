<?php
/**
 * Legacy URL — old code and bookmarks used view_emp.php; the list page is view_emp1.php.
 */
require_once dirname(__DIR__) . '/includes/hrms_paths.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$target = hrms_admin_panel_url('view_emp1.php');
if ($id > 0) {
    $target .= '?highlight=' . $id;
}
header('Location: ' . $target, true, 302);
exit;
