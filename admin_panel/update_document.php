<?php
include('session.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        $id = $_POST['employee_id'];
        $document_type = $_POST['document_type']; // 'visa' or 'passport'
        $file = $_FILES['document'];
        
        // Validate file type
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only PDF and images are allowed.');
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }
        
        // Create upload directory
        $upload_dir = dirname(__FILE__) . '/uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = $document_type . '_' . $id . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        // Get old document
        $column_name = $document_type . '_document';
        $query = "SELECT $column_name FROM employees WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_doc = mysqli_fetch_assoc($result)[$column_name];
        
        // Delete old document if exists
        if ($old_doc) {
            $old_file = $upload_dir . basename($old_doc);
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        // Upload new file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Update database
            $update_query = "UPDATE employees SET $column_name = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $new_filename, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['status'] = 'success';
                $response['message'] = 'Document updated successfully';
                $response['file_path'] = 'uploads/documents/' . $new_filename;
            } else {
                throw new Exception('Failed to update database.');
            }
        } else {
            throw new Exception('Failed to upload file.');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>