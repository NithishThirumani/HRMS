<?php
session_start();
include('connection.php');

// Include PHPMailer classes
require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/SMTP.php';
require_once '../PHPMailer/PHPMailer.php';

// PHPMailer use statements
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_email_config':
                // Update email configuration
                $host = $_POST['host'];
                $port = $_POST['port'];
                $username = $_POST['username'];
                $password = $_POST['password'];
                $from_email = $_POST['from_email'];
                $from_name = $_POST['from_name'];
                $secure = $_POST['secure'];
                
                // Update email_config table
                $update_query = "UPDATE email_config SET 
                    host = ?, port = ?, username = ?, password = ?, 
                    from_email = ?, from_name = ?, secure = ?, updated_at = NOW()
                    WHERE id = 1";
                
                $stmt = $con->prepare($update_query);
                $stmt->bind_param("sisssss", $host, $port, $username, $password, $from_email, $from_name, $secure);
                
                if ($stmt->execute()) {
                    $success_message = "Email configuration updated successfully!";
                } else {
                    $error_message = "Error updating email configuration: " . $con->error;
                }
                break;
                
            case 'update_notification_settings':
                // Update notification settings
                $leave_application_enabled = isset($_POST['leave_application_enabled']) ? 1 : 0;
                $leave_recommendation_enabled = isset($_POST['leave_recommendation_enabled']) ? 1 : 0;
                $leave_approval_enabled = isset($_POST['leave_approval_enabled']) ? 1 : 0;
                $notify_applicant = isset($_POST['notify_applicant']) ? 1 : 0;
                $notify_recommender = isset($_POST['notify_recommender']) ? 1 : 0;
                $notify_approver = isset($_POST['notify_approver']) ? 1 : 0;
                $notify_hr = isset($_POST['notify_hr']) ? 1 : 0;
                
                $update_query = "UPDATE email_notification_settings SET 
                    leave_application_enabled = ?, leave_recommendation_enabled = ?, 
                    leave_approval_enabled = ?, notify_applicant = ?, notify_recommender = ?, 
                    notify_approver = ?, notify_hr = ?, updated_at = NOW()
                    WHERE id = 1";
                
                $stmt = $con->prepare($update_query);
                $stmt->bind_param("iiiiiii", $leave_application_enabled, $leave_recommendation_enabled, 
                    $leave_approval_enabled, $notify_applicant, $notify_recommender, $notify_approver, $notify_hr);
                
                if ($stmt->execute()) {
                    $success_message = "Notification settings updated successfully!";
                } else {
                    $error_message = "Error updating notification settings: " . $con->error;
                }
                break;
                
            case 'test_email':
                // Test email functionality
                $test_email = $_POST['test_email'];
                
                if (!empty($test_email) && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                    // Get current email config
                    $config_query = "SELECT * FROM email_config WHERE id = 1";
                    $config_result = $con->query($config_query);
                    $config = $config_result->fetch_assoc();
                    
                    if ($config) {
                        // Send test email
                        $test_result = sendTestEmail($config, $test_email);
                        if ($test_result) {
                            $success_message = "Test email sent successfully to " . $test_email;
                        } else {
                            $error_message = "Failed to send test email. Check your email configuration.";
                        }
                    } else {
                        $error_message = "Email configuration not found.";
                    }
                } else {
                    $error_message = "Please enter a valid email address.";
                }
                break;
        }
    }
}

// Get current email configuration
$config_query = "SELECT * FROM email_config WHERE id = 1";
$config_result = $con->query($config_query);
$email_config = $config_result->fetch_assoc();

// Get current notification settings
$settings_query = "SELECT * FROM email_notification_settings WHERE id = 1";
$settings_result = $con->query($settings_query);
$notification_settings = $settings_result->fetch_assoc();

// If no settings exist, create default ones
if (!$notification_settings) {
    $insert_query = "INSERT INTO email_notification_settings 
        (leave_application_enabled, leave_recommendation_enabled, leave_approval_enabled,
         notify_applicant, notify_recommender, notify_approver, notify_hr, created_at, updated_at)
        VALUES (1, 1, 1, 1, 1, 1, 0, NOW(), NOW())";
    $con->query($insert_query);
    
    $settings_result = $con->query($settings_query);
    $notification_settings = $settings_result->fetch_assoc();
}

// Get employees for email assignment
$employees_query = "SELECT id, eid, full_name, email, department_id FROM employees ORDER BY full_name";
$employees_result = $con->query($employees_query);

// Get departments
$departments_query = "SELECT id, name FROM departments ORDER BY name";
$departments_result = $con->query($departments_query);

