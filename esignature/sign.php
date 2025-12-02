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

// Get document details
$doc_query = "SELECT d.*, w.level, w.status as approval_status 
              FROM esign_documents d 
              INNER JOIN esign_workflow w ON d.id = w.document_id 
              WHERE d.id = ? AND w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($doc_query);
$stmt->bind_param("ii", $document_id, $user_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: index.php');
    exit();
}

// Get workflow details
$workflow_query = "SELECT w.*, 
                    CASE 
                        WHEN w.approver_type = 'employee' THEN e.full_name
                        WHEN w.approver_type = 'department_head' THEN dh.head_name
                        ELSE 'Unknown'
                    END as approver_name,
                    r.role_name 
                  FROM esign_workflow w 
                  LEFT JOIN employees e ON w.approver_id = e.id AND w.approver_type = 'employee'
                  LEFT JOIN department_heads dh ON w.approver_id = dh.id AND w.approver_type = 'department_head'
                  LEFT JOIN roles r ON w.role_id = r.id 
                  WHERE w.document_id = ? 
                  ORDER BY w.level";
$stmt = $con->prepare($workflow_query);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$workflow = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Document - <?php echo htmlspecialchars($document['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .signature-pad {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .workflow-timeline {
            position: relative;
            padding: 20px 0;
        }
        .workflow-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: #dee2e6;
        }
        .workflow-step {
            position: relative;
            margin-bottom: 30px;
        }
        .workflow-step.active {
            color: #0d6efd;
        }
        .workflow-step.completed {
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($document['title']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($document['description']); ?></p>
                        
                        <?php 
                        // Fix file path to use the correct domain and directory structure
                        $file_path = str_replace('../', '', $document['file_path']);
                        if ($document['file_type'] === 'pdf'): 
                        ?>
                            <iframe src="https://communik.san-solutions.in/esignature/<?php echo htmlspecialchars($file_path); ?>" width="100%" height="600px"></iframe>
                        <?php else: ?>
                            <img src="https://communik.san-solutions.in/esignature/<?php echo htmlspecialchars($file_path); ?>" class="img-fluid" alt="Document">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Sign Document</h5>
                        
                        <ul class="nav nav-tabs mb-3" id="signatureTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="draw-tab" data-bs-toggle="tab" href="#draw" role="tab">Draw</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="type-tab" data-bs-toggle="tab" href="#type" role="tab">Type</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="upload-tab" data-bs-toggle="tab" href="#upload" role="tab">Upload</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="signatureTabsContent">
                            <div class="tab-pane fade show active" id="draw" role="tabpanel">
                                <canvas id="signaturePad" class="signature-pad" width="300" height="200"></canvas>
                                <button class="btn btn-secondary btn-sm" id="clearSignature">Clear</button>
                            </div>
                            
                            <div class="tab-pane fade" id="type" role="tabpanel">
                                <input type="text" class="form-control mb-2" id="typedSignature" placeholder="Type your signature">
                                <select class="form-select mb-2" id="signatureFont">
                                    <option value="Dancing Script">Dancing Script</option>
                                    <option value="Great Vibes">Great Vibes</option>
                                    <option value="Satisfy">Satisfy</option>
                                </select>
                            </div>
                            
                            <div class="tab-pane fade" id="upload" role="tabpanel">
                                <input type="file" class="form-control" id="signatureUpload" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <textarea class="form-control mb-2" id="signatureComment" placeholder="Add a comment (optional)"></textarea>
                            <button class="btn btn-success w-100" id="approveBtn">Approve & Sign</button>
                            <button class="btn btn-danger w-100 mt-2" id="rejectBtn">Reject</button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Approval Flow</h5>
                        <div class="workflow-timeline">
                            <?php foreach ($workflow as $step): ?>
                                <div class="workflow-step <?php 
                                    echo $step['level'] < $document['level'] ? 'completed' : 
                                        ($step['level'] === $document['level'] ? 'active' : ''); 
                                ?>">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($step['approver_name'] ?? ''); ?></strong>
                                            <div class="text-muted"><?php echo htmlspecialchars($step['role_name'] ?? ''); ?></div>
                                        </div>
                                        <div>
                                            <?php if ($step['level'] < $document['level']): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php elseif ($step['level'] === $document['level']): ?>
                                                <i class="fas fa-clock text-primary"></i>
                                            <?php else: ?>
                                                <i class="fas fa-circle text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize signature pad
            const canvas = document.getElementById('signaturePad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            // Clear signature
            $('#clearSignature').click(function() {
                signaturePad.clear();
            });

            // Handle signature submission
            $('#approveBtn').click(function() {
                let signatureData = '';
                const activeTab = $('.nav-tabs .active').attr('id');
                const comment = $('#signatureComment').val();

                switch(activeTab) {
                    case 'draw-tab':
                        if (signaturePad.isEmpty()) {
                            alert('Please provide a signature');
                            return;
                        }
                        signatureData = signaturePad.toDataURL();
                        submitSignature(signatureData, 'draw', 'approve', comment);
                        break;
                    case 'type-tab':
                        const typedSignature = $('#typedSignature').val();
                        if (!typedSignature) {
                            alert('Please type your signature');
                            return;
                        }
                        signatureData = typedSignature;
                        submitSignature(signatureData, 'type', 'approve', comment);
                        break;
                    case 'upload-tab':
                        const file = $('#signatureUpload')[0].files[0];
                        if (!file) {
                            alert('Please upload a signature');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            signatureData = e.target.result;
                            submitSignature(signatureData, 'upload', 'approve', comment);
                        };
                        reader.readAsDataURL(file);
                        break;
                }
            });

            $('#rejectBtn').click(function() {
                const comment = $('#signatureComment').val();
                if (!comment) {
                    alert('Please provide a reason for rejection');
                    return;
                }
                submitSignature('', 'none', 'reject', comment);
            });

            function submitSignature(signatureData, signatureType, action, comment) {
                $.ajax({
                    url: 'process/submit_signature.php',
                    method: 'POST',
                    data: {
                        document_id: <?php echo $document_id; ?>,
                        signature_data: signatureData,
                        signature_type: signatureType,
                        action: action,
                        comment: comment
                    },
                    success: function(response) {
                        try {
                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.status === 'success') {
                                alert('Document processed successfully!');
                                window.location.href = 'index.php';
                            } else {
                                alert('Error: ' + (result.message || 'Unknown error occurred'));
                            }
                        } catch (e) {
                            alert('Error processing response: ' + e.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error submitting signature: ' + error);
                    }
                });
            }
        });
    </script>
</body>
</html> 