<?php
include('../connection.php');
include('../session.php');

// Admin role verification
$stmt = $con->prepare("SELECT role FROM admin WHERE user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: /emps/admin_panel/login.php');
    exit;
}

$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id) {
    header('Location: admin_documents.php');
    exit;
}

// Get document details
$stmt = $con->prepare("SELECT 
    da.*,
    dt.doc_title,
    e.full_name as employee_name,
    hr.full_name as hr_name
FROM document_assignments da
JOIN document_templates dt ON da.template_id = dt.template_id
JOIN employees e ON da.emp_id = e.id
LEFT JOIN employees hr ON da.signed_hr_id = hr.id
WHERE da.assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    die("Document not found");
}

// Find the HR-signed document
$hr_signed_file = '';
if (!empty($document['hrsigned_file_path'])) {
    $hr_signed_file = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $document['hrsigned_file_path'];
    
    if (!file_exists($hr_signed_file)) {
        // Try alternative path
        $alt_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/hr_signed/hr_signed_' . $assignment_id . '_*.pdf';
        $matching_files = glob($alt_path);
        if (!empty($matching_files)) {
            $hr_signed_file = $matching_files[0];
        }
    }
}

if (empty($hr_signed_file) || !file_exists($hr_signed_file)) {
    die("HR signed document not found");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - View Document</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">View Document</h1>
                    <p class="mb-4">Viewing document details and content.</p>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?= htmlspecialchars($document['doc_title']) ?> - 
                                        <?= htmlspecialchars($document['document_number']) ?>
                                    </h6>
                                    <div>
                                        <a href="admin_documents.php" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-arrow-left"></i> Back to List
                                        </a>
                                        <a href="sign_admin_document.php?id=<?= $assignment_id ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-signature"></i> Sign Document
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5>Document Information</h5>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Document Number</th>
                                                    <td><?= htmlspecialchars($document['document_number']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Title</th>
                                                    <td><?= htmlspecialchars($document['doc_title']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Employee</th>
                                                    <td><?= htmlspecialchars($document['employee_name']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Assigned Date</th>
                                                    <td><?= date('M j, Y', strtotime($document['assigned_date'])) ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Signature Information</h5>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Employee Signed</th>
                                                    <td>
                                                        <?php if ($document['signed_date']): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                <?= date('M j, Y', strtotime($document['signed_date'])) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>HR Signed</th>
                                                    <td>
                                                        <?php if ($document['hr_signed_date']): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                <?= date('M j, Y', strtotime($document['hr_signed_date'])) ?>
                                                            </span>
                                                            <div class="mt-1">By: <?= htmlspecialchars($document['hr_name']) ?></div>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Admin Signed</th>
                                                    <td>
                                                        <?php if ($document['admin_signed_date']): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                <?= date('M j, Y', strtotime($document['admin_signed_date'])) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        <?php if ($document['status'] == 'Completed'): ?>
                                                            <span class="badge badge-success">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-primary"><?= htmlspecialchars($document['status']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="document-preview">
                                        <h5 class="mb-3">Document Preview</h5>
                                        <div class="embed-responsive embed-responsive-1by1" style="height: 800px;">
                                            <iframe class="embed-responsive-item" src="/emps/<?= str_replace($_SERVER['DOCUMENT_ROOT'] . '/emps/', '', $hr_signed_file) ?>"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('../footer.php'); ?>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html>