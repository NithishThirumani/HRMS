<?php
session_start();
require_once(__DIR__ . '/../../../connection.php');
require_once(__DIR__ . '/../../../classes/AppraisalPeriod.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $period = new AppraisalPeriod();
    $result = $period->addPeriod($_POST['start_date'], $_POST['end_date']);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add period']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}