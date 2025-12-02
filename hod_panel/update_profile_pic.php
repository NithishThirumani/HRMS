<?php
include('session.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        $id = $_POST['id'];
        $file = $_FILES['profile_pic'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }
        
        // Create upload directory if it doesn't exist
       $upload_url = 'https://san-solutions.in/emps/uploads/profile_pics/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid('profile_') . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        // Delete old profile picture if exists
        $query = "SELECT profile_pic FROM employees WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_pic = mysqli_fetch_assoc($result)['profile_pic'];
        
        if ($old_pic && $old_pic != 'default.jpg') {
            $old_file = $upload_dir . basename($old_pic);
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        
        // Upload new file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Update database
            $update_query = "UPDATE employees SET profile_pic = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $new_filename, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['status'] = 'success';
                $response['message'] = 'Profile picture updated successfully';
                $response['file_path'] = 'uploads/profile_pics/' . $new_filename;
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