<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/UserDocumentManager.php';

// Replace existing DocumentManager instantiation
$documentManager = new UserDocumentManager($con);
$assignmentId = isset($_GET['id']) ? $_GET['id'] : 0;  // Changed from doc_id to id
$empId = $_SESSION['eid'];

// Get document details first
$sql = "SELECT da.*, dt.* 
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        WHERE da.assignment_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typedSignature = trim($_POST['typed_signature']);
    
    if (empty($typedSignature)) {
        die("Please enter your full name for signature");
    }

    // Get original PDF path from template data
    $originalPdfPath = 'c:/xampp/htdocs/emps/' . $document['file_path'];
    $signedDir = 'c:/xampp/htdocs/emps/documents/signed/';
    
    // Create signed directory if not exists
    if (!file_exists($signedDir)) {
        mkdir($signedDir, 0755, true);
    }

    // Generate unique filename
    $signedFilename = 'signed_'.$assignmentId.'_'.time().'.pdf';
    $signedPdfPath = $signedDir . $signedFilename;

    // Add signature to PDF using mPDF
    require_once 'C:/xampp/htdocs/emps/vendor/autoload.php';
    
    $mpdf = new \Mpdf\Mpdf();
    
    // Import original PDF
    $pageCount = $mpdf->SetSourceFile($originalPdfPath);
    
    // Add all pages from original PDF
    for ($i = 1; $i <= $pageCount; $i++) {
        $templateId = $mpdf->ImportPage($i);
        $mpdf->AddPage();
        $mpdf->UseTemplate($templateId);
        
        // Add signature text to each page
        $mpdf->SetXY(30, 250);
        $mpdf->WriteText(30, 250, "Signed By: $typedSignature");
    }
    
    // Save the PDF
    $mpdf->Output($signedPdfPath, \Mpdf\Output\Destination::FILE);

    // Update database
    $updateStmt = $con->prepare("UPDATE document_assignments 
                               SET emp_signed_path = ?, 
                                   signed_date = NOW(), 
                                   signing_status = 'completed'
                               WHERE assignment_id = ?");
    $updateStmt->bind_param("si", $signedFilename, $assignmentId);
    
    if ($updateStmt->execute()) {
        header('Location: pending_signatures.php?success=1');
        exit;
    } else {
        die("Error saving signature: " . $con->error);
    }
}

$document = $documentManager->getEmployeeDocuments($empId);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sign Document</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .signature-area canvas {
            border: 1px solid #ddd;
            background: white;
            width: 100%;
            height: 200px;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>Sign Document</h2>
        <div class="document-preview">
            <iframe src="view_document.php?id=<?php echo $assignmentId; ?>" width="100%" height="500px"></iframe>
        </div>

        <form method="POST" id="signatureForm">
            <div class="form-group">
                <label>Type Your Full Name</label>
                <input type="text" name="typed_signature" class="form-control" 
                    placeholder="Enter your full name as signature" required>
            </div>

            <button type="submit" class="btn btn-primary">Sign Document</button>
        </form>
    </div>

    <script>
        // Initialize signature pad with proper dimensions
        var canvas = document.getElementById('signaturePad');
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)' // white background
        });

        // Handle form submission
        document.getElementById('signatureForm').addEventListener('submit', function (e) {
            var signatureType = document.getElementById('signatureType').value;
            
            if (signatureType === 'draw') {
                if (signaturePad.isEmpty()) {
                    alert('Please draw your signature first!');
                    e.preventDefault();
                    return;
                }
                document.getElementById('signatureData').value = signaturePad.toDataURL();
                document.getElementById('signatureImage').value = signaturePad.toDataURL();
            } else {
                // Trim typed signature and validate
                var typedName = document.querySelector('input[name="typed_signature"]').value.trim();
                if (!typedName) {
                    alert('Please type your full name!');
                    e.preventDefault();
                }
                document.getElementById('signatureData').value = typedName;
            }
        });

        // Add window resize handler
        window.addEventListener('resize', function() {
            canvas.width = canvas.offsetWidth;
            signaturePad.clear(); // Optional: clear on resize
        });
    </script>
</body>

</html>