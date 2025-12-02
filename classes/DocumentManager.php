<?php
class DocumentManager
{
    protected $con;
    protected $user_type;
    protected $user_id;
    protected $department_id;
    private $baseDocPath = 'c:/xampp/htdocs/emps/documents/';

    // Single constructor with proper parameter name
    public function __construct($con, $user_type = null, $user_id = null, $department_id = null)
    {
        $this->con = $con;
        $this->user_type = $user_type;
        $this->user_id = $user_id;
        $this->department_id = $department_id;
    }

    public function uploadTemplate($title, $docType, $file)
    {
        $targetDir = $this->baseDocPath . 'templates/';
        $fileName = time() . '_' . basename($file['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $sql = "INSERT INTO document_templates (doc_title, doc_type, file_path) 
                   VALUES (?, ?, ?)";
            $stmt = $this->con->prepare($sql);
            return $stmt->execute([$title, $docType, $fileName]);
        }
        return false;
    }

    public function assignDocumentToEmployee($empId, $templateId, $adminId)
    {
        try {
            error_log("Assignment attempt - EmpID: $empId, TemplateID: $templateId, AdminID: $adminId");

            // Insert new assignment directly (simplified for testing)
            $sql = "INSERT INTO document_assignments (template_id, emp_id, assigned_date, assigned_by, status) 
                    VALUES (?, ?, NOW(), ?, 'active')";
            $stmt = $this->con->prepare($sql);  // Changed from $this->db
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }

            $stmt->bind_param("iii", $templateId, $empId, $adminId);
            $success = $stmt->execute();

            if (!$success) {
                error_log("Execute failed: " . $stmt->error);
                return false;
            }

            error_log("Assignment successful - Last Insert ID: " . $this->db->insert_id);
            return true;
        
        } catch (Exception $e) {
            error_log("Exception in assignDocumentToEmployee: " . $e->getMessage());
            return false;
        }
    }

    public function logDocumentAction($templateId, $empId, $action, $isActualId = false)
    {
        if ($isActualId) {
            $sql = "INSERT INTO document_audit_log (doc_id, emp_id, action, action_date, ip_address) 
                    VALUES (?, ?, ?, NOW(), ?)";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("iiss", $templateId, $empId, $action, $_SERVER['REMOTE_ADDR']);
        } else {
            $sql = "INSERT INTO document_audit_log (doc_id, emp_id, action, action_date, ip_address) 
                    SELECT ed.doc_id, e.id, ?, NOW(), ? 
                    FROM employees e 
                    JOIN employee_documents ed ON ed.emp_id = e.id
                    WHERE e.eid = ? AND ed.template_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("sssi", $action, $_SERVER['REMOTE_ADDR'], $empId, $templateId);
        }
        return $stmt->execute();
    }

    public function saveSignedDocument($assignmentId, $signatureData, $signatureImage = null)
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        // Get document details
        $sql = "SELECT da.*, dt.file_path, dt.doc_title, e.eid 
                FROM document_assignments da
                JOIN document_templates dt ON da.template_id = dt.template_id
                JOIN employees e ON da.emp_id = e.id
                WHERE da.assignment_id = ?";
        $stmt = $this->con->prepare($sql);  // Changed from $this->db (line 96)
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $document = $stmt->get_result()->fetch_assoc();

