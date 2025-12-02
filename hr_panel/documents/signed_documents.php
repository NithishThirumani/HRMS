<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
include('../connection.php');
include('../session.php');

// Verify HR role
$stmt = $con->prepare("SELECT e.role FROM employees e
                      JOIN emp_login el ON e.eid = el.emp_id
                      WHERE el.user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1 || strtolower(trim($result->fetch_assoc()['role'] ?? '')) !== 'hr') {
    header('Location: /emps/hr_panel/index.php');
    exit;
}

// Get signed documents
// Update the document query to match signed document storage
$stmt = $con->prepare("SELECT da.assignment_id, dt.doc_title, e.full_name as employee_name,
                      da.signed_date, da.emp_signed_path
                      FROM document_assignments da
                      JOIN document_templates dt ON da.template_id = dt.template_id
                      JOIN employees e ON da.emp_id = e.id
                      WHERE da.signing_status = 'completed'
                      ORDER BY da.signed_date DESC");
$stmt->execute();
$documents = $stmt->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Signed Documents</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body class="sb-nav-fixed">
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Signed Documents</h1>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">

                                <thead>
                                    <tr>
                                        <th>Document Title</th>
                                        <th>Employee</th>
                                        <th>Signed Date</th>
                                        <th>Download</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($documents->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No signed documents found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($doc = $documents->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                                <td><?= htmlspecialchars($doc['employee_name']) ?></td>
                                                <td>
                                                    <?php if (!empty($doc['signed_date']) && $doc['signed_date'] !== '0000-00-00 00:00:00'): ?>
                                                        <?= date('M j, Y H:i', strtotime($doc['signed_date'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-danger">Date Error</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($doc['emp_signed_path'])): ?>
                                                        <a href="/emps/documents/signed/<?= htmlspecialchars(basename($doc['emp_signed_path'])) ?>"
                                                            class="btn btn-primary btn-sm" download>
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not Available</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include('../footer.php'); ?>
    </div>
</body>

</html>