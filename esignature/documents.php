<?php
require_once '../config.php';
require_once '../connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details based on role
$user = null;
$user_type = null;

// Check if user is an admin
$stmt = $con->prepare("SELECT id, email, role FROM admin WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($admin) {
    $user = $admin;
    $user_type = 'admin';
} else {
    // Check if user is a department head
    $stmt = $con->prepare("SELECT dh.id, dh.head_email as email, dh.head_name as full_name, dh.department_id 
                          FROM department_heads dh 
                          WHERE dh.head_email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $dept_head = $stmt->get_result()->fetch_assoc();

    if ($dept_head) {
        $user = $dept_head;
        $user_type = 'department_head';
    } else {
        // Check if user is an employee
        $stmt = $con->prepare("SELECT e.id, e.eid, e.full_name, e.department_id 
                              FROM employees e
                              WHERE e.email = ? AND e.status = 'active'");
        $stmt->bind_param("s", $_SESSION['email']);
        $stmt->execute();
        $employee = $stmt->get_result()->fetch_assoc();

        if ($employee) {
            $user = $employee;
            $user_type = 'employee';
        }
    }
}

if (!$user) {
    header('Location: ../login.php');
    exit();
}

$user_id = $user['id'];
$user_name = $user['full_name'] ?? 'User';

// Get pending documents count for sidebar
$pending_query = "SELECT COUNT(*) as count FROM esign_documents d 
                 INNER JOIN esign_workflow w ON d.id = w.document_id 
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

$docs = [];
$stmt = $con->prepare("SELECT * FROM esign_documents ORDER BY created_at DESC");
$stmt->execute();
$docs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Documents</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/header.php'); ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">All Documents</h1>
                    
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Created At</th>
                                            <th>Status</th>
                                            <th>Workflow</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($docs as $doc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($doc['id']) ?></td>
                                            <td><?= htmlspecialchars($doc['title']) ?></td>
                                            <td><?= htmlspecialchars($doc['created_at']) ?></td>
                                            <td><?= htmlspecialchars($doc['status']) ?></td>
                                            <td>
                                                <a href="workflow.php?id=<?= urlencode($doc['id']) ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Workflow
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>
</body>
</html>