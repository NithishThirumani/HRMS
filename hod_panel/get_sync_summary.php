<?php
include('session.php');
header('Content-Type: application/json');

$start_date = date('Y-m-d', strtotime($_POST['start_date']));
$end_date = date('Y-m-d', strtotime($_POST['end_date']));

// Get counts
$query = "SELECT 
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count,
            SUM(CASE WHEN status = 'success' THEN records_count ELSE 0 END) as total_records,
            MAX(sync_time) as last_sync
          FROM sync_logs 
          WHERE DATE(sync_time) BETWEEN ? AND ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary = mysqli_fetch_assoc($result);

echo json_encode($summary);
?>