        if ($document) {
            // Fix path construction using basename()
            $templatePath = 'c:/xampp/htdocs/emps/Uploads/templates/' . basename($document['file_path']);
            $signedDir = 'c:/xampp/htdocs/emps/documents/signed';

            // Ensure directory exists and is writable
            if (!file_exists($signedDir)) {
                mkdir($signedDir, 0777, true);
            } else {
                // Make sure the directory is writable
                chmod($signedDir, 0777);
            }

            // Clean the filename to prevent path issues
            $cleanFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $document['eid']) . '_' .
                preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($document['file_path'], PATHINFO_FILENAME)) .
                '_signed.pdf';
            $signedFilePath = $signedDir . '/' . $cleanFileName;

            // Initialize mPDF with specific margins
            $mpdf = new \Mpdf\Mpdf([
                'margin_bottom' => 60,
                'margin_footer' => 5,
                'tempDir' => 'c:/xampp/htdocs/emps/temp' // Specify a temp directory
            ]);

            $pagecount = $mpdf->setSourceFile($templatePath);
            $tplId = $mpdf->importPage(1);
            $mpdf->useTemplate($tplId);

            // Create signature block at the bottom
            $mpdf->SetY(-55); // Move to bottom of page

            // Add horizontal line
            $mpdf->Line(15, $mpdf->y, $mpdf->w - 15, $mpdf->y);
            $mpdf->y += 2;

            // Signature section
            $mpdf->SetFont('Arial', 'B', 10);
            if ($signatureImage) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureImage));
                $tempFile = tempnam(sys_get_temp_dir(), 'sig');
                file_put_contents($tempFile, $imageData);
                $mpdf->Image($tempFile, 15, $mpdf->y, 40, 15);
                unlink($tempFile);
            } else {
                $mpdf->SetFont('Arial', '', 10);
                $mpdf->WriteText(15, $mpdf->y + 10, $signatureData);
            }

            // Date and time (right side)
            $mpdf->SetFont('Arial', '', 8);
            $mpdf->WriteText($mpdf->w - 60, $mpdf->y, 'Signed on: ' . date('Y-m-d H:i:s'));



            // Disclaimer right below signature and time
            $mpdf->SetFont('Arial', '', 7);
            $mpdf->SetY($mpdf->y + 15); // Position below signature and timestamp
            $disclaimer = "I confirm that I have read and understood this document. The signature is mine, placed voluntarily without any influence.";
            $mpdf->MultiCell(0, 3, $disclaimer, 0, 'L');


            // Save the signed PDF
            // Try to save the file with error handling
            try {
                $mpdf->Output($signedFilePath, 'F');
            } catch (Exception $e) {
                error_log("Failed to save PDF: " . $e->getMessage());
                throw new Exception("Unable to save signed document. Please try again.");
            }

            // Verify user has permission to sign
            $sql = "SELECT da.*, e.department_id 
                    FROM document_assignments da
                    JOIN employees e ON da.emp_id = e.id
                    WHERE da.assignment_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $assignmentId);
            $stmt->execute();
            $document = $stmt->get_result()->fetch_assoc();

            if (!$document) {
                return false;
            }

            // Check authorization based on user type
            if ($this->user_type === 'employee') {
                if ($document['emp_id'] != $this->user_id) {
                    return false;
                }
            } elseif ($this->user_type === 'department_head') {
                if ($document['department_id'] != $this->department_id) {
                    return false;
                }
            }

            // Update document with signature
            $sql = "UPDATE document_assignments 
                    SET status = 'signed',
                        signature_data = ?,
                        signature_image = ?,
                        signed_date = CURRENT_TIMESTAMP,
                        signed_file_path = ?
                    WHERE assignment_id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("sssi", $signatureData, $signatureImage, $cleanFileName, $assignmentId);
            return $stmt->execute();
        }
        return false;
    }

    public function getEmployeeDocuments($empId)
    {
        $sql = "SELECT da.*, dt.doc_title, dt.doc_type 
                FROM document_assignments da
                JOIN document_templates dt ON da.template_id = dt.template_id
                JOIN employees e ON da.emp_id = e.id
                WHERE e.eid = ?";

        // Add authorization based on user type
        if ($this->user_type === 'employee') {
            $sql .= " AND e.eid = ?";
        } elseif ($this->user_type === 'department_head') {
            $sql .= " AND e.department_id = ?";
        }

        $sql .= " AND da.status = 'active'
                ORDER BY da.assigned_date DESC";

        $stmt = $this->con->prepare($sql);

        if ($this->user_type === 'employee') {
            $stmt->bind_param("ss", $empId, $empId);
        } elseif ($this->user_type === 'department_head') {
            $stmt->bind_param("si", $empId, $this->department_id);
        } else {
        $stmt->bind_param("s", $empId);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>