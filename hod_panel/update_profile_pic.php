<?php
ob_start();
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/includes/hrms_paths.php';
require_once dirname(__DIR__) . '/includes/employee_registration_helpers.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

header('Content-Type: application/json');

$panel = basename(__DIR__);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_pic'], $_POST['id'])) {
        throw new Exception('Invalid request');
    }

    $id = (int) $_POST['id'];
    if ($id <= 0) {
        throw new Exception('Invalid employee id');
    }

    $storedPath = hrms_save_employee_upload(
        $_FILES['profile_pic'],
        $panel,
        'profile_pics',
        'profile_' . $id,
        ['jpg', 'jpeg', 'png', 'gif'],
        false,
        5242880,
        true
    );

    $stmt = mysqli_prepare($con, 'SELECT profile_pic FROM employees WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $oldPath);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!empty($oldPath)) {
        $oldFile = dirname(__DIR__) . '/' . $panel . '/' . ltrim(str_replace('\\', '/', $oldPath), '/');
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }

    $update = mysqli_prepare($con, 'UPDATE employees SET profile_pic = ? WHERE id = ?');
    mysqli_stmt_bind_param($update, 'si', $storedPath, $id);
    if (!mysqli_stmt_execute($update)) {
        throw new Exception('Database update failed: ' . mysqli_error($con));
    }
    mysqli_stmt_close($update);

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile picture updated',
        'new_path' => $storedPath,
        'url' => hrms_employee_upload_url($storedPath, $panel),
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
?>