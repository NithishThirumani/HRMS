<?php
require_once('../../connection.php');
require_once('../../classes/AppraisalCriteria.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $criteria = new AppraisalCriteria();
    $result = $criteria->updateCriteria(
        $_POST['criteria_id'],
        $_POST['criteria_name'],
        $_POST['description'],
        $_POST['weightage']
    );

    echo json_encode(['success' => $result]);
}