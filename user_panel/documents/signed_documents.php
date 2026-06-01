<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';


$documentManager = new DocumentManager($con);
$empId = $_SESSION['eid'];

// Get signed documents
// Update the SQL query to track signing stages
$sql = "SELECT da.*, dt.doc_title, dt.doc_type,
            CONCAT('/emps/documents/signed/', da.signed_file_path) as signed_file_path,
            CASE 
                WHEN da.signed_hr_id IS NOT NULL THEN 'hr_signed'
                WHEN da.signed_admin_id IS NOT NULL THEN 'admin_signed'
                ELSE 'employee_signed'
            END as signing_stage
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        WHERE da.emp_id = (SELECT id FROM employees WHERE eid = ?)
        AND da.status = 'signed'
        ORDER BY da.signed_date DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $empId);
$stmt->execute();
$result = $stmt->get_result();
$signedDocuments = $result->fetch_all(MYSQLI_ASSOC);

// Notification logic update
if (!empty($signedDocuments)) {
    // Notify HR only initially
    $hrResult = $con->query("SELECT id FROM employees WHERE role = 'hr'");
    $hrIds = $hrResult->fetch_all(MYSQLI_ASSOC);

    $notifStmt = $con->prepare("INSERT INTO notifications 
        (emp_id, document_id, type, title, message, created_at)
        VALUES (?, ?, 'document_pending', ?, ?, NOW())");

    $checkStmt = $con->prepare("SELECT id FROM notifications 
        WHERE document_id = ? AND emp_id = ? AND type = 'document_pending'");

    foreach ($signedDocuments as $doc) {
        // Only notify HR if not already signed by HR
        if (empty($doc['signed_hr_id'])) {
            $title = "Document Requires HR Signature: {$doc['doc_title']}";
            $message = "Employee #$empId signed {$doc['doc_title']} - awaiting HR approval";

            foreach ($hrIds as $hr) {
                $checkStmt->bind_param("ii", $doc['assignment_id'], $hr['id']);
                $checkStmt->execute();

                if ($checkStmt->get_result()->num_rows === 0) {
                    $notifStmt->bind_param("iiss", $hr['id'], $doc['assignment_id'], $title, $message);
                    $notifStmt->execute();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Signed Documents</title>
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="../css/main.css">

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/reg_emp.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/document-styles.css">
</head>

<body id="page-top">
    <?php
    $base_path = '/emps/';  // Add this line
    include('../sidebar.php');
    ?>
    <?php include('../topbar.php'); ?>
    <div class="container">
        <h2>Signed Documents</h2>

        <div class="documents-list">
            <?php if (empty($signedDocuments)): ?>
                <div class="alert alert-info">No signed documents found.</div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="background-color: #2c3e50; color: white;">Document Title</th>
                            <th style="background-color: #2c3e50; color: white;">Type</th>
                            <th style="background-color: #2c3e50; color: white;">Signed Date</th>
                            <th style="background-color: #2c3e50; color: white;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($signedDocuments as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['doc_title']); ?></td>
                                <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($doc['signed_date'])); ?></td>
                                <td>
                                    <a href="<?php echo !empty($doc['signed_file_path']) ? $doc['signed_file_path'] : 'view_document.php?id=' . $doc['assignment_id']; ?>"
                                        class="btn btn-info btn-sm" target="_blank">
                                        View
                                    </a>
                                    <a href="download_document.php?doc_id=<?php echo $doc['assignment_id']; ?>"
                                        class="btn btn-primary btn-sm" target="_blank">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php include('../footer.php'); ?>
    <!-- Scroll to Top Button -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="/emps/user_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap core JavaScript -->
    <!-- Update script paths to user_panel -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <script src="../js/datetime.js"></script>


</body>

</html>