<?php
session_start();
require_once '../../../connection.php';
require_once '../../../classes/EmployeeAppraisal.php';
require_once '../../../classes/AppraisalRating.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $period_id = $_GET['period_id'] ?? '';
    $department_id = $_GET['department_id'] ?? '';

    $query = "SELECT ea.*, e.first_name, e.last_name, e.position, d.name as department_name,
              (SELECT AVG(hod_rating) FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as hod_rating
              FROM employee_appraisals ea
              JOIN employees e ON ea.employee_id = e.id
              JOIN department d ON e.department_id = d.id
              WHERE ea.status = 'HOD_Reviewed'";
    
    if ($period_id) {
        $query .= " AND ea.period_id = '$period_id'";
    }
    if ($department_id) {
        $query .= " AND e.department_id = '$department_id'";
    }

    $result = $con->query($query);
    $appraisals = [];
    while ($row = $result->fetch_assoc()) {
        $row['hod_rating'] = number_format($row['hod_rating'], 2);
        $appraisals[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $appraisals]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'submit_review':
            $appraisal_id = $_POST['appraisal_id'];
            $ratings = $_POST['ratings'];
            $comments = $_POST['comments'];
            
            $appraisalRating = new AppraisalRating();
            $success = true;

            foreach ($ratings as $criteria_id => $rating) {
                $result = $appraisalRating->updateHRRating(
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
                $appraisal->updateStatus($appraisal_id, 'HR_Reviewed');
                echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit review']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>