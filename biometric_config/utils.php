/**
 * Format attendance status
 * 
 * @param string $status Raw status from device
 * @return string Formatted status (check-in/check-out)
 */
function formatAttendanceStatus($status) {
    // Map device-specific status codes to standard format
    $status_map = [
        '0' => 'check-in',
        '1' => 'check-out',
        'in' => 'check-in',
        'out' => 'check-out',
        'i' => 'check-in',
        'o' => 'check-out'
    ];
    
    return isset($status_map[strtolower($status)]) ? $status_map[strtolower($status)] : 'unknown';
}

/**
 * Calculate working hours
 * 
 * @param string $check_in Check-in time
 * @param string $check_out Check-out time
 * @return float Working hours
 */
function calculateWorkingHours($check_in, $check_out) {
    if (empty($check_in) || empty($check_out)) {
        return 0;
    }
    
    $in_time = strtotime($check_in);
    $out_time = strtotime($check_out);
    
    if ($out_time <= $in_time) {
        return 0;
    }
    
    // Calculate difference in hours
    $hours = ($out_time - $in_time) / 3600;
    
    return round($hours, 2);
}

/**
 * Check if time is within working hours
 * 
 * @param string $time Time to check
 * @return bool Whether time is within working hours
 */
function isWithinWorkingHours($time) {
    global $attendance_settings;
    
    $start = strtotime(date('Y-m-d') . ' ' . $attendance_settings['working_hours']['start']);
    $end = strtotime(date('Y-m-d') . ' ' . $attendance_settings['working_hours']['end']);
    $check_time = strtotime($time);
    
    return ($check_time >= $start && $check_time <= $end);
}

/**
 * Get device name by ID
 * 
 * @param int $device_id Device ID
 * @return string Device name
 */
function getDeviceNameById($device_id) {
    global $biometric_config;
    
    foreach ($biometric_config as $name => $config) {
        if ($config['device_id'] == $device_id) {
            return $name;
        }
    }
    
    return 'unknown';
}

/**
 * Log error to file
 * 
 * @param string $message Error message
 * @param string $source Error source
 */
function logBiometricError($message, $source = 'general') {
    $log_dir = __DIR__ . '/logs';
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/biometric_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp] [$source] $message" . PHP_EOL;
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * Get user information by ID
 * 
 * @param int $user_id User ID
 * @return array User information
 */
function getUserById($user_id) {
    $db = new Database();
    
    $query = "SELECT e.*, l.username, l.email 
              FROM employees e 
              LEFT JOIN emp_login l ON e.id = l.emp_id 
              WHERE e.id = ?";
    $result = $db->query($query, [$user_id]);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Check if user is registered with biometric device
 * 
 * @param int $user_id User ID
 * @param string $device Device name
 * @return bool Registration status
 */
function isUserRegisteredWithDevice($user_id, $device = 'default_device') {
    $db = new Database();
    
    $query = "SELECT * FROM biometric_users WHERE user_id = ? AND device = ?";
    $result = $db->query($query, [$user_id, $device]);
    
    return ($result && $result->num_rows > 0);
}

/**
 * Get all registered biometric devices
 * 
 * @return array List of devices
 */
function getAllBiometricDevices() {
    global $biometric_config;
    
    $devices = [];
    foreach ($biometric_config as $name => $config) {
        $devices[] = [
            'name' => $name,
            'ip' => $config['ip_address'],
            'model' => $config['model'],
            'device_id' => $config['device_id']
        ];
    }
    
    return $devices;
}

/**
 * Get attendance summary for a user
 * 
 * @param int $user_id User ID
 * @param string $start_date Start date (format: 'Y-m-d')
 * @param string $end_date End date (format: 'Y-m-d')
 * @return array Attendance summary
 */
function getUserAttendanceSummary($user_id, $start_date, $end_date) {
    $db = new Database();
    
    $query = "SELECT DATE(timestamp) as date, 
              MIN(CASE WHEN status = 'check-in' THEN timestamp END) as first_check_in,
              MAX(CASE WHEN status = 'check-out' THEN timestamp END) as last_check_out
              FROM attendance_logs 
              WHERE user_id = ? AND DATE(timestamp) BETWEEN ? AND ?
              GROUP BY DATE(timestamp)
              ORDER BY DATE(timestamp)";
    
    $result = $db->query($query, [$user_id, $start_date, $end_date]);
    
    $summary = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $hours = calculateWorkingHours($row['first_check_in'], $row['last_check_out']);
            $summary[] = [
                'date' => $row['date'],
                'check_in' => $row['first_check_in'],
                'check_out' => $row['last_check_out'],
                'hours' => $hours,
                'status' => determineAttendanceStatus($row['first_check_in'], $row['last_check_out'], $hours)
            ];
        }
    }
    
    return $summary;
}

/**
 * Determine attendance status based on check-in/out times
 * 
 * @param string $check_in Check-in time
 * @param string $check_out Check-out time
 * @param float $hours Working hours
 * @return string Attendance status
 */
function determineAttendanceStatus($check_in, $check_out, $hours) {
    global $attendance_settings;
    
    if (empty($check_in)) {
        return 'absent';
    }
    
    if (empty($check_out)) {
        return 'incomplete';
    }
    
    $min_hours = isset($attendance_settings['min_working_hours']) ? 
                 $attendance_settings['min_working_hours'] : 8;
    
    if ($hours < $min_hours) {
        return 'short';
    }
    
    return 'present';
}