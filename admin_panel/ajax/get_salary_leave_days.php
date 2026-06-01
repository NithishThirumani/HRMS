<?php
require_once dirname(__DIR__) . '/session.php';
require_once dirname(__DIR__, 2) . '/includes/db_connection.php';
require_once dirname(__DIR__, 2) . '/includes/hrms_employees.php';

header('Content-Type: application/json');

$eid = trim((string) ($_GET['eid'] ?? ''));
$month = trim((string) ($_GET['month'] ?? ''));

if ($eid === '' || !preg_match('/^\d{4}-\d{2}$/', $month)) {
    echo json_encode(['ok' => false, 'error' => 'Employee and salary month are required.']);
    exit;
}

$firstDay = $month . '-01';
$lastDay = date('Y-m-t', strtotime($firstDay));
$join = hrms_leave_employee_join('l', 'e');

$sql = "SELECT COALESCE(SUM(l.total_days), 0) AS unpaid_days
        FROM leaves l
        JOIN employees e ON {$join}
        WHERE e.eid = ?
          AND l.hod_status = 'approved'
          AND l.hr_status = 'approved'
          AND LOWER(TRIM(l.status)) NOT IN ('rejected')
          AND (
            LOWER(l.type_of_leave) LIKE '%unpaid%'
            OR LOWER(l.type_of_leave) LIKE '%loss of pay%'
            OR LOWER(l.type_of_leave) LIKE '%lop%'
          )
          AND l.start_date <= ?
          AND l.end_date >= ?";

$stmt = $con->prepare($sql);
$stmt->bind_param('sss', $eid, $lastDay, $firstDay);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$unpaidDays = (float) ($row['unpaid_days'] ?? 0);

echo json_encode([
    'ok' => true,
    'unpaid_leave_days' => $unpaidDays,
    'month' => $month,
]);
