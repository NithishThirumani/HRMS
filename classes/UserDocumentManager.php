<?php
require_once 'DocumentManager.php';

class UserDocumentManager extends DocumentManager
{
    // Add constructor to initialize database connection
    public function __construct($con, $user_id)
    {
        parent::__construct($con, 'employee', $user_id);
    }

    // User-specific document operations
    public function getEmployeeDocuments($employeeId)
    {
        // Verify the employee is accessing their own documents
        if ($employeeId != $this->user_id) {
            return [];
        }

        $sql = "SELECT da.*, dt.doc_title, dt.doc_type 
                FROM document_assignments da
                JOIN document_templates dt ON da.template_id = dt.template_id
                JOIN employees e ON da.emp_id = e.id
                WHERE e.id = ? 
                AND da.signing_status = 'pending'
                ORDER BY da.assigned_date DESC";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Add user-specific methods below
    public function validateUserOwnership($assignmentId, $userId)
    {
        $sql = "SELECT e.eid 
                FROM document_assignments da
                JOIN employees e ON da.emp_id = e.id
                WHERE da.assignment_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return ($result['eid'] == $userId);
    }

    // Remove duplicate class declaration and fix method placement
    public function saveSignedDocument($assignmentId, $signatureData, $signatureImage = null)
    {
        $stmt = $this->con->prepare("
            UPDATE document_assignments 
            SET signature_data = ?,
                signature_image = ?,
                signed_date = NOW(),
                signing_status = 'signed'
            WHERE assignment_id = ?
        ");
        $stmt->bind_param("ssi", $signatureData, $signatureImage, $assignmentId);
        return $stmt->execute();
    }
}
?>