<?php
include('../connection.php');
include('../session.php');

// Admin role verification
$stmt = $con->prepare("SELECT id, role FROM admin WHERE user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();

if ($result->num_rows !== 1) {
    header('Location: /emps/admin_panel/login.php');
    exit;
}

// Check if assignment_id is provided
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$assignment_id) {
    die("Missing document ID");
}

// Get document details
$stmt = $con->prepare("SELECT 
    da.admin_signed_file_path,
    da.document_number,
    dt.doc_title,
    e.full_name as employee_name
FROM document_assignments da
JOIN document_templates dt ON da.template_id = dt.template_id
JOIN employees e ON da.emp_id = e.id
WHERE da.assignment_id = ? AND da.admin_signstatus = 'Signed'");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document || empty($document['admin_signed_file_path'])) {
    die("Signed document not found for assignment ID: $assignment_id");
}

$file_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $document['admin_signed_file_path'];

if (!file_exists($file_path)) {
    die("Document file not found at path: " . $document['admin_signed_file_path']);
}

// Set headers for file download
$file_name = "Document_" . $document['document_number'] . "_" . str_replace(' ', '_', $document['doc_title']) . "_" . str_replace(' ', '_', $document['employee_name']) . ".pdf";
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($file_path);
exit;
?>