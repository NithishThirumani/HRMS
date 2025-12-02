<?php
include('session.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    $eid = $_SESSION['eid'];

    $query = "SELECT * FROM attendance 
              WHERE eid = ? 
              AND attendance_date BETWEEN ? AND ?
              ORDER BY attendance_date DESC";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sss", $eid, $fromDate, $toDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $attendance = [];
    $summary = ['present' => 0, 'halfDay' => 0, 'absent' => 0];

    while ($row = mysqli_fetch_assoc($result)) {
        $total_hours = $row['last_out'] ? 
            round((strtotime($row['last_out']) - strtotime($row['first_in']))/3600, 2) : 0;

        if ($total_hours >= 8) {
            $status = 'Present';
            $summary['present']++;
        } elseif ($total_hours >= 4) {
            $status = 'Half Day';
            $summary['halfDay']++;
        } else {
            $status = 'Absent';
            $summary['absent']++;
        }

        $attendance[] = [
            'attendance_date' => $row['attendance_date'],
            'week_day' => $row['week_day'],
            'first_in' => $row['first_in'],
            'last_out' => $row['last_out'],
            'total_hours' => $total_hours,
            'status' => $status,
            'attendance_type' => $row['attendance_type'],
            'location_address' => $row['location_address']
        ];
    }

    echo json_encode(['attendance' => $attendance, 'summary' => $summary]);
}
?>