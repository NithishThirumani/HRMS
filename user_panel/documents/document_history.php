<?php
include('../connection.php');
include('../session.php');

$empId = $_SESSION['eid'];

// Get document history using esign_documents table
$sql = "SELECT 
        CONCAT('DOC', 
            DATE_FORMAT(ed.created_at, '%m%y'), 
            '-', 
            LPAD(ed.id, 4, '0')
        ) as document_number,
        ed.id,
        ed.title, 
        ed.file_type,
        ed.created_at,
        ed.status,
        CASE 
            WHEN ed.status = 'completed' THEN 'Fully Executed'
            WHEN ed.status = 'in_progress' THEN 'In Progress'
            WHEN ed.status = 'pending' THEN 'Pending'
            WHEN ed.status = 'rejected' THEN 'Rejected'
            ELSE ed.status
        END as status_text
        FROM esign_documents ed
        WHERE ed.created_by = ?
        AND ed.is_deleted = 0
        ORDER BY ed.created_at DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $empId);
$stmt->execute();
$result = $stmt->get_result();
$documentHistory = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Document History</title>
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/document-styles.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-completed { background-color: #28a745; color: white; }
        .status-in_progress { background-color: #17a2b8; color: white; }
        .status-pending { background-color: #ffc107; color: black; }
        .status-rejected { background-color: #dc3545; color: white; }
    </style>
</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <div class="container">
        <h2>Document History</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="documents-list">
            <?php if (empty($documentHistory)): ?>
                <div class="alert alert-info">No document history found.</div>
            <?php else: ?>
                <table class="table" id="documentHistoryTable">
                    <thead>
                        <tr>
                            <th style="background-color: #2c3e50; color: white;">Doc ID</th>
                            <th style="background-color: #2c3e50; color: white;">Document</th>
                            <th style="background-color: #2c3e50; color: white;">Type</th>
                            <th style="background-color: #2c3e50; color: white;">Created Date</th>
                            <th style="background-color: #2c3e50; color: white;">Status</th>
                            <th style="background-color: #2c3e50; color: white;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentHistory as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['document_number']) ?></td>
                                <td>
                                    <div class="font-weight-bold"><?= htmlspecialchars($doc['title']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($doc['file_type']) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $doc['status'] ?>">
                                        <?= $doc['status_text'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($doc['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($doc['status'] === 'pending'): ?>
                                            <a href="sign_document.php?id=<?= $doc['id'] ?>"
                                                class="btn btn-success btn-sm">
                                                Sign
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($doc['status'] === 'completed'): ?>
                                            <a href="download_document.php?id=<?= $doc['id'] ?>"
                                                class="btn btn-info btn-sm">
                                                Download
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="view_document.php?id=<?= $doc['id'] ?>"
                                        class="btn btn-primary btn-sm">
                                        View
                                    </a>
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
                    <a class="btn btn-success" href="http://localhost/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap core JavaScript -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/datetime.js"></script>
    <script>
        $(document).ready(function () {
            $('#documentHistoryTable').DataTable({
                "order": [[3, "desc"]] // Sort by assigned date by default
            });
        });
    </script>
</body>

</html>