<?php
session_start();
require_once(__DIR__ . '/../../connection.php');

$period_id = isset($_GET['period_id']) ? $_GET['period_id'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT aa.*, e.first_name, e.last_name, e.department, ap.start_date, ap.end_date 
        FROM appraisal_assignments aa
        JOIN employees e ON aa.employee_id = e.id
        JOIN appraisal_periods ap ON aa.period_id = ap.period_id
        WHERE 1=1";

if (!empty($period_id)) {
    $sql .= " AND aa.period_id = '" . mysqli_real_escape_string($con, $period_id) . "'";
}
if (!empty($department)) {
    $sql .= " AND e.department = '" . mysqli_real_escape_string($con, $department) . "'";
}
if (!empty($status)) {
    $sql .= " AND aa.status = '" . mysqli_real_escape_string($con, $status) . "'";
}

$result = mysqli_query($con, $sql);
$data = array();

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = array(
        "employee" => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
        "department" => htmlspecialchars($row['department']),
        "period" => date('M Y', strtotime($row['start_date'])) . ' - ' . date('M Y', strtotime($row['end_date'])),
        "status" => htmlspecialchars($row['status']),
        "final_rating" => $row['final_rating'] ?? 'N/A',
        "actions" => '<a href="view_single_appraisal.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">View</a>'
    );
}

echo json_encode(array("data" => $data));