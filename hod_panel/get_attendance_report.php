<?php
include('session.php');
header('Content-Type: application/json');

$dateRange = explode(' - ', $_POST['dateRange']);
$startDate = date('Y-m-d', strtotime($dateRange[0]));
$endDate = date('Y-m-d', strtotime($dateRange[1]));
$department = $_POST['department'];
$employee = $_POST['employee'];

// Build query conditions
$conditions = ["a.attendance_date BETWEEN ? AND ?"];
$params = [$startDate, $endDate];
$types = "ss";

if (!empty($department)) {
    $conditions[] = "e.department = ?";
    $params[] = $department;
    $types .= "s";
}

if (!empty($employee)) {
    $conditions[] = "e.eid = ?";
    $params[] = $employee;
    $types .= "s";
}

$whereClause = implode(" AND ", $conditions);

// Get attendance records
$query = "SELECT a.*, e.full_name, e.department 
          FROM attendance a 
          JOIN employees e ON a.eid = e.eid 
          WHERE $whereClause
          ORDER BY a.attendance_date DESC, e.department, e.full_name";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$records = [];
$analytics = [
    'present' => 0,
    'absent' => 0,
    'halfDay' => 0,
    'totalHours' => 0,
    'recordCount' => 0
];

while ($row = mysqli_fetch_assoc($result)) {
    $total_hours = 0;
    if ($row['last_out'] && $row['first_in']) {
        $total_hours = round((strtotime($row['last_out']) - strtotime($row['first_in']))/3600, 2);
        if ($total_hours >= 8) {
            $status = 'Present';
            $analytics['present']++;
        } elseif ($total_hours >= 4) {
            $status = 'Half Day';
            $analytics['halfDay']++;
        } else {
            $status = 'Absent';
            $analytics['absent']++;
        }
    } else {
        $status = 'In Progress';
    }

    $analytics['totalHours'] += $total_hours;
    $analytics['recordCount']++;

    $records[] = [
        $row['attendance_date'],
        $row['department'],
        $row['full_name'],
        $row['first_in'],
        $row['last_out'],
        $total_hours,
        $status,
        $row['attendance_type']
    ];
}

$analytics['avgHours'] = $analytics['recordCount'] > 0 ? 
    $analytics['totalHours'] / $analytics['recordCount'] : 0;

echo json_encode([
    'records' => $records,
    'analytics' => $analytics
]);
?>