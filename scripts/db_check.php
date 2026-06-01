<?php
require_once __DIR__ . '/../connection.php';
$r = mysqli_query($con, 'SELECT COUNT(*) as c FROM employee_attendance');
if ($r) {
    $a = mysqli_fetch_assoc($r);
    echo "attendance_count:" . $a['c'] . "\n";
} else {
    echo "query_error:" . mysqli_error($con) . "\n";
}
?>