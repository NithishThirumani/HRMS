<?php
// This file will receive data from the biometric device
include('../connection.php');

// Receive data from biometric device (usually through API or direct database connection)
function syncBiometricData($deviceData) {
    global $con;
    
    // Sample data structure from device:
    // staff_code (eid), timestamp, type (IN/OUT)
    
    $stmt = $con->prepare("INSERT INTO attendance 
        (eid, attendance_date, first_in, last_out, attendance_type) 
        VALUES (?, CURRENT_DATE(), 
            CASE WHEN ? = 'IN' THEN ? ELSE NULL END,
            CASE WHEN ? = 'OUT' THEN ? ELSE NULL END,
            'biometric')
        ON DUPLICATE KEY UPDATE 
            last_out = CASE WHEN ? = 'OUT' THEN ? ELSE last_out END,
            first_in = CASE WHEN ? = 'IN' AND first_in IS NULL THEN ? ELSE first_in END");
            
    // Process each attendance record
    foreach($deviceData as $record) {
        $stmt->bind_param("ssssssss", 
            $record['staff_code'], 
            $record['type'], $record['timestamp'],
            $record['type'], $record['timestamp'],
            $record['type'], $record['timestamp'],
            $record['type'], $record['timestamp']
        );
        $stmt->execute();
    }
}
?>