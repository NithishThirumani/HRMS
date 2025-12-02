<?php
include('../../session.php');
include('../includes/leave_functions.php');
include('../../../includes/email_functions.php');

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

        // Get leave details with employee information
        $stmt = $con->prepare("
            SELECT l.*, e.id as emp_numeric_id, e.full_name as employee_name, e.email as employee_email,
                   e.department_id, d.name as department_name
            FROM leaves l 
            JOIN employees e ON l.emp_id = e.eid 
            JOIN departments d ON e.department_id = d.id
            WHERE l.id = ?
        ");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave = $result->fetch_assoc();

        if (!$leave) {
            throw new Exception("Leave request not found");
        }

        // Verify leave is pending approval
        if ($leave['status'] !== 'Recommended') {
            throw new Exception("Invalid leave status for approval");
        }

        // Get approver's numeric ID
        $stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
        $stmt->bind_param("s", $approver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approver = $result->fetch_assoc();
        $approver_numeric_id = $approver['id'];

        // Verify the approver has authority
        $stmt = $con->prepare("
            SELECT lh.* 
            FROM leave_hierarchy lh
            WHERE lh.employee_id = ? AND lh.type = 'approver' AND lh.approver_id = ?
        ");
        $stmt->bind_param("ii", $leave['emp_numeric_id'], $approver_numeric_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $hierarchy_check = $result->fetch_assoc();

        if (!$hierarchy_check) {
            throw new Exception("You don't have permission to approve this leave");
        }

        // Update leave status
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        
        $query = "UPDATE leaves SET 
                  status = ?,
                  approver_id = ?,
                  approver_remarks = ?,
                  approver_action_date = NOW()
                  WHERE id = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param(
            "sisi",
            $status,
            $approver_numeric_id,
            $remarks,
            $leave_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error processing approval");
        }

        // Get HR emails for notification
        $hr_emails = getHREmails($con);
        $hr_email = !empty($hr_emails) ? $hr_emails[0] : null;

        // Send notifications
        $leave_data = [
            'employee_name' => $leave['employee_name'],
            'leave_type' => $leave['type_of_leave'],
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'total_days' => $leave['total_days'],
            'reason' => $leave['reason'],
            'approver_remarks' => $remarks
        ];

        // Get approver name
        $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
        $stmt->bind_param("i", $approver_numeric_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approver_info = $result->fetch_assoc();
        $approver_name = $approver_info['full_name'];

        // Send email notifications
        sendLeaveApprovalEmail($leave_data, $approver_name, $status, $leave['employee_email'], $hr_email);

        $con->commit();
        $_SESSION['success'] = "Leave request has been " . ($action === 'approve' ? 'approved' : 'rejected');

    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: ../approver_dashboard.php");
    exit();
}