<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tab = $_POST['tab'];
        $data = json_decode($_POST['data'], true);
        
        if (!$data) {
            throw new Exception('Invalid data format');
        }

        // Store in session
        $_SESSION['emp_data'][$tab] = $data;
        
        // Log the data for debugging
        error_log("Saved {$tab} data: " . print_r($data, true));

        echo json_encode([
            'status' => 'success',
            'message' => ucfirst($tab) . ' details saved successfully'
        ]);
    } catch (Exception $e) {
        error_log("Error saving {$tab} data: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>