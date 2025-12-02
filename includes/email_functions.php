<?php
require_once dirname(__FILE__) . '/../PHPMailer/Exception.php';
require_once dirname(__FILE__) . '/../PHPMailer/SMTP.php';
require_once dirname(__FILE__) . '/../PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Get email configuration from database
 */
function getEmailConfig($con) {
    $query = "SELECT * FROM email_config WHERE id = 1";
    $result = $con->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get email notification settings from database
 */
function getEmailNotificationSettings($con) {
    $query = "SELECT * FROM email_notification_settings WHERE id = 1";
    $result = $con->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Log email sending attempt
 */
function logEmailAttempt($con, $recipient_email, $recipient_type, $email_type, $subject, $status, $error_message = null, $leave_id = null) {
    $query = "INSERT INTO email_logs (recipient_email, recipient_type, email_type, subject, status, error_message, leave_id, sent_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssssi", $recipient_email, $recipient_type, $email_type, $subject, $status, $error_message, $leave_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Send email notification for leave application
 */
function sendLeaveApplicationEmail($con, $leave_data, $applicant_email, $recommender_email, $approver_email) {
    $config = getEmailConfig($con);
    $settings = getEmailNotificationSettings($con);
    
    // If email config is not available, skip email sending but don't fail the leave application
    if (!$config) {
        error_log("Email configuration not found - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    // If email settings are not available or disabled, skip email sending
    if (!$settings || !isset($settings['leave_application_enabled']) || !$settings['leave_application_enabled']) {
        error_log("Email notifications are disabled - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Check if all required config keys exist
        $required_keys = ['host', 'username', 'password', 'port', 'from_email', 'from_name'];
        foreach ($required_keys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                error_log("Missing email config key: $key - skipping email notification");
                return true; // Return true to not block the leave application
            }
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if (isset($config['secure']) && $config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        
        // Email content
        $subject = "Leave Application - " . $leave_data['employee_name'] . " (" . $leave_data['leave_type'] . ")";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .details { background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
                .action-btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Leave Application Notification</h2>
            </div>
            <div class='content'>
                <p>A new leave application has been submitted.</p>
                
                <div class='details'>
                    <h3>Leave Details:</h3>
                    <p><strong>Employee:</strong> " . htmlspecialchars($leave_data['employee_name']) . "</p>
                    <p><strong>Leave Type:</strong> " . htmlspecialchars($leave_data['leave_type']) . "</p>
                    <p><strong>Start Date:</strong> " . htmlspecialchars($leave_data['start_date']) . "</p>
                    <p><strong>End Date:</strong> " . htmlspecialchars($leave_data['end_date']) . "</p>
                    <p><strong>Total Days:</strong> " . htmlspecialchars($leave_data['total_days']) . "</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($leave_data['reason']) . "</p>
                    <p><strong>Application Date:</strong> " . htmlspecialchars($leave_data['applied_at']) . "</p>
                </div>
                
                <p>Please review and take appropriate action.</p>
            </div>
            <div class='footer'>
                <p>This is an automated notification from HRMS System</p>
            </div>
        </body>
        </html>";
        
        $success_count = 0;
        
        // Send to recommender
        if (isset($settings['notify_recommender']) && $settings['notify_recommender'] && !empty($recommender_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($recommender_email);
            $mail->Subject = $subject . " - Recommender Notification";
            $mail->isHTML(true);
            $mail->Body = $body . "<p><strong>Action Required:</strong> Please review and recommend this leave application.</p>";
            $mail->send();
                logEmailAttempt($con, $recommender_email, 'recommender', 'leave_application', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
                error_log("Email sent to recommender: " . $recommender_email);
            } catch (Exception $e) {
                logEmailAttempt($con, $recommender_email, 'recommender', 'leave_application', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
                error_log("Failed to send email to recommender: " . $e->getMessage());
            }
        }
        
        // Send to approver
        if (isset($settings['notify_approver']) && $settings['notify_approver'] && !empty($approver_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($approver_email);
            $mail->Subject = $subject . " - Approver Notification";
            $mail->isHTML(true);
            $mail->Body = $body . "<p><strong>Action Required:</strong> Please review and approve/reject this leave application.</p>";
            $mail->send();
                logEmailAttempt($con, $approver_email, 'approver', 'leave_application', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
                error_log("Email sent to approver: " . $approver_email);
            } catch (Exception $e) {
                logEmailAttempt($con, $approver_email, 'approver', 'leave_application', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
                error_log("Failed to send email to approver: " . $e->getMessage());
            }
        }
        
        // Send confirmation to applicant
        if (isset($settings['notify_applicant']) && $settings['notify_applicant'] && !empty($applicant_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($applicant_email);
                $mail->Subject = $subject . " - Confirmation";
            $mail->isHTML(true);
                $mail->Body = $body . "<p><strong>Status:</strong> Your leave application has been submitted successfully and is pending review.</p>";
            $mail->send();
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_application', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
                error_log("Email sent to applicant: " . $applicant_email);
            } catch (Exception $e) {
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_application', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
                error_log("Failed to send email to applicant: " . $e->getMessage());
            }
        }
        
        return $success_count > 0;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email notification for leave recommendation
 */
function sendLeaveRecommendationEmail($con, $leave_data, $recommender_name, $recommendation_status, $applicant_email, $approver_email) {
    $config = getEmailConfig($con);
    $settings = getEmailNotificationSettings($con);
    
    // If email config is not available, skip email sending but don't fail the leave application
    if (!$config) {
        error_log("Email configuration not found - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    // If email settings are not available or disabled, skip email sending
    if (!$settings || !isset($settings['leave_recommendation_enabled']) || !$settings['leave_recommendation_enabled']) {
        error_log("Email notifications are disabled - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Check if all required config keys exist
        $required_keys = ['host', 'username', 'password', 'port', 'from_email', 'from_name'];
        foreach ($required_keys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                error_log("Missing email config key: $key - skipping email notification");
                return true; // Return true to not block the leave application
            }
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if (isset($config['secure']) && $config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        
        $status_color = ($recommendation_status == 'Recommended') ? '#28a745' : '#dc3545';
        $status_text = ($recommendation_status == 'Recommended') ? 'Recommended' : 'Not Recommended';
        
        $subject = "Leave Recommendation - " . $leave_data['employee_name'] . " (" . $recommendation_status . ")";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: " . $status_color . "; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .details { background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Leave Recommendation Update</h2>
            </div>
            <div class='content'>
                <p>The leave application has been reviewed by the recommender.</p>
                
                <div class='details'>
                    <h3>Leave Details:</h3>
                    <p><strong>Employee:</strong> " . htmlspecialchars($leave_data['employee_name']) . "</p>
                    <p><strong>Leave Type:</strong> " . htmlspecialchars($leave_data['leave_type']) . "</p>
                    <p><strong>Start Date:</strong> " . htmlspecialchars($leave_data['start_date']) . "</p>
                    <p><strong>End Date:</strong> " . htmlspecialchars($leave_data['end_date']) . "</p>
                    <p><strong>Total Days:</strong> " . htmlspecialchars($leave_data['total_days']) . "</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($leave_data['reason']) . "</p>
                    <p><strong>Recommender:</strong> " . htmlspecialchars($recommender_name) . "</p>
                    <p><strong>Recommendation:</strong> <span style='color: " . $status_color . "; font-weight: bold;'>" . $status_text . "</span></p>
                    " . (!empty($leave_data['recommender_remarks']) ? "<p><strong>Remarks:</strong> " . htmlspecialchars($leave_data['recommender_remarks']) . "</p>" : "") . "
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated notification from HRMS System</p>
            </div>
        </body>
        </html>";
        
        $success_count = 0;
        
        // Send to approver
        if (isset($settings['notify_approver']) && $settings['notify_approver'] && !empty($approver_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($approver_email);
            $mail->Subject = $subject . " - Approver Notification";
            $mail->isHTML(true);
            $mail->Body = $body . "<p><strong>Action Required:</strong> Please review and make final decision on this leave application.</p>";
            $mail->send();
                logEmailAttempt($con, $approver_email, 'approver', 'leave_recommendation', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
            } catch (Exception $e) {
                logEmailAttempt($con, $approver_email, 'approver', 'leave_recommendation', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
            }
        }
        
        // Send to applicant
        if (isset($settings['notify_applicant']) && $settings['notify_applicant'] && !empty($applicant_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($applicant_email);
            $mail->Subject = "Leave Recommendation Update - " . $status_text;
            $mail->isHTML(true);
            $mail->Body = $body . "<p>Your leave application has been reviewed and " . strtolower($status_text) . " by your recommender. Final approval is pending.</p>";
            $mail->send();
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_recommendation', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
            } catch (Exception $e) {
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_recommendation', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
            }
        }
        
        return $success_count > 0;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email notification for leave approval
 */
function sendLeaveApprovalEmail($con, $leave_data, $approver_name, $approval_status, $applicant_email, $recommender_email) {
    $config = getEmailConfig($con);
    $settings = getEmailNotificationSettings($con);
    
    // If email config is not available, skip email sending but don't fail the leave application
    if (!$config) {
        error_log("Email configuration not found - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    // If email settings are not available or disabled, skip email sending
    if (!$settings || !isset($settings['leave_approval_enabled']) || !$settings['leave_approval_enabled']) {
        error_log("Email notifications are disabled - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Check if all required config keys exist
        $required_keys = ['host', 'username', 'password', 'port', 'from_email', 'from_name'];
        foreach ($required_keys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                error_log("Missing email config key: $key - skipping email notification");
                return true; // Return true to not block the leave application
            }
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if (isset($config['secure']) && $config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        
        $status_color = ($approval_status == 'Approved') ? '#28a745' : '#dc3545';
        $status_text = ($approval_status == 'Approved') ? 'Approved' : 'Rejected';
        
        $subject = "Leave Approval - " . $leave_data['employee_name'] . " (" . $approval_status . ")";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: " . $status_color . "; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .details { background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Leave Approval Decision</h2>
            </div>
            <div class='content'>
                <p>The leave application has been reviewed and a decision has been made.</p>
                
                <div class='details'>
                    <h3>Leave Details:</h3>
                    <p><strong>Employee:</strong> " . htmlspecialchars($leave_data['employee_name']) . "</p>
                    <p><strong>Leave Type:</strong> " . htmlspecialchars($leave_data['leave_type']) . "</p>
                    <p><strong>Start Date:</strong> " . htmlspecialchars($leave_data['start_date']) . "</p>
                    <p><strong>End Date:</strong> " . htmlspecialchars($leave_data['end_date']) . "</p>
                    <p><strong>Total Days:</strong> " . htmlspecialchars($leave_data['total_days']) . "</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($leave_data['reason']) . "</p>
                    <p><strong>Approver:</strong> " . htmlspecialchars($approver_name) . "</p>
                    <p><strong>Decision:</strong> <span style='color: " . $status_color . "; font-weight: bold;'>" . $status_text . "</span></p>
                    " . (!empty($leave_data['hr_remarks']) ? "<p><strong>Remarks:</strong> " . htmlspecialchars($leave_data['hr_remarks']) . "</p>" : "") . "
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated notification from HRMS System</p>
            </div>
        </body>
        </html>";
        
        $success_count = 0;
        
        // Send to applicant
        if (isset($settings['notify_applicant']) && $settings['notify_applicant'] && !empty($applicant_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($applicant_email);
                $mail->Subject = "Leave Application " . $status_text . " - " . $leave_data['employee_name'];
            $mail->isHTML(true);
                $mail->Body = $body . "<p>Your leave application has been " . strtolower($status_text) . ".</p>";
            $mail->send();
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_approval', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
            } catch (Exception $e) {
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_approval', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
            }
        }
        
        // Send to recommender
        if (isset($settings['notify_recommender']) && $settings['notify_recommender'] && !empty($recommender_email)) {
            try {
            $mail->clearAddresses();
            $mail->addAddress($recommender_email);
            $mail->Subject = $subject . " - Recommender Notification";
            $mail->isHTML(true);
            $mail->Body = $body . "<p>The leave application you recommended has been " . strtolower($approval_status) . ".</p>";
            $mail->send();
                logEmailAttempt($con, $recommender_email, 'recommender', 'leave_approval', $subject, 'sent', null, $leave_data['leave_id'] ?? null);
                $success_count++;
            } catch (Exception $e) {
                logEmailAttempt($con, $recommender_email, 'recommender', 'leave_approval', $subject, 'failed', $e->getMessage(), $leave_data['leave_id'] ?? null);
            }
        }
        
        return $success_count > 0;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get employee email by emp_id from employees table
 */
function getEmployeeEmail($con, $emp_id) {
    if (empty($emp_id)) {
        return null;
    }
    
    // First try to get email using numeric ID from employees table
    $query = "SELECT email FROM employees WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['email'];
    }
    
    // If not found, try using eid (string ID) from employees table
    $query = "SELECT email FROM employees WHERE eid = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['email'];
    }
    
    // If not found in employees table, try emp_login table
    $query = "SELECT email FROM emp_login WHERE emp_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['email'];
    }
    
    return null;
}

/**
 * Get employee email from emp_login table (fallback)
 */
function getEmployeeEmailFromLogin($con, $emp_id) {
    if (empty($emp_id)) {
        return null;
    }
    
    $query = "SELECT email FROM emp_login WHERE emp_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['email'];
    }
    return null;
}

/**
 * Get employee details including email by emp_id
 */
function getEmployeeDetails($con, $emp_id) {
    if (empty($emp_id)) {
        return null;
    }
    
    $query = "SELECT eid, full_name, email, department_id FROM employees WHERE eid = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    
    // If not found in employees table, try emp_login table
    $query = "SELECT emp_id as eid, user_name as full_name, email FROM emp_login WHERE emp_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    
    return null;
}

/**
 * Send email notification for leave reassignment
 */
function sendLeaveReassignmentEmail($con, $leave_data, $old_recommender, $new_recommender, $reason, $new_recommender_email, $applicant_email) {
    $config = getEmailConfig($con);
    $settings = getEmailNotificationSettings($con);
    
    // If email config is not available, skip email sending but don't fail the leave application
    if (!$config) {
        error_log("Email configuration not found - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    // If email settings are not available or disabled, skip email sending
    if (!$settings || !isset($settings['leave_application_enabled']) || !$settings['leave_application_enabled']) {
        error_log("Email notifications are disabled - skipping email notification");
        return true; // Return true to not block the leave application
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Check if all required config keys exist
        $required_keys = ['host', 'username', 'password', 'port', 'from_email', 'from_name'];
        foreach ($required_keys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                error_log("Missing email config key: $key - skipping email notification");
                return true; // Return true to not block the leave application
            }
        }
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if (isset($config['secure']) && $config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        
        // Email content
        $subject = "Leave Application Reassigned - " . $leave_data['user_name'] . " (" . $leave_data['type_of_leave'] . ")";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: #ffc107; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .details { background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Leave Application Reassigned</h2>
            </div>
            <div class='content'>
                <p>A leave application has been reassigned to you for recommendation.</p>
                
                <div class='details'>
                    <h3>Leave Details:</h3>
                    <p><strong>Employee:</strong> " . $leave_data['user_name'] . "</p>
                    <p><strong>Leave Type:</strong> " . $leave_data['type_of_leave'] . "</p>
                    <p><strong>Start Date:</strong> " . $leave_data['start_date'] . "</p>
                    <p><strong>End Date:</strong> " . $leave_data['end_date'] . "</p>
                    <p><strong>Total Days:</strong> " . $leave_data['total_days'] . "</p>
                    <p><strong>Reason:</strong> " . $leave_data['reason'] . "</p>
                </div>
                
                <div class='warning'>
                    <h4>Reassignment Information:</h4>
                    <p><strong>Previous Recommender:</strong> " . $old_recommender . "</p>
                    <p><strong>New Recommender:</strong> " . $new_recommender . "</p>
                    <p><strong>Reason for Reassignment:</strong> " . $reason . "</p>
                </div>
                
                <p>Please review this leave application and provide your recommendation.</p>
            </div>
            <div class='footer'>
                <p>This is an automated notification from HRMS System</p>
            </div>
        </body>
        </html>";
        
        // Send to new recommender
        if ($new_recommender_email && isset($settings['notify_recommender']) && $settings['notify_recommender']) {
            $mail->clearAddresses();
            $mail->addAddress($new_recommender_email);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $body;
            
            if ($mail->send()) {
                logEmailAttempt($con, $new_recommender_email, 'recommender', 'leave_application', $subject, 'sent', null, $leave_data['id']);
            } else {
                logEmailAttempt($con, $new_recommender_email, 'recommender', 'leave_application', $subject, 'failed', 'Failed to send email', $leave_data['id']);
            }
        }
        
        // Send notification to applicant
        if ($applicant_email && $settings['notify_applicant']) {
            $mail->clearAddresses();
            $mail->addAddress($applicant_email);
            $mail->Subject = "Your Leave Application Has Been Reassigned";
            
            $applicant_body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .details { background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
                    .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h2>Leave Application Reassigned</h2>
                </div>
                <div class='content'>
                    <p>Your leave application has been reassigned to a different recommender.</p>
                    
                    <div class='details'>
                        <h3>Leave Details:</h3>
                        <p><strong>Leave Type:</strong> " . $leave_data['type_of_leave'] . "</p>
                        <p><strong>Start Date:</strong> " . $leave_data['start_date'] . "</p>
                        <p><strong>End Date:</strong> " . $leave_data['end_date'] . "</p>
                        <p><strong>Total Days:</strong> " . $leave_data['total_days'] . "</p>
                        <p><strong>New Recommender:</strong> " . $new_recommender . "</p>
                        <p><strong>Reason for Reassignment:</strong> " . $reason . "</p>
                    </div>
                    
                    <p>Your application is being processed and you will be notified of the decision.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated notification from HRMS System</p>
                </div>
            </body>
            </html>";
            
            $mail->Body = $applicant_body;
            
            if ($mail->send()) {
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_application', $mail->Subject, 'sent', null, $leave_data['id']);
            } else {
                logEmailAttempt($con, $applicant_email, 'applicant', 'leave_application', $mail->Subject, 'failed', 'Failed to send email', $leave_data['id']);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Reassignment email failed: " . $e->getMessage());
        return false;
    }
}
?>