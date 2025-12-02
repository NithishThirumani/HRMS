<?php
include('session.php');
include('connection.php');

// Check if user is logged in
$user_name = $_SESSION['username'] ?? null; // Ensure it matches session key

// Check if username is set
if (!$user_name) {
    echo "No username found in session.";
    exit;
}

// Get employee ID from employees table
$emp_query = "SELECT eid FROM employees WHERE user_name = ?";
$stmt = $con->prepare($emp_query);
$stmt->bind_param('s', $user_name);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    echo json_encode(['error' => 'Employee not found']);
    exit();
}

$emp_id = $employee['eid'];

// Fetch leave balance, availed, and authorized leaves
$leave_query = "
SELECT 
    COALESCE(SUM(lb.balance_leaves), 0) AS leave_balance,
    COALESCE(SUM(l.total_days), 0) AS leaves_availed,
    COALESCE(SUM(CASE WHEN l.status = 'Authorized' THEN l.total_days ELSE 0 END), 0) AS leaves_authorized
FROM leave_balance lb
LEFT JOIN leaves l ON lb.emp_id = l.emp_id AND lb.type_of_leave = l.type_of_leave
WHERE lb.emp_id = ?
GROUP BY lb.emp_id
";

$stmt = $con->prepare($leave_query);
$stmt->bind_param('s', $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$leave_data = $result->fetch_assoc() ?? [
    'leave_balance' => 0,
    'leaves_availed' => 0,
    'leaves_authorized' => 0
];

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($leave_data);

$stmt->close();
$con->close();
?>
