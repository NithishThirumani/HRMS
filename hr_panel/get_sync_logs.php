<?php
include('session.php');
header('Content-Type: application/json');

$start_date = date('Y-m-d', strtotime($_POST['start_date']));
$end_date = date('Y-m-d', strtotime($_POST['end_date']));

$query = "SELECT * FROM sync_logs 
          WHERE DATE(sync_time) BETWEEN ? AND ?
          ORDER BY sync_time DESC";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$logs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $logs[] = $row;
}

echo json_encode(['data' => $logs]);
?>