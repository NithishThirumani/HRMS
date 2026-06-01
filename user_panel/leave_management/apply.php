<?php
// Include the main session file first
include('../session.php');
// Include other required files
include('../connection.php');
// Make sure this path is correct and the file exists
include('includes/leave_functions.php'); 
include_once '../../config/config.php';
include '../../includes/email_functions.php';

// Enhanced session validation - check multiple session variables
$session_valid = false;
$redirect_url = '';

// Check if user is logged in with multiple validation methods
if (isset($_SESSION['eid']) && !empty($_SESSION['eid'])) {
    $session_valid = true;
} elseif (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
    // If eid is not set but user_name is, try to get eid from database
    $user_query = "SELECT e.eid FROM employees e 
                   JOIN emp_login el ON e.eid = el.emp_id 
                   WHERE el.user_name = ? AND el.status = 'Active'";
    $stmt = $con->prepare($user_query);
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['user_name']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $_SESSION['eid'] = $user_data['eid']; // Set eid in session
            $session_valid = true;
            error_log("Recovered eid from user_name: " . $user_data['eid']);
        }
        $stmt->close();
    }
}

// If session is still not valid, redirect to login
if (!$session_valid) {
    error_log("Session validation failed in apply.php - redirecting to login");
    header("Location: ../login.php");
    exit();
}

// Ensure we have the employee ID
$emp_id = $_SESSION['eid'];
$errors = [];
$success_message = '';

// Initialize $emp_data to prevent errors if the query fails or returns no data
$emp_data = null;
$leave_balances_data = []; // Initialize leave balances array
// Get employee details from the database
// Ensure department_heads table has department_id, head_id, head_name
// Ensure employees table has department_id (corrected from deaprtment_id) and doj
$emp_query = "SELECT e.*, d.name as dept_name, e.department_id 
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.id 
              WHERE e.eid = ?";
$stmt_emp = $con->prepare($emp_query);
$stmt_emp->bind_param("s", $_SESSION['eid']);
$stmt_emp->execute();
$emp_data = $stmt_emp->get_result()->fetch_assoc();
$stmt_emp->close();

// Check if employee data was found
if (!$emp_data) {
    error_log("Employee not found in database for eid: " . $_SESSION['eid']);
    $errors[] = "Employee data not found. Please contact HR.";
    // Don't proceed with the rest of the script
} else {
    $emp_id = $emp_data['eid'];
    $dept_id = $emp_data['department_id'];
}

// Fetch HOD details for the department
$hod_id = null;
$hod_name = '';
if ($emp_data && isset($dept_id)) {
    $hod_query = $con->prepare("SELECT id, head_name FROM department_heads WHERE department_id = ?");
    $hod_query->bind_param("i", $dept_id);
    $hod_query->execute();
    $hod_query->bind_result($hod_id, $hod_name);
    $hod_query->fetch();
    $hod_query->close();
}

// For debugging
error_log("HOD ID: " . $hod_id);
error_log("HOD Name: " . $hod_name);

// Get all leave types from policies
$leave_types = [];
if ($emp_data && isset($emp_data['gender'])) {
    // MODIFIED QUERY: Added DISTINCT and other potentially needed columns from leave_policies
    // Ensure you select all columns from leave_policies that you might iterate over later when building the options
    $leave_query = "SELECT DISTINCT leave_type, gender_restriction, min_service_months, max_days, monthly_accrual, requires_certificate, is_paid 
                    FROM leave_policies 
                    WHERE (gender_restriction = 'all' OR gender_restriction = ?) 
                      AND (is_active IS NULL OR is_active = 1)
                    ORDER BY leave_type";
    $stmt_leave_types = $con->prepare($leave_query);
    if ($stmt_leave_types) {
        $stmt_leave_types->bind_param("s", $emp_data['gender']);
        $stmt_leave_types->execute();
        $leave_types_result = $stmt_leave_types->get_result();
        $leave_types = $leave_types_result->fetch_all(MYSQLI_ASSOC);
        $stmt_leave_types->close();
        
        // Debug: Log all fetched leave types
        error_log("Fetched leave types for employee gender '" . $emp_data['gender'] . "':");
        foreach ($leave_types as $lt) {
            error_log("  - " . $lt['leave_type'] . " (Gender: " . $lt['gender_restriction'] . ", Service: " . $lt['min_service_months'] . ")");
        }
    } else {
        $errors[] = "Error preparing leave types query: " . $con->error;
    }
} elseif (!$emp_data) {
    // Error already added for employee data not found
} else if ($emp_data && !isset($emp_data['gender'])){
    $errors[] = "Employee gender not found in your profile. Cannot fetch applicable leave policies.";
}

