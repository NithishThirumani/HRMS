<?php
require_once '../../config.php';
require_once '../../connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
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
    } else {
        // Check if user is an employee
        $stmt = $con->prepare("SELECT e.id, e.eid, e.full_name, e.department_id 
                              FROM employees e
                              WHERE e.email = ? AND e.status = 'active'");
        $stmt->bind_param("s", $_SESSION['email']);
        $stmt->execute();
        $employee = $stmt->get_result()->fetch_assoc();

        if ($employee) {
            $user = $employee;
            $user_type = 'employee';
        }
    }
}

if (!$user) {
    header('Location: ../../login.php');
    exit();
}

// Only admin and HR can create documents
if ($user_type != 'admin' && $user_type != 'hr') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['title', 'document_type', 'departments', 'approvers'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header('Location: ../create.php');
            exit();
        }
    }

    // Validate arrays have same length
    if (count($_POST['departments']) !== count($_POST['approvers'])) {
        $_SESSION['error'] = "Invalid approval flow configuration.";
        header('Location: ../create.php');
        exit();
    }

    // Validate file upload
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Please upload a valid document file.";
        header('Location: ../create.php');
        exit();
    }

    $file = $_FILES['document_file'];
    $allowed_types = ['application/pdf'];
    $max_size = 10 * 1024 * 1024; // 10MB

    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Only PDF files are allowed.";
        header('Location: ../create.php');
        exit();
    }

    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "File size must be less than 10MB.";
        header('Location: ../create.php');
        exit();
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $_SESSION['error'] = "Failed to upload file. Please try again.";
        header('Location: ../create.php');
        exit();
    }

    // Start transaction
    $con->begin_transaction();

    try {
        // Insert document record
        $stmt = $con->prepare("INSERT INTO esign_documents (title, document_type, description, file_path, created_by, created_at, status, expiry_date) VALUES (?, ?, ?, ?, ?, NOW(), 'pending', ?)");
        
        $expiry_date = null;
        if (isset($_POST['has_expiry']) && !empty($_POST['expiry_date'])) {
            $expiry_date = $_POST['expiry_date'];
        }
        
        $stmt->bind_param("ssssss", 
            $_POST['title'],
            $_POST['document_type'],
            $_POST['description'],
            $filepath,
            $user['id'],
            $expiry_date
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create document record.");
        }

        $document_id = $con->insert_id;

        // Create approval workflow
        $stmt = $con->prepare("INSERT INTO esign_workflow (document_id, approver_id, approver_type, level, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        
        foreach ($_POST['approvers'] as $index => $approver) {
            // Parse approver type and ID
            list($type, $id) = explode('_', $approver);
            
            $stmt->bind_param("issi", 
                $document_id,
                $id,
                $type,
                $index
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create approval workflow.");
            }
        }

        // Commit transaction
        $con->commit();

        $_SESSION['success'] = "Document created successfully.";
        header('Location: ../index.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $con->rollback();
        
        // Delete uploaded file
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: ../create.php');
        exit();
    }
} else {
    header('Location: ../create.php');
    exit();
} 