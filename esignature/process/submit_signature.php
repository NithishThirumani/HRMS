<?php
require_once '../../config.php';
require_once '../../connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
}

$document_id = $_POST['document_id'] ?? 0;
$signature_data = $_POST['signature_data'] ?? '';
$signature_type = $_POST['signature_type'] ?? '';
$action = $_POST['action'] ?? '';
$comment = $_POST['comment'] ?? '';

if (!$document_id || !$action) {
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
}

try {
    $con->begin_transaction();

    // Get user details based on role
    $user = null;
    $user_type = null;

    // Check if user is an admin
    $stmt = $con->prepare("SELECT id, email, role FROM admin WHERE email = ? AND LOWER(status) = 'active'");
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
                                  WHERE e.email = ? AND LOWER(e.status) = 'active'");
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
        throw new Exception('User not found');
    }

    $user_id = $user['id'];

    // Get current workflow level
    $workflow_query = "SELECT w.*, d.total_levels 
                      FROM esign_workflow w 
                      INNER JOIN esign_documents d ON w.document_id = d.id 
                      WHERE w.document_id = ? AND w.approver_id = ? AND w.status = 'pending'";
    $stmt = $con->prepare($workflow_query);
    $stmt->bind_param("ii", $document_id, $user_id);
    $stmt->execute();
    $workflow = $stmt->get_result()->fetch_assoc();

    if (!$workflow) {
        throw new Exception('No pending workflow found for this document');
    }

    // Process signature data based on type
    $processed_signature = '';
    if ($action === 'approve') {
        switch ($signature_type) {
            case 'draw':
            case 'upload':
                // For drawn and uploaded signatures, save as image file
                $signature_dir = '../../uploads/signatures/';
                if (!file_exists($signature_dir)) {
                    mkdir($signature_dir, 0777, true);
                }
                
                $signature_filename = 'signature_' . $document_id . '_' . $user_id . '_' . time() . '.png';
                $signature_path = $signature_dir . $signature_filename;
                
                // Remove data URL prefix if present
                $signature_data = preg_replace('/^data:image\/\w+;base64,/', '', $signature_data);
                $signature_data = base64_decode($signature_data);
                
                if (file_put_contents($signature_path, $signature_data)) {
                    $processed_signature = 'uploads/signatures/' . $signature_filename;
                } else {
                    throw new Exception('Failed to save signature image');
                }
                break;
                
            case 'type':
                // For typed signatures, store the text directly
                $processed_signature = $signature_data;
                break;
                
            default:
                throw new Exception('Invalid signature type');
        }
    }

    // Update current workflow step
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $con->prepare("UPDATE esign_workflow 
                          SET status = ?, 
                              signed_at = CURRENT_TIMESTAMP,
                              signature_type = ?,
                              signature_data = ?,
                              comments = ?
                          WHERE document_id = ? AND level = ?");
    $stmt->bind_param("ssssii", $status, $signature_type, $processed_signature, $comment, $document_id, $workflow['level']);
    $stmt->execute();

    // Insert comment into esign_comments table
    $stmt = $con->prepare("INSERT INTO esign_comments (document_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $document_id, $user_id, $comment);
    $stmt->execute();

    // If rejected, mark document as rejected
    if ($action === 'reject') {
        $update_doc = "UPDATE esign_documents SET status = 'rejected' WHERE id = ?";
        $stmt = $con->prepare($update_doc);
        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        // Notify document creator
        $notif_query = "INSERT INTO esign_notifications (document_id, user_id, message) 
                       SELECT id, created_by, ? 
                       FROM esign_documents WHERE id = ?";
        $reject_message = 'Your document has been rejected. Reason: ' . $comment;
        $stmt = $con->prepare($notif_query);
        $stmt->bind_param("si", $reject_message, $document_id);
        $stmt->execute();
    } 
    // If approved and not the last level, move to next level
    elseif ($action === 'approve' && $workflow['level'] < $workflow['total_levels']) {
        $next_level = $workflow['level'] + 1;
        
        // Get next approver
        $next_approver_query = "SELECT approver_id FROM esign_workflow 
                              WHERE document_id = ? AND level = ?";
        $stmt = $con->prepare($next_approver_query);
        $stmt->bind_param("ii", $document_id, $next_level);
        $stmt->execute();
        $next_approver = $stmt->get_result()->fetch_assoc();

        if ($next_approver) {
            // Notify next approver
            $notif_query = "INSERT INTO esign_notifications (document_id, user_id, message) 
                           VALUES (?, ?, 'A document requires your signature')";
            $stmt = $con->prepare($notif_query);
            $stmt->bind_param("ii", $document_id, $next_approver['approver_id']);
            $stmt->execute();
        }
    }
    // If approved and last level, mark document as completed
    elseif ($action === 'approve' && $workflow['level'] === $workflow['total_levels']) {
        $update_doc = "UPDATE esign_documents SET status = 'completed' WHERE id = ?";
        $stmt = $con->prepare($update_doc);
        $stmt->bind_param("i", $document_id);
        $stmt->execute();

        // Notify document creator
        $notif_query = "INSERT INTO esign_notifications (document_id, user_id, message) 
                       SELECT id, created_by, 'Your document has been fully approved' 
                       FROM esign_documents WHERE id = ?";
        $stmt = $con->prepare($notif_query);
        $stmt->bind_param("i", $document_id);
        $stmt->execute();
    }

    $con->commit();
    echo json_encode(['status' => 'success', 'message' => 'Document processed successfully']);

} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 