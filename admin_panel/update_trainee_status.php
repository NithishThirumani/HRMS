<?php
include('connection.php');
include('session.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $traineeId = mysqli_real_escape_string($mysqli, $_POST['traineeId']);
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);

    $query = "UPDATE trainees SET status = ? WHERE trainee_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $status, $traineeId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 