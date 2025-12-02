<?php
include('session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    
    mysqli_begin_transaction($con);
    try {
        // Update HR approval status
        $update_sql = "UPDATE leaves 
                      SET hr_status = ?, hr_remarks = ?, 
                          hr_action_date = NOW(), 
                          hr_by = ? 
                      WHERE id = ?";
        $stmt = $con->prepare($update_sql);
        $stmt->bind_param("sssi", $action, $remarks, $_SESSION['username'], $leave_id);
        $stmt->execute();
        
        // If both HR and Admin approved, update final status and leave balance
        if($action == 'approved') {
            $check_admin = "SELECT admin_status FROM leaves WHERE id = ?";
            $stmt = $con->prepare($check_admin);
            $stmt->bind_param("i", $leave_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $leave = $result->fetch_assoc();
            
            // Get employee email
            $emp_query = "SELECT e.email, e.full_name 
                         FROM leaves l 
                         JOIN employees e ON l.emp_id = e.id 
                         WHERE l.id = ?";
            $stmt = $con->prepare($emp_query);
            $stmt->bind_param("i", $leave_id);
            $stmt->execute();
            $emp_result = $stmt->get_result();
            $employee = $emp_result->fetch_assoc();
            
            // Send email notification
            include('../includes/email_functions.php');
            $subject = "Leave Request Update";
            $message = "Dear {$employee['full_name']},<br><br>
                       Your leave request has been " . strtoupper($action) . " by HR.<br>
                       Remarks: {$remarks}<br><br>
                       Best regards,<br>
                       HR Department";
            
            sendLeaveNotification($employee['email'], $subject, $message);
            if($leave['admin_status'] == 'approved') {
                // Update final status
                mysqli_query($con, "UPDATE leaves SET status = 'Approved' WHERE id = '$leave_id'");
                
                // Update leave balance
                $leave_details = mysqli_query($con, "SELECT * FROM leaves WHERE id = '$leave_id'");
                $leave_data = mysqli_fetch_assoc($leave_details);
                
                $update_balance = "UPDATE employee_leave_balance 
                                 SET balance = balance - {$leave_data['total_days']}
                                 WHERE emp_id = '{$leave_data['emp_id']}'
                                 AND leave_type = '{$leave_data['type_of_leave']}'
                                 AND year = YEAR(CURRENT_DATE)";
                mysqli_query($con, $update_balance);
            }
        }
        
        mysqli_commit($con);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "error";
    }
}
?>