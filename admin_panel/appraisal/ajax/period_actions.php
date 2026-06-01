<?php
require_once __DIR__ . '/../bootstrap_session.php';
require_once dirname(__DIR__, 3) . '/connection.php';
require_once dirname(__DIR__, 3) . '/classes/AppraisalPeriod.php';

header('Content-Type: application/json');

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