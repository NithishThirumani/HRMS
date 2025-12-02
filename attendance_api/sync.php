<?php
/**
 * Sync Controller
 * 
 * Handles synchronization of attendance data from biometric devices
 */

require_once '../biometric_config/config.php';
require_once '../biometric_config/database.php';
require_once '../biometric_config/device_manager.php';

class SyncController {
    private $deviceManager;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
        $this->deviceManager = new BiometricDeviceManager();
    }
    
    /**
     * Handle GET requests
     * Get sync status or last sync time
     */
    public function get() {
        $query = "SELECT * FROM sync_logs ORDER BY sync_time DESC LIMIT 1";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                'status' => 'success',
                'last_sync' => $row['sync_time'],
                'records_synced' => $row['records_synced'],
                'sync_status' => $row['status']
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'last_sync' => null,
                'message' => 'No sync history found'
            ]);
        }
    }
    
    /**
     * Handle POST requests
     * Trigger manual sync from devices
     */
    public function post() {
        $input = json_decode(file_get_contents('php://input'), true);
        $device = isset($input['device']) ? $input['device'] : 'default_device';
        
        try {
            $this->deviceManager = new BiometricDeviceManager($device);
            $logs = $this->deviceManager->getAttendanceLogs();
            
            if (isset($logs['error'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $logs['error']
                ]);
                return;
            }
            
            // Process and save logs to database
            $count = $this->processAttendanceLogs($logs);
            
            // Log the sync operation
            $query = "INSERT INTO sync_logs (device, records_synced, status, sync_time) 
                      VALUES (?, ?, 'success', NOW())";
            $this->db->query($query, [$device, $count]);
            
            echo json_encode([
                'status' => 'success',
                'message' => "Successfully synced $count attendance records",
                'records' => $count
            ]);
            
        } catch (Exception $e) {
            // Log the sync error
            $query = "INSERT INTO sync_logs (device, status, error_message, sync_time) 
                      VALUES (?, 'error', ?, NOW())";
            $this->db->query($query, [$device, $e->getMessage()]);
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Process attendance logs and save to database
     * 
     * @param array $logs Attendance logs from device
     * @return int Number of records processed
     */
    private function processAttendanceLogs($logs) {
        $count = 0;
        
        foreach ($logs as $log) {
            // Verify employee exists
            $query = "SELECT id FROM employees WHERE id = ?";
            $result = $this->db->query($query, [$log['user_id']]);
            
            if (!$result || $result->num_rows == 0) {
                // Skip records for unknown employees
                continue;
            }
            
            // Check if log already exists
            $query = "SELECT id FROM attendance_logs 
                      WHERE user_id = ? AND timestamp = ?";
            $result = $this->db->query($query, [$log['user_id'], $log['timestamp']]);
            
            if ($result && $result->num_rows == 0) {
                // Insert new log
                $query = "INSERT INTO attendance_logs (user_id, timestamp, status, device_id) 
                          VALUES (?, ?, ?, ?)";
                $this->db->query($query, [
                    $log['user_id'], 
                    $log['timestamp'], 
                    $log['status'], 
                    $this->deviceManager->device_id
                ]);
                
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Handle PUT requests
     * Update sync settings
     */
    public function put() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['settings'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required field: settings'
            ]);
            return;
        }
        
        $settings = $input['settings'];
        
        // Update settings in database
        foreach ($settings as $key => $value) {
            $query = "UPDATE biometric_settings SET value = ? WHERE name = ?";
            $this->db->query($query, [$value, "sync_$key"]);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Sync settings updated successfully'
        ]);
    }
    
    /**
     * Handle DELETE requests
     * Clear sync history or logs
     */
    public function delete() {
        $input = json_decode(file_get_contents('php://input'), true);
        $device = isset($input['device']) ? $input['device'] : null;
        $clear_device = isset($input['clear_device']) ? $input['clear_device'] : false;
        
        if ($clear_device && $device) {
            try {
                $this->deviceManager = new BiometricDeviceManager($device);
                $result = $this->deviceManager->clearAttendanceLogs();
                
                if ($result) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => "Successfully cleared attendance logs from device: $device"
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Failed to clear attendance logs from device: $device"
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            // Clear sync history from database
            $query = "DELETE FROM sync_logs";
            if ($device) {
                $query .= " WHERE device = ?";
                $this->db->query($query, [$device]);
            } else {
                $this->db->query($query);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Sync history cleared successfully'
            ]);
        }
    }
}