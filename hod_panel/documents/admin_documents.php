<?php
include('../connection.php');
include('../session.php');

// Admin role verification - using the correct column name user_name
$stmt = $con->prepare("SELECT role FROM admin WHERE user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: /emps/admin_panel/login.php');
    exit;
}

// Get documents pending admin signature that have already been signed by HR
$stmt = $con->prepare("SELECT 
    adq.queue_id,
    adq.assignment_id,
    adq.assigned_date,
    da.document_number,
    dt.doc_title,
    e.full_name as employee_name,
    hr.full_name as hr_name,
    da.signed_date as emp_signed_date,
    da.hr_signed_date,
    da.admin_signstatus
FROM admin_document_queue adq
JOIN document_assignments da ON adq.assignment_id = da.assignment_id
JOIN document_templates dt ON da.template_id = dt.template_id
JOIN employees e ON da.emp_id = e.id
LEFT JOIN employees hr ON da.signed_hr_id = hr.id
WHERE adq.status = 'Pending' 
AND da.hr_signed_date IS NOT NULL
ORDER BY adq.assigned_date DESC");
$stmt->execute();
$documents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Documents Pending Signature</title>
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
                    <h1 class="h3 mb-2 text-gray-800">Documents Pending Signature</h1>
                    <p class="mb-4">Review and sign documents that have been approved by HR.</p>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Document signed successfully!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Documents Requiring Admin Signature</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="documentsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Document #</th>
                                            <th>Title</th>
                                            <th>Employee</th>
                                            <th>HR Signed</th>
                                            <th>Assigned Date</th>
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
                                                <td>
                                                    <?php if ($doc['hr_signed_date']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i>
                                                        <?= date('M j, Y', strtotime($doc['hr_signed_date'])) ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($doc['assigned_date'])) ?></td>
                                                <td>
                                                    <a href="view_document.php?id=<?= $doc['assignment_id'] ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="sign_admin_document.php?id=<?= $doc['assignment_id'] ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-signature"></i> Sign
                                                    </a>
                                                    <!-- Download button for signed documents -->
                                                    <?php if (isset($doc['admin_signstatus']) && $doc['admin_signstatus'] == 'Signed'): ?>
                                                        <a href="download_admin_signed.php?id=<?= $doc['assignment_id'] ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No documents pending admin signature</td>
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
        $(document).ready(function() {
            $('#documentsTable').DataTable();
        });
    </script>
</body>
</html>