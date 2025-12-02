<?php
session_start();
require_once '../../../connection.php';
require_once '../../../classes/EmployeeAppraisal.php';
require_once '../../../classes/AppraisalRating.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $appraisal_id = $_POST['appraisal_id'] ?? '';
    $ratings = $_POST['ratings'] ?? [];
    $comments = $_POST['comments'] ?? [];

    switch ($action) {
        case 'submit_assessment':
            $appraisalRating = new AppraisalRating();
            $success = true;

            foreach ($ratings as $criteria_id => $rating) {
                $result = $appraisalRating->updateSelfRating(
                    $appraisal_id,
                    $criteria_id,
                    $rating,
                    $comments[$criteria_id] ?? ''
                );
                if (!$result) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                $appraisal = new EmployeeAppraisal();
                $appraisal->updateStatus($appraisal_id, 'Self_Submitted');
                echo json_encode(['status' => 'success', 'message' => 'Assessment submitted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit assessment']);
            }
            break;

        case 'save_draft':
            $appraisalRating = new AppraisalRating();
            $success = true;

            foreach ($ratings as $criteria_id => $rating) {
                $result = $appraisalRating->updateSelfRating(
                    $appraisal_id,
                    $criteria_id,
                    $rating,
                    $comments[$criteria_id] ?? ''
                );
                if (!$result) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Draft saved successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save draft']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>