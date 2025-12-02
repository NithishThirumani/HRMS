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

    // Get the HR-signed document path
    $stmt = $con->prepare("SELECT da.hrsigned_file_path, da.emp_id, dt.doc_title
                          FROM document_assignments da
                          JOIN document_templates dt ON da.template_id = dt.template_id
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

    // Create admin signed folder if it doesn't exist
    $admin_signed_dir = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/admin_signed';
    if (!file_exists($admin_signed_dir)) {
        mkdir($admin_signed_dir, 0777, true);
    }

    // Generate a unique filename for the admin-signed document
    $timestamp = time();
    $admin_signed_filename = "admin_signed_{$assignment_id}_{$timestamp}.pdf";
    $admin_signed_path = "documents/admin_signed/{$admin_signed_filename}";
    $admin_signed_full_path = $_SERVER['DOCUMENT_ROOT'] . '/emps/' . $admin_signed_path;

    // Save the signature image
    $signature_image_data = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_image_data = str_replace(' ', '+', $signature_image_data);
    $signature_image_data = base64_decode($signature_image_data);

    $signature_dir = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/signatures';
    if (!file_exists($signature_dir)) {
        mkdir($signature_dir, 0777, true);
    }

    $signature_filename = "admin_signature_{$assignment_id}_{$timestamp}.png";
    $signature_path = $signature_dir . '/' . $signature_filename;
    file_put_contents($signature_path, $signature_image_data);

    // Copy the HR-signed PDF to the admin-signed location
    copy($hr_signed_file, $admin_signed_full_path);
    
    // Add signature and date to the PDF using mPDF
    require_once($_SERVER['DOCUMENT_ROOT'] . '/emps/vendor/autoload.php');
    
    // Create temporary HTML file for signature with improved positioning and styling
    $signatureHtml = '<html><head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10pt; }
            .signature-container { 
                position: absolute; 
                bottom: 150px; 
                left: 50%; 
                transform: translateX(-50%);
                text-align: center; 
                border: 1px solid #ccc; 
                padding: 12px; 
                background-color: rgba(255, 255, 255, 0.95);
                width: 200px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .signature-image { 
                width: 150px; 
                border-bottom: 1px solid #003366; 
                padding-bottom: 6px;
                filter: sepia(20%) hue-rotate(190deg) saturate(800%);
            }
            .signature-date { font-size: 8pt; margin-top: 8px; color: #003366; }
            .signature-name { font-family: "Arial", sans-serif; font-size: 9pt; font-weight: bold; margin-top: 4px; color: #003366; }
            .signature-title { font-size: 8pt; color: #003366; margin-top: 2px; }
        </style>
    </head><body>
        <div class="signature-container">
            <img class="signature-image" src="' . $signature_path . '" />
            <div class="signature-date">Date: ' . date('F j, Y, g:i a') . '</div>
            <div class="signature-name">Admin ID: ' . $admin_id . '</div>
            <div class="signature-title">Administrator Signature</div>
        </div>
    </body></html>';
    
    $tempHtmlFile = $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/temp_signature.html';
    file_put_contents($tempHtmlFile, $signatureHtml);
    
    // Initialize mPDF with better settings
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => $_SERVER['DOCUMENT_ROOT'] . '/emps/documents/temp',
        'format' => 'A4',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0
    ]);
    
    // Load the existing PDF
    $pageCount = $mpdf->SetSourceFile($admin_signed_full_path);
    
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
    $mpdf->Output($admin_signed_full_path, \Mpdf\Output\Destination::FILE);
    
    // Clean up temporary file
    if (file_exists($tempHtmlFile)) {
        unlink($tempHtmlFile);
    }

    // Update the database with admin signature information
    $stmt = $con->prepare("UPDATE document_assignments 
                          SET admin_signature_image = ?, 
                              admin_signed_date = NOW(), 
                              signed_admin_id = ?,
                              admin_signed_file_path = ?,
                              status = 'Completed',
                              admin_signstatus = 'Signed'
                          WHERE assignment_id = ?");
    $signature_db_path = "documents/signatures/{$signature_filename}";
    $stmt->bind_param("sisi", $signature_db_path, $admin_id, $admin_signed_path, $assignment_id);

    if ($stmt->execute()) {
        // Update the admin document queue
        $stmt = $con->prepare("UPDATE admin_document_queue 
                              SET status = 'Completed', 
                                  signed_date = NOW() 
                              WHERE assignment_id = ? AND status = 'Pending'");
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();

        // Redirect back to the document list
        header('Location: admin_documents.php?success=1');
        exit;
    } else {
        die("Error updating document: " . $con->error);
    }
} else {
    header('Location: admin_documents.php');
    exit;
}
?>