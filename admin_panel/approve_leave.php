<?php
include('session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    $user_role = $_SESSION['role'] ?? 'admin'; // Default to admin if role not set

    mysqli_begin_transaction($con);
    try {
        // Get current leave status and employee details
        $status_query = "SELECT l.*, e.email, e.full_name, e.department 
                         FROM leaves l 
                         JOIN employees e ON l.emp_id = e.id 
                         WHERE l.id = ?";
        $stmt = $con->prepare($status_query);
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave = $result->fetch_assoc();

        // Determine which status to update based on user role
        if ($user_role == 'hod') {
            // HOD can only approve/reject leaves from their department
            if ($leave['department'] != $_SESSION['department']) {
                throw new Exception("You can only approve leaves from your department");
            }

            $update_sql = "UPDATE leaves 
                          SET hod_status = ?, hod_remarks = ?, 
                              hod_action_date = NOW(), 
                              hod_by = ? 
                          WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("sssi", $action, $remarks, $_SESSION['username'], $leave_id);
            $stmt->execute();

            // If rejected by HOD, automatically reject at HR and Admin levels
            if ($action == 'rejected') {
                $reject_sql = "UPDATE leaves 
                              SET hr_status = 'rejected', admin_status = 'rejected',
                                  hr_remarks = 'Auto-rejected (HOD rejected)', 
                                  admin_remarks = 'Auto-rejected (HOD rejected)',
                                  hr_action_date = NOW(), 
                                  admin_action_date = NOW(),
                                  status = 'Rejected'
                              WHERE id = ?";
                $stmt = $con->prepare($reject_sql);
                $stmt->bind_param("i", $leave_id);
                $stmt->execute();
            }
        } elseif ($user_role == 'hr') {
            // HR can bypass HOD approval if needed
            if ($leave['hod_status'] == 'pending' && $action == 'approved') {
                // Bypass HOD approval
                $bypass_sql = "UPDATE leaves 
                              SET hod_status = 'approved', 
                                  hod_remarks = 'Auto-approved by HR bypass', 
                                  hod_action_date = NOW(),
                                  hod_by = ?
                              WHERE id = ?";
                $stmt = $con->prepare($bypass_sql);
                $stmt->bind_param("si", $_SESSION['username'], $leave_id);
                $stmt->execute();
            }

            // Update HR status
            $update_sql = "UPDATE leaves 
                          SET hr_status = ?, hr_remarks = ?, 
                              hr_action_date = NOW(), 
                              hr_by = ? 
                          WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("sssi", $action, $remarks, $_SESSION['username'], $leave_id);
            $stmt->execute();

            // If rejected by HR, automatically reject at Admin level
            if ($action == 'rejected') {
                $reject_sql = "UPDATE leaves 
                              SET admin_status = 'rejected',
                                  admin_remarks = 'Auto-rejected (HR rejected)',
                                  admin_action_date = NOW(),
                                  status = 'Rejected'
                              WHERE id = ?";
                $stmt = $con->prepare($reject_sql);
                $stmt->bind_param("i", $leave_id);
                $stmt->execute();
            }
        } else { // Admin
            // Admin can bypass both HOD and HR approval if needed
            if ($leave['hod_status'] == 'pending' && $action == 'approved') {
                // Bypass HOD approval
                $bypass_hod_sql = "UPDATE leaves 
                                  SET hod_status = 'approved', 
                                      hod_remarks = 'Auto-approved by Admin bypass', 
                                      hod_action_date = NOW(),
                                      hod_by = ?
                                  WHERE id = ?";
                $stmt = $con->prepare($bypass_hod_sql);
                $stmt->bind_param("si", $_SESSION['username'], $leave_id);
                $stmt->execute();
            }

            if ($leave['hr_status'] == 'pending' && $action == 'approved') {
                // Bypass HR approval
                $bypass_hr_sql = "UPDATE leaves 
                                 SET hr_status = 'approved', 
                                     hr_remarks = 'Auto-approved by Admin bypass', 
                                     hr_action_date = NOW(),
                                     hr_by = ?
                                 WHERE id = ?";
                $stmt = $con->prepare($bypass_hr_sql);
                $stmt->bind_param("si", $_SESSION['username'], $leave_id);
                $stmt->execute();
            }

            // Update admin approval status
            $update_sql = "UPDATE leaves 
                          SET admin_status = ?, admin_remarks = ?, 
                              admin_action_date = NOW(), 
                              admin_by = ? 
                          WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("sssi", $action, $remarks, $_SESSION['username'], $leave_id);
            $stmt->execute();
        }

        // If all approvals are complete and approved, update final status and leave balance
        if ($action == 'approved') {
            // Re-fetch the leave status after updates
            $stmt = $con->prepare($status_query);
            $stmt->bind_param("i", $leave_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $updated_leave = $result->fetch_assoc();

            if (
                $updated_leave['hod_status'] == 'approved' &&
                $updated_leave['hr_status'] == 'approved' &&
                $updated_leave['admin_status'] == 'approved'
            ) {

                // Update final status
                $final_sql = "UPDATE leaves SET status = 'Approved' WHERE id = ?";
                $stmt = $con->prepare($final_sql);
                $stmt->bind_param("i", $leave_id);
                $stmt->execute();

                // Update leave balance
                $update_balance = "UPDATE employee_leave_balance 
                                 SET balance = balance - ?
                                 WHERE emp_id = ? 
                                 AND leave_type = ?
                                 AND year = YEAR(CURRENT_DATE)";
                $stmt = $con->prepare($update_balance);
                $stmt->bind_param("iis", $updated_leave['total_days'], $updated_leave['emp_id'], $updated_leave['type_of_leave']);
                $stmt->execute();
            }
        } else if ($action == 'rejected') {
            // Update final status to rejected
            $final_sql = "UPDATE leaves SET status = 'Rejected' WHERE id = ?";
            $stmt = $con->prepare($final_sql);
            $stmt->bind_param("i", $leave_id);
            $stmt->execute();
        }

        // Send email notification
        include('../includes/email_functions.php');
        $subject = "Leave Request Update";
        $message = "Dear {$leave['full_name']},<br><br>
                   Your leave request has been " . strtoupper($action) . " by " . ucfirst($user_role) . ".<br>
                   Remarks: {$remarks}<br><br>
                   Best regards,<br>
                   " . ucfirst($user_role) . " Department";

        sendLeaveNotification($leave['email'], $subject, $message);

        mysqli_commit($con);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "error: " . $e->getMessage();
    }
}
?>