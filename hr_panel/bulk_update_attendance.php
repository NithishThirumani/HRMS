<?php
include('session.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'];
    $status = $_POST['status'];
    $type = $_POST['type'];

    // Convert status to appropriate values
    $first_in = null;
    $last_out = null;
    $total_hours = 0;

    switch($status) {
        case 'Present':
            $first_in = date('H:i:s', strtotime('09:00:00'));
            $last_out = date('H:i:s', strtotime('18:00:00'));
            $total_hours = 9;
            break;
        case 'Half Day':
            $first_in = date('H:i:s', strtotime('09:00:00'));
            $last_out = date('H:i:s', strtotime('13:00:00'));
            $total_hours = 4;
            break;
        case 'Absent':
            $first_in = null;
            $last_out = null;
            $total_hours = 0;
            break;
    }

    $success = true;
    foreach ($ids as $id) {
        $query = "UPDATE attendance SET 
                 status = ?, 
                 attendance_type = ?,
                 first_in = ?,
                 last_out = ?,
                 total_hours = ?
                 WHERE id = ?";
                 
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssssdi", $status, $type, $first_in, $last_out, $total_hours, $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
            break;
        }
    }

    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>