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

// Get pending documents count for sidebar
$pending_query = "SELECT COUNT(*) as count FROM esign_documents d 
                 INNER JOIN esign_workflow w ON d.id = w.document_id 
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// Get all departments
$departments_query = "SELECT * FROM departments ORDER BY name";
$departments = $con->query($departments_query);

// Fetch all employees into an array
$employees_query = "SELECT e.*, d.name as department_name 
                   FROM employees e 
                   LEFT JOIN departments d ON e.department_id = d.id 
                   ORDER BY e.full_name";
$employees_result = $con->query($employees_query);
$employees = [];
while ($row = $employees_result->fetch_assoc()) {
    $employees[] = $row;
}

// Fetch all department heads into an array
$heads_query = "SELECT dh.*, d.name as department_name 
                FROM department_heads dh 
                LEFT JOIN departments d ON dh.department_id = d.id 
                ORDER BY dh.head_name";
$department_heads_result = $con->query($heads_query);
$department_heads = [];
while ($row = $department_heads_result->fetch_assoc()) {
    $department_heads[] = $row;
}

// Get all templates
$templates_query = "SELECT * FROM esign_templates ORDER BY title";
$templates = $con->query($templates_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Document - E-Signature Module</title>
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
        .approval-level {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .approval-level:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Create New Document</h4>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <form action="process/create_document.php" method="POST" enctype="multipart/form-data">
                    <!-- Document Details -->
                    <div class="mb-4">
                        <h5 class="mb-3">Document Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Document Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="document_type" class="form-label">Document Type</label>
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="">Select Type</option>
                                    <option value="contract">Contract</option>
                                    <option value="agreement">Agreement</option>
                                    <option value="policy">Policy</option>
                                    <option value="form">Form</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="template" class="form-label">Use Template (Optional)</label>
                                <select class="form-select" id="template" name="template">
                                    <option value="">Select Template</option>
                                    <?php while ($template = $templates->fetch_assoc()): ?>
                                        <option value="<?php echo $template['id']; ?>">
                                            <?php echo htmlspecialchars($template['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="document_file" class="form-label">Document File (PDF)</label>
                                <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf" required>
                                <small class="text-muted">Maximum file size: 10MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Flow -->
                    <div class="mb-4">
                        <h5 class="mb-3">Approval Flow</h5>
                        <div id="approval_levels">
                            <div class="approval-level">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-select department-select" name="departments[]" required>
                                            <option value="">Select Department</option>
                                            <?php while ($dept = $departments->fetch_assoc()): ?>
                                                <option value="<?php echo $dept['id']; ?>">
                                                    <?php echo htmlspecialchars($dept['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Approver</label>
                                        <select class="form-select approver-select" name="approvers[]" required>
                                            <option value="">Select Approver</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="add_level">
                            <i class="fas fa-plus"></i> Add Approval Level
                        </button>
                    </div>

                    <!-- Document Expiry -->
                    <div class="mb-4">
                        <h5 class="mb-3">Document Expiry</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_expiry" name="has_expiry" value="1">
                                    <label class="form-check-label" for="has_expiry">
                                        Set Document Expiry
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const employees = <?php echo json_encode($employees); ?>;
    const departmentHeads = <?php echo json_encode($department_heads); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // Handle department selection and approver population
        function updateApprovers(departmentSelect, approverSelect) {
            const departmentId = departmentSelect.value;
            approverSelect.innerHTML = '<option value="">Select Approver</option>';
            if (!departmentId) return;

            // Add department heads
            departmentHeads.forEach(function(head) {
                if (head.department_id == departmentId) {
                    const option = document.createElement('option');
                    option.value = 'head_' + head.id;
                    option.textContent = head.head_name + ' (Department Head)';
                    approverSelect.appendChild(option);
                }
            });
            // Add employees
            employees.forEach(function(emp) {
                if (emp.department_id == departmentId) {
                    const option = document.createElement('option');
                    option.value = 'emp_' + emp.id;
                    option.textContent = emp.full_name + ' (Employee)';
                    approverSelect.appendChild(option);
                }
            });
        }

        // Add event listeners to existing department selects
        document.querySelectorAll('.department-select').forEach(select => {
            select.addEventListener('change', function() {
                const approverSelect = this.closest('.approval-level').querySelector('.approver-select');
                updateApprovers(this, approverSelect);
            });
        });

        // Add new approval level
        document.getElementById('add_level').addEventListener('click', function() {
            const levels = document.getElementById('approval_levels');
            const newLevel = levels.children[0].cloneNode(true);
            
            // Clear selections
            newLevel.querySelector('.department-select').value = '';
            newLevel.querySelector('.approver-select').innerHTML = '<option value="">Select Approver</option>';
            
            // Add event listener to new department select
            newLevel.querySelector('.department-select').addEventListener('change', function() {
                const approverSelect = this.closest('.approval-level').querySelector('.approver-select');
                updateApprovers(this, approverSelect);
            });
            
            levels.appendChild(newLevel);
        });

        // Handle document expiry
        const hasExpiry = document.getElementById('has_expiry');
        const expiryDate = document.getElementById('expiry_date');
        
        hasExpiry.addEventListener('change', function() {
            expiryDate.disabled = !this.checked;
            if (this.checked) {
                // Set minimum date to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                expiryDate.min = tomorrow.toISOString().split('T')[0];
            }
        });

        // Handle template selection
        document.getElementById('template').addEventListener('change', function() {
            const fileInput = document.getElementById('document_file');
            fileInput.required = !this.value;
        });
    });
    </script>
</body>
</html> 