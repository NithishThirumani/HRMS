<?php
include('connection.php');
include('hr_panel/session.php');

// Verify valid HR user (same verification as before)
$stmt = $con->prepare("SELECT e.role FROM employees e
                      JOIN emp_login el ON e.eid = el.emp_id
                      WHERE el.user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1 || strtolower(trim($result->fetch_assoc()['role'] ?? '')) !== 'hr') {
    http_response_code(403);
    exit;
}

// Get document path from assignment ID
$assignment_id = $_GET['id'] ?? null;
// Update the query to get both original and signed paths
$stmt = $con->prepare("SELECT dt.file_path, da.signed_file_path 
                      FROM document_assignments da
                      JOIN document_templates dt ON da.template_id = dt.template_id
                      WHERE da.assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    http_response_code(404);
    exit;
}

// Serve the file securely
// Update the file path construction
// Determine which file to serve
$full_path = 'c:/xampp/htdocs/emps/' . $document['file_path'];

// For signed documents (keep existing logic)
if (!empty($document['signed_file_path'])) {
    $full_path = 'c:/xampp/htdocs/emps/documents/signed/' . basename($document['signed_file_path']);

    // Add cache busting to ensure fresh version
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

if (!file_exists($full_path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/pdf');
if (isset($_GET['download'])) {
    header('Content-Disposition: attachment; filename="document_' . basename($full_path) . '"');
}
readfile($full_path);