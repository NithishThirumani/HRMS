<?php
include('../connection.php');
include('../session.php');

if (!isset($_GET['id'])) {
    die("No template ID specified");
}

$templateId = $_GET['id'];
$sql = "SELECT file_path, doc_title FROM document_templates WHERE template_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $templateId);
$stmt->execute();
$result = $stmt->get_result();
$template = $result->fetch_assoc();
$stmt->close();

if ($template) {
    $baseDir = dirname(dirname(dirname(__FILE__))) . '/uploads/templates/';
    $filePath = $baseDir . basename($template['file_path']);
    
    if (file_exists($filePath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $template['doc_title'] . '.pdf"');
        readfile($filePath);
        exit;
    } else {
        die("Template file not found at: " . $filePath);
    }
} else {
    die("Template not found");
}
?>