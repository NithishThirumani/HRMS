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

// Check if user has permission to edit templates
if ($user_type !== 'admin' && $user_type !== 'department_head') {
    header('Location: templates.php?error=permission');
    exit();
}

// Get template details
$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$template_id) {
    header('Location: templates.php?error=invalid');
    exit();
}

$stmt = $con->prepare("SELECT * FROM esign_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();

if (!$template) {
    header('Location: templates.php?error=notfound');
    exit();
}

// Get pending documents count for sidebar
$pending_query = "SELECT COUNT(*) as count FROM esign_documents d 
                 INNER JOIN esign_workflow w ON d.id = w.document_id 
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $document_type = trim($_POST['document_type'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $document_type === '') {
        $error_message = "Please fill in all required fields.";
    } else {
        $update_query = "UPDATE esign_templates SET 
                        title = ?, 
                        description = ?, 
                        document_type = ?";
        $params = [$title, $description, $document_type];
        $types = "sss";

        // Handle file upload if a new file is provided
        if (isset($_FILES['template_file']) && $_FILES['template_file']['size'] > 0) {
            $file = $_FILES['template_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($ext !== 'pdf') {
                $error_message = "Only PDF files are allowed.";
            } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB
                $error_message = "File size must be less than 10MB.";
            } else {
                $upload_dir = '../../uploads/templates/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $filename = uniqid('template_', true) . '.pdf';
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Delete old file
                    if (file_exists($upload_dir . $template['file_path'])) {
                        unlink($upload_dir . $template['file_path']);
                    }
                    
                    $update_query .= ", file_path = ?";
                    $params[] = $filename;
                    $types .= "s";
                } else {
                    $error_message = "Error uploading file.";
                }
            }
        }

        if (!isset($error_message)) {
            $update_query .= " WHERE id = ?";
            $params[] = $template_id;
            $types .= "i";

            $stmt = $con->prepare($update_query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                header('Location: templates.php?success=updated');
                exit();
            } else {
                $error_message = "Error updating template.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template - E-Signature Module</title>
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
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit Template</h4>
                <a href="templates.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Templates
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Template Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($template['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="contract" <?php echo $template['document_type'] === 'contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="agreement" <?php echo $template['document_type'] === 'agreement' ? 'selected' : ''; ?>>Agreement</option>
                            <option value="policy" <?php echo $template['document_type'] === 'policy' ? 'selected' : ''; ?>>Policy</option>
                            <option value="form" <?php echo $template['document_type'] === 'form' ? 'selected' : ''; ?>>Form</option>
                            <option value="other" <?php echo $template['document_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($template['description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="template_file" class="form-label">Template File (PDF)</label>
                        <input type="file" class="form-control" id="template_file" name="template_file" accept=".pdf">
                        <small class="text-muted">Leave empty to keep the current file. Only PDF files are allowed.</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 