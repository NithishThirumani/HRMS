<?php

function getApprovalChain($employeeId, $requestType, $con) {
    // Get employee's role and department
    $sql = "SELECT e.role_id, e.department_id, ah.role as employee_role 
            FROM employees e 
            JOIN approval_hierarchy ah ON e.role_id = ah.id 
            WHERE e.eid = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $employeeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $employee = mysqli_fetch_assoc($result);

    // Get approval route for this employee's role and request type
    $sql = "SELECT approval_sequence 
            FROM approval_routes 
            WHERE request_type = ? 
            AND employee_role = ? 
            AND (department_id IS NULL OR department_id = ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $requestType, $employee['employee_role'], $employee['department_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $route = mysqli_fetch_assoc($result);

    if (!$route) {
        return null;
    }

    $approvalSequence = json_decode($route['approval_sequence'], true);
    $approvers = [];

    // Get actual approver IDs for each role in the sequence
    foreach ($approvalSequence as $level) {
        $sql = "SELECT e.eid 
                FROM employees e 
                JOIN approval_hierarchy ah ON e.role_id = ah.id 
                WHERE ah.role = ? 
                AND (e.department_id = ? OR ah.department_id IS NULL)
                AND FIND_IN_SET(?, ah.can_approve_types)
                LIMIT 1";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sis", $level['role'], $employee['department_id'], $requestType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $approver = mysqli_fetch_assoc($result);
        
        if ($approver) {
            $approvers[] = $approver['eid'];
        }
    }

    return $approvers;
}

function createApprovalWorkflow($employeeId, $requestType, $requestId, $con) {
    $approvers = getApprovalChain($employeeId, $requestType, $con);
    
    if (empty($approvers)) {
        return false;
    }

    $currentApprover = $approvers[0];
    $nextApprover = isset($approvers[1]) ? $approvers[1] : null;
    $approvalChain = json_encode($approvers);

    $sql = "INSERT INTO approval_workflow (
                request_type, request_id, employee_id, 
                current_approver, next_approver, 
                status, approval_chain
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param(
        $stmt, 
        "sisss", 
        $requestType, 
        $requestId, 
        $employeeId, 
        $currentApprover, 
        $nextApprover, 
        $approvalChain
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $workflowId = mysqli_insert_id($con);
        notifyApprover($currentApprover, $employeeId, $requestType, $workflowId, $con);
        return $workflowId;
    }
    
    return false;
}

function processApproval($workflowId, $approverId, $action, $comments, $con) {
    // Get current workflow status
    $sql = "SELECT * FROM approval_workflow WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $workflowId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $workflow = mysqli_fetch_assoc($result);

    if (!$workflow || $workflow['current_approver'] !== $approverId) {
        return false;
    }

    $approvalChain = json_decode($workflow['approval_chain'], true);
    $currentLevel = $workflow['current_level'];

    // Record the approval action
    $sql = "INSERT INTO approval_history (
                workflow_id, approver_id, action, comments
            ) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $workflowId, $approverId, $action, $comments);
    mysqli_stmt_execute($stmt);

    if ($action === 'rejected') {
        // Update workflow as rejected
        $sql = "UPDATE approval_workflow 
                SET status = 'rejected', 
                    comments = CONCAT(IFNULL(comments,''), '\n', ?)
                WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "si", $comments, $workflowId);
        mysqli_stmt_execute($stmt);

        // Update the original request status
        updateRequestStatus($workflow['request_type'], $workflow['request_id'], 'rejected', $con);
        
        return true;
    }

    if ($action === 'approved') {
        // Check if this is the final approval
        if (!isset($approvalChain[$currentLevel])) {
            // Final approval
            $sql = "UPDATE approval_workflow 
                    SET status = 'approved', 
                        comments = CONCAT(IFNULL(comments,''), '\n', ?)
                    WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $comments, $workflowId);
            mysqli_stmt_execute($stmt);

            // Update the original request status
            updateRequestStatus($workflow['request_type'], $workflow['request_id'], 'approved', $con);
        } else {
            // Move to next approver
            $nextApprover = $approvalChain[$currentLevel];
            $nextNextApprover = isset($approvalChain[$currentLevel + 1]) ? $approvalChain[$currentLevel + 1] : null;

            $sql = "UPDATE approval_workflow 
                    SET current_level = current_level + 1,
                        current_approver = ?,
                        next_approver = ?,
                        status = 'in_progress',
                        comments = CONCAT(IFNULL(comments,''), '\n', ?)
                    WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $nextApprover, $nextNextApprover, $comments, $workflowId);
            mysqli_stmt_execute($stmt);

            // Notify next approver
            notifyApprover($nextApprover, $workflow['employee_id'], $workflow['request_type'], $workflowId, $con);
        }
        
        return true;
    }

    return false;
}

function updateRequestStatus($requestType, $requestId, $status, $con) {
    switch ($requestType) {
        case 'leave':
            $sql = "UPDATE leaves SET status = ? WHERE id = ?";
            break;
        case 'appraisal':
            $sql = "UPDATE appraisal_assignments SET status = ? WHERE id = ?";
            break;
        case 'esignature':
            $sql = "UPDATE admin_document_queue SET status = ? WHERE queue_id = ?";
            break;
        default:
            return false;
    }

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $requestId);
    return mysqli_stmt_execute($stmt);
}

function notifyApprover($approverId, $employeeId, $requestType, $workflowId, $con) {
    // Get approver and employee details
    $sql = "SELECT e1.email as approver_email, e1.full_name as approver_name,
                   e2.full_name as employee_name
            FROM employees e1 
            JOIN employees e2 ON e2.eid = ?
            WHERE e1.eid = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $employeeId, $approverId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    // Send email notification
    require_once 'PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'your-smtp-host';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email';
    $mail->Password = 'your-password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your-email', 'Your Company');
    $mail->addAddress($data['approver_email'], $data['approver_name']);
    $mail->isHTML(true);

    $mail->Subject = ucfirst($requestType) . ' Request Pending Approval';
    $mail->Body = "Dear {$data['approver_name']},<br><br>
                  A new {$requestType} request from {$data['employee_name']} requires your approval.<br><br>
                  Please login to the system to review and process this request.<br><br>
                  Workflow ID: {$workflowId}";

    return $mail->send();
} 