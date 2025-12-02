<?php
include('../connection.php');
header('Content-Type: application/json');

// API Authentication (basic example - enhance security as needed)
$api_key = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
if ($api_key !== 'your_secure_api_key') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get JSON data from biometric device
$json_data = file_get_contents('php://input');
$device_data = json_decode($json_data, true);

if (!$device_data || !isset($device_data['records'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data format']);
    exit;
}

try {
    include('sync_attendance.php');
    
    // Process the attendance records
    syncBiometricData($device_data['records']);
    
    // Log successful sync
    $log_query = "INSERT INTO sync_logs (device_id, sync_time, records_count, status) 
                  VALUES (?, NOW(), ?, 'success')";
    $stmt = $con->prepare($log_query);
    $records_count = count($device_data['records']);
    $device_id = $device_data['device_id'] ?? 'unknown';
    $stmt->bind_param('si', $device_id, $records_count);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Data synchronized successfully',
        'records_processed' => $records_count
    ]);

} catch (Exception $e) {
    // Log sync error
    $error_query = "INSERT INTO sync_logs (device_id, sync_time, error_message, status) 
                    VALUES (?, NOW(), ?, 'error')";
    $stmt = $con->prepare($error_query);
    $error_msg = $e->getMessage();
    $device_id = $device_data['device_id'] ?? 'unknown';
    $stmt->bind_param('ss', $device_id, $error_msg);
    $stmt->execute();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Sync failed',
        'message' => $e->getMessage()
    ]);
}

/*
Expected JSON format from biometric device:
{
    "device_id": "DEVICE001",
    "records": [
        {
            "staff_code": "EMP001",
            "timestamp": "2023-08-17 09:00:00",
            "type": "IN"
        },
        {
            "staff_code": "EMP001",
            "timestamp": "2023-08-17 17:00:00",
            "type": "OUT"
        }
    ]
}
*/
?>