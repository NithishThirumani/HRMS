<?php
include('../../session.php');
include('../../connection.php');
include('../includes/leave_functions.php');
include('../../../includes/email_functions.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$eid = $_SESSION['eid'];

// Get the numeric ID of the current HOD
$hod_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $hod_numeric_id = $result->fetch_assoc()['id'];
}

if (!$hod_numeric_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'HOD not found']);
    exit();
}

try {
    // Log the incoming request for debugging
    error_log("HOD Recommendation - Request received: " . json_encode($_POST));
    
    // Validate input
    if (!isset($_POST['leave_id']) || !isset($_POST['action'])) {
        throw new Exception("Missing required parameters");
    }

    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';

    // Validate action
    if (!in_array($action, ['recommend', 'reject'])) {
        throw new Exception("Invalid action: " . $action);
    }

    error_log("HOD Recommendation - Processing leave ID: " . $leave_id . ", Action: " . $action);

    // Get leave details
    $stmt = $con->prepare("
        SELECT l.*, e.full_name as employee_name, e.email as employee_email,
               d.name as department_name
        FROM leaves l
        LEFT JOIN employees e ON l.emp_id = e.eid
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE l.id = ? AND l.status = 'Pending'
    ");
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();

    if (!$leave) {
        throw new Exception("Leave not found or not in pending status");
    }

    error_log("HOD Recommendation - Found leave: " . json_encode($leave));

    // Check if HOD has recommendation rights for this leave
    $stmt = $con->prepare("
        SELECT 1 FROM leaves l
        LEFT JOIN employees e ON l.emp_id = e.eid
        WHERE l.id = ? AND (
            l.hod_id = ? 
            OR EXISTS (
                SELECT 1 FROM leave_hierarchy lh 
                WHERE lh.employee_id = e.id 
                AND lh.recommender_id = ? 
                AND lh.type = 'recommender'
            )
        )
    ");
    $stmt->bind_param("iii", $leave_id, $hod_numeric_id, $hod_numeric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("You don't have recommendation rights for this leave");
    }

    error_log("HOD Recommendation - HOD has rights, processing action: " . $action);

    // Process the action
    if ($action === 'recommend') {
        // Recommend the leave
        $status = 'Recommended';
        
        $stmt = $con->prepare("
            UPDATE leaves 
            SET status = ?,
                recommender_id = ?,
                recommender_remarks = ?,
                recommender_action_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sisi", $status, $hod_numeric_id, $remarks, $leave_id);
        
        $result = $stmt->execute();
        
        if ($result) {
            error_log("HOD Recommendation - Successfully recommended leave ID: " . $leave_id);
            
            // Send recommendation notification
            $leave_data = [
                'employee_name' => $leave['employee_name'],
                'leave_type' => $leave['type_of_leave'],
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'total_days' => $leave['total_days'],
                'reason' => $leave['reason'],
                'recommender_remarks' => $remarks
            ];
            
            // Get HOD name
            $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
            $stmt->bind_param("i", $hod_numeric_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $hod_info = $result->fetch_assoc();
            $hod_name = $hod_info['full_name'];
            
            // Send email notifications
            try {
                sendLeaveRecommendationEmail($leave_data, $hod_name, 'Recommended', $leave['employee_email'], null);
                error_log("HOD Recommendation - Email sent successfully");
            } catch (Exception $email_error) {
                error_log("HOD Recommendation - Email error: " . $email_error->getMessage());
                // Continue even if email fails - don't break the main functionality
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Leave recommended successfully']);
        } else {
            throw new Exception("Failed to recommend leave: " . $stmt->error);
        }
        
    } else if ($action === 'reject') {
        // Reject the leave
        if (empty($remarks)) {
            throw new Exception("Rejection reason is required");
        }
        
        $status = 'Rejected';
        
        $stmt = $con->prepare("
            UPDATE leaves 
            SET status = ?,
                recommender_id = ?,
                recommender_remarks = ?,
                recommender_action_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sisi", $status, $hod_numeric_id, $remarks, $leave_id);
        
        $result = $stmt->execute();
        
        if ($result) {
            error_log("HOD Recommendation - Successfully rejected leave ID: " . $leave_id);
            
            // Send rejection notification
            $leave_data = [
                'employee_name' => $leave['employee_name'],
                'leave_type' => $leave['type_of_leave'],
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'total_days' => $leave['total_days'],
                'reason' => $leave['reason'],
                'recommender_remarks' => $remarks
            ];
            
            // Get HOD name
            $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
            $stmt->bind_param("i", $hod_numeric_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $hod_info = $result->fetch_assoc();
            $hod_name = $hod_info['full_name'];
            
            // Send email notifications
            try {
                sendLeaveRecommendationEmail($leave_data, $hod_name, 'Not Recommended', $leave['employee_email'], null);
                error_log("HOD Recommendation - Rejection email sent successfully");
            } catch (Exception $email_error) {
                error_log("HOD Recommendation - Email error: " . $email_error->getMessage());
                // Continue even if email fails - don't break the main functionality
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Leave not recommended']);
        } else {
            throw new Exception("Failed to reject leave: " . $stmt->error);
        }
    }
    
} catch (Exception $e) {
    error_log("HOD Recommendation - Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>