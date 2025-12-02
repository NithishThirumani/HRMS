<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empId = $_POST['employee_id'] ?? null;
    $templateId = $_POST['template_id'] ?? null;

    if (!$empId || !$templateId) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    // Get admin ID
    $adminSql = "SELECT id FROM admin WHERE user_name = ?";
    $adminStmt = $con->prepare($adminSql);
    $adminStmt->bind_param("s", $_SESSION['username']);
    $adminStmt->execute();
    $admin = $adminStmt->get_result()->fetch_assoc();
    $adminStmt->close();

    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'Admin not found']);
        exit;
    }

    $documentManager = new DocumentManager($con);
    $result = $documentManager->assignDocumentToEmployee($empId, $templateId, $admin['id']);

    echo json_encode(['success' => $result]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);