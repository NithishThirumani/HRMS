<?php
require_once('../../connection.php');
require_once('../../classes/AppraisalCriteria.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $criteria = new AppraisalCriteria();
    $result = $criteria->toggleStatus($_POST['criteria_id']);

    echo json_encode(['success' => $result]);
}