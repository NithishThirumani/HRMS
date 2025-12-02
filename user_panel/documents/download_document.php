<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$documentManager = new DocumentManager($con);
// Add validation for document ID
if (!isset($_GET['doc_id']) || empty($_GET['doc_id'])) {
    error_log("No valid document ID provided");
    $_SESSION['error'] = "Invalid document ID.";
    header('Location: signed_documents.php');
    exit;
}

$assignmentId = (int)$_GET['doc_id'];
$empId = $_SESSION['eid'];

error_log("Assignment ID: " . $assignmentId);
error_log("Employee ID: " . $empId);

// Get document details
$sql = "SELECT da.assignment_id, da.template_id, da.signed_file_path, da.status,
               dt.doc_title, dt.file_path
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        WHERE da.assignment_id = ? 
        AND da.emp_id = (SELECT id FROM employees WHERE eid = ?)
        AND da.status = 'signed'
        AND da.signed_file_path IS NOT NULL";  // Make sure we have a signed file
$stmt = $con->prepare($sql);
$stmt->bind_param("is", $assignmentId, $empId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if ($document) {
    $signedFilePath = 'C:/xampp/htdocs/emps/documents/signed/' . $document['signed_file_path'];
    
    error_log("Document found - Path: " . $signedFilePath);
    error_log("Status: " . $document['status']);
    error_log("Signed file name: " . $document['signed_file_path']);
    
    if (file_exists($signedFilePath)) {
        // Log download action
        $documentManager->logDocumentAction($document['template_id'], $empId, 'downloaded');

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $document['doc_title'] . '_signed.pdf"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($signedFilePath));

        // Output file
        readfile($signedFilePath);
        exit;
    } else {
        error_log("File not found at path: " . $signedFilePath);
        $_SESSION['error'] = "Signed document file not found.";
    }
} else {
    error_log("No signed document found for Assignment ID: " . $assignmentId);
    $_SESSION['error'] = "No signed document found.";
}