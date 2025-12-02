/**
 * Handle POST requests
 * Register new user with biometric device
 */
public function post() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user_id']) || !isset($input['name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields: user_id, name'
        ]);
        return;
    }
    
    $user_id = $input['user_id'];
    $name = $input['name'];
    $fingerprint = isset($input['fingerprint']) ? $input['fingerprint'] : null;
    $device = isset($input['device']) ? $input['device'] : 'default_device';
    
    // Verify employee exists
    $db = new Database();
    $query = "SELECT id, first_name, last_name FROM employees WHERE id = ?";
    $result = $db->query($query, [$user_id]);
    
    if (!$result || $result->num_rows == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Employee not found with ID: ' . $user_id
        ]);
        return;
    }
    
    $employee = $result->fetch_assoc();
    
    try {
        $this->deviceManager = new BiometricDeviceManager($device);
        $result = $this->deviceManager->registerUser($user_id, $name, $fingerprint);
        
        if ($result) {
            // Log the registration in database
            $query = "INSERT INTO biometric_users (user_id, name, device, registered_at) 
                      VALUES (?, ?, ?, NOW())";
            $this->db->query($query, [$user_id, $name, $device]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee registered successfully with biometric device'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to register employee with biometric device'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}