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
    $stmt = $con->prepare("SELECT dh.id, dh.email, dh.head_name as full_name, dh.department_id 
                          FROM department_heads dh 
                          WHERE dh.email = ?");
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

// Get all templates
$query = "SELECT * FROM esign_templates ORDER BY created_at DESC";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Templates - E-Signature Module</title>
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
        .template-card {
            transition: transform 0.2s;
        }
        .template-card:hover {
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Document Templates</h4>
                <?php if ($user_type === 'admin' || $user_type === 'department_head'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                    <i class="fas fa-plus"></i> Add Template
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($template = $result->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card template-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($template['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <small>
                                                <i class="fas fa-file-alt"></i> Type: <?php echo ucfirst($template['document_type']); ?><br>
                                                <i class="fas fa-clock"></i> Created: <?php echo date('M d, Y', strtotime($template['created_at'])); ?>
                                            </small>
                                        </p>
                                        <p class="card-text"><?php echo htmlspecialchars($template['description']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="create.php?template=<?php echo $template['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-medical"></i> Use Template
                                            </a>
                                            <?php if ($user_type === 'admin' || $user_type === 'department_head'): ?>
                                            <div class="btn-group">
                                                <a href="edit_template.php?id=<?php echo $template['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteTemplate(<?php echo $template['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5>No Templates Found</h5>
                        <p class="text-muted">Create your first document template to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process/add_template.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document Type</label>
                            <select class="form-select" name="document_type" required>
                                <option value="contract">Contract</option>
                                <option value="agreement">Agreement</option>
                                <option value="policy">Policy</option>
                                <option value="form">Form</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Template File (PDF)</label>
                            <input type="file" class="form-control" name="template_file" accept=".pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteTemplate(id) {
        if (confirm('Are you sure you want to delete this template?')) {
            window.location.href = `process/delete_template.php?id=${id}`;
        }
    }
    </script>
</body>
</html> 