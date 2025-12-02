<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../session.php');
require_once '../../../connection.php';
require_once '../../../classes/EmployeeAppraisal.php';
require_once '../../../classes/AppraisalPeriod.php';

// Check if user is logged in and is HOD (session.php already handles this)
// The session.php file will redirect to login if not properly authenticated

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $departments = $_POST['departments'];
    
    $appraisalPeriod = new AppraisalPeriod();
    
    try {
        // Create new appraisal period
        $period_id = $appraisalPeriod->createPeriod($start_date, $end_date, $_SESSION['user_id']);
        
        if ($period_id) {
            // Send notifications to department employees
            foreach ($departments as $dept_id) {
                $query = "SELECT email FROM employees WHERE department_id = ? AND status = 1";
                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $dept_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    // Send email notification
                    $to = $row['email'];
                    $subject = "New Performance Appraisal Cycle Initiated";
                    $message = "A new appraisal cycle has been initiated from $start_date to $end_date.";
                    mail($to, $subject, $message);
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Appraisal cycle initiated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create appraisal period']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>