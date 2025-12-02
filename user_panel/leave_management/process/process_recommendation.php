<?php
include('../../session.php');
include('../../connection.php');
include('../includes/leave_functions.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header('Location: ../pending_leaves.php');
    exit;
}

$leave_id = $_POST['leave_id'] ?? null;
$action = $_POST['action'] ?? null;
$remarks = $_POST['remarks'] ?? '';

if (!$leave_id || !$action) {
    $_SESSION['error'] = "Missing required parameters";
    header('Location: ../pending_leaves.php');
    exit;
}

// Get recommender's numeric ID
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$result = $stmt->get_result();
$recommender = $result->fetch_assoc();
$recommender_id = $recommender['id'];

// Verify the recommender has authority
$stmt = $con->prepare("
    SELECT l.*, e.id as emp_numeric_id 
    FROM leaves l 
    JOIN employees e ON l.emp_id = e.eid 
    JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
    WHERE l.id = ? AND lh.recommender_id = ? AND l.status = 'Pending'
");
$stmt->bind_param("ii", $leave_id, $recommender_id);
$stmt->execute();
$result = $stmt->get_result();
$leave = $result->fetch_assoc();

if (!$leave) {
    $_SESSION['error'] = "You don't have permission to recommend this leave";
    header('Location: ../pending_leaves.php');
    exit;
}

// Start transaction
$con->begin_transaction();

try {
    if ($action === 'recommend') {
        // Update leave status
        $stmt = $con->prepare("
            UPDATE leaves 
            SET status = 'Recommended',
                recommender_id = ?,
                recommender_remarks = ?,
                recommender_action_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("isi", $recommender_id, $remarks, $leave_id);
        $stmt->execute();

        // Get next approver from workflow
        $next_approver = getNextApprover($leave['emp_numeric_id'], $leave['department_id']);
        
        if ($next_approver) {
            // Update workflow
            $stmt = $con->prepare("
                UPDATE leave_workflow 
                SET current_approver_role = ?,
                    next_approver_role = ?,
                    status = 'Recommended',
                    updated_at = NOW()
                WHERE leave_id = ?
            ");
            $stmt->bind_param("ssi", $next_approver['role'], $next_approver['next_role'], $leave_id);
            $stmt->execute();
        }

        $_SESSION['success'] = "Leave has been recommended successfully";
    } else if ($action === 'reject') {
        if (empty($remarks)) {
            throw new Exception("Rejection reason is required");
        }

        // Update leave status
        $stmt = $con->prepare("
            UPDATE leaves 
            SET status = 'Rejected',
                recommender_id = ?,
                recommender_remarks = ?,
                recommender_action_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("isi", $recommender_id, $remarks, $leave_id);
        $stmt->execute();

        // Update workflow
        $stmt = $con->prepare("
            UPDATE leave_workflow 
            SET status = 'Rejected',
                updated_at = NOW()
            WHERE leave_id = ?
        ");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();

        $_SESSION['success'] = "Leave has been rejected";
    }

    $con->commit();
} catch (Exception $e) {
    $con->rollback();
    $_SESSION['error'] = "Error processing leave: " . $e->getMessage();
}

header('Location: ../pending_leaves.php');
exit;