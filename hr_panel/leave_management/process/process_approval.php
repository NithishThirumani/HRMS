<?php
include('../../includes/session.php');
include('../includes/leave_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $leave_id = $_POST['leave_id'];
        $action = $_POST['action'];
        $remarks = $_POST['remarks'];
        $approver_id = $_SESSION['eid'];
        $approver_role = $_SESSION['role'];

        // Start transaction
        $con->begin_transaction();

        // Verify user has approver rights
        if (!hasApproverRights($approver_role)) {
            throw new Exception("You don't have permission to approve leaves");
        }

        // Get leave details
        $leave = getLeaveDetails($leave_id);
        if (!$leave) {
            throw new Exception("Leave request not found");
        }

        // Verify leave is pending approval
        if ($leave['status'] !== 'Recommended' || $leave['current_approver_role'] !== $approver_role) {
            throw new Exception("Invalid leave status for approval");
        }

        // Get next approver (if any)
        $workflow = getApprovalWorkflow($leave['emp_id']);
        $next_approver = $action === 'approve' ? $workflow['next_approver'] : null;

        // Update leave status
        $status = $action === 'approve' ? 
                 ($next_approver ? 'In Progress' : 'Approved') : 
                 'Rejected';

        $query = "UPDATE leaves SET 
                  status = ?,
                  current_approver_role = ?,
                  approver_status = ?,
                  approver_remarks = ?,
                  approver_action_date = NOW(),
                  approver_id = ?
                  WHERE id = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param(
            "sssssi",
            $status,
            $next_approver,
            $action === 'approve' ? 'Approved' : 'Rejected',
            $remarks,
            $approver_id,
            $leave_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error processing approval");
        }

        // If approved and no next approver, update leave balance
        if ($action === 'approve' && !$next_approver) {
            updateLeaveBalance($leave['emp_id'], $leave['type_of_leave'], $leave['total_days']);
        }

        // Send notifications
        if ($action === 'approve' && $next_approver) {
            sendLeaveNotifications($next_approver, $leave['emp_id'], $leave_id);
        }
        sendStatusNotification($leave['emp_id'], $leave_id, $status);

        $con->commit();
        $_SESSION['success'] = "Leave request has been " . ($action === 'approve' ? 'approved' : 'rejected');

    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: ../approver_dashboard.php");
    exit();
}