<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();
    
    echo json_encode(['exists' => $count > 0]);
}
?>