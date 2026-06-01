<?php
require_once __DIR__ . '/../bootstrap_session.php';
require_once dirname(__DIR__, 3) . '/connection.php';
require_once dirname(__DIR__, 3) . '/classes/EmployeeAppraisal.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $period_id = $_GET['period_id'] ?? '';
    $department_id = $_GET['department_id'] ?? '';
    $status = $_GET['status'] ?? '';

    $query = "SELECT ea.*, e.first_name, e.last_name, d.name as department_name,
              ap.start_date, ap.end_date, 
              (SELECT AVG(hr_rating) FROM appraisal_ratings WHERE appraisal_id = ea.appraisal_id) as final_rating
              FROM employee_appraisals ea
              JOIN employees e ON ea.employee_id = e.id
              JOIN departments d ON e.department_id = d.id
              JOIN appraisal_periods ap ON ea.period_id = ap.period_id
              WHERE 1=1";

    $params = [];
    
    if ($period_id) {
        $query .= " AND ea.period_id = ?";
        $params[] = $period_id;
    }
    
    if ($department_id) {
        $query .= " AND e.department_id = ?";
        $params[] = $department_id;
    }
    
    if ($status) {
        $query .= " AND ea.status = ?";
        $params[] = $status;
    }

    $stmt = $con->prepare($query);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $appraisals = [];

    while ($row = $result->fetch_assoc()) {
        $row['final_rating'] = number_format($row['final_rating'], 2);
        $appraisals[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $appraisals]);
}
?>