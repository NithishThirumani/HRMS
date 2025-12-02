<?php
session_start();
require_once(__DIR__ . '/../../connection.php');

header('Content-Type: application/json');

try {
    $period_id = isset($_GET['period_id']) ? $_GET['period_id'] : '';
    $department = isset($_GET['department']) ? $_GET['department'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    $sql = "SELECT 
                ea.appraisal_id,
                ea.status,
                ea.final_rating,
                e.full_name,
                e.department,
                ap.start_date,
                ap.end_date
            FROM employee_appraisals ea
            INNER JOIN employees e ON ea.employee_id = e.id
            INNER JOIN appraisal_periods ap ON ea.period_id = ap.period_id
            LEFT JOIN appraisal_ratings ar ON ea.appraisal_id = ar.appraisal_id";

    $where_conditions = [];
    
    if (!empty($period_id)) {
        $where_conditions[] = "ea.period_id = '" . mysqli_real_escape_string($con, $period_id) . "'";
    }
    if (!empty($department)) {
        $where_conditions[] = "e.department = '" . mysqli_real_escape_string($con, $department) . "'";
    }
    if (!empty($status)) {
        $where_conditions[] = "ea.status = '" . mysqli_real_escape_string($con, $status) . "'";
    }

    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $sql .= " GROUP BY ea.appraisal_id ORDER BY ap.end_date DESC";

    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            "employee" => $row['full_name'],
            "department" => $row['department'],
            "period" => date('M Y', strtotime($row['start_date'])) . ' - ' . date('M Y', strtotime($row['end_date'])),
            "status" => $row['status'],
            "final_rating" => $row['final_rating'] ?: 'N/A',
            "actions" => '<a href="view_appraisal_details.php?appraisal_id=' . $row['appraisal_id'] . '" class="btn btn-primary btn-sm">View</a>'
        ];
    }

    $response = [
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        "recordsTotal" => mysqli_num_rows($result),
        "recordsFiltered" => mysqli_num_rows($result),
        "data" => $data
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}