<?php
require_once '../../config.php';
require_once '../../connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details based on role
$user = null;
$user_type = null;

// Check if user is an admin
$stmt = $con->prepare("SELECT id, email, role FROM admin WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($admin) {
    $user = $admin;
    $user_type = 'admin';
} else {
    // Check if user is a department head
    $stmt = $con->prepare("SELECT dh.id, dh.head_email as email, dh.head_name as full_name, dh.department_id 
                          FROM department_heads dh 
                          WHERE dh.head_email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $dept_head = $stmt->get_result()->fetch_assoc();

    if ($dept_head) {
        $user = $dept_head;
        $user_type = 'department_head';
    }
}

// Only allow admin and department heads to delete templates
if (!$user || ($user_type !== 'admin' && $user_type !== 'department_head')) {
    header('Location: ../templates.php?error=permission');
    exit();
}

// Get template ID
$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$template_id) {
    header('Location: ../templates.php?error=invalid');
    exit();
}

// Get template details to delete the file
$stmt = $con->prepare("SELECT file_path FROM esign_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();

if ($template) {
    // Delete the template file
    $file_path = '../../uploads/templates/' . $template['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Delete from database
    $stmt = $con->prepare("DELETE FROM esign_templates WHERE id = ?");
    $stmt->bind_param("i", $template_id);
    
    if ($stmt->execute()) {
        header('Location: ../templates.php?success=deleted');
    } else {
        header('Location: ../templates.php?error=delete');
    }
} else {
    header('Location: ../templates.php?error=notfound');
}
exit(); 