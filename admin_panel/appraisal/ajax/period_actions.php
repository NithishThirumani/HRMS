<?php
session_start();
require_once '../../../connection.php';
require_once '../../../classes/AppraisalPeriod.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$period = new AppraisalPeriod();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            if ($period->addPeriod($start_date, $end_date)) {
                echo json_encode(['status' => 'success', 'message' => 'Period added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add period']);
            }
            break;

        case 'edit':
            $period_id = $_POST['period_id'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            if ($period->updatePeriod($period_id, $start_date, $end_date)) {
                echo json_encode(['status' => 'success', 'message' => 'Period updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update period']);
            }
            break;

        case 'delete':
            $period_id = $_POST['period_id'];
            
            if ($period->deletePeriod($period_id)) {
                echo json_encode(['status' => 'success', 'message' => 'Period deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete period']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>