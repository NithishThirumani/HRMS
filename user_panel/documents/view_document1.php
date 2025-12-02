<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$documentManager = new DocumentManager($con);
$assignmentId = isset($_GET['id']) ? $_GET['id'] : 0;
$empId = $_SESSION['eid'];

// Add debug logging
error_log("Assignment ID: " . $assignmentId);

if ($_SESSION['role'] == 'admin') {
    $docCheck = $con->prepare("SELECT signed_hr_id FROM document_assignments WHERE assignment_id = ?");
    $docCheck->bind_param("i", $_GET['id']);
    $docCheck->execute();
    $hrSigned = $docCheck->get_result()->fetch_assoc()['signed_hr_id'];

    if (!$hrSigned) {
        $readOnly = true;
        $alert = "This document requires HR signature before admin approval";
    }
}

// Get document details
$sql = "SELECT dt.*, da.* 
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        WHERE da.assignment_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

// Add debug logging
error_log("Document found: " . ($document ? "Yes" : "No"));
if ($document) {
    error_log("File path: " . $document['file_path']);
}

if (!$document) {
    die("Document not found - Assignment ID: " . $assignmentId);
}

// Get file path and check if file exists
$filePath = 'c:/xampp/htdocs/emps/Uploads/templates/' . $document['file_path'];
error_log("Full file path: " . $filePath);
error_log("File exists: " . (file_exists($filePath) ? "Yes" : "No"));
if (file_exists($filePath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
    readfile($filePath);
    exit;
} else {
    echo "Document not found.";
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>View Document</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body>
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div class="container mt-4">
        <?php if (isset($readOnly)): ?>
            <div class="alert alert-warning"><?= $alert ?></div>
            <div class="document-view bg-light p-4">
                <h4><?= htmlspecialchars($document['doc_title']) ?></h4>
                <embed src="<?= $filePath ?>" type="application/pdf" width="100%" height="600px">
            </div>
        <?php else: ?>
            <!-- Editable/signable version would go here -->
            <div class="document-edit">
                <embed src="<?= $filePath ?>" type="application/pdf" width="100%" height="600px">
                <!-- Add signature controls here -->
            </div>
        <?php endif; ?>
    </div>

    <?php include('../footer.php'); ?>
</body>

</html>