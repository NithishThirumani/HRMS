<?php
require_once __DIR__ . '/bootstrap_session.php';
require_once dirname(__DIR__, 2) . '/connection.php';

header('Content-Type: application/json');

try {
    $period_id = isset($_GET['period_id']) ? (int) $_GET['period_id'] : 0;
    $department_id = isset($_GET['department_id']) ? (int) $_GET['department_id'] : 0;
    $status = trim((string) ($_GET['status'] ?? ''));

    $sql = "SELECT 
                ea.appraisal_id,
                ea.status,
                ea.final_rating,
                e.full_name,
                COALESCE(d.name, 'N/A') AS department,
                ap.start_date,
                ap.end_date
            FROM employee_appraisals ea
            INNER JOIN employees e ON ea.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            INNER JOIN appraisal_periods ap ON ea.period_id = ap.period_id
            WHERE 1=1";

    $params = [];
    $types = '';

    if ($period_id > 0) {
        $sql .= ' AND ea.period_id = ?';
        $params[] = $period_id;
        $types .= 'i';
    }
    if ($department_id > 0) {
        $sql .= ' AND e.department_id = ?';
        $params[] = $department_id;
        $types .= 'i';
    }
    if ($status !== '') {
        $sql .= ' AND ea.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    $sql .= ' ORDER BY ap.end_date DESC, e.full_name ASC';

    $stmt = $con->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'employee' => $row['full_name'],
            'department' => $row['department'],
            'period' => date('M Y', strtotime($row['start_date'])) . ' - ' . date('M Y', strtotime($row['end_date'])),
            'status' => $row['status'],
            'final_rating' => $row['final_rating'] !== null ? number_format((float) $row['final_rating'], 2) : 'N/A',
            'actions' => '<a href="view_appraisal_details.php?appraisal_id=' . (int) $row['appraisal_id'] . '" class="btn btn-primary btn-sm">View</a>',
        ];
    }

    echo json_encode([
        'draw' => isset($_GET['draw']) ? (int) $_GET['draw'] : 1,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'data' => [],
    ]);
}
