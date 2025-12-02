<?php
session_start();
require_once '../../../connection.php';
require_once '../../../classes/EmployeeAppraisal.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $employee_id = $_SESSION['user_id'];
    $period_id = $_GET['period_id'] ?? '';

    // Get appraisal history
    $query = "SELECT 
        ap.start_date, ap.end_date,
        ea.status, ea.appraisal_id,
        ROUND(AVG(ar.self_rating), 2) as self_rating,
        ROUND(AVG(ar.hod_rating), 2) as hod_rating,
        ROUND(AVG(ar.hr_rating), 2) as hr_rating,
        ROUND(AVG((ar.self_rating + COALESCE(ar.hod_rating, 0) + COALESCE(ar.hr_rating, 0))/3), 2) as final_rating
        FROM employee_appraisals ea
        JOIN appraisal_periods ap ON ea.period_id = ap.period_id
        LEFT JOIN appraisal_ratings ar ON ea.appraisal_id = ar.appraisal_id
        WHERE ea.employee_id = ? " .
        ($period_id ? "AND ea.period_id = ? " : "") .
        "GROUP BY ea.appraisal_id
        ORDER BY ap.start_date DESC";

    $stmt = $con->prepare($query);
    if ($period_id) {
        $stmt->bind_param('ii', $employee_id, $period_id);
    } else {
        $stmt->bind_param('i', $employee_id);
    }
    $stmt->execute();
    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get rating trends
    $query = "SELECT 
        ap.start_date,
        ROUND(AVG(ar.self_rating), 2) as self_rating,
        ROUND(AVG(ar.hod_rating), 2) as hod_rating,
        ROUND(AVG(ar.hr_rating), 2) as hr_rating
        FROM employee_appraisals ea
        JOIN appraisal_periods ap ON ea.period_id = ap.period_id
        LEFT JOIN appraisal_ratings ar ON ea.appraisal_id = ar.appraisal_id
        WHERE ea.employee_id = ?
        GROUP BY ea.period_id
        ORDER BY ap.start_date";

    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $trends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get criteria performance
    $query = "SELECT 
        ac.name as criteria_name,
        ROUND(AVG(ar.self_rating), 2) as avg_self,
        ROUND(AVG(ar.hod_rating), 2) as avg_hod,
        ROUND(AVG(ar.hr_rating), 2) as avg_hr
        FROM appraisal_criteria ac
        JOIN appraisal_ratings ar ON ac.id = ar.criteria_id
        JOIN employee_appraisals ea ON ar.appraisal_id = ea.appraisal_id
        WHERE ea.employee_id = ?
        GROUP BY ac.id, ac.name";

    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $criteria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'history' => $history,
        'trends' => $trends,
        'criteria' => $criteria
    ]);
}
?>