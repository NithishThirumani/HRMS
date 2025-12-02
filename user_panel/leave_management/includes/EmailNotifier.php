<?php
class EmailNotifier {
    private $con;
    
    public function __construct($con) {
        $this->con = $con;
    }
    
    public function sendLeaveNotificationEmail($leave_id, $recipient_role) {
        // Get leave details
        $query = "SELECT l.*, e.full_name, e.email, d.name as department 
                 FROM leaves l
                 JOIN employees e ON l.emp_id = e.eid
                 JOIN departments d ON e.department_id = d.id
                 WHERE l.id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $leave = $stmt->get_result()->fetch_assoc();
        
        // Get recipients (all users with the specified role)
        $query = "SELECT email, full_name FROM employees 
                 WHERE new_role_id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $recipient_role);
        $stmt->execute();
        $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($recipients as $recipient) {
            $subject = "New Leave Request Pending - #" . $leave_id;
            
            $message = "Dear {$recipient['full_name']},\n\n";
            $message .= "A new leave request requires your attention:\n\n";
            $message .= "Employee: {$leave['full_name']}\n";
            $message .= "Department: {$leave['department']}\n";
            $message .= "Leave Type: {$leave['type_of_leave']}\n";
            $message .= "Duration: " . date('M d, Y', strtotime($leave['start_date'])) . 
                       " to " . date('M d, Y', strtotime($leave['end_date'])) . "\n";
            $message .= "Total Days: {$leave['total_days']}\n\n";
            $message .= "Please login to the system to review this request.\n";
            $message .= "URL: http://yourdomain.com/leave_management/view_leave.php?id=" . $leave_id;
            
            mail($recipient['email'], $subject, $message);
        }
    }
}