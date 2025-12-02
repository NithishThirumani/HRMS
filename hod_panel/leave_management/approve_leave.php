<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../session.php');
include('../connection.php');

// Check if user is HOD
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'HOD') {
    echo "Unauthorized access";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $remarks = $_POST['remarks'] ?? '';
    $hod_id = $_SESSION['eid'] ?? null;
    $hod_department_id = $_SESSION['department_id'] ?? null;
    
    if (!$leave_id || !$action || !$hod_id || !$hod_department_id) {
        echo "Missing required parameters";
        exit();
    }
    
    // Verify the leave request belongs to HOD's department
    $verify_query = "SELECT l.*, e.full_name, e.email, e.department_id 
                     FROM leaves l
                     JOIN employees e ON l.emp_id = e.eid
                     WHERE l.id = ? AND e.department_id = ? AND l.status = 'Pending'";
    
    $stmt = $con->prepare($verify_query);
    $stmt->bind_param("ii", $leave_id, $hod_department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if (!$leave) {
        echo "Leave request not found or you don't have permission to approve it";
        exit();
    }
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Update leave status
        $new_status = ($action === 'Approved') ? 'Approved' : 'Rejected';
        $update_query = "UPDATE leaves SET 
                        status = ?,
                        hod_remarks = ?,
                        hod_action_date = NOW(),
                        hod_by = ?
                        WHERE id = ?";
        
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("sssi", $new_status, $remarks, $hod_id, $leave_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating leave status: " . $stmt->error);
        }
        
        // Add to leave history
        $history_query = "INSERT INTO leave_history (leave_id, action, actor_id, remarks) 
                         VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($history_query);
        $action_text = ($action === 'Approved') ? 'Approved' : 'Rejected';
        $stmt->bind_param("isss", $leave_id, $action_text, $hod_id, $remarks);
        
        if (!$stmt->execute()) {
            throw new Exception("Error adding to history: " . $stmt->error);
        }
        
        // Send email notification to employee
        $employee_email = $leave['email'];
        $employee_name = $leave['full_name'];
        $hod_name = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'HOD';
        
        if ($employee_email) {
            $subject = "Leave Request " . $action_text . " - " . $employee_name;
            $message = "Dear " . $employee_name . ",<br><br>
                       Your leave request has been <strong>" . strtolower($action_text) . "</strong> by your HOD.<br><br>
                       <strong>Leave Details:</strong><br>
                       Type: " . $leave['type_of_leave'] . "<br>
                       From: " . date('d M Y', strtotime($leave['start_date'])) . "<br>
                       To: " . date('d M Y', strtotime($leave['end_date'])) . "<br>
                       Days: " . $leave['total_days'] . "<br>
                       Reason: " . $leave['reason'] . "<br><br>
                       <strong>HOD Remarks:</strong><br>
                       " . ($remarks ?: 'No remarks provided') . "<br><br>
                       Best regards,<br>
                       HR System";
            
            // Include email sending function
            if (function_exists('sendEmail')) {
                sendEmail($employee_email, $subject, $message);
            }
        }
        
        // Commit transaction
        $con->commit();
        
        echo "success";
        
    } catch (Exception $e) {
        // Rollback transaction
        $con->rollback();
        echo "Error: " . $e->getMessage();
    }
    
} else {
    echo "Invalid request method";
}
?> 