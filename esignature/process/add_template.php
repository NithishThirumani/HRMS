<?php
require_once '../../config.php';
require_once '../../connection.php';
session_start();

// Only allow admin and department head to add templates
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

// Get user type
$user_type = null;
$user_id = null;
$stmt = $con->prepare("SELECT id FROM admin WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
if ($admin) {
    $user_type = 'admin';
    $user_id = $admin['id'];
} else {
    $stmt = $con->prepare("SELECT id FROM department_heads WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $dh = $stmt->get_result()->fetch_assoc();
    if ($dh) {
        $user_type = 'department_head';
        $user_id = $dh['id'];
    }
}
if (!$user_type) {
    header('Location: ../templates.php?error=permission');
    exit();
}

// Validate form
$title = trim($_POST['title'] ?? '');
$document_type = trim($_POST['document_type'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '' || $document_type === '' || !isset($_FILES['template_file'])) {
    header('Location: ../templates.php?error=missing');
    exit();
}

// Handle file upload
$upload_dir = '../../uploads/templates/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$file = $_FILES['template_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    header('Location: ../templates.php?error=filetype');
    exit();
}
if ($file['size'] > 10 * 1024 * 1024) { // 10MB
    header('Location: ../templates.php?error=filesize');
    exit();
}
$filename = uniqid('template_', true) . '.pdf';
$filepath = $upload_dir . $filename;
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    header('Location: ../templates.php?error=upload');
    exit();
}

// Save to DB
$stmt = $con->prepare("INSERT INTO esign_templates (title, description, document_type, file_path, created_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $title, $description, $document_type, $filename, $user_id);
if ($stmt->execute()) {
    header('Location: ../templates.php?success=1');
} else {
    header('Location: ../templates.php?error=db');
}
exit(); 