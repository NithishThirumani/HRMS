<?php
require_once '../config.php';
require_once '../connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details based on role
$user = null;
$user_type = null;

// Check if user is an admin
$stmt = $con->prepare("SELECT id, email, role FROM admin WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($admin) {
    $user = $admin;
    $user_type = 'admin';
} else {
    // Check if user is a department head
    $stmt = $con->prepare("SELECT dh.id, dh.head_email as email, dh.head_name as full_name, dh.department_id 
                          FROM department_heads dh 
                          WHERE dh.head_email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $dept_head = $stmt->get_result()->fetch_assoc();

    if ($dept_head) {
        $user = $dept_head;
        $user_type = 'department_head';
    } else {
        // Check if user is an employee
        $stmt = $con->prepare("SELECT e.id, e.eid, e.full_name, e.department_id 
                              FROM employees e
                              WHERE e.email = ? AND e.status = 'active'");
        $stmt->bind_param("s", $_SESSION['email']);
        $stmt->execute();
        $employee = $stmt->get_result()->fetch_assoc();

        if ($employee) {
            $user = $employee;
            $user_type = 'employee';
        }
    }
}

if (!$user) {
    header('Location: ../login.php');
    exit();
}

$user_id = $user['id'];
$user_name = $user['full_name'] ?? 'User';

// Get pending documents count for sidebar
$pending_query = "SELECT COUNT(*) as count FROM esign_documents d 
                 INNER JOIN esign_workflow w ON d.id = w.document_id 
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// Get user's signature settings
$settings_query = "SELECT * FROM esign_settings WHERE user_id = ?";
$stmt = $con->prepare($settings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $signature_type = $_POST['signature_type'] ?? 'draw';
    $notification_email = $_POST['notification_email'] ?? 1;
    $notification_sms = $_POST['notification_sms'] ?? 0;
    $default_expiry = $_POST['default_expiry'] ?? 30;
    $auto_reminder = $_POST['auto_reminder'] ?? 1;
    $reminder_days = $_POST['reminder_days'] ?? 3;

    if ($settings) {
        // Update existing settings
        $update_query = "UPDATE esign_settings SET 
                        signature_type = ?, 
                        notification_email = ?, 
                        notification_sms = ?, 
                        default_expiry = ?, 
                        auto_reminder = ?, 
                        reminder_days = ? 
                        WHERE user_id = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("siiiiii", $signature_type, $notification_email, $notification_sms, 
                         $default_expiry, $auto_reminder, $reminder_days, $user_id);
    } else {
        // Insert new settings
        $insert_query = "INSERT INTO esign_settings 
                        (user_id, signature_type, notification_email, notification_sms, 
                         default_expiry, auto_reminder, reminder_days) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("isiiiii", $user_id, $signature_type, $notification_email, 
                         $notification_sms, $default_expiry, $auto_reminder, $reminder_days);
    }

    if ($stmt->execute()) {
        $success_message = "Settings updated successfully!";
        // Refresh settings
        $stmt = $con->prepare($settings_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $settings = $stmt->get_result()->fetch_assoc();
    } else {
        $error_message = "Error updating settings. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - E-Signature Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-left: 250px;
            background: #f8f9fa;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-check {
            margin-bottom: 1rem;
        }
        .settings-section {
            margin-bottom: 2rem;
        }
        .settings-section:last-child {
            margin-bottom: 0;
        }
        .alert {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">E-Signature Settings</h4>
            </div>
            <div class="card-body">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Signature Preferences -->
                    <div class="settings-section">
                        <h5 class="mb-3">Signature Preferences</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="signature_type" 
                                   value="draw" id="signature_draw" 
                                   <?php echo ($settings['signature_type'] ?? 'draw') === 'draw' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="signature_draw">
                                Draw Signature
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="signature_type" 
                                   value="type" id="signature_type" 
                                   <?php echo ($settings['signature_type'] ?? '') === 'type' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="signature_type">
                                Type Signature
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="signature_type" 
                                   value="upload" id="signature_upload" 
                                   <?php echo ($settings['signature_type'] ?? '') === 'upload' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="signature_upload">
                                Upload Signature Image
                            </label>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="settings-section">
                        <h5 class="mb-3">Notification Settings</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notification_email" 
                                   value="1" id="notification_email" 
                                   <?php echo ($settings['notification_email'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notification_email">
                                Email Notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notification_sms" 
                                   value="1" id="notification_sms" 
                                   <?php echo ($settings['notification_sms'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notification_sms">
                                SMS Notifications
                            </label>
                        </div>
                    </div>

                    <!-- Document Defaults -->
                    <div class="settings-section">
                        <h5 class="mb-3">Document Defaults</h5>
                        <div class="mb-3">
                            <label for="default_expiry" class="form-label">Default Document Expiry (days)</label>
                            <input type="number" class="form-control" id="default_expiry" name="default_expiry" 
                                   value="<?php echo $settings['default_expiry'] ?? 30; ?>" min="1" max="365">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="auto_reminder" 
                                   value="1" id="auto_reminder" 
                                   <?php echo ($settings['auto_reminder'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="auto_reminder">
                                Enable Automatic Reminders
                            </label>
                        </div>
                        <div class="mb-3">
                            <label for="reminder_days" class="form-label">Send Reminder After (days)</label>
                            <input type="number" class="form-control" id="reminder_days" name="reminder_days" 
                                   value="<?php echo $settings['reminder_days'] ?? 3; ?>" min="1" max="30">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 