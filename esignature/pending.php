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

// Get pending documents
$query = "SELECT d.*, e.full_name as creator_name, w.status as approval_status 
          FROM esign_documents d 
          INNER JOIN esign_workflow w ON d.id = w.document_id 
          LEFT JOIN employees e ON d.created_by = e.id 
          WHERE w.approver_id = ? AND w.status = 'pending' 
          ORDER BY d.created_at DESC";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Documents - E-Signature Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-left: 250px;
            background: #f8f9fa;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .document-card {
            transition: transform 0.2s;
        }
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.8rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Pending Documents</h4>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($doc = $result->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card document-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($doc['title'] ?? ''); ?></h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <i class="fas fa-user"></i> Created by: <?php echo htmlspecialchars($doc['creator_name'] ?? ''); ?><br>
                                                <i class="fas fa-clock"></i> Created: <?php echo date('M d, Y', strtotime($doc['created_at'] ?? '')); ?><br>
                                                <i class="fas fa-file-alt"></i> Type: <?php echo ucfirst($doc['document_type'] ?? ''); ?>
                                            </small>
                                        </p>
                                        <p class="card-text"><?php echo htmlspecialchars($doc['description'] ?? ''); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-warning">Pending Approval</span>
                                            <a href="sign.php?id=<?php echo $doc['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-signature"></i> Sign Document
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Pending Documents</h5>
                        <p class="text-muted">You don't have any documents waiting for your signature.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 