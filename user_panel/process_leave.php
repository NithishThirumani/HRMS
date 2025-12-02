<?php
include('session.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $_SESSION['user_id'];
    $leave_type = $_POST['type_of_leave'];
    $start_date = $_POST['sd'];
    $end_date = $_POST['ed'];
    $reason = $_POST['reason'];
    
    // Calculate total days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;
    
    // Check leave balance
    $balance_query = "SELECT elb.balance, lp.requires_certificate, lp.max_days 
                     FROM employee_leave_balance elb
                     JOIN leave_policies lp ON elb.leave_type = lp.leave_type
                     WHERE elb.emp_id = '$emp_id' 
                     AND elb.leave_type = '$leave_type'
                     AND elb.year = YEAR(CURRENT_DATE)";
    
    $balance_result = mysqli_query($con, $balance_query);
    $balance_info = mysqli_fetch_assoc($balance_result);
    
    // Validate request
    $errors = [];
    
    if ($total_days > $balance_info['balance']) {
        $errors[] = "Insufficient leave balance. Available: " . $balance_info['balance'] . " days";
    }
    
    if ($balance_info['requires_certificate'] && !isset($_FILES['doctor_cert'])) {
        $errors[] = "Medical certificate is required for this type of leave";
    }
    
    if (empty($errors)) {
        // Upload certificate if provided
        $certificate_path = null;
        if (isset($_FILES['doctor_cert'])) {
            $certificate_path = uploadCertificate($_FILES['doctor_cert'], $emp_id);
        }
        
        // Insert leave request
        $status = "Pending Head Approval";
        $insert_query = "INSERT INTO leaves (
            emp_id, type_of_leave, start_date, end_date, 
            total_days, reason, status, doctor_cert
        ) VALUES (
            '$emp_id', '$leave_type', '$start_date', '$end_date', 
            $total_days, '$reason', '$status', '$certificate_path'
        )";
        
        if (mysqli_query($con, $insert_query)) {
            // Deduct from balance only after approval
            echo "<script>
                alert('Leave request submitted successfully');
                window.location.href='leaves.php';
            </script>";
        } else {
            echo "<script>
                alert('Error submitting leave request');
                window.location.href='request_leave.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Errors: " . implode("\\n", $errors) . "');
            window.location.href='request_leave.php';
        </script>";
    }
}

function uploadCertificate($file, $emp_id) {
    $target_dir = "../uploads/certificates/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = $emp_id . "_" . date('Ymd_His') . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $new_filename;
    }
    return null;
}
?>

$message = "";

if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $leave_id = $_POST['leave_id'];
    $role = $_POST['role'];
    $status = isset($_POST['approve']) ? 'Approved' : 'Rejected';

    if ($role == 'head') {
        $query = "UPDATE leaves SET head_approval = '$status', status = '" . ($status == 'Approved' ? 'Pending HR Approval' : 'Rejected') . "' WHERE id = '$leave_id'";
        $message = $status == 'Approved' ? "Leave approved by Department Head and sent to HR." : "Leave rejected by Department Head.";
    } elseif ($role == 'hr') {
        $query = "UPDATE leaves SET hr_approval = '$status', status = '" . ($status == 'Approved' ? 'Final Approved' : 'Rejected by HR') . "' WHERE id = '$leave_id'";
        $message = $status == 'Approved' ? "Leave final approved by HR." : "Leave rejected by HR.";
    }

    if (mysqli_query($con, $query)) {
        $success = true;
    } else {
        $success = false;
        $message = "Error updating record: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Leave</title>
    <link rel="stylesheet" href="styles.css"> <!-- Ensure this CSS file exists -->
    <script>
        function redirectToApproval() {
            window.location.href = "leave_approval.php";
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Leave Processing</h2>
        
        <?php if (isset($success)) { ?>
            <div class="<?php echo $success ? 'success-msg' : 'error-msg'; ?>">
                <p><?php echo $message; ?></p>
            </div>
            <button onclick="redirectToApproval()">Back to Leave Approval</button>
        <?php } else { ?>
            <p class="error-msg">Invalid Request</p>
            <button onclick="redirectToApproval()">Back to Leave Approval</button>
        <?php } ?>
    </div>
</body>
</html>
