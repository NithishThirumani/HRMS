<?php
include('session.php');
require 'vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    if ($_FILES['attendance_file']['error'] > 0) {
        throw new Exception('File upload error');
    }

    $inputFileName = $_FILES['attendance_file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Remove header row
    array_shift($rows);
    
    $success = 0;
    $failed = 0;
    
    foreach ($rows as $row) {
        $eid = $row[0];
        $date = date('Y-m-d', strtotime($row[1]));
        $first_in = $row[2];
        $last_out = $row[3];
        $device_type = $_POST['device_type'];
        
        // Calculate total hours
        $total_hours = (strtotime($last_out) - strtotime($first_in)) / 3600;
        
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
            $success++;
        } else {
            $failed++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully imported $success records. Failed: $failed"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>