<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

// Validate template ID
$templateId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$templateId) {
    die("Invalid template ID");
}

// Get template details
$sql = "SELECT file_path, doc_title FROM document_templates WHERE template_id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$templateId]);
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$template || !isset($template['file_path']) || !file_exists($template['file_path'])) {
    die("Template file not found");
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($template['file_path']) . '"');
header('Content-Length: ' . filesize($template['file_path']));

// Output file content
readfile($template['file_path']);
exit();
?>