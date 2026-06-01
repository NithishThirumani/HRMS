<?php
include('session.php');
include('connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: /emps/admin_panel/access_control.php');
    exit();
}

$stmt = $con->prepare("DELETE FROM access_rights WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: /emps/admin_panel/access_control.php');
exit();

?>
