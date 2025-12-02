<?php
include('../connection.php');

require_once '../../classes/DocumentManager.php';
$templateId = isset($_GET['id']) ? $_GET['id'] : 0;

// Toggle the status
$sql = "UPDATE document_templates 
        SET is_active = NOT is_active 
        WHERE template_id = ?";
$stmt = $con->prepare($sql);
$success = $stmt->execute([$templateId]);

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>