function sendTestEmail($config, $test_email) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if ($config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($test_email);
        $mail->Subject = "HRMS Email Test - " . date('Y-m-d H:i:s');
        $mail->isHTML(true);
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>HRMS Email Test</h2>
            </div>
            <div class='content'>
                <p>This is a test email from your HRMS system.</p>
                <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>Email Configuration:</strong></p>
                <ul>
                    <li>Host: " . $config['host'] . "</li>
                    <li>Port: " . $config['port'] . "</li>
                    <li>Username: " . $config['username'] . "</li>
                    <li>From Email: " . $config['from_email'] . "</li>
                </ul>
                <p>If you received this email, your email configuration is working correctly!</p>
            </div>
            <div class='footer'>
                <p>This is an automated test email from HRMS System</p>
            </div>
        </body>
        </html>";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Test email failed: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Email Settings - Admin Panel</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .config-section { margin-bottom: 30px; }
        .test-email-section { background-color: #f8f9fa; padding: 20px; border-radius: 5px; }
        .notification-matrix { margin-top: 20px; }
        .matrix-table { width: 100%; border-collapse: collapse; }
        .matrix-table th, .matrix-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .matrix-table th { background-color: #f8f9fa; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Email Settings</h1>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Email Configuration Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">SMTP Email Configuration</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_email_config">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="host">SMTP Host</label>
                                            <input type="text" class="form-control" id="host" name="host" 
                                                   value="<?php echo htmlspecialchars($email_config['host'] ?? ''); ?>" required>
                                            <small class="form-text text-muted">e.g., smtp.gmail.com, smtp.outlook.com</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="port">SMTP Port</label>
                                            <input type="number" class="form-control" id="port" name="port" 
                                                   value="<?php echo htmlspecialchars($email_config['port'] ?? '587'); ?>" required>
                                            <small class="form-text text-muted">587 for TLS, 465 for SSL</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Email Username</label>
                                            <input type="email" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($email_config['username'] ?? ''); ?>" required>
                                            <small class="form-text text-muted">Your email address</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Email Password</label>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   value="<?php echo htmlspecialchars($email_config['password'] ?? ''); ?>" required>
                                            <small class="form-text text-muted">Use App Password for Gmail</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="from_email">From Email</label>
                                            <input type="email" class="form-control" id="from_email" name="from_email" 
                                                   value="<?php echo htmlspecialchars($email_config['from_email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="from_name">From Name</label>
                                            <input type="text" class="form-control" id="from_name" name="from_name" 
                                                   value="<?php echo htmlspecialchars($email_config['from_name'] ?? 'HRMS System'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="secure">Security Type</label>
                                    <select class="form-control" id="secure" name="secure">
                                        <option value="tls" <?php echo ($email_config['secure'] ?? '') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($email_config['secure'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Email Configuration</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Test Email Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Test Email Configuration</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="test_email">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="test_email">Test Email Address</label>
                                            <input type="email" class="form-control" id="test_email" name="test_email" 
                                                   placeholder="Enter email address to send test email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-paper-plane"></i> Send Test Email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notification Settings Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Email Notification Settings</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_notification_settings">
                                
                                <h5>Enable Email Notifications</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="leave_application_enabled" 
                                                   name="leave_application_enabled" 
                                                   <?php echo ($notification_settings['leave_application_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="leave_application_enabled">
                                                Leave Application Notifications
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="leave_recommendation_enabled" 
                                                   name="leave_recommendation_enabled" 
                                                   <?php echo ($notification_settings['leave_recommendation_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="leave_recommendation_enabled">
                                                Leave Recommendation Notifications
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="leave_approval_enabled" 
                                                   name="leave_approval_enabled" 
                                                   <?php echo ($notification_settings['leave_approval_enabled'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="leave_approval_enabled">
                                                Leave Approval Notifications
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h5>Recipient Settings</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="notify_applicant" 
                                                   name="notify_applicant" 
                                                   <?php echo ($notification_settings['notify_applicant'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_applicant">
                                                Notify Applicant
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="notify_recommender" 
                                                   name="notify_recommender" 
                                                   <?php echo ($notification_settings['notify_recommender'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_recommender">
                                                Notify Recommender
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="notify_approver" 
                                                   name="notify_approver" 
                                                   <?php echo ($notification_settings['notify_approver'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_approver">
                                                Notify Approver
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="notify_hr" 
                                                   name="notify_hr" 
                                                   <?php echo ($notification_settings['notify_hr'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notify_hr">
                                                Notify HR Department
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary mt-3">Update Notification Settings</button>
                            </form>
            
                            <!-- Notification Matrix -->
                            <div class="notification-matrix">
                                <h5 class="mt-4">Email Notification Matrix</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered matrix-table">
                                        <thead>
                                            <tr>
                                                <th>Event</th>
                                                <th>Applicant</th>
                                                <th>Recommender</th>
                                                <th>Approver</th>
                                                <th>HR Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Leave Application Submitted</strong></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-times text-muted"></i></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Leave Recommendation Made</strong></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-times text-muted"></i></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-times text-muted"></i></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Leave Approval Decision</strong></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-check text-success"></i></td>
                                                <td><i class="fas fa-times text-muted"></i></td>
                                                <td><i class="fas fa-times text-muted"></i></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Templates Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Email Templates</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                            <h5>Leave Application</h5>
                                            <p class="text-muted">Sent when a new leave application is submitted</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="previewTemplate('application')">
                                                Preview Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-thumbs-up fa-3x text-warning mb-3"></i>
                                            <h5>Leave Recommendation</h5>
                                            <p class="text-muted">Sent when recommender makes a decision</p>
                                            <button class="btn btn-outline-warning btn-sm" onclick="previewTemplate('recommendation')">
                                                Preview Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                            <h5>Leave Approval</h5>
                                            <p class="text-muted">Sent when final approval decision is made</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="previewTemplate('approval')">
                                                Preview Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include('footer.php'); ?>
        </div>
    </div>
    
    <!-- Template Preview Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalTitle">Email Template Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="templateModalBody">
                    <!-- Template content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    
    <script>
        function previewTemplate(type) {
            let title = '';
            let content = '';
            
            switch(type) {
                case 'application':
                    title = 'Leave Application Email Template';
                    content = `
                        <div style="font-family: Arial, sans-serif;">
                            <div style="background-color: #007bff; color: white; padding: 20px; text-align: center;">
                                <h2>Leave Application Notification</h2>
                            </div>
                            <div style="padding: 20px;">
                                <p>A new leave application has been submitted.</p>
                                <div style="background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">
                                    <h3>Leave Details:</h3>
                                    <p><strong>Employee:</strong> [Employee Name]</p>
                                    <p><strong>Leave Type:</strong> [Leave Type]</p>
                                    <p><strong>Start Date:</strong> [Start Date]</p>
                                    <p><strong>End Date:</strong> [End Date]</p>
                                    <p><strong>Total Days:</strong> [Total Days]</p>
                                    <p><strong>Reason:</strong> [Reason]</p>
                                    <p><strong>Application Date:</strong> [Application Date]</p>
                                </div>
                                <p>Please review and take appropriate action.</p>
                            </div>
                            <div style="background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;">
                                <p>This is an automated notification from HRMS System</p>
                            </div>
                        </div>
                    `;
                    break;
                case 'recommendation':
                    title = 'Leave Recommendation Email Template';
                    content = `
                        <div style="font-family: Arial, sans-serif;">
                            <div style="background-color: #ffc107; color: white; padding: 20px; text-align: center;">
                                <h2>Leave Recommendation Update</h2>
                            </div>
                            <div style="padding: 20px;">
                                <p>The leave application has been reviewed by the recommender.</p>
                                <div style="background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">
                                    <h3>Leave Details:</h3>
                                    <p><strong>Employee:</strong> [Employee Name]</p>
                                    <p><strong>Leave Type:</strong> [Leave Type]</p>
                                    <p><strong>Recommender:</strong> [Recommender Name]</p>
                                    <p><strong>Recommendation:</strong> <span style="color: #28a745; font-weight: bold;">[Status]</span></p>
                                    <p><strong>Remarks:</strong> [Remarks]</p>
                                </div>
                            </div>
                            <div style="background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;">
                                <p>This is an automated notification from HRMS System</p>
                            </div>
                        </div>
                    `;
                    break;
                case 'approval':
                    title = 'Leave Approval Email Template';
                    content = `
                        <div style="font-family: Arial, sans-serif;">
                            <div style="background-color: #28a745; color: white; padding: 20px; text-align: center;">
                                <h2>Leave Approval Decision</h2>
                            </div>
                            <div style="padding: 20px;">
                                <p>The leave application has been reviewed and a decision has been made.</p>
                                <div style="background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;">
                                    <h3>Leave Details:</h3>
                                    <p><strong>Employee:</strong> [Employee Name]</p>
                                    <p><strong>Leave Type:</strong> [Leave Type]</p>
                                    <p><strong>Approver:</strong> [Approver Name]</p>
                                    <p><strong>Decision:</strong> <span style="color: #28a745; font-weight: bold;">[Status]</span></p>
                                    <p><strong>Remarks:</strong> [Remarks]</p>
                                </div>
                            </div>
                            <div style="background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;">
                                <p>This is an automated notification from HRMS System</p>
                            </div>
                        </div>
                    `;
                    break;
            }
            
            document.getElementById('templateModalTitle').textContent = title;
            document.getElementById('templateModalBody').innerHTML = content;
            $('#templateModal').modal('show');
        }
    </script>
</body>
</html> 