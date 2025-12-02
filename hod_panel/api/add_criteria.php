<?php
session_start();
require_once('../../connection.php');
require_once('../../classes/AppraisalCriteria.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['criteria_name']) || !isset($_POST['description']) || !isset($_POST['weightage'])) {
            throw new Exception('Missing required fields');
        }

        $criteria = new AppraisalCriteria();
        $result = $criteria->addCriteria(
            $_POST['criteria_name'],
            $_POST['description'],
            (int)$_POST['weightage']
        );

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Criteria added successfully']);
        } else {
            throw new Exception('Failed to add criteria');
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}