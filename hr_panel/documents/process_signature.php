<?php
include('../connection.php');
include('../session.php');

// The session.php already verifies HR role, so we can proceed directly

// Get form data
$document_id = $_POST['document_id'] ?? null;
$workflow_id = $_POST['workflow_id'] ?? null;
$signature_data = $_POST['signature_data'] ?? null;
$comments = $_POST['comments'] ?? '';

if (!$document_id || !$workflow_id || !$signature_data) {
    header('Location: hr_documents.php?error=missing_data');
    exit;
}

// Verify the document exists and HR can sign it
$stmt = $con->prepare("SELECT 
    ed.id as document_id,
    ed.title as doc_title,
    ed.file_path,
    ed.current_level,
    ed.total_levels,
    ew.id as workflow_id,
    ew.level as workflow_level,
    ew.status as workflow_status,
    ew.approver_type,
    e.full_name as employee_name
FROM esign_documents ed
LEFT JOIN esign_workflow ew ON ed.id = ew.document_id
LEFT JOIN employees e ON ew.approver_id = e.id
WHERE ed.id = ? AND ew.id = ? AND ed.is_deleted = 0");
$stmt->bind_param("ii", $document_id, $workflow_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: hr_documents.php?error=document_not_found');
    exit;
}

// Check if HR can sign this document
if ($document['workflow_status'] !== 'pending' || $document['approver_type'] !== 'head') {
    header('Location: hr_documents.php?error=not_authorized');
    exit;
}

// Get HR user ID from session
$hr_user_id = $_SESSION['eid'] ?? null;
if (!$hr_user_id) {
    header('Location: hr_documents.php?error=user_not_found');
    exit;
}

// Save signature image
$signature_filename = '';
if (!empty($signature_data)) {
    // Decode base64 signature data
    $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
    $signature_data = str_replace(' ', '+', $signature_data);
    $signature_data = base64_decode($signature_data);
    
    if ($signature_data !== false) {
        // Create signature directory if it doesn't exist
        $signature_dir = $_SERVER['DOCUMENT_ROOT'] . '/emps/uploads/signatures/';
        if (!is_dir($signature_dir)) {
            mkdir($signature_dir, 0755, true);
        }
        
        // Generate unique filename
        $signature_filename = 'hr_signature_' . $document_id . '_' . time() . '.png';
        $signature_path = $signature_dir . $signature_filename;
        
        // Save signature image
        if (file_put_contents($signature_path, $signature_data)) {
            $signature_filename = 'uploads/signatures/' . $signature_filename;
        } else {
            header('Location: hr_documents.php?error=signature_save_failed');
            exit;
        }
    }
}

// Update workflow status
$stmt = $con->prepare("UPDATE esign_workflow 
                      SET status = 'approved', 
                          signed_at = NOW(),
                          signature_data = ?,
                          comments = ?
                      WHERE id = ? AND document_id = ?");
$stmt->bind_param("ssii", $signature_filename, $comments, $workflow_id, $document_id);

if (!$stmt->execute()) {
    header('Location: hr_documents.php?error=workflow_update_failed');
    exit;
}

// Check if this was the final level
$stmt = $con->prepare("SELECT COUNT(*) as total_levels, 
                              SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_levels
                       FROM esign_workflow 
                       WHERE document_id = ?");
$stmt->bind_param("i", $document_id);
$stmt->execute();
$level_status = $stmt->get_result()->fetch_assoc();

// Update document status if all levels are approved
if ($level_status['total_levels'] == $level_status['approved_levels']) {
    $stmt = $con->prepare("UPDATE esign_documents 
                          SET status = 'completed', 
                              current_level = ?
                          WHERE id = ?");
    $stmt->bind_param("ii", $level_status['total_levels'], $document_id);
    $stmt->execute();
} else {
    // Move to next level
    $next_level = $document['current_level'] + 1;
    $stmt = $con->prepare("UPDATE esign_documents 
                          SET current_level = ?
                          WHERE id = ?");
    $stmt->bind_param("ii", $next_level, $document_id);
    $stmt->execute();
}

// Create notification for next approver or completion
if ($level_status['total_levels'] == $level_status['approved_levels']) {
    // Document completed - notify relevant parties
    $notification_message = "Document '{$document['doc_title']}' has been fully signed and completed.";
} else {
    // Notify next approver
    $next_workflow_stmt = $con->prepare("SELECT ew.*, e.full_name 
                                        FROM esign_workflow ew
                                        LEFT JOIN employees e ON ew.approver_id = e.id
                                        WHERE ew.document_id = ? AND ew.level = ? AND ew.status = 'pending'");
    $next_level = $document['current_level'] + 1;
    $next_workflow_stmt->bind_param("ii", $document_id, $next_level);
    $next_workflow_stmt->execute();
    $next_approver = $next_workflow_stmt->get_result()->fetch_assoc();
    
    if ($next_approver) {
        $notification_message = "Document '{$document['doc_title']}' is ready for your signature.";
        // You can add email notification here if needed
    }
}

// Log the action
$log_stmt = $con->prepare("INSERT INTO activity_log 
                          (eid, performed_by, activity, type, date) 
                          VALUES (?, ?, ?, 'document_signed', NOW())");
$log_details = "HR signed document ID: {$document_id}, Title: {$document['doc_title']}";
$performed_by = $_SESSION['email'] ?? 'HR User';
$log_stmt->bind_param("sss", $hr_user_id, $performed_by, $log_details);
$log_stmt->execute();

// Redirect with success message
header('Location: hr_documents.php?success=document_signed');
exit;
?>