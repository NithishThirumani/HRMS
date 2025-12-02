<?php
/**
 * Biometric Device Manager
 * 
 * Handles communication with biometric devices
 */

require_once 'config.php';

class BiometricDeviceManager {
    private $ip;
    private $port;
    public $device_id;
    private $timeout;
    private $socket;
    private $comm_key;
    
    /**
     * Constructor
     * 
     * @param string $device_name Name of the device from config
     */
    public function __construct($device_name = 'default_device') {
        global $biometric_config;
        
        if (!isset($biometric_config[$device_name])) {
            throw new Exception("Device configuration not found for: $device_name");
        }
        
        $config = $biometric_config[$device_name];
        $this->ip = $config['ip_address'];
        $this->port = $config['port'];
        $this->device_id = $config['device_id'];
        $this->timeout = $config['timeout'];
        $this->comm_key = $config['comm_key'];
    }
    
    /**
     * Connect to the biometric device
     * 
     * @return bool Connection status
     */
    public function connect() {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        
        if ($this->socket === false) {
            return false;
        }
        
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->timeout, 'usec' => 0]);
        
        return true;
    }
    
    /**
     * Disconnect from the biometric device
     */
    public function disconnect() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
    
    /**
     * Get attendance logs from device
     * 
     * @param string $start_time Start time for logs (format: 'Y-m-d H:i:s')
     * @param string $end_time End time for logs (format: 'Y-m-d H:i:s')
     * @return array Array of attendance logs
     */
    public function getAttendanceLogs($start_time = null, $end_time = null) {
        if (!$this->connect()) {
            return ['error' => 'Failed to connect to device'];
        }
        
        // Implementation would depend on the specific device protocol
        // This is a simplified example
        $command = $this->createCommand('GET_ATTENDANCE');
        $response = $this->sendCommand($command);
        
        $this->disconnect();
        
        return $this->parseAttendanceLogs($response);
    }
    
    /**
     * Clear attendance logs from device
     * 
     * @return bool Success status
     */
    public function clearAttendanceLogs() {
        if (!$this->connect()) {
            return false;
        }
        
        $command = $this->createCommand('CLEAR_ATTENDANCE');
        $response = $this->sendCommand($command);
        
        $this->disconnect();
        
        return $this->parseResponse($response);
    }
    
    /**
     * Register user with biometric device
     * 
     * @param int $user_id User ID
     * @param string $name User name
     * @param string $fingerprint Fingerprint template data
     * @return bool Success status
     */
    public function registerUser($user_id, $name, $fingerprint = null) {
        if (!$this->connect()) {
            return false;
        }
        
        $command = $this->createCommand('SET_USER', [
            'user_id' => $user_id,
            'name' => $name,
            'fingerprint' => $fingerprint
        ]);
        
        $response = $this->sendCommand($command);
        
        $this->disconnect();
        
        return $this->parseResponse($response);
    }
    
    /**
     * Update user information on biometric device
     * 
     * @param int $user_id User ID
     * @param string $name User name
     * @param string $fingerprint Fingerprint template data
     * @return bool Success status
     */
    public function updateUser($user_id, $name = null, $fingerprint = null) {
        if (!$this->connect()) {
            return false;
        }
        
        $command = $this->createCommand('UPDATE_USER', [
            'user_id' => $user_id,
            'name' => $name,
            'fingerprint' => $fingerprint
        ]);
        
        $response = $this->sendCommand($command);
        
        $this->disconnect();
        
        return $this->parseResponse($response);
    }
    
    /**
     * Delete user from biometric device
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function deleteUser($user_id) {
        if (!$this->connect()) {
            return false;
        }
        
        $command = $this->createCommand('DELETE_USER', [
            'user_id' => $user_id
        ]);
        
        $response = $this->sendCommand($command);
        
        $this->disconnect();
        
        return $this->parseResponse($response);
    }
    
    /**
     * Create command packet for device
     * 
     * @param string $command_type Type of command
     * @param array $data Additional data for command
     * @return string Command packet
     */
    private function createCommand($command_type, $data = []) {
        // Implementation would depend on the specific device protocol
        // This is a placeholder
        return json_encode([
            'command' => $command_type,
            'device_id' => $this->device_id,
            'data' => $data
        ]);
    }
    
    /**
     * Send command to device
     * 
     * @param string $command Command packet
     * @return string Response from device
     */
    private function sendCommand($command) {
        // Implementation would depend on the specific device protocol
        // This is a simplified example
        socket_sendto($this->socket, $command, strlen($command), 0, $this->ip, $this->port);
        
        $response = '';
        socket_recvfrom($this->socket, $response, 1024, 0, $this->ip, $this->port);
        
        return $response;
    }
    
    /**
     * Parse attendance logs from device response
     * 
     * @param string $response Device response
     * @return array Parsed attendance logs
     */
    private function parseAttendanceLogs($response) {
        // Implementation would depend on the specific device protocol
        // This is a placeholder
        $logs = [];
        
        // Parse the response and convert to array of logs
        // Example format:
        // [
        //     ['user_id' => 1, 'timestamp' => '2023-06-01 08:30:00', 'status' => 'check-in'],
        //     ['user_id' => 2, 'timestamp' => '2023-06-01 08:45:00', 'status' => 'check-in'],
        // ]
        
        return $logs;
    }
    
    /**
     * Parse general response from device
     * 
     * @param string $response Device response
     * @return bool Success status
     */
    private function parseResponse($response) {
        // Implementation would depend on the specific device protocol
        // This is a placeholder
        return strpos($response, 'SUCCESS') !== false;
    }
}