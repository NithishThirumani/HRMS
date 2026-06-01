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
        // Basic validation: ensure required columns exist
        $eid = isset($row[0]) ? trim($row[0]) : '';
        $raw_date = isset($row[1]) ? trim($row[1]) : '';
        $first_in = isset($row[2]) ? trim($row[2]) : '';
        $last_out = isset($row[3]) ? trim($row[3]) : '';
        $device_type = isset($_POST['device_type']) ? $_POST['device_type'] : '';

        if ($eid === '' || $raw_date === '') {
            $failed++;
            continue;
        }

        $date = date('Y-m-d', strtotime($raw_date));

        // Calculate total hours safely
        $total_hours = 0.0;
        if (!empty($first_in) && !empty($last_out)) {
            $t1 = strtotime($first_in);
            $t2 = strtotime($last_out);
            if ($t1 !== false && $t2 !== false && $t2 >= $t1) {
                $total_hours = ($t2 - $t1) / 3600.0;
            }
        }

        // Determine status
        if ($total_hours >= 8) $status = 'Present';
        elseif ($total_hours >= 4) $status = 'Half Day';
        else $status = 'Absent';

        $query = "INSERT INTO attendance (eid, attendance_date, first_in, last_out, 
                  total_hours, attendance_type, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE 
                  first_in = VALUES(first_in),
                  last_out = VALUES(last_out),
                  total_hours = VALUES(total_hours),
                  status = VALUES(status)";

        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            $failed++;
            error_log('Prepare failed: ' . mysqli_error($con));
            continue;
        }

        // Bind params: eid (s), date (s), first_in (s), last_out (s), total_hours (d), device_type (s), status (s)
        mysqli_stmt_bind_param($stmt, "ssssdss", $eid, $date, $first_in, $last_out, $total_hours, $device_type, $status);

        if (mysqli_stmt_execute($stmt)) {
            $success++;
        } else {
            $failed++;
            error_log('Execute failed: ' . mysqli_stmt_error($stmt));
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