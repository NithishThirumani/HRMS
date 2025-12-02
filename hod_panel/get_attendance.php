<?php
include('session.php');
header('Content-Type: application/json');

if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    $query = "SELECT * FROM attendance WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $aid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attendance = mysqli_fetch_assoc($result);
    
    echo json_encode($attendance);
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>