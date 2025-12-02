<?php
include('../connection.php');
include('../session.php');

// HR Role Verification
$stmt = $con->prepare("SELECT e.role, e.id 
                      FROM employees e
                      JOIN emp_login el ON e.eid = el.emp_id
                      WHERE el.user_name = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$hr_data = $result->fetch_assoc();

if ($result->num_rows !== 1 || strtolower(trim($hr_data['role'] ?? '')) !== 'hr') {
    header('Location: /emps/hr_panel/index.php');
    exit;
}

$hr_id = $hr_data['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'] ?? null;
    $signature_method = $_POST['signatureMethod'] ?? 'draw';

    // Get the signature data based on method
    if ($signature_method === 'draw') {
        $signature_data = $_POST['signature'] ?? '';
    } else {
        // For typed signatures, create an image from the text
        $typed_signature = $_POST['typedSignature'] ?? '';
        if (!empty($typed_signature)) {
            // Create a signature image from the typed text
            $font = 5; // Built-in font
            $width = imagefontwidth($font) * strlen($typed_signature) + 20;
            $height = imagefontheight($font) + 20;

            $image = imagecreatetruecolor($width, $height);
            $bg_color = imagecolorallocate($image, 255, 255, 255);
            $text_color = imagecolorallocate($image, 0, 0, 0);

            imagefill($image, 0, 0, $bg_color);
            imagestring($image, $font, 10, 10, $typed_signature, $text_color);

            // Convert to base64
            ob_start();
            imagepng($image);
            $image_data = ob_get_clean();
            $signature_data = 'data:image/png;base64,' . base64_encode($image_data);

            imagedestroy($image);
        } else {
            $signature_data = '';
        }
    }

    if (empty($assignment_id) || empty($signature_data)) {
        die("Missing required data");
    }

    // Get the original document path
    $stmt = $con->prepare("SELECT da.emp_signed_path, da.emp_id, dt.doc_title
                          FROM document_assignments da
                          JOIN document_templates dt ON da.template_id = dt.template_id
                          WHERE da.assignment_id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $document = $stmt->get_result()->fetch_assoc();

    if (!$document) {
        die("Document not found");
    }

    // Find the employee-signed document
    $emp_signed_file = '';
    if (!empty($document['emp_signed_path'])) {
        $emp_signed_file = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/signed/' . basename($document['emp_signed_path']);
    
        if (!file_exists($emp_signed_file)) {
            // Try alternative path
            $alt_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $document['emp_signed_path'];
            if (file_exists($alt_path)) {
                $emp_signed_file = $alt_path;
            } else {
                // Try with pattern matching
                $pattern = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/signed/signed_' . $assignment_id . '_*.pdf';
                $matching_files = glob($pattern);
                if (!empty($matching_files)) {
                    $emp_signed_file = $matching_files[0];
                }
            }
        }
    }

    if (empty($emp_signed_file) || !file_exists($emp_signed_file)) {
        die("Employee signed document not found");
    }

    // Create HR signed folder if it doesn't exist
    $hr_signed_dir = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/hr_signed';
    if (!file_exists($hr_signed_dir)) {
        mkdir($hr_signed_dir, 0777, true);
    }

    // Generate a unique filename for the HR-signed document
    $timestamp = time();
    $hr_signed_filename = "hr_signed_{$assignment_id}_{$timestamp}.pdf";
    $hr_signed_path = "documents/hr_signed/{$hr_signed_filename}";
    $hr_signed_full_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $hr_signed_path;

    // Save the signature image
    $signature_image_data = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_image_data = str_replace(' ', '+', $signature_image_data);
    $signature_image_data = base64_decode($signature_image_data);

    $signature_dir = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/signatures';
    if (!file_exists($signature_dir)) {
        mkdir($signature_dir, 0777, true);
    }

    $signature_filename = "hr_signature_{$assignment_id}_{$timestamp}.png";
    $signature_path = $signature_dir . '/' . $signature_filename;
    file_put_contents($signature_path, $signature_image_data);

    // Copy the employee-signed PDF to the HR-signed location
    copy($emp_signed_file, $hr_signed_full_path);
    
    // Add signature and date to the PDF using mPDF
    require_once($_SERVER['DOCUMENT_ROOT'] . '/emps/vendor/autoload.php');
    
    // Create temporary HTML file for signature
    $signatureHtml = '<html><head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 8pt; }
            .signature-container { position: absolute; bottom: 40px; right: 30px; text-align: right; }
            .signature-image { width: 120px; }
            .signature-date { font-size: 8pt; margin-top: 5px; }
            .signature-name { font-family: "Brush Script MT", cursive; font-size: 8pt; font-style: italic; }
        </style>
    </head><body>
        <div class="signature-container">
            <img class="signature-image" src="' . $signature_path . '" />
            <div class="signature-date">Date: ' . date('Y-m-d') . '</div>
            <div class="signature-name">HR: ' . $hr_data['id'] . '</div>
        </div>
    </body></html>';
    
    $tempHtmlFile = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/temp_signature.html';
    file_put_contents($tempHtmlFile, $signatureHtml);
    
    // Initialize mPDF
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/temp'
    ]);
    
    // Load the existing PDF
    $pageCount = $mpdf->SetSourceFile($hr_signed_full_path);
    
    // Import all pages
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplId = $mpdf->ImportPage($pageNo);
        $mpdf->AddPage();
        $mpdf->UseTemplate($tplId);
        
        // Add signature to the last page only
        if ($pageNo == $pageCount) {
            $mpdf->WriteHTML($signatureHtml);
        }
    }
    
    // Save the modified PDF
    $mpdf->Output($hr_signed_full_path, \Mpdf\Output\Destination::FILE);
    
    // Clean up temporary file
    if (file_exists($tempHtmlFile)) {
        unlink($tempHtmlFile);
    }

    // Update the database with HR signature information
    $stmt = $con->prepare("UPDATE document_assignments 
                          SET signature_image = ?, 
                              hr_signed_date = NOW(), 
                              signed_hr_id = ?,
                              hrsigned_file_path = ?,
                              status = 'HR Signed',
                              hr_signstatus = 'Signed'
                          WHERE assignment_id = ?");
    $signature_db_path = "documents/signatures/{$signature_filename}";
    $stmt->bind_param("sisi", $signature_db_path, $hr_id, $hr_signed_path, $assignment_id);

    if ($stmt->execute()) {
        // Assign to admin for final signature
        $stmt = $con->prepare("INSERT INTO admin_document_queue 
                              (assignment_id, assigned_date, status) 
                              VALUES (?, NOW(), 'Pending')");
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();

        // Redirect back to the document list
        header('Location: hr_documents.php?success=1');
        exit;
    } else {
        die("Error updating document: " . $con->error);
    }
} else {
    header('Location: hr_documents.php');
    exit;
}
?>