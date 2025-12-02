<?php
include('session.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = isset($_POST['aid']) ? $_POST['aid'] : null;
    $eid = $_POST['employee'];
    $date = $_POST['date'];
    $first_in = $_POST['first_in'];
    $last_out = $_POST['last_out'];
    $type = $_POST['type'];
    
    // Calculate total hours and status
    $total_hours = 0;
    if ($first_in && $last_out) {
        $total_hours = round((strtotime($last_out) - strtotime($first_in))/3600, 2);
        if ($total_hours >= 8) {
            $status = 'Present';
        } elseif ($total_hours >= 4) {
            $status = 'Half Day';
        } else {
            $status = 'Absent';
        }
    } else {
        $status = 'In Progress';
    }

    if ($aid) {
        // Update existing record
        $query = "UPDATE attendance SET 
                 eid = ?, 
                 attendance_date = ?,
                 first_in = ?,
                 last_out = ?,
                 total_hours = ?,
                 attendance_type = ?,
                 status = ?
                 WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "isssdssi", $eid, $date, $first_in, $last_out, $total_hours, $type, $status, $aid);
    } else {
        // Insert new record
        $query = "INSERT INTO attendance 
                 (eid, attendance_date, first_in, last_out, total_hours, attendance_type, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "isssdss", $eid, $date, $first_in, $last_out, $total_hours, $type, $status);
    }
    
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>