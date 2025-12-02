<?php
session_start();
require_once '../../../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $department_id = $_GET['department_id'];
    $period_id = $_GET['period_id'];

    // Get employee performance data
    $query = "SELECT 
        e.id, e.first_name, e.last_name, e.position,
        ea.status, ea.appraisal_id,
        (SELECT AVG(self_rating) FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as self_rating,
        (SELECT AVG(hod_rating) FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as hod_rating,
        (SELECT AVG(hr_rating) FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as hr_rating,
        (SELECT AVG((self_rating + hod_rating + COALESCE(hr_rating, 0))/3) 
         FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as final_rating
        FROM employees e
        LEFT JOIN employee_appraisals ea ON e.id = ea.employee_id
        WHERE e.department_id = ? AND ea.period_id = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $department_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $row['self_rating'] = number_format($row['self_rating'], 2);
        $row['hod_rating'] = number_format($row['hod_rating'], 2);
        $row['hr_rating'] = number_format($row['hr_rating'], 2);
        $row['final_rating'] = number_format($row['final_rating'], 2);
        $employees[] = $row;
    }

    // Get rating distribution
    $query = "SELECT 
        FLOOR(AVG((self_rating + hod_rating + COALESCE(hr_rating, 0))/3)) as rating_range,
        COUNT(*) as count
        FROM employee_appraisals ea
        JOIN appraisal_ratings ar ON ea.appraisal_id = ar.appraisal_id
        WHERE ea.period_id = ? AND ea.department_id = ?
        GROUP BY FLOOR(AVG((self_rating + hod_rating + COALESCE(hr_rating, 0))/3))";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $period_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $distribution = [];
    while ($row = $result->fetch_assoc()) {
        $distribution[] = $row;
    }

    // Get criteria performance
    $query = "SELECT 
        ac.name as criteria_name,
        AVG(ar.self_rating) as avg_self,
        AVG(ar.hod_rating) as avg_hod,
        AVG(ar.hr_rating) as avg_hr
        FROM appraisal_criteria ac
        JOIN appraisal_ratings ar ON ac.id = ar.criteria_id
        JOIN employee_appraisals ea ON ar.appraisal_id = ea.appraisal_id
        WHERE ea.period_id = ? AND ea.department_id = ?
        GROUP BY ac.id, ac.name";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $period_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $criteria = [];
    while ($row = $result->fetch_assoc()) {
        $criteria[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'employees' => $employees,
        'distribution' => $distribution,
        'criteria' => $criteria
    ]);
}
?>