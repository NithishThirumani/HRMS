<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$documentManager = new DocumentManager($con);
$assignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$currentUserRole = $_SESSION['role'];
$currentUserId = $_SESSION['eid'];

// Modify the SQL query to include file_path from document_templates
$sql = "SELECT da.*, dt.doc_title, dt.doc_type, dt.file_path,
        e1.eid as employee_id, e2.eid as hr_id, e3.eid as admin_id
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        LEFT JOIN employees e1 ON da.emp_id = e1.id
        LEFT JOIN employees e2 ON da.signed_hr_id = e2.id
        LEFT JOIN employees e3 ON da.signed_admin_id = e3.id
        WHERE da.assignment_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

// After fetching document
if (!$document) {
    die("Document not found");
}

// Get absolute server path to templates
// Fix path construction with proper directory traversal
$basePath = realpath(dirname(__DIR__) . '/../Uploads/templates/');
if (!$basePath) {
    error_log("[ERROR] Templates directory not found at: " . dirname(__DIR__) . '/../Uploads/templates/');
    die("Server configuration error: Please contact administrator");
}

// Sanitize filename and ensure proper path construction
$fileName = basename(trim($document['file_path']));  // Should output "67cef8f11a569_Appointment Letter.pdf"
$filePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fileName);

// Update the iframe src to use clean filename


error_log("[DEBUG] Clean server path: " . $filePath);

if (!file_exists($filePath)) {
    error_log("[ERROR] File not found at: " . $filePath);
    die("File not found on server: Please contact administrator");
}

error_log("[DEBUG] Attempting to access file: " . $filePath);
error_log("[DEBUG] Current working directory: " . getcwd());

if (!file_exists($filePath)) {
    error_log("[ERROR] File not found at: " . $filePath);
    die("File not found on server: Please contact administrator");
}

// Verify MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    error_log("[ERROR] Invalid file type: " . $mimeType);
    die("Invalid document format");
}

// Check signing requirements
$readOnly = false;
$showSignButton = false;
$alert = '';

// Admin access rules
if ($currentUserRole === 'admin') {
    if (!$document['signed_hr_id']) {
        $readOnly = true;
        $alert = "This document requires HR signature before admin approval";
    }
}
// HR access rules
elseif ($currentUserRole === 'hr') {
    if ($document['signed_hr_id']) {
        $readOnly = true;
        $alert = "This document has already been signed by HR";
    } else {
        $showSignButton = true;
    }
}
// Employee access rules
else {
    if ($document['status'] === 'signed') {
        $readOnly = true;
        $alert = "This document has already been signed";
    } else {
        $showSignButton = true;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($document['doc_title']) ?></title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <style>
        .document-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-top: 20px;
        }

        .signature-panel {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <?php if ($alert): ?>
                    <div class="alert alert-warning mb-4"><?= $alert ?></div>
                <?php endif; ?>

                <div class="document-container p-4">
                    <h4 class="mb-4"><?= htmlspecialchars($document['doc_title']) ?></h4>

                    <!-- Document Preview -->
                    <div class="embed-container">
                        <iframe src="/emps/Uploads/templates/<?= htmlspecialchars($fileName) ?>#toolbar=0" width="100%"
                            height="600px" style="border: 1px solid #dee2e6; border-radius: 4px;">
                        </iframe>
                        <div class="mt-3 text-center">
                            <a href="/emps/Uploads/templates/<?= htmlspecialchars($fileName) ?>" class="btn btn-primary"
                                target="_blank">
                                <i class="fas fa-download"></i> Download Document
                            </a>
                        </div>
                    </div>

                    <!-- Signing Information -->
                    <div class="signature-panel mt-4">
                        <h5 class="mb-3">Signing Status</h5>
                        <div class="row">

                            <div class="col-md-4">
                                <p><strong>Employee:</strong><br>
                                    <?= htmlspecialchars($document['employee_id']) ?><br>
                                    <?= $document['signed_date'] ? date('M j, Y H:i', strtotime($document['signed_date'])) : 'Pending' ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>HR Approval:</strong><br>
                                    <?= $document['hr_id'] ?? 'Pending' ?><br>
                                    <?= $document['hr_signed_date'] ? date('M j, Y H:i', strtotime($document['hr_signed_date'])) : '' ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Admin Approval:</strong><br>
                                    <?= $document['admin_id'] ?? 'Pending' ?><br>
                                    <?= $document['admin_signed_date'] ? date('M j, Y H:i', strtotime($document['admin_signed_date'])) : '' ?>
                                </p>
                            </div>
                        </div>

                        <?php if (!$readOnly && $showSignButton): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-primary" id="signDocumentBtn">
                                    <i class="fas fa-signature"></i> Sign Document
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
        <?php include('../footer.php'); ?>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>

    <?php if (!$readOnly && $showSignButton): ?>
        <script>
            document.getElementById('signDocumentBtn').addEventListener('click', function () {
                // Add digital signature logic here
                if (confirm('Are you sure you want to sign this document?')) {
                    // Submit signature to server
                    window.location.href = `sign_document.php?id=<?= $assignmentId ?>&role=<?= $currentUserRole ?>`;
                }
            });
        </script>
    <?php endif; ?>



</body>

</html>