<?php
include('../connection.php');
include('../session.php');

// Admin role verification
$stmt = $con->prepare("SELECT id, role FROM admin WHERE user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();

if ($result->num_rows !== 1) {
    header('Location: /emps/admin_panel/login.php');
    exit;
}

$admin_id = $admin_data['id'];
$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id) {
    header('Location: admin_documents.php');
    exit;
}

// Get document details
$stmt = $con->prepare("SELECT 
    da.*,
    dt.doc_title,
    e.full_name as employee_name,
    hr.full_name as hr_name
FROM document_assignments da
JOIN document_templates dt ON da.template_id = dt.template_id
JOIN employees e ON da.emp_id = e.id
LEFT JOIN employees hr ON da.signed_hr_id = hr.id
WHERE da.assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    die("Document not found");
}

// Find the HR-signed document
$hr_signed_file = '';
if (!empty($document['hrsigned_file_path'])) {
    $hr_signed_file = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $document['hrsigned_file_path'];

    if (!file_exists($hr_signed_file)) {
        // Try alternative path
        $alt_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/hr_signed/hr_signed_' . $assignment_id . '_*.pdf';
        $matching_files = glob($alt_path);
        if (!empty($matching_files)) {
            $hr_signed_file = $matching_files[0];
        }
    }
}

if (empty($hr_signed_file) || !file_exists($hr_signed_file)) {
    die("HR signed document not found");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Sign Document</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        #signatureCanvas {
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-options {
            margin-bottom: 20px;
        }

        .signature-type-container {
            margin-top: 15px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Sign Document</h1>
                    <p class="mb-4">Review and sign the document below.</p>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?= htmlspecialchars($document['doc_title']) ?> -
                                        <?= htmlspecialchars($document['document_number']) ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="document-info mb-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Employee:</strong>
                                                    <?= htmlspecialchars($document['employee_name']) ?></p>
                                                <p><strong>Assigned Date:</strong>
                                                    <?= date('M j, Y', strtotime($document['assigned_date'])) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>HR Signed:</strong>
                                                    <?= htmlspecialchars($document['hr_name']) ?></p>
                                                <p><strong>HR Signed Date:</strong>
                                                    <?= date('M j, Y', strtotime($document['hr_signed_date'])) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="document-preview mb-4">
                                        <h5 class="mb-3">Document Preview</h5>
                                        <div class="embed-responsive embed-responsive-1by1" style="height: 600px;">
                                            <iframe class="embed-responsive-item"
                                                src="/emps/<?= str_replace($_SERVER['DOCUMENT_ROOT'] . '/emps/', '', $hr_signed_file) ?>"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Signature</h6>
                                </div>
                                <div class="card-body">
                                    <form id="signatureForm" method="post" action="process_admin_signature.php">
                                        <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">

                                        <div class="signature-options">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="signatureMethod"
                                                    id="drawSignature" value="draw" checked>
                                                <label class="form-check-label" for="drawSignature">Draw
                                                    Signature</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="signatureMethod"
                                                    id="typeSignature" value="type">
                                                <label class="form-check-label" for="typeSignature">Type
                                                    Signature</label>
                                            </div>
                                        </div>

                                        <div id="drawSignatureContainer">
                                            <canvas id="signatureCanvas" width="320" height="200"></canvas>
                                            <input type="hidden" name="signature" id="signatureData">
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    id="clearSignature">Clear</button>
                                            </div>
                                        </div>

                                        <div id="typeSignatureContainer" class="signature-type-container"
                                            style="display: none;">
                                            <div class="form-group">
                                                <label for="typedSignature">Type your signature:</label>
                                                <input type="text" class="form-control" id="typedSignature"
                                                    name="typedSignature">
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary btn-block">Sign
                                                Document</button>
                                            <a href="admin_documents.php"
                                                class="btn btn-secondary btn-block mt-2">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('../footer.php'); ?>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script>
        $(document).ready(function () {
            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d');
            let isDrawing = false;

            // Set up canvas
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000000';
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Drawing functions
            function startDrawing(e) {
                isDrawing = true;
                draw(e);
            }

            function stopDrawing() {
                isDrawing = false;
                ctx.beginPath();
                updateSignatureData();
            }

            function draw(e) {
                if (!isDrawing) return;

                const rect = canvas.getBoundingClientRect();
                const x = (e.clientX || e.touches[0].clientX) - rect.left;
                const y = (e.clientY || e.touches[0].clientY) - rect.top;

                ctx.lineTo(x, y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(x, y);
            }

            function updateSignatureData() {
                document.getElementById('signatureData').value = canvas.toDataURL();
            }

            // Event listeners for mouse
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Event listeners for touch
            canvas.addEventListener('touchstart', function (e) {
                e.preventDefault();
                startDrawing(e);
            });
            canvas.addEventListener('touchmove', function (e) {
                e.preventDefault();
                draw(e);
            });
            canvas.addEventListener('touchend', stopDrawing);

            // Clear signature
            document.getElementById('clearSignature').addEventListener('click', function () {
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.beginPath();
                document.getElementById('signatureData').value = '';
            });

            // Toggle signature method
            $('input[name="signatureMethod"]').change(function () {
                if (this.value === 'draw') {
                    $('#drawSignatureContainer').show();
                    $('#typeSignatureContainer').hide();
                } else {
                    $('#drawSignatureContainer').hide();
                    $('#typeSignatureContainer').show();
                }
            });

            // Form submission
            $('#signatureForm').submit(function () {
                const signatureMethod = $('input[name="signatureMethod"]:checked').val();

                if (signatureMethod === 'draw') {
                    const signatureData = $('#signatureData').val();
                    if (!signatureData) {
                        alert('Please draw your signature');
                        return false;
                    }
                } else {
                    const typedSignature = $('#typedSignature').val().trim();
                    if (!typedSignature) {
                        alert('Please type your signature');
                        return false;
                    }
                }

                return true;
            });
        });
    </script>
</body>

</html>