// Build eligibility info for each leave type
$leave_types_with_eligibility = [];
if ($emp_data && isset($emp_data['doj']) && !empty($emp_data['doj'])) {
    $join_date = new DateTime($emp_data['doj']);
    $today = new DateTime();
    $service_interval = $join_date->diff($today);
    $service_months = $service_interval->m + ($service_interval->y * 12);
    
    // Debug information
    error_log("Employee Gender: " . $emp_data['gender']);
    error_log("Service Months: " . $service_months);
    
    foreach ($leave_types as $type) {
        $eligible = true;
        $reason = '';
        
        // Debug each leave type
        error_log("Checking leave type: " . $type['leave_type']);
        error_log("  - Gender restriction: " . $type['gender_restriction']);
        error_log("  - Min service months: " . $type['min_service_months']);
        
        if ($service_months < $type['min_service_months']) {
            $eligible = false;
            $reason = 'Requires ' . $type['min_service_months'] . ' months of service';
            error_log("  - Service months check failed");
        }
        
        if ($type['gender_restriction'] != 'all' && strtolower(trim($type['gender_restriction'])) != strtolower(trim($emp_data['gender']))) {
            $eligible = false;
            $reason = 'Only for ' . $type['gender_restriction'] . ' employees';
            error_log("  - Gender restriction check failed. Expected: " . $type['gender_restriction'] . ", Got: " . $emp_data['gender']);
        }
        
        error_log("  - Final eligibility: " . ($eligible ? 'YES' : 'NO') . " - " . $reason);
        
        $leave_types_with_eligibility[] = [
            'leave_type' => $type['leave_type'],
            'eligible' => $eligible,
            'reason' => $reason
        ];
    }
} else {
    // If DOJ missing, mark all as ineligible
    foreach ($leave_types as $type) {
        $leave_types_with_eligibility[] = [
            'leave_type' => $type['leave_type'],
            'eligible' => false,
            'reason' => 'Join date missing in profile'
        ];
    }
}

// Get leave balances for the employee
if ($emp_data && !empty($emp_id)) {
    try {
        $leave_balances_data = getLeaveBalances($emp_id);
        error_log("Leave balances loaded for employee: " . $emp_id);
    } catch (Exception $e) {
        error_log("Error loading leave balances: " . $e->getMessage());
        $leave_balances_data = [];
    }
} else {
    error_log("Cannot load leave balances - employee data or ID missing");
    $leave_balances_data = [];
}


// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors) && $emp_data) { // Proceed only if initial data fetch was okay and emp_data exists
    $leave_type = $_POST['leave_type'];
    $start_date_str = $_POST['start_date'];
    $end_date_str = $_POST['end_date'];
    $reason = $_POST['reason'];
    
    // Validate dates
    try {
        $start_date_obj = new DateTime($start_date_str);
        $end_date_obj = new DateTime($end_date_str);
        $current_date = new DateTime();
        
        // Reset time to midnight for date-only comparison
        $start_date_obj->setTime(0, 0, 0);
        $current_date->setTime(0, 0, 0);
        
        // Check if start date is from current date onwards
        if ($start_date_obj < $current_date) {
            $errors[] = "Start date cannot be in the past. Please select a date from today onwards.";
        }
        
        if ($start_date_obj > $end_date_obj) {
            $errors[] = "Start date cannot be after end date.";
        }
    } catch (Exception $e) {
        $errors[] = "Invalid date format. Please use YYYY-MM-DD.";
    }

    if (empty($errors)) {
        $total_days = calculateWorkingDays($start_date_str, $end_date_str); 
        if ($total_days <= 0) { 
             $errors[] = "Total leave days must be at least 1. Please check your dates.";
        }

        $policy_query = "SELECT * FROM leave_policies WHERE leave_type = ?";
        $stmt_policy = $con->prepare($policy_query);
        $policy = null;
        if ($stmt_policy) {
            $stmt_policy->bind_param("s", $leave_type);
            $stmt_policy->execute();
            $policy_result = $stmt_policy->get_result();
            $policy = $policy_result->fetch_assoc();
            $stmt_policy->close();
        } else {
            $errors[] = "Error preparing policy query: " . $con->error;
        }

        if ($policy && empty($errors)) {
            // Check service months
            if (isset($emp_data['doj']) && !empty($emp_data['doj'])) {
                try {
                    $join_date = new DateTime($emp_data['doj']);
                    $today = new DateTime();
                    // Ensure join date is not in the future
                    if ($join_date > $today) {
                        $errors[] = "Join date cannot be in the future.";
                    } else {
                        $service_interval = $join_date->diff($today);
                        $service_months = $service_interval->m + ($service_interval->y * 12);

                        if ($service_months < $policy['min_service_months']) {
                            $errors[] = "You need a minimum of {$policy['min_service_months']} months of service to apply for this leave type. Your service: {$service_months} months.";
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Error calculating service months. Invalid join date (doj: {$emp_data['doj']}) format in your employee profile.";
                }
            } else {
                $errors[] = "Employee join date (doj) not found or is empty in your profile. Cannot calculate service months.";
            }
            
            if ($policy['gender_restriction'] != 'all' && $policy['gender_restriction'] != $emp_data['gender']) {
                $errors[] = "This leave type is only available for {$policy['gender_restriction']} employees.";
            }
            
            // Pass $con to the function call
            $available_balance = getAvailableLeaveBalance($con, $emp_id, $leave_type); 
            if ($total_days > $available_balance) {
                $errors[] = "Insufficient leave balance. Available: {$available_balance} days, Requested: {$total_days} days.";
            }
        } elseif (!$policy) {
            $errors[] = "Leave policy not found for the selected leave type: {$leave_type}.";
        }
    }
    
    if (empty($errors) && $policy && $emp_data) {
        // Fetch recommenders and approver from leave_hierarchy
        $recommenders = [];
        $approver_id = null;
        $lh_query = "SELECT * FROM leave_hierarchy WHERE employee_id = ?";
        $stmt_lh = $con->prepare($lh_query);
        $stmt_lh->bind_param("i", $emp_data['id']);
        $stmt_lh->execute();
        $lh_result = $stmt_lh->get_result();
        while ($lh_row = $lh_result->fetch_assoc()) {
            if ($lh_row['type'] === 'recommender' && $lh_row['recommender_id']) {
                $recommenders[] = $lh_row['recommender_id'];
            } elseif ($lh_row['type'] === 'approver' && $lh_row['approver_id']) {
                $approver_id = $lh_row['approver_id'];
            }
        }
        $stmt_lh->close();
        if (empty($recommenders) && !$approver_id) {
            $errors[] = "No recommender or approver assigned for you in the leave hierarchy. Please contact HR.";
        } else {
            // Pick the first recommender if multiple
            $recommender_id = !empty($recommenders) ? $recommenders[0] : null;
            // Get names for display (optional)
            $rec_name = $recommender_id ? getEmployeeNameById($con, $recommender_id) : '';
            $app_name = $approver_id ? getEmployeeNameById($con, $approver_id) : '';
            
            // Check for overlapping leave applications - ROBUST VERSION
            error_log("DEBUG: Checking for overlaps for employee: $emp_id");
            error_log("DEBUG: Employee ID from employees table: " . $emp_data['id']);
            error_log("DEBUG: New leave dates - Start: $start_date_str, End: $end_date_str");
            
            $overlap_query = "SELECT COUNT(*) as overlap_count FROM leaves 
                WHERE emp_id = ? 
                  AND status != 'Rejected'
                  AND (
                    -- New leave starts during existing leave
                    (start_date <= ? AND end_date >= ?) OR
                    -- New leave ends during existing leave  
                    (start_date <= ? AND end_date >= ?) OR
                    -- New leave completely contains existing leave
                    (start_date <= ? AND end_date >= ?) OR
                    -- New leave is completely within existing leave
                    (start_date >= ? AND end_date <= ?)
                  )";
            
            $stmt_overlap = $con->prepare($overlap_query);
            $stmt_overlap->bind_param("issssssss",
                $emp_data['id'],  // Use the numeric ID from employees table
                $start_date_str, $start_date_str,  // New leave starts during existing
                $end_date_str, $end_date_str,      // New leave ends during existing  
                $start_date_str, $end_date_str,    // New leave contains existing
                $start_date_str, $end_date_str     // New leave within existing
            );
            $stmt_overlap->execute();
            $overlap_result = $stmt_overlap->get_result();
            $overlap_data = $overlap_result->fetch_assoc();
            $overlap_count = $overlap_data['overlap_count'];
            $stmt_overlap->close();
            
            error_log("DEBUG: Overlap count found: $overlap_count");
            
            if ($overlap_count > 0) {
                error_log("DEBUG: Overlap detected! Blocking leave application.");
                $errors[] = "You already have a leave application for the selected date range or overlapping dates. Please check your existing leave applications.";
            } else {
                error_log("DEBUG: No overlaps found. Proceeding with leave application.");
                
                // Calculate current month leave days (set to 0 for now)
                $current_month_ldays = 0;
                
                // Prepare variables for bind_param - ensure proper types
                $status = 'Pending';
                $applied_at = date('Y-m-d H:i:s');
                
                // Simple direct SQL approach to avoid bind_param issues
                $emp_id_escaped = $con->real_escape_string($emp_data['eid']);
                $emp_name_escaped = $con->real_escape_string($emp_data['full_name']);
                $leave_type_escaped = $con->real_escape_string($leave_type);
                $start_date_escaped = $con->real_escape_string($start_date_str);
                $end_date_escaped = $con->real_escape_string($end_date_str);
                $total_days_int = (int)$total_days;
                $reason_escaped = $con->real_escape_string($reason);
                $status_escaped = $con->real_escape_string($status);
                $hod_id_int = $hod_id ? (int)$hod_id : 0;
                $hod_name_escaped = $con->real_escape_string($hod_name);
                $applied_at_escaped = $con->real_escape_string($applied_at);
                $current_month_ldays_int = (int)$current_month_ldays;
                $recommender_id_int = $recommender_id ? (int)$recommender_id : 0;
                $approver_id_int = $approver_id ? (int)$approver_id : 0;
                $rec_name_escaped = $con->real_escape_string($rec_name);
                $app_name_escaped = $con->real_escape_string($app_name);
                
                // Insert leave application using direct SQL
                $insert_query = "INSERT INTO leaves (
                    emp_id, 
                    user_name, 
                    reason,
                    type_of_leave, 
                    start_date, 
                    end_date, 
                    current_month_ldays,
                    total_days, 
                    applied_at, 
                    status, 
                    hod_id, 
                    hod_name,
                    recommender_id,
                    recommender_name,
                    approver_id,
                    approver_name,
                    current_level,
                    max_level,
                    current_approver_role,
                    next_approver_role
                ) VALUES (
                    '$emp_id_escaped',
                    '$emp_name_escaped',
                    '$reason_escaped',
                    '$leave_type_escaped',
                    '$start_date_escaped',
                    '$end_date_escaped',
                    $current_month_ldays_int,
                    $total_days_int,
                    '$applied_at_escaped',
                    '$status_escaped',
                    $hod_id_int,
                    '$hod_name_escaped',
                    $recommender_id_int,
                    '$rec_name_escaped',
                    $approver_id_int,
                    '$app_name_escaped',
                    1,
                    2,
                    'HOD',
                    'MANAGER'
                )";
                
                $result = $con->query($insert_query);
            
            if ($result) {
                $leave_id = $con->insert_id;
                
                // Prepare leave data for email notification
                $leave_data = array(
                    'employee_name' => $emp_data['full_name'],
                    'leave_type' => $leave_type,
                    'start_date' => $start_date_str,
                    'end_date' => $end_date_str,
                    'total_days' => $total_days,
                    'reason' => $reason,
                    'applied_at' => date('Y-m-d H:i:s')
                );
                
                // Get email addresses from employees table
                $applicant_email = getEmployeeEmail($con, $emp_id);
                $recommender_email = null;
                $approver_email = null;
                
                // Get recommender email if exists
                if (!empty($recommender_id)) {
                    $recommender_email = getEmployeeEmail($con, $recommender_id);
                }
                
                // Get approver email if exists
                if (!empty($approver_id)) {
                    $approver_email = getEmployeeEmail($con, $approver_id);
                }
                
                // Log email addresses for debugging
                error_log("DEBUG: Applicant email: " . ($applicant_email ?? 'NULL'));
                error_log("DEBUG: Recommender email: " . ($recommender_email ?? 'NULL'));
                error_log("DEBUG: Approver email: " . ($approver_email ?? 'NULL'));
                
                                 // Send email notifications
                 $email_sent = sendLeaveApplicationEmail($con, $leave_data, $applicant_email, $recommender_email, $approver_email);
                
                // Log email status
                if ($email_sent) {
                    error_log("Leave application emails sent successfully for leave ID: " . $leave_id);
                } else {
                    error_log("Failed to send leave application emails for leave ID: " . $leave_id);
                }
                
                $_SESSION['success'] = "Leave application submitted successfully! Leave ID: " . $leave_id;
                // #region agent log
                @file_put_contents(__DIR__ . '/../../debug-a77d8c.log', json_encode(['sessionId'=>'a77d8c','hypothesisId'=>'A','location'=>'apply.php:461','message'=>'Leave redirect','data'=>['headers_sent'=>headers_sent(),'leave_id'=>$leave_id],'timestamp'=>round(microtime(true)*1000)]).PHP_EOL, FILE_APPEND);
                // #endregion
                header("Location: /emps/user_panel/leave_management/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Error submitting leave application: " . $con->error;
                header("Location: /emps/user_panel/leave_management/apply.php");
                exit();
            }
            }
        }
    }
}

// Repopulate form fields if there was an error
$form_leave_type = $_POST['leave_type'] ?? '';
$form_start_date = $_POST['start_date'] ?? '';
$form_end_date = $_POST['end_date'] ?? '';
$form_reason = $_POST['reason'] ?? '';

$employee_name_display = $emp_data['full_name'] ?? 'N/A';
$department_name_display = $emp_data['dept_name'] ?? 'N/A';

// Helper function to get employee name by id
function getEmployeeNameById($con, $emp_id) {
    $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
    return $name;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Apply Leave</title>
    <link href="img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet"> 
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">     
    <link rel="stylesheet" href="../css/custom.css">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
   <script src="js/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/search.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
            
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .quote-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            border-left: 4px solid var(--primary-color);
        }
    
        .container {
            margin-top: 50px;
        }
        .form-group label {
            font-weight: bold;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('../sidebar.php'); ?>
  
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
            <?php include('../topbar.php'); ?>
    <div class="container-fluid">
        <h2>Apply for Leave</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
     

        <?php if ($emp_data): // Only show form if employee data was loaded ?>
        <form method="POST" action="apply.php">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Employee Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee_name_display); ?>" readonly>
                </div>
                <div class="form-group col-md-6">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($department_name_display); ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label for="leave_type">Leave Type</label>
                <select class="form-control" id="leave_type" name="leave_type" required>
                    <option value="">Select Leave Type</option>
                    <?php foreach ($leave_types_with_eligibility as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['leave_type']); ?>" <?php echo ($form_leave_type == $type['leave_type']) ? 'selected' : ''; ?> <?php echo !$type['eligible'] ? 'disabled style="color:gray;"' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type['leave_type']))); ?>
                            <?php if (!$type['eligible']): ?> (<?php echo htmlspecialchars($type['reason']); ?>)<?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($form_start_date); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($form_end_date); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required><?php echo htmlspecialchars($form_reason); ?></textarea>
            </div>

            <!-- Display Recommender and Approver Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Approval Flow</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info"><i class="fas fa-user-check"></i> Recommender</h6>
                            <?php
                            // Fetch current recommender and approver for display
                            $current_recommenders = [];
                            $current_approver_id = null;
                            $current_approver_name = '';
                            
                            if ($emp_data) {
                                $lh_display_query = "SELECT * FROM leave_hierarchy WHERE employee_id = ?";
                                $stmt_lh_display = $con->prepare($lh_display_query);
                                $stmt_lh_display->bind_param("i", $emp_data['id']);
                                $stmt_lh_display->execute();
                                $lh_display_result = $stmt_lh_display->get_result();
                                while ($lh_display_row = $lh_display_result->fetch_assoc()) {
                                    if ($lh_display_row['type'] === 'recommender' && $lh_display_row['recommender_id']) {
                                        $current_recommenders[] = $lh_display_row['recommender_id'];
                                    } elseif ($lh_display_row['type'] === 'approver' && $lh_display_row['approver_id']) {
                                        $current_approver_id = $lh_display_row['approver_id'];
                                    }
                                }
                                $stmt_lh_display->close();
                                
                                // Get recommender names
                                if (!empty($current_recommenders)) {
                                    foreach ($current_recommenders as $rec_id) {
                                        $rec_name = getEmployeeNameById($con, $rec_id);
                                        echo '<p class="mb-1"><strong>' . htmlspecialchars($rec_name) . '</strong></p>';
                                    }
                                } else {
                                    echo '<p class="text-muted mb-1">No recommender assigned</p>';
                                }
                            }
                            ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-user-shield"></i> Approver</h6>
                            <?php
                            if ($current_approver_id) {
                                $current_approver_name = getEmployeeNameById($con, $current_approver_id);
                                echo '<p class="mb-1"><strong>' . htmlspecialchars($current_approver_name) . '</strong></p>';
                            } else {
                                echo '<p class="text-muted mb-1">No approver assigned</p>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Your leave application will be reviewed by the recommender first, then by the approver.
                        </small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">Cannot display leave application form due to missing employee data. Please contact HR.</div>
        <?php endif; ?>
 </div>
 <!-- Display Leave Balances -->
 <?php if (!empty($leave_balances_data)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Your Leave Balances (Current Year)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Eligible Days</th>
                                            <th>Availed Days</th>
                                            <th>Applied Leave</th>
                                            <th>Available Balance</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leave_balances_data as $balance_item): ?>
                                        <tr class="<?php echo $balance_item['is_eligible'] ? '' : 'table-warning'; ?>">
                                            <td><?php echo htmlspecialchars($balance_item['leave_type']); ?></td>
                                            <td><?php echo htmlspecialchars($balance_item['total_days']); ?></td>
                                            <td><?php echo htmlspecialchars($balance_item['used_days']); ?></td>
                                            <td><?php echo htmlspecialchars($balance_item['applied_days']); ?></td>
                                            <td><?php echo htmlspecialchars($balance_item['available_days']); ?></td>
                                            <td>
                                                <?php if ($balance_item['is_eligible']): ?>
                                                    <span class="badge badge-success">Eligible</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        Eligible from: <?php echo htmlspecialchars(date('d M Y', strtotime($balance_item['eligibility_date']))); ?>
                                                        <br><small>(<?php echo $balance_item['service_months_needed']; ?> months more)</small>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php elseif (empty($errors)): // Only show if no other critical errors like employee not found ?>
                        <div class="alert alert-info">Leave balance information could not be loaded.</div>
                    <?php endif; ?>
                    <!-- End Display Leave Balances -->
</div>
    <?php include('../footer.php'); ?>
</div>
</div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        </div>
    </a>
  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current
                    session.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="/emps/user_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS and dependencies -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Set minimum date to today for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('min', today);
            document.getElementById('end_date').setAttribute('min', today);
            
            // Update end date minimum when start date changes
            document.getElementById('start_date').addEventListener('change', function() {
                var startDate = this.value;
                if (startDate) {
                    document.getElementById('end_date').setAttribute('min', startDate);
                }
            });
        });
    </script>
</body>
</html>