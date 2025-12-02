<?php
session_start();
require_once '../../../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hr') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $period_id = $_GET['period_id'] ?? '';
    
    // Get department statistics
    $query = "SELECT 
        d.id, d.name,
        COUNT(DISTINCT e.id) as total_employees,
        COUNT(DISTINCT CASE WHEN ea.status = 'Completed' THEN ea.employee_id END) as completed,
        COUNT(DISTINCT CASE WHEN ea.status != 'Completed' THEN ea.employee_id END) as pending,
        ROUND(AVG(
            SELECT AVG(hr_rating) 
            FROM appraisal_ratings 
            WHERE appraisal_id = ea.appraisal_id
        ), 2) as avg_rating
        FROM department d
        LEFT JOIN employees e ON d.id = e.department_id
        LEFT JOIN employee_appraisals ea ON e.id = ea.employee_id
        WHERE d.status = 1 " . 
        ($period_id ? "AND ea.period_id = '$period_id'" : "") . "
        GROUP BY d.id, d.name";

    $result = $con->query($query);
    $stats = [];
    
    while ($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $stats]);
}
?>