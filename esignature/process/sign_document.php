<?php
require_once '../../config.php';
require_once '../../connection.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$document_id = $_POST['document_id'] ?? 0;
$signature = $_POST['signature'] ?? '';
$comment = $_POST['comment'] ?? '';
$user_email = $_SESSION['email'];

// Find user ID
$stmt = $con->prepare("SELECT id FROM employees WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['id'] ?? 0;

if (!$user_id || !$document_id || !$signature) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

// Save signature (you may want to save as a file or in DB)
$stmt = $con->prepare("UPDATE esign_workflow SET status='signed', signed_at=NOW(), signature=?, comment=? WHERE document_id=? AND approver_id=?");
$stmt->bind_param("ssii", $signature, $comment, $document_id, $user_id);
$stmt->execute();

echo json_encode(['success' => true]);