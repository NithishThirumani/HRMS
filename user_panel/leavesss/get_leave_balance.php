<?php
include('session.php');
include('connection.php');

if (!isset($_SESSION['user_id']) || !isset($_POST['leave_type'])) {
    echo '0';
    exit();
}

$emp_id = $_SESSION['user_id'];
$leave_type = $_POST['leave_type'];

// Get current balance for the specific leave type
$balance_query = "SELECT COALESCE(elb.balance, lp.max_days) as current_balance
    FROM leave_policies lp
    LEFT JOIN employee_leave_balance elb ON 
        elb.emp_id = ? AND 
        elb.leave_type = ? AND 
        elb.year = YEAR(CURRENT_DATE)
    WHERE lp.leave_type = ?";

$stmt = $con->prepare($balance_query);
$stmt->bind_param("iss", $emp_id, $leave_type, $leave_type);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Return the balance, or 0 if no balance found
echo $row['current_balance'] ?? '0';
?>