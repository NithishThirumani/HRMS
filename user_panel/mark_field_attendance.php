<?php
include('session.php');
include('connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid = $_SESSION['eid'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $location_address = $_POST['location_address'];
    $current_time = date('H:i:s');
    $current_date = date('Y-m-d');

    // Check if attendance already exists for today
    $check_query = "SELECT * FROM attendance WHERE eid = ? AND attendance_date = CURRENT_DATE()";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $eid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Update last_out time
        $update_query = "UPDATE attendance SET 
                        last_out = ?, 
                        location_coordinates = CONCAT(location_coordinates, ';', ?, ',', ?),
                        location_address = CONCAT(location_address, '; ', ?)
                        WHERE eid = ? AND attendance_date = CURRENT_DATE()";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "sssss", $current_time, $latitude, $longitude, $location_address, $eid);
        $success = mysqli_stmt_execute($stmt);
    } else {
        // Insert new attendance record
        $insert_query = "INSERT INTO attendance (eid, attendance_date, first_in, last_out, 
                        attendance_type, location_coordinates, location_address, week_day) 
                        VALUES (?, ?, ?, ?, 'field', ?, ?, DAYNAME(?))";
        $coordinates = $latitude . ',' . $longitude;
        $stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssssss", $eid, $current_date, $current_time, 
                             $current_time, $coordinates, $location_address, $current_date);
        $success = mysqli_stmt_execute($stmt);
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
?>