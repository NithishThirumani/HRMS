<?php
include('../../session.php');
include('../includes/leave_functions.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    try {
        $action = $_GET['action'];
        $leave_id = $_GET['id'];
        $current_role = $_SESSION['role'];
        
        // Start transaction
        $con->begin_transaction();

        // Get leave details
        $leave = getLeaveDetails($leave_id);
        if (!$leave) {
            throw new Exception("Leave request not found");
        }

        // Verify current user has authority
        if ($leave['current_approver_role'] !== $current_role) {
            throw new Exception("You don't have permission to perform this action");
        }

        // Get next approver from workflow
        $next_approver = getNextApprover($leave['emp_id'], $current_role);

        // Update leave status
        $status = '';
        $remarks_field = '';
        $action_date_field = '';
        $by_field = '';

        switch ($current_role) {
            case 'HOD':
                $remarks_field = 'hod_remarks';
                $action_date_field = 'hod_action_date';
                $by_field = 'hod_by';
                break;
            case 'HR':
                $remarks_field = 'hr_remarks';
                $action_date_field = 'hr_action_date';
                $by_field = 'hr_by';
                break;
            default:
                $remarks_field = 'admin_remarks';
                $action_date_field = 'admin_action_date';
                $by_field = 'admin_by';
        }

        if ($action === 'approve') {
            $status = $next_approver ? 'Pending' : 'Approved';
        } else {
            $status = 'Rejected';
        }

        $query = "UPDATE leaves SET 
                  status = ?,
                  current_approver_role = ?,
                  $remarks_field = ?,
                  $action_date_field = NOW(),
                  $by_field = ?
                  WHERE id = ?";

        $stmt = $con->prepare($query);
        $remarks = $action === 'approve' ? 'Approved' : 'Rejected';
        $next_approver = $next_approver ?: null;
        
        $stmt->bind_param(
            "ssssi",
            $status,
            $next_approver,
            $remarks,
            $_SESSION['eid'],
            $leave_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error processing leave action");
        }

        // Send notifications
        if ($action === 'approve' && $next_approver) {
            sendLeaveNotifications($next_approver, $leave['emp_id'], $leave_id);
        }
        sendStatusNotification($leave['emp_id'], $leave_id, $status);

        $con->commit();
        
        $_SESSION['success'] = "Leave request " . ucfirst($action) . "d successfully";
        header("Location: ../pending_actions.php");
        exit();

    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pending_actions.php");
        exit();
    }
}