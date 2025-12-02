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

// Get all documents signed by admin
$stmt = $con->prepare("SELECT 
    da.assignment_id,
    da.document_number,
    dt.doc_title,
    e.full_name as employee_name,
    da.admin_signed_date,
    da.admin_signed_file_path,
    a.user_name as admin_name
FROM document_assignments da
JOIN document_templates dt ON da.template_id = dt.template_id
JOIN employees e ON da.emp_id = e.id
LEFT JOIN admin a ON da.signed_admin_id = a.id
WHERE da.admin_signstatus = 'Signed'
ORDER BY da.admin_signed_date DESC");
$stmt->execute();
$documents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Signed Documents</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Admin Signed Documents</h1>
                    <p class="mb-4">View and download all documents signed by administrators.</p>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Signed Documents</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="documentsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Document #</th>
                                            <th>Title</th>
                                            <th>Employee</th>
                                            <th>Signed By</th>
                                            <th>Signed Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($documents->num_rows > 0): ?>
                                            <?php while ($doc = $documents->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($doc['document_number']) ?></td>
                                                    <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                                    <td><?= htmlspecialchars($doc['employee_name']) ?></td>
                                                    <td><?= htmlspecialchars($doc['admin_name']) ?></td>
                                                    <td><?= date('M j, Y, g:i a', strtotime($doc['admin_signed_date'])) ?></td>
                                                    <td>
                                                        <a href="view_document.php?id=<?= $doc['assignment_id'] ?>"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <a href="download_admin_signed.php?id=<?= $doc['assignment_id'] ?>"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No signed documents found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#documentsTable').DataTable({
                "order": [[4, "desc"]] // Sort by signed date (column 4) in descending order
            });
        });
    </script>
</body>

</html>