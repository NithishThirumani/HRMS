<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../session.php');
require_once '../../../connection.php';
require_once '../../../classes/AppraisalCriteria.php';

// Check if user is logged in and is HOD (session.php already handles this)
// The session.php file will redirect to login if not properly authenticated

$criteria = new AppraisalCriteria();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = $_POST['criteria_name'];
            $description = $_POST['description'];
            $weightage = $_POST['weightage'];
            
            if ($criteria->addCriteria($name, $description, $weightage)) {
                echo json_encode(['status' => 'success', 'message' => 'Criteria added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add criteria']);
            }
            break;

        case 'edit':
            $id = $_POST['criteria_id'];
            $name = $_POST['criteria_name'];
            $description = $_POST['description'];
            $weightage = $_POST['weightage'];
            
            if ($criteria->updateCriteria($id, $name, $description, $weightage)) {
                echo json_encode(['status' => 'success', 'message' => 'Criteria updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update criteria']);
            }
            break;

        case 'toggle':
            $id = $_POST['criteria_id'];
            
            if ($criteria->toggleStatus($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>