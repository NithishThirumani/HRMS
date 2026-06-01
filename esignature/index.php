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
$stmt = $con->prepare("SELECT id, email, role FROM admin WHERE email = ? AND LOWER(status) = 'active'");
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
                              WHERE e.email = ? AND LOWER(e.status) = 'active'");
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

// Get pending and signed document counts
if ($user_type === 'admin') {
    // Admin sees all
    $pending_query = "SELECT COUNT(*) as count FROM esign_workflow WHERE status = 'pending'";
    $stmt = $con->prepare($pending_query);
    $stmt->execute();
    $pending_count = $stmt->get_result()->fetch_assoc()['count'];

    $signed_query = "SELECT COUNT(*) as count FROM esign_workflow WHERE status = 'approved'";
    $stmt = $con->prepare($signed_query);
    $stmt->execute();
    $signed_count = $stmt->get_result()->fetch_assoc()['count'];
} else {
    // Non-admin: only their own
    $pending_query = "SELECT COUNT(*) as count FROM esign_documents d 
                     INNER JOIN esign_workflow w ON d.id = w.document_id 
                     WHERE w.approver_id = ? AND w.status = 'pending'";
    $stmt = $con->prepare($pending_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pending_count = $stmt->get_result()->fetch_assoc()['count'];

    $signed_query = "SELECT COUNT(*) as count FROM esign_documents d 
                    INNER JOIN esign_workflow w ON d.id = w.document_id 
                    WHERE w.approver_id = ? AND w.status = 'approved'";
    $stmt = $con->prepare($signed_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $signed_count = $stmt->get_result()->fetch_assoc()['count'];
}

// Get documents created by user
$created_query = "SELECT COUNT(*) as count FROM esign_documents WHERE created_by = ?";
$stmt = $con->prepare($created_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$created_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Signature Module</title>
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
        .welcome-banner {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
        .card-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0;
        }
        .quick-actions .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-banner">
            <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
            <p class="mb-0">Role: <?php echo htmlspecialchars($user_type); ?></p>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary">
                    <div class="card-body">
                        <h5 class="card-title text-dark">Pending Signatures</h5>
                        <h2 class="card-value text-dark"><?php echo $pending_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success">
                    <div class="card-body">
                        <h5 class="card-title text-dark">Documents Created</h5>
                        <h2 class="card-value text-dark"><?php echo $created_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info">
                    <div class="card-body">
                        <h5 class="card-title text-dark">Signed Documents</h5>
                        <h2 class="card-value text-dark"><?php echo $signed_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="row quick-actions">
                            <?php if ($user_type == 'admin' || $user_type == 'hr'): ?>
                            <div class="col-md-3 mb-3">
                                <a href="create.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> New Document
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-3 mb-3">
                                <a href="pending.php" class="btn btn-warning w-100">
                                    <i class="fas fa-clock"></i> Pending Signatures
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="signed.php" class="btn btn-success w-100">
                                    <i class="fas fa-check-circle"></i> Signed Documents
                                </a>
                            </div>
                            <?php if ($user_type == 'admin' || $user_type == 'hr'): ?>
                            <div class="col-md-3 mb-3">
                                <a href="templates.php" class="btn btn-info w-100">
                                    <i class="fas fa-file-alt"></i> Templates
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 