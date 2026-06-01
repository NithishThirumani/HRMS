<?php
// Include the main session file first
include('../session.php');
// Include other required files
include('../connection.php');
// Make sure this path is correct and the file exists
include('includes/leave_functions.php'); 
include '../../config/config.php';
include '../../includes/email_functions.php';

// Check if user is logged in
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

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

$emp_id = $emp_data['eid'];
$dept_id = $emp_data['department_id'];

// Fetch HOD details for the department
$hod_id = null;
$hod_name = '';
$hod_query = $con->prepare("SELECT id, head_name FROM department_heads WHERE department_id = ?");
$hod_query->bind_param("i", $dept_id);
$hod_query->execute();
$hod_query->bind_result($hod_id, $hod_name);
$hod_query->fetch();
$hod_query->close();

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
            
            if ($policy['gender_restriction'] != 'all' && strtolower(trim($policy['gender_restriction'])) != strtolower(trim($emp_data['gender']))) {
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
            
            // Check for overlapping leave applications - ROBUST VERSION
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
                $emp_id,
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
            
            if ($overlap_count > 0) {
                $errors[] = "You already have a leave application for the selected date range or overlapping dates. Please check your existing leave applications.";
            } else {
                // Insert leave application
                error_log("DEBUG: HR emp_id being inserted: " . $emp_id);
                                 // Get recommender and approver names
                 $rec_name = $recommender_id ? getEmployeeNameById($con, $recommender_id) : '';
                 $app_name = $approver_id ? getEmployeeNameById($con, $approver_id) : '';
                 
                 $insert_query = "INSERT INTO leaves (
                     emp_id, 
                     user_name, 
                     type_of_leave, 
                     start_date, 
                     end_date, 
                     total_days, 
                     reason, 
                     status, 
                     hod_id, 
                     hod_name, 
                     applied_at, 
                     recommender_id,
                     recommender_name,
                     approver_id,
                     approver_name
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW(), ?, ?, ?, ?)";
                 $stmt_insert = $con->prepare($insert_query);
                 $stmt_insert->bind_param("sssssissiiss",
                     $emp_id, 
                     $emp_data['full_name'], 
                     $leave_type, 
                     $start_date_str, 
                     $end_date_str, 
                     $total_days, 
                     $reason, 
                     $hod_id, 
                     $hod_name, 
                     $recommender_id,
                     $rec_name,
                     $approver_id,
                     $app_name
                 );
                
                if ($stmt_insert->execute()) {
                    $leave_id = $stmt_insert->insert_id; // Get the leave ID
                    
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
                    error_log("DEBUG: HR Applicant email: " . ($applicant_email ?? 'NULL'));
                    error_log("DEBUG: HR Recommender email: " . ($recommender_email ?? 'NULL'));
                    error_log("DEBUG: HR Approver email: " . ($approver_email ?? 'NULL'));
                    
                                         // Send email notifications
                     sendLeaveApplicationEmail($con, $leave_data, $applicant_email, $recommender_email, $approver_email);
                    
                    $_SESSION['success'] = "Leave application submitted successfully! Leave ID: " . $leave_id;
                    $_SESSION['leave_id'] = $leave_id; // Store leave ID in session
                    header("Location: dashboard.php"); 
                    exit();
                } else {
                    $errors[] = "Error submitting leave application: " . $stmt_insert->error;
                }
                $stmt_insert->close();
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
            <?php include('../header.php'); ?>
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
                        <option value="<?php echo htmlspecialchars($type['leave_type']); ?>" <?php echo ($form_leave_type == $type['leave_type']) ? 'selected' : ''; ?> <?php echo !$type['eligible'] ? 'disabled style=\"color:gray;\"' : ''; ?>>
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
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
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
</body>
</html>