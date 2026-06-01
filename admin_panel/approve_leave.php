<?php
include('session.php');
require_once dirname(__DIR__) . '/includes/hrms_employees.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

$leave_id = (int) ($_POST['leave_id'] ?? 0);
$action = strtolower(trim($_POST['action'] ?? ''));
$remarks = $_POST['remarks'] ?? '';
$user_role = strtolower($_SESSION['role'] ?? 'admin');

if ($leave_id <= 0 || !in_array($action, ['approved', 'rejected'], true)) {
    echo 'error: Invalid request';
    exit();
}

$join = hrms_leave_employee_join('l', 'e');
$status_query = "SELECT l.*, e.email, e.full_name, e.department_id, e.eid AS employee_eid, d.name AS department
                 FROM leaves l
                 JOIN employees e ON {$join}
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE l.id = ?";

mysqli_begin_transaction($con);
try {
    $stmt = $con->prepare($status_query);
    $stmt->bind_param('i', $leave_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();

    if (!$leave) {
        throw new Exception('Leave request not found');
    }

    $username = $_SESSION['username'] ?? $_SESSION['email'] ?? 'admin';

    if ($user_role === 'hod') {
        $sessionDept = $_SESSION['department_id'] ?? $_SESSION['department'] ?? null;
        if ($sessionDept !== null && (string) $leave['department_id'] !== (string) $sessionDept) {
            throw new Exception('You can only approve leaves from your department');
        }

        $stmt = $con->prepare("UPDATE leaves SET hod_status = ?, hod_remarks = ?, hod_action_date = NOW(), hod_by = ? WHERE id = ?");
        $stmt->bind_param('sssi', $action, $remarks, $username, $leave_id);
        $stmt->execute();

        if ($action === 'rejected') {
            $stmt = $con->prepare("UPDATE leaves SET hr_status = 'rejected', status = 'Rejected' WHERE id = ?");
            $stmt->bind_param('i', $leave_id);
            $stmt->execute();
        }
    } elseif ($user_role === 'hr') {
        if ($leave['hod_status'] === 'pending' && $action === 'approved') {
            $stmt = $con->prepare("UPDATE leaves SET hod_status = 'approved', hod_remarks = 'Auto-approved by HR bypass', hod_action_date = NOW(), hod_by = ? WHERE id = ?");
            $stmt->bind_param('si', $username, $leave_id);
            $stmt->execute();
        }

        $stmt = $con->prepare("UPDATE leaves SET hr_status = ?, hr_remarks = ?, hr_action_date = NOW(), hr_by = ? WHERE id = ?");
        $stmt->bind_param('sssi', $action, $remarks, $username, $leave_id);
        $stmt->execute();

        if ($action === 'rejected') {
            $stmt = $con->prepare("UPDATE leaves SET status = 'Rejected' WHERE id = ?");
            $stmt->bind_param('i', $leave_id);
            $stmt->execute();
        }
    } else {
        if ($leave['hod_status'] === 'pending' && $action === 'approved') {
            $stmt = $con->prepare("UPDATE leaves SET hod_status = 'approved', hod_remarks = 'Auto-approved by Admin', hod_action_date = NOW(), hod_by = ? WHERE id = ?");
            $stmt->bind_param('si', $username, $leave_id);
            $stmt->execute();
        }

        if ($leave['hr_status'] === 'pending' && $action === 'approved') {
            $stmt = $con->prepare("UPDATE leaves SET hr_status = 'approved', hr_remarks = 'Auto-approved by Admin', hr_action_date = NOW(), hr_by = ? WHERE id = ?");
            $stmt->bind_param('si', $username, $leave_id);
            $stmt->execute();
        }

        if ($action === 'rejected') {
            $stmt = $con->prepare("UPDATE leaves SET hod_status = 'rejected', hr_status = 'rejected', status = 'Rejected' WHERE id = ?");
            $stmt->bind_param('i', $leave_id);
            $stmt->execute();
        }
    }

    if ($action === 'approved') {
        $stmt = $con->prepare($status_query);
        $stmt->bind_param('i', $leave_id);
        $stmt->execute();
        $updated = $stmt->get_result()->fetch_assoc();

        if ($updated && $updated['hod_status'] === 'approved' && $updated['hr_status'] === 'approved') {
            $stmt = $con->prepare("UPDATE leaves SET status = 'Approved' WHERE id = ?");
            $stmt->bind_param('i', $leave_id);
            $stmt->execute();

            $balanceEmpId = hrms_leave_balance_emp_id($updated);
            $balanceSql = 'UPDATE employee_leave_balance SET balance = balance - ?
                           WHERE emp_id = ? AND leave_type = ? AND year = YEAR(CURRENT_DATE)';
            $stmt = $con->prepare($balanceSql);
            if ($stmt && $balanceEmpId !== '') {
                $totalDays = (int) $updated['total_days'];
                $leaveType = (string) $updated['type_of_leave'];
                $stmt->bind_param('iss', $totalDays, $balanceEmpId, $leaveType);
                $stmt->execute();
            }
        }
    } elseif ($action === 'rejected') {
        $stmt = $con->prepare("UPDATE leaves SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param('i', $leave_id);
        $stmt->execute();
    }

    if (file_exists(__DIR__ . '/../includes/email_functions.php')) {
        include_once __DIR__ . '/../includes/email_functions.php';
        if (function_exists('sendLeaveNotification') && !empty($leave['email'])) {
            $subject = 'Leave Request Update';
            $message = "Dear {$leave['full_name']},<br><br>Your leave request has been " . strtoupper($action) . " by " . ucfirst($user_role) . ".<br>Remarks: {$remarks}";
            @sendLeaveNotification($leave['email'], $subject, $message);
        }
    }

    mysqli_commit($con);
    echo 'success';
} catch (Exception $e) {
    mysqli_rollback($con);
    echo 'error: ' . $e->getMessage();
}
