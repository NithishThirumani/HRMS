<?php
/**
 * Biometric Test Mode
 * 
 * Simulates biometric device communication for testing
 */

require_once 'config.php';
require_once 'database.php';

class BiometricTestMode {
    private $db;
    private $device_name;
    
    /**
     * Constructor
     * 
     * @param string $device_name Name of the device
     */
    public function __construct($device_name = 'test_device') {
        $this->db = new Database();
        $this->device_name = $device_name;
    }
    
    /**
     * Generate random attendance logs
     * 
     * @param string $start_date Start date (format: 'Y-m-d')
     * @param string $end_date End date (format: 'Y-m-d')
     * @param int $num_employees Number of employees to generate logs for
     * @return array Generated attendance logs
     */
    public function generateAttendanceLogs($start_date = null, $end_date = null, $num_employees = 5) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-7 days'));
        }
        
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }
        
        // Get employees from database
        $query = "SELECT id FROM employees LIMIT ?";
        $result = $this->db->query($query, [$num_employees]);
        
        $employees = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row['id'];
            }
        }
        
        // If no employees found, create dummy IDs
        if (empty($employees)) {
            for ($i = 1; $i <= $num_employees; $i++) {
                $employees[] = $i;
            }
        }
        
        $logs = [];
        $current_date = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end->modify('+1 day'); // Include end date
        
        while ($current_date < $end) {
            $date = $current_date->format('Y-m-d');
            
            // Skip weekends
            $day_of_week = $current_date->format('N');
            if ($day_of_week < 6) { // 6 = Saturday, 7 = Sunday
                foreach ($employees as $employee_id) {
                    // Random check-in time between 8:00 and 9:30
                    $check_in_hour = rand(8, 9);
                    $check_in_minute = $check_in_hour == 9 ? rand(0, 30) : rand(0, 59);
                    $check_in = sprintf('%s %02d:%02d:00', $date, $check_in_hour, $check_in_minute);
                    
                    // Random check-out time between 16:30 and 18:00
                    $check_out_hour = rand(16, 18);
                    $check_out_minute = $check_out_hour == 16 ? rand(30, 59) : ($check_out_hour == 18 ? 0 : rand(0, 59));
                    $check_out = sprintf('%s %02d:%02d:00', $date, $check_out_hour, $check_out_minute);
                    
                    // Add check-in log
                    $logs[] = [
                        'user_id' => $employee_id,
                        'timestamp' => $check_in,
                        'status' => 'check-in'
                    ];
                    
                    // Add check-out log
                    $logs[] = [
                        'user_id' => $employee_id,
                        'timestamp' => $check_out,
                        'status' => 'check-out'
                    ];
                }
            }
            
            $current_date->modify('+1 day');
        }
        
        return $logs;
    }
    
    /**
     * Simulate connection to device
     * 
     * @return bool Always returns true in test mode
     */
    public function connect() {
        // Simulate connection delay
        usleep(500000); // 0.5 seconds
        return true;
    }
    
    /**
     * Simulate disconnection from device
     */
    public function disconnect() {
        // Nothing to do in test mode
    }
    
    /**
     * Get simulated attendance logs
     * 
     * @param string $start_time Start time for logs
     * @param string $end_time End time for logs
     * @return array Simulated attendance logs
     */
    public function getAttendanceLogs($start_time = null, $end_time = null) {
        $start_date = $start_time ? date('Y-m-d', strtotime($start_time)) : null;
        $end_date = $end_time ? date('Y-m-d', strtotime($end_time)) : null;
        
        return $this->generateAttendanceLogs($start_date, $end_date);
    }
    
    /**
     * Simulate clearing attendance logs
     * 
     * @return bool Always returns true in test mode
     */
    public function clearAttendanceLogs() {
        return true;
    }
    
    /**
     * Simulate registering user with device
     * 
     * @param int $user_id User ID
     * @param string $name User name
     * @param string $fingerprint Fingerprint data (not used in test mode)
     * @return bool Always returns true in test mode
     */
    public function registerUser($user_id, $name, $fingerprint = null) {
        // In test mode, just record the registration in database
        $query = "INSERT INTO biometric_users (user_id, name, device, registered_at) 
                  VALUES (?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE name = ?, updated_at = NOW()";
        
        $this->db->query($query, [$user_id, $name, $this->device_name, $name]);
        
        return true;
    }
    
    /**
     * Simulate updating user
     * 
     * @param int $user_id User ID
     * @param string $name User name
     * @param string $fingerprint Fingerprint data (not used in test mode)
     * @return bool Always returns true in test mode
     */
    public function updateUser($user_id, $name = null, $fingerprint = null) {
        return true;
    }
    
    /**
     * Simulate deleting user
     * 
     * @param int $user_id User ID
     * @return bool Always returns true in test mode
     */
    public function deleteUser($user_id) {
        // Remove from biometric_users table
        $query = "DELETE FROM biometric_users WHERE user_id = ? AND device = ?";
        $this->db->query($query, [$user_id, $this->device_name]);
        
        return true;
    }
}