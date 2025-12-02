<?php
include('connection.php');
include('approval_functions.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_leave'])) {
    $emp_id = $_SESSION['eid'];
    $reason = $_POST['reason'];
    $leave_type = $_POST['type_of_leave'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_days = $_POST['total_days'];
    
    // Calculate days in current month
    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $current_month_days = 0;
    
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($start, $interval, $end);
    
    foreach($daterange as $date) {
        if ($date->format('Y-m-d') >= $current_month_start && 
            $date->format('Y-m-d') <= $current_month_end) {
            $current_month_days++;
        }
    }

    // Check leave policy
    $policy_sql = "SELECT * FROM leave_policies WHERE leave_type = ?";
    $stmt = mysqli_prepare($con, $policy_sql);
    mysqli_stmt_bind_param($stmt, "s", $leave_type);
    mysqli_stmt_execute($stmt);
    $policy_result = mysqli_stmt_get_result($stmt);
    $policy = mysqli_fetch_assoc($policy_result);

    if (!$policy) {
        echo "<script>alert('Invalid leave type selected.'); window.history.back();</script>";
        exit();
    }

    // Check if employee meets minimum service requirement
    $emp_sql = "SELECT DATEDIFF(CURRENT_DATE, created_at) as service_days 
                FROM employees WHERE eid = ?";
    $stmt = mysqli_prepare($con, $emp_sql);
    mysqli_stmt_bind_param($stmt, "s", $emp_id);
    mysqli_stmt_execute($stmt);
    $emp_result = mysqli_stmt_get_result($stmt);
    $emp_data = mysqli_fetch_assoc($emp_result);

    if ($emp_data['service_days'] < ($policy['min_service_months'] * 30)) {
        echo "<script>alert('You do not meet the minimum service requirement for this leave type.'); window.history.back();</script>";
        exit();
    }

    // Check if maximum days limit is exceeded
    $year_leaves_sql = "SELECT SUM(total_days) as used_days 
                        FROM leaves 
                        WHERE emp_id = ? 
                        AND type_of_leave = ? 
                        AND YEAR(start_date) = YEAR(CURRENT_DATE)
                        AND status != 'rejected'";
    $stmt = mysqli_prepare($con, $year_leaves_sql);
    mysqli_stmt_bind_param($stmt, "ss", $emp_id, $leave_type);
    mysqli_stmt_execute($stmt);
    $year_result = mysqli_stmt_get_result($stmt);
    $year_data = mysqli_fetch_assoc($year_result);

    if (($year_data['used_days'] + $total_days) > $policy['max_days']) {
        echo "<script>alert('This leave request exceeds your annual limit for this leave type.'); window.history.back();</script>";
        exit();
    }

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Insert leave request
        $sql = "INSERT INTO leaves (
            emp_id, reason, type_of_leave, 
            start_date, end_date, 
            current_month_ldays, total_days,
            applied_at, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            "sssssii",
            $emp_id,
            $reason,
            $leave_type,
            $start_date,
            $end_date,
            $current_month_days,
            $total_days
        );

        if (mysqli_stmt_execute($stmt)) {
            $leave_id = mysqli_insert_id($con);
            
            // Create approval workflow
            $workflow_id = createApprovalWorkflow($emp_id, 'leave', $leave_id, $con);
            
            if ($workflow_id) {
                mysqli_commit($con);
                echo "<script>
                    alert('Leave request submitted successfully and sent for approval.');
                    window.location.href='view_leaves.php';
                </script>";
                exit();
            } else {
                throw new Exception("Failed to create approval workflow");
            }
        } else {
            throw new Exception("Failed to submit leave request");
        }
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Leave request error: " . $e->getMessage());
        echo "<script>
            alert('Error submitting leave request. Please try again.');
            window.history.back();
        </script>";
        exit();
    }
}
?>

<!-- Leave Request Form -->
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Submit Leave Request</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="leave_request.php">
                <div class="form-group">
                    <label>Leave Type</label>
                    <select name="type_of_leave" class="form-control" required>
                        <?php
                        $types_sql = "SELECT leave_type, max_days FROM leave_policies";
                        $types_result = mysqli_query($con, $types_sql);
                        while ($type = mysqli_fetch_assoc($types_result)) {
                            echo "<option value='" . $type['leave_type'] . "'>" . 
                                 $type['leave_type'] . " (Max: " . $type['max_days'] . " days)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Total Days</label>
                    <input type="number" name="total_days" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <textarea name="reason" class="form-control" required></textarea>
                </div>
                <button type="submit" name="submit_leave" class="btn btn-primary">Submit Request</button>
            </form>
        </div>
    </div>
</div>

<script>
// Calculate total days when dates change
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const totalDays = document.querySelector('input[name="total_days"]');

    function calculateDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            totalDays.value = diffDays;
        }
    }

    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);
});
</script> 