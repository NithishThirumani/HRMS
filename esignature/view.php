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
$document_id = $_GET['id'] ?? 0;

// Get document details with proper authorization
$query = "SELECT d.*, e.full_name as creator_name,
          (SELECT GROUP_CONCAT(
              CONCAT(
                  e2.full_name, ' (', r.role_name, ')',
                  ' - ', w2.status,
                  CASE 
                      WHEN w2.signed_at IS NOT NULL 
                      THEN CONCAT(' on ', DATE_FORMAT(w2.signed_at, '%Y-%m-%d %H:%i'))
                      ELSE ''
                  END
              ) SEPARATOR ' → '
          )
          FROM esign_workflow w2
          INNER JOIN employees e2 ON w2.approver_id = e2.id
          INNER JOIN roles r ON w2.role_id = r.id
          WHERE w2.document_id = d.id
          ORDER BY w2.level) as approval_flow
          FROM esign_documents d 
          INNER JOIN employees e ON d.created_by = e.id
          WHERE d.id = ?";

// Add authorization check based on user type
if ($user_type === 'employee') {
    $query .= " AND (d.created_by = ? OR EXISTS (
        SELECT 1 FROM esign_workflow w 
        WHERE w.document_id = d.id 
        AND w.approver_id = ?
    ))";
} elseif ($user_type === 'department_head') {
    $query .= " AND EXISTS (
        SELECT 1 FROM esign_workflow w 
        INNER JOIN employees e ON w.approver_id = e.id
        WHERE w.document_id = d.id 
        AND e.department_id = ?
    )";
}

$stmt = $con->prepare($query);

if ($user_type === 'employee') {
    $stmt->bind_param("iii", $document_id, $user_id, $user_id);
} elseif ($user_type === 'department_head') {
    $stmt->bind_param("ii", $document_id, $user['department_id']);
} else {
    $stmt->bind_param("i", $document_id);
}

$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: index.php');
    exit();
}

// Get signatures with proper authorization
$signatures_query = "SELECT w.*, e.full_name as approver_name, r.role_name
                    FROM esign_workflow w
                    INNER JOIN employees e ON w.approver_id = e.id
                    INNER JOIN roles r ON w.role_id = r.id
                    WHERE w.document_id = ?";
$stmt = $con->prepare($signatures_query);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$signatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - <?php echo htmlspecialchars($document['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .signature-container {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .signature-image {
            max-width: 200px;
            max-height: 100px;
        }
        .typed-signature {
            font-family: 'Dancing Script', cursive;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo htmlspecialchars($document['title']); ?></h2>
            <div>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <?php if ($document['file_type'] === 'pdf'): ?>
                    <a href="download.php?id=<?php echo $document_id; ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Download
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Document Details</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($document['description']); ?></p>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Created by: <?php echo htmlspecialchars($document['creator_name']); ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Created: <?php echo date('Y-m-d H:i', strtotime($document['created_at'])); ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-random"></i> Approval Flow:<br>
                                <?php echo htmlspecialchars($document['approval_flow']); ?>
                            </small>
                        </div>
                        
                        <?php if ($document['file_type'] === 'pdf'): ?>
                            <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" width="100%" height="600px"></iframe>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($document['file_path']); ?>" class="img-fluid" alt="Document">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Signatures</h5>
                        
                        <?php foreach ($signatures as $signature): ?>
                            <div class="signature-container">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($signature['approver_name']); ?></strong>
                                        <div class="text-muted"><?php echo htmlspecialchars($signature['role_name']); ?></div>
                                    </div>
                                    <span class="badge <?php 
                                        echo $signature['status'] === 'approved' ? 'bg-success' : 
                                            ($signature['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); 
                                    ?>">
                                        <?php echo ucfirst($signature['status']); ?>
                                    </span>
                                </div>
                                
                                <?php if ($signature['signed_at']): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            Signed on: <?php echo date('Y-m-d H:i', strtotime($signature['signed_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($signature['signature_data']): ?>
                                        <div class="mb-2">
                                            <?php if ($signature['signature_type'] === 'draw' || $signature['signature_type'] === 'upload'): ?>
                                                <img src="<?php echo htmlspecialchars($signature['signature_data']); ?>" 
                                                     class="signature-image" alt="Signature">
                                            <?php else: ?>
                                                <div class="typed-signature">
                                                    <?php echo htmlspecialchars($signature['signature_data']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($signature['comments']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">Comment:</small>
                                            <p class="mb-0"><?php echo htmlspecialchars($signature['comments']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 