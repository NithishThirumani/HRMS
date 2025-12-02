<?php
require_once 'DocumentManager.php';

class HRDocumentManager extends DocumentManager {
    public function __construct($con, $user_id) {
        parent::__construct($con, 'hr', $user_id);
    }

    // HR-specific countersign method
    public function saveCounterSignedDocument($assignmentId, $signatureData, $signatureImage) {
        // Verify HR has permission to sign
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

        $stmt = $this->con->prepare("
            UPDATE document_assignments 
            SET hr_signed_by = ?,
                hr_signature_data = ?,
                hr_signature_image = ?,
                hr_signed_at = NOW()
            WHERE assignment_id = ?
        ");
        $stmt->bind_param("issi", $this->user_id, $signatureData, $signatureImage, $assignmentId);
        return $stmt->execute();
    }
    
    public function getPendingHRDocuments() {
        $stmt = $this->con->prepare("
            SELECT da.document_number, da.assignment_id, dt.doc_title, 
                   e.full_name, da.assigned_date 
            FROM document_assignments da
            JOIN document_templates dt ON da.template_id = dt.template_id
            JOIN employees e ON da.emp_id = e.id
            WHERE da.hr_signstatus = 'pending'
            ORDER BY da.assigned_date DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}