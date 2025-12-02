<?php
$base_path = 'http://' . $_SERVER['HTTP_HOST'] . '/emps/';
include('../connection.php');
include('../session.php');

// Add employee ID initialization here
$empId = isset($_SESSION['eid']) ? (int) filter_var($_SESSION['eid'], FILTER_SANITIZE_NUMBER_INT) : null;
if (!$empId) {
    die("Invalid employee session");
}

// Modified query to use esign_documents table
$stmt = $con->prepare("SELECT id, title, file_type, created_at, status 
                      FROM esign_documents 
                      WHERE created_by = ? 
                      AND status = 'pending'
                      AND is_deleted = 0
                      ORDER BY created_at DESC");

// Add debug output
error_log("Employee ID: " . $empId);
if (!$stmt) {
    die("Prepare failed: (" . $con->errno . ") " . $con->error);
}

$stmt->bind_param("i", $empId);

if (!$stmt->execute()) {
    die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
}

$result = $stmt->get_result();
$pendingDocuments = $result->fetch_all(MYSQLI_ASSOC);

// Debug results
error_log("Query matched rows: " . $result->num_rows);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Pending Signatures</title>
    <!-- Update paths to use user_panel assets -->
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

</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>
    <div id="wrapper">


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">


                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <h2>Documents Pending Signature</h2>

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
                        <?php if (empty($pendingDocuments)): ?>
                            <div class="alert alert-info">No documents pending signature.</div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="background-color: #2c3e50; color: white;">Document Title</th>
                                        <th style="background-color: #2c3e50; color: white;">Type</th>
                                        <th style="background-color: #2c3e50; color: white;">Assigned Date</th>
                                        <th style="background-color: #2c3e50; color: white;">Status</th>
                                        <th style="background-color: #2c3e50; color: white;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingDocuments as $doc): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($doc['created_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-warning">Pending</span>
                                            </td>
                                            <td>
                                                <a href="view_document.php?id=<?php echo $doc['id']; ?>"
                                                    class="btn btn-primary btn-sm">View</a>
                                                <a href="sign_document.php?id=<?php echo $doc['id']; ?>"
                                                    class="btn btn-success btn-sm">Sign</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End Page Content -->
            </div>
            <!-- End Main Content -->

            <?php include('../footer.php'); ?>
        </div>
        <!-- End Content Wrapper -->
    </div>
    <!-- End Page Wrapper -->

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