<?php
include('connection.php');

$email = $_REQUEST['em'];
$token = $_REQUEST['token'];

// Verify the token and email
$sql = "SELECT e.*, d.name as department_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.email = ? AND e.token = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ss", $email, $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    if ($row['status'] == "active") {
            echo "<script>alert('Account is already activated.');</script>";
        } else {
        // Start approval workflow
        $department_id = $row['department_id'];
        $role = $row['role'];
        $emp_id = $row['eid'];

        // Create approval workflow record
        $workflow_sql = "INSERT INTO approval_workflow 
                        (employee_id, current_approver, status, created_at) 
                        VALUES (?, ?, 'pending', NOW())";
        $workflow_stmt = mysqli_prepare($con, $workflow_sql);
        
        // Get the first approver based on hierarchy
        $approver_sql = "SELECT eid FROM employees 
                        WHERE role = 'HOD' AND department_id = ? 
                        LIMIT 1";
        $approver_stmt = mysqli_prepare($con, $approver_sql);
        mysqli_stmt_bind_param($approver_stmt, "i", $department_id);
        mysqli_stmt_execute($approver_stmt);
        $approver_result = mysqli_stmt_get_result($approver_stmt);
        $approver = mysqli_fetch_assoc($approver_result);
        
        $first_approver = $approver ? $approver['eid'] : null;
        
        if (!$first_approver) {
            // If no HOD, get HR as approver
            $hr_sql = "SELECT eid FROM employees WHERE role = 'HR' LIMIT 1";
            $hr_result = mysqli_query($con, $hr_sql);
            $hr = mysqli_fetch_assoc($hr_result);
            $first_approver = $hr ? $hr['eid'] : null;
        }

        mysqli_stmt_bind_param($workflow_stmt, "ss", $emp_id, $first_approver);
        
        if (mysqli_stmt_execute($workflow_stmt)) {
            // Update employee status to pending_approval
            $update_sql = "UPDATE employees 
                          SET status = 'pending_approval', 
                              token = NULL,
                              email_verified = 1
                          WHERE eid = ?";
            $update_stmt = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "s", $emp_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                // Send email to first approver
                require 'PHPMailer/PHPMailerAutoload.php';
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = 'your-smtp-host';
                $mail->SMTPAuth = true;
                $mail->Username = 'your-email';
                $mail->Password = 'your-password';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('your-email', 'Your Company');
                
                // Get approver's email
                $approver_email_sql = "SELECT email FROM employees WHERE eid = ?";
                $approver_email_stmt = mysqli_prepare($con, $approver_email_sql);
                mysqli_stmt_bind_param($approver_email_stmt, "s", $first_approver);
                mysqli_stmt_execute($approver_email_stmt);
                $approver_email_result = mysqli_stmt_get_result($approver_email_stmt);
                $approver_email_row = mysqli_fetch_assoc($approver_email_result);
                
                if ($approver_email_row) {
                    $mail->addAddress($approver_email_row['email']);
                    $mail->isHTML(true);
                    
                    $mail->Subject = 'New Employee Account Approval Required';
                    $mail->Body = "A new employee account requires your approval:<br><br>
                                  Name: {$row['full_name']}<br>
                                  Email: {$row['email']}<br>
                                  Department: {$row['department_name']}<br><br>
                                  Please login to the system to approve or reject this account.";
                    
                    $mail->send();
                }
                
                echo "<script>alert('Email verified successfully! Your account is pending approval from management.');</script>";
            } else {
                echo "<script>alert('Error updating account status. Please contact support.');</script>";
            }
        } else {
            echo "<script>alert('Error creating approval workflow. Please contact support.');</script>";
        }
    }
} else {
    echo "<script>alert('Invalid verification link or email address.');</script>";
}
?>
<script>window.location.href = "login.php";</script>
