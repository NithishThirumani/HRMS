<?php
include('session.php');
header('Content-Type: application/json');

try {
    $type = $_POST['type'];
    $attendance_date = $_POST['attendance_date'];
    
    // Get employee ID
    $emp_query = "SELECT e.eid FROM employees e 
                  JOIN emp_login el ON e.eid = el.emp_id 
                  WHERE el.user_name = ?";
    $stmt = mysqli_prepare($con, $emp_query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $emp_data = mysqli_fetch_assoc($result);
    
    if (!$emp_data) {
        throw new Exception('Employee not found');
    }
    
    $current_time = date('H:i:s');
    $location_data = '';
    
    if ($type === 'field') {
        $location_data = $_POST['latitude'] . ',' . $_POST['longitude'];
    }
    
    // Check existing attendance
    $check_query = "SELECT * FROM attendance WHERE eid = ? AND attendance_date = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $emp_data['eid'], $attendance_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    
    if (!$existing) {
        // First entry of the day
        $query = "INSERT INTO attendance (eid, attendance_date, week_day, first_in, attendance_type, 
                                        shift_type, location_coordinates, location_address, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'In Progress', NOW())";
        $stmt = mysqli_prepare($con, $query);
        
        $week_day = date('l', strtotime($attendance_date));
        $shift_type = 'day'; // Default shift, you might want to make this dynamic
        $location_address = $type === 'field' ? 'Field Location' : 'Office';
        
        mysqli_stmt_bind_param($stmt, "ssssssss", 
            $emp_data['eid'],
            $attendance_date,
            $week_day,
            $current_time,
            $type,
            $shift_type,
            $location_data,
            $location_address
        );
        
        // Set method based on type
        $method = ($type === 'field') ? 'Mobile' : 'Web Portal';
        
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $emp_data['eid'], 
            $attendance_date,
            $current_time,
            $type,
            $method,
            $location_data
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Attendance marked successfully at ' . $current_time
            ]);
        } else {
            throw new Exception(mysqli_error($con));
        }
    } else if (!$existing['last_out']) {
        // Mark exit time
        $total_hours = (strtotime($current_time) - strtotime($existing['first_in'])) / 3600;
        $status = ($total_hours >= 8) ? 'Present' : (($total_hours >= 4) ? 'Half Day' : 'Absent');
        
        $query = "UPDATE attendance SET last_out = ?, total_hours = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sdsi", 
            $current_time,
            $total_hours,
            $status,
            $existing['id']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Exit time recorded successfully at ' . $current_time
            ]);
        } else {
            throw new Exception(mysqli_error($con));
        }
    } else {
        throw new Exception('Attendance already marked for today');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>