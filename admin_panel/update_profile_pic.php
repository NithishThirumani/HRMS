<?php
ob_start();
include('session.php');
include('connection.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic']) && isset($_POST['id'])) {
        $id = $_POST['id'];

        // Alternative file type validation
        $file = $_FILES['profile_pic'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Only JPG, PNG, and GIF files are allowed');
        }

        // Additional MIME type check using getimagesize
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            throw new Exception('Invalid image file');
        }

        $upload_dir = 'uploads/profile_pics/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file = $_FILES['profile_pic'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = $id . '_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception('File upload failed. Check directory permissions');
        }

        // Delete old profile picture if it exists
        $stmt = mysqli_prepare($con, "SELECT profile_pic FROM employees WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($con));
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $old_file);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($old_file && file_exists($upload_dir . $old_file)) {
            unlink($upload_dir . $old_file);
        }

        // Update database with new filename
        $update_stmt = mysqli_prepare($con, "UPDATE employees SET profile_pic = ? WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception('Database prepare failed: ' . mysqli_error($con));
        }

        mysqli_stmt_bind_param($update_stmt, "si", $file_name, $id);

        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception('Database update failed: ' . mysqli_error($con));
        }

        mysqli_stmt_close($update_stmt);

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile picture updated',
            'new_path' => $file_name
        ]);

    } else {
        throw new Exception('Invalid request');
    }

} catch (Exception $e) {
    error_log('Profile pic update error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Clear any buffered output
ob_end_flush();
?>