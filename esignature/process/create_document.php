<?php
require_once '../../config.php';
require_once '../../connection.php';
require_once '../includes/auth_user.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

// Get user details based on role
$resolved = esign_resolve_user($con);
if (!$resolved) {
    header('Location: ../../login.php');
    exit();
}
$user = $resolved['user'];
$user_type = $resolved['user_type'];

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
    $upload_dir = __DIR__ . '/../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $disk_path = $upload_dir . $filename;
    $filepath = 'uploads/documents/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $disk_path)) {
        $_SESSION['error'] = "Failed to upload file. Please try again.";
        header('Location: ../create.php');
        exit();
    }

    // Start transaction
    $con->begin_transaction();

    try {
        $total_levels = count($_POST['approvers']);
        if ($total_levels < 1) {
            throw new Exception('At least one approver is required.');
        }

        $expiry_date = null;
        if (isset($_POST['has_expiry']) && !empty($_POST['expiry_date'])) {
            $expiry_date = $_POST['expiry_date'];
        }

        $file_type = 'pdf';
        $stmt = $con->prepare("INSERT INTO esign_documents (title, document_type, description, file_path, file_type, created_by, status, current_level, total_levels, expiry_date) VALUES (?, ?, ?, ?, ?, ?, 'pending', 1, ?, ?)");
        
        $stmt->bind_param("sssssiis", 
            $_POST['title'],
            $_POST['document_type'],
            $_POST['description'],
            $filepath,
            $file_type,
            $user['id'],
            $total_levels,
            $expiry_date
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create document record: ' . $stmt->error);
        }

        $document_id = $con->insert_id;

        // Create approval workflow (role_id is required in DB)
        $stmt = $con->prepare("INSERT INTO esign_workflow (document_id, level, approver_id, role_id, approver_type, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        
        foreach ($_POST['approvers'] as $index => $approver) {
            $parts = explode('_', (string)$approver, 2);
            if (count($parts) !== 2) {
                throw new Exception('Invalid approver selected.');
            }
            list($type, $id) = $parts;
            $approverId = (int)$id;
            $roleId = esign_approver_role_id($con, $type, $approverId);
            
            $stmt->bind_param("iiiis", 
                $document_id,
                $index,
                $approverId,
                $roleId,
                $type
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create approval workflow: " . $stmt->error);
            }
        }

        // Commit transaction
        $con->commit();

        $_SESSION['success'] = "Document created successfully.";
        header('Location: ../documents.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $con->rollback();
        
        // Delete uploaded file
        if (file_exists($disk_path)) {
            unlink($disk_path);
        }
        
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: ../create.php');
        exit();
    }
} else {
    header('Location: ../create.php');
    exit();
} 