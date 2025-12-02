<?php
include('session.php');
header('Content-Type: application/json');

try {
    $eid = $_POST['eid'];
    $date = $_POST['attendance_date'];
    $first_in = $_POST['first_in'];
    $last_out = $_POST['last_out'];
    $device_type = $_POST['device_type'];
    
    // Calculate total hours
    $total_hours = $last_out ? (strtotime($last_out) - strtotime($first_in)) / 3600 : 0;
    
    // Determine status
    $status = ($total_hours >= 8) ? 'Present' : (($total_hours >= 4) ? 'Half Day' : 'Absent');
    
    $query = "INSERT INTO attendance (eid, attendance_date, first_in, last_out, 
              total_hours, attendance_type, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
              ON DUPLICATE KEY UPDATE 
              first_in = VALUES(first_in),
              last_out = VALUES(last_out),
              total_hours = VALUES(total_hours),
              status = VALUES(status)";
              
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssdss", 
        $eid, $date, $first_in, $last_out, $total_hours, $device_type, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance recorded successfully'
        ]);
    } else {
        throw new Exception(mysqli_error($con));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>