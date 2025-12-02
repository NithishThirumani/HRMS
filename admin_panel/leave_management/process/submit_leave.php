<?php
include('../../includes/session.php');
include('../includes/leave_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $leave_type = $_POST['leave_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = $_POST['reason'];
        $emp_id = $_SESSION['eid'];

        // Calculate total days
        $total_days = calculateLeaveDays($start_date, $end_date);

        // Validate leave balance
        if (!hasEnoughLeaveBalance($emp_id, $leave_type, $total_days)) {
            throw new Exception("Insufficient leave balance");
        }

        // Handle document upload
        $document_path = null;
        if (isset($_FILES['document']) && $_FILES['document']['size'] > 0) {
            $document_path = uploadDocument($_FILES['document']);
        }

        // Get approval workflow
        $workflow = getApprovalWorkflow($emp_id);
        
        // Start transaction
        $con->begin_transaction();

        // Insert leave request
        $query = "INSERT INTO leaves (
            emp_id, 
            type_of_leave, 
            start_date, 
            end_date, 
            reason, 
            total_days, 
            status,
            current_approver_role,
            doctor_cert
        ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)";

        $stmt = $con->prepare($query);
        $stmt->bind_param(
            "ssssssss",
            $emp_id,
            $leave_type,
            $start_date,
            $end_date,
            $reason,
            $total_days,
            $workflow['first_approver'],
            $document_path
        );

        if (!$stmt->execute()) {
            throw new Exception("Error submitting leave request");
        }

        $leave_id = $stmt->insert_id;

        // Send notifications
        sendLeaveNotifications($workflow['first_approver'], $emp_id, $leave_id);

        $con->commit();
        
        $_SESSION['success'] = "Leave application submitted successfully";
        header("Location: ../my_leaves.php");
        exit();

    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../apply.php");
        exit();
    }
}