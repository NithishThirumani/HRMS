<?php
include('session.php');
header('Content-Type: application/json');

function parse_report_date(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }

    $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'M d, Y', 'd M Y', 'j M Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $raw);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($raw);
    return $ts !== false ? date('Y-m-d', $ts) : null;
}

try {
    $dateRangeRaw = trim($_POST['dateRange'] ?? '');
    if ($dateRangeRaw === '') {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
    } else {
        $parts = preg_split('/\s*-\s*/', $dateRangeRaw, 2);
        $startDate = parse_report_date($parts[0] ?? '');
        $endDate = parse_report_date($parts[1] ?? ($parts[0] ?? ''));

        if ($startDate === null || $endDate === null) {
            throw new Exception('Invalid date range. Use the picker or format: DD/MM/YYYY - DD/MM/YYYY');
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }
    }

    $department = trim($_POST['department'] ?? '');
    $employee = trim($_POST['employee'] ?? '');

    $conditions = ['a.attendance_date BETWEEN ? AND ?'];
    $params = [$startDate, $endDate];
    $types = 'ss';

    if ($department !== '') {
        $conditions[] = 'd.name = ?';
        $params[] = $department;
        $types .= 's';
    }

    if ($employee !== '') {
        $conditions[] = 'e.eid = ?';
        $params[] = $employee;
        $types .= 's';
    }

    $whereClause = implode(' AND ', $conditions);

    $query = "SELECT a.*, e.full_name, COALESCE(d.name, '') AS department
              FROM attendance a
              INNER JOIN employees e ON a.eid = e.eid
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE {$whereClause}
              ORDER BY a.attendance_date DESC, d.name, e.full_name";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        throw new Exception(mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $records = [];
    $analytics = [
        'present' => 0,
        'absent' => 0,
        'halfDay' => 0,
        'totalHours' => 0,
        'recordCount' => 0,
    ];

    while ($row = mysqli_fetch_assoc($result)) {
        $total_hours = 0;
        $dbStatus = trim((string)($row['status'] ?? ''));

        if (!empty($row['last_out']) && !empty($row['first_in'])) {
            $total_hours = round((strtotime($row['last_out']) - strtotime($row['first_in'])) / 3600, 2);
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
        } elseif ($dbStatus !== '') {
            $status = $dbStatus;
            $lower = strtolower($dbStatus);
            if (strpos($lower, 'present') !== false) {
                $analytics['present']++;
            } elseif (strpos($lower, 'half') !== false) {
                $analytics['halfDay']++;
            } elseif (strpos($lower, 'absent') !== false) {
                $analytics['absent']++;
            }
        } else {
            $status = 'Present';
            $analytics['present']++;
        }

        $analytics['totalHours'] += $total_hours;
        $analytics['recordCount']++;

        $records[] = [
            $row['attendance_date'],
            $row['department'] ?? '',
            $row['full_name'] ?? '',
            $row['first_in'] ?? '',
            $row['last_out'] ?? '',
            $total_hours,
            $status,
            $row['attendance_type'] ?? 'import',
        ];
    }

    $analytics['avgHours'] = $analytics['recordCount'] > 0
        ? $analytics['totalHours'] / $analytics['recordCount']
        : 0;

    echo json_encode([
        'ok' => true,
        'records' => $records,
        'analytics' => $analytics,
        'range' => ['start' => $startDate, 'end' => $endDate],
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'records' => [],
        'analytics' => [
            'present' => 0,
            'absent' => 0,
            'halfDay' => 0,
            'totalHours' => 0,
            'recordCount' => 0,
            'avgHours' => 0,
        ],
    ]);
}
