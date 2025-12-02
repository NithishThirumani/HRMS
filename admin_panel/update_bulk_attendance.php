<?php
include('session.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$employees = $_POST['employees'] ?? [];
$status = $_POST['status'] ?? '';
$date = $_POST['date'] ?? '';

// Validate input
if (empty($employees) || empty($status) || empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($con);

    foreach ($employees as $employeeId) {
        // Sanitize the employee ID
        $employeeId = mysqli_real_escape_string($con, $employeeId);
        $safeStatus = mysqli_real_escape_string($con, $status);
        $safeDate = mysqli_real_escape_string($con, $date);

        // Check if attendance record exists
        $checkQuery = "SELECT id FROM attendance WHERE employee_id = '$employeeId' AND date = '$safeDate'";
        $result = mysqli_query($con, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Update existing record
            $updateQuery = "UPDATE attendance SET status = '$safeStatus' WHERE employee_id = '$employeeId' AND date = '$safeDate'";
            mysqli_query($con, $updateQuery);
        } else {
            // Insert new record
            $insertQuery = "INSERT INTO attendance (employee_id, date, status) VALUES ('$employeeId', '$safeDate', '$safeStatus')";
            mysqli_query($con, $insertQuery);
        }
    }

    // Commit transaction
    mysqli_commit($con);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>