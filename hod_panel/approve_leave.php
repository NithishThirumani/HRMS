<?php
include('session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    $user_role = $_SESSION['role'] ?? 'hod'; // Default to hod if role not set

    mysqli_begin_transaction($con);
    try {
        // Get current leave status and employee details
        $status_query = "SELECT l.*, e.email, e.full_name, d.name as department 
                         FROM leaves l 
                         JOIN employees e ON l.emp_id = e.eid 
                         JOIN departments d ON e.department_id = d.id
                         WHERE l.id = ?";
        $stmt = $con->prepare($status_query);
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave = $result->fetch_assoc();

        if (!$leave) {
            throw new Exception("Leave request not found");
        }

        // Verify HOD has authority
        if ($user_role == 'hod') {
            // HOD can only approve/reject leaves from their department
            if ($leave['department'] != $_SESSION['department']) {
                throw new Exception("You can only approve leaves from your department");
            }

            // Update leave status
            $update_sql = "UPDATE leaves 
                          SET status = ?, 
                              hod_remarks = ?, 
                              hod_action_date = NOW(), 
                              hod_by = ? 
                          WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("sssi", $action, $remarks, $_SESSION['username'], $leave_id);
            $stmt->execute();

            // If rejected by HOD, update final status
            if ($action == 'Rejected') {
                $reject_sql = "UPDATE leaves 
                              SET status = 'Rejected'
                              WHERE id = ?";
                $stmt = $con->prepare($reject_sql);
                $stmt->bind_param("i", $leave_id);
                $stmt->execute();
            }

            // Send email notification
            include('../../includes/email_functions.php');
            $subject = "Leave Request Update";
            $message = "Dear {$leave['full_name']},<br><br>
                       Your leave request has been " . strtoupper($action) . " by HOD.<br>
                       Remarks: {$remarks}<br><br>
                       Best regards,<br>
                       HOD Department";

            sendLeaveNotification($leave['email'], $subject, $message);

            mysqli_commit($con);
            echo "success";
        } else {
            throw new Exception("Unauthorized access");
        }
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "error: " . $e->getMessage();
    }
}
?>