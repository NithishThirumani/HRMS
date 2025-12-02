<?php
include('../connection.php');
include('../session.php');

// The session.php already verifies HR role, so we can proceed directly

// Get document details using esign_documents table
$document_id = $_GET['id'] ?? null;
if (!$document_id) {
    header('Location: hr_documents.php?error=invalid_id');
    exit;
}

// Get document details from esign_documents table
$stmt = $con->prepare("SELECT 
    ed.id as document_id,
    ed.title as doc_title,
    ed.description,
    ed.file_path,
    ed.document_type,
    ed.created_at as assigned_date,
    ed.status as document_status,
    ed.current_level,
    ed.total_levels,
    ed.expiry_date,
    ew.id as workflow_id,
    ew.level as workflow_level,
    ew.status as workflow_status,
    ew.signed_at,
    ew.signature_data,
    ew.approver_type,
    e.full_name as employee_name,
    e.eid as employee_id
FROM esign_documents ed
LEFT JOIN esign_workflow ew ON ed.id = ew.document_id
LEFT JOIN employees e ON ew.approver_id = e.id
WHERE ed.id = ? AND ed.is_deleted = 0");
$stmt->bind_param("i", $document_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: hr_documents.php?error=not_found');
    exit;
}

// Get the document file path
$document_file_path = '';
$web_path = '';

if (!empty($document['file_path'])) {
    // Check if the file exists in the uploads directory
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $document['file_path'];
    
    if (file_exists($full_path)) {
        $document_file_path = $full_path;
        $web_path = '/emps/' . $document['file_path'];
    } else {
        // Try alternative paths
        $alt_paths = [
            $_SERVER['DOCUMENT_ROOT'] . '/emps/uploads/documents/' . basename($document['file_path']),
            $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/' . basename($document['file_path']),
            $document['file_path'] // Try as absolute path
        ];
        
        foreach ($alt_paths as $path) {
            if (file_exists($path)) {
                $document_file_path = $path;
                $web_path = '/emps/' . str_replace($_SERVER['DOCUMENT_ROOT'] . '/emps/', '', $path);
                break;
            }
        }
    }
}

// Check if HR can sign this document
$can_sign = false;
$is_completed = false;

if ($document['workflow_status'] === 'pending' && $document['approver_type'] === 'head') {
    $can_sign = true;
} elseif ($document['workflow_status'] === 'approved' || $document['document_status'] === 'completed') {
    $is_completed = true;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>HR Document Review</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <style>
        canvas {
            touch-action: none;
            /* Prevent scrolling while drawing on mobile */
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .document-viewer {
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 500px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <ol class="breadcrumb mb-4 mt-4">
                    <li class="breadcrumb-item"><a href="hr_documents.php">HR Documents</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($document['doc_title']) ?></li>
                </ol>

                <div class="card mb-4">
                    <div class="card-header" style="background-color: #2c3e50; color: white;">
                        <h5 class="m-0 font-weight-bold">
                            <i class="fas fa-file-contract mr-2"></i>
                            Document Review - <?= htmlspecialchars($document['doc_title']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-card bg-light p-4 rounded">
                                    <h6 class="text-primary mb-3"><i class="fas fa-user-tie mr-2"></i>Document Details</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">Title:</dt>
                                        <dd class="col-sm-8"><?= htmlspecialchars($document['doc_title']) ?></dd>

                                        <dt class="col-sm-4">Type:</dt>
                                        <dd class="col-sm-8"><?= htmlspecialchars($document['document_type']) ?></dd>

                                        <dt class="col-sm-4">Created Date:</dt>
                                        <dd class="col-sm-8"><?= date('M j, Y H:i', strtotime($document['assigned_date'])) ?></dd>

                                        <dt class="col-sm-4">Current Level:</dt>
                                        <dd class="col-sm-8"><?= $document['current_level'] ?>/<?= $document['total_levels'] ?></dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="status-card bg-light p-4 rounded">
                                    <h6 class="text-primary mb-3"><i class="fas fa-clipboard-check mr-2"></i>Signature Status</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">Document Status:</dt>
                                        <dd class="col-sm-7">
                                            <?php if ($document['document_status'] === 'completed'): ?>
                                                <span class="badge badge-success">Completed</span>
                                            <?php elseif ($document['document_status'] === 'in_progress'): ?>
                                                <span class="badge badge-warning">In Progress</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><?= ucfirst($document['document_status']) ?></span>
                                            <?php endif; ?>
                                        </dd>

                                        <dt class="col-sm-5">Workflow Status:</dt>
                                        <dd class="col-sm-7">
                                            <?php if ($document['workflow_status'] === 'approved'): ?>
                                                <span class="badge badge-success">Approved</span>
                                            <?php elseif ($document['workflow_status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= ucfirst($document['workflow_status'] ?? 'Unknown') ?></span>
                                            <?php endif; ?>
                                        </dd>

                                        <dt class="col-sm-5">Can Sign:</dt>
                                        <dd class="col-sm-7">
                                            <?php if ($can_sign): ?>
                                                <span class="badge badge-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">No</span>
                                            <?php endif; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Document Viewer -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-file-pdf mr-2"></i>Document Preview</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($web_path): ?>
                                            <iframe src="<?= $web_path ?>" class="document-viewer w-100" style="height: 600px;"></iframe>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Document file not found. Please contact the administrator.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Section -->
                        <?php if ($can_sign): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header" style="background-color: #2c3e50; color: white;">
                                        <h6 class="mb-0"><i class="fas fa-signature mr-2"></i>HR Signature</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="signatureForm" action="process_signature.php" method="POST">
                                            <input type="hidden" name="document_id" value="<?= $document['document_id'] ?>">
                                            <input type="hidden" name="workflow_id" value="<?= $document['workflow_id'] ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="signatureCanvas">Draw Your Signature:</label>
                                                        <canvas id="signatureCanvas" width="400" height="200"></canvas>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearCanvas()">Clear</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="comments">Comments (Optional):</label>
                                                        <textarea class="form-control" name="comments" id="comments" rows="4" placeholder="Add any comments..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-signature mr-2"></i>Sign Document
                                                </button>
                                                <a href="hr_documents.php" class="btn btn-secondary ml-2">
                                                    <i class="fas fa-arrow-left mr-2"></i>Back to Documents
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($is_completed): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header" style="background-color: #28a745; color: white;">
                                        <h6 class="mb-0"><i class="fas fa-check-circle mr-2"></i>Document Completed</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-success"><i class="fas fa-signature mr-2"></i>Signature Details</h6>
                                                <dl class="row">
                                                    <dt class="col-sm-4">Signed Date:</dt>
                                                    <dd class="col-sm-8"><?= $document['signed_at'] ? date('M j, Y H:i:s', strtotime($document['signed_at'])) : 'N/A' ?></dd>
                                                    
                                                    <dt class="col-sm-4">Status:</dt>
                                                    <dd class="col-sm-8">
                                                        <?php if ($document['document_status'] === 'completed'): ?>
                                                            <span class="badge badge-success">Fully Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-info">HR Signed</span>
                                                        <?php endif; ?>
                                                    </dd>
                                                    
                                                    <dt class="col-sm-4">Current Level:</dt>
                                                    <dd class="col-sm-8"><?= $document['current_level'] ?>/<?= $document['total_levels'] ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if ($document['signature_data']): ?>
                                                <h6 class="text-success"><i class="fas fa-image mr-2"></i>Signature Image</h6>
                                                <div class="text-center">
                                                    <img src="<?= $document['signature_data'] ?>" 
                                                         alt="HR Signature" 
                                                         class="img-fluid border rounded" 
                                                         style="max-width: 200px; max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <a href="hr_documents.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left mr-2"></i>Back to Documents
                                            </a>
                                            <?php if ($document['signature_data']): ?>
                                            <a href="<?= $document['signature_data'] ?>" 
                                               class="btn btn-info ml-2" 
                                               target="_blank">
                                                <i class="fas fa-download mr-2"></i>Download Signature
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    This document is not ready for HR signature or has already been processed.
                                </div>
                                <a href="hr_documents.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Back to Documents
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    
    <script>
        // Signature canvas functionality
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Set canvas style
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events for mobile
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);

        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.stroke();
            
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const x = touch.clientX - rect.left;
            const y = touch.clientY - rect.top;
            
            if (e.type === 'touchstart') {
                startDrawing({ offsetX: x, offsetY: y });
            } else if (e.type === 'touchmove') {
                draw({ offsetX: x, offsetY: y });
            }
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // Form submission
        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get signature data
            const signatureData = canvas.toDataURL();
            
            // Add signature data to form
            const signatureInput = document.createElement('input');
            signatureInput.type = 'hidden';
            signatureInput.name = 'signature_data';
            signatureInput.value = signatureData;
            this.appendChild(signatureInput);
            
            // Submit form
            this.submit();
        });
    </script>
</body>

</html>