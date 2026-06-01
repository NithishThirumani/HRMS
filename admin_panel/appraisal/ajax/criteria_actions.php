<?php
require_once __DIR__ . '/../bootstrap_session.php';
require_once dirname(__DIR__, 3) . '/connection.php';
require_once dirname(__DIR__, 3) . '/classes/AppraisalCriteria.php';

header('Content-Type: application/json');

$criteria = new AppraisalCriteria();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = $_POST['criteria_name'];
            $description = $_POST['description'];
            $weightage = $_POST['weightage'];
            
            if ($criteria->addCriteria($name, $description, (int) $weightage)) {
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