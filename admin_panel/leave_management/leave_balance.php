<?php
// Include the main session file first
include('../session.php');
// Include other required files
include('../connection.php');
// Make sure this path is correct and the file exists
include('includes/leave_functions.php'); 

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
$emp_query = "SELECT e.*, d.name AS dept_name, 
                 dh.head_id, dh.head_name
                 FROM employees e
                 LEFT JOIN departments d ON e.department_id = d.id
                 LEFT JOIN department_heads dh ON d.id = dh.department_id
                 WHERE e.eid = ?";
$stmt_emp = $con->prepare($emp_query);
if ($stmt_emp) {
    $stmt_emp->bind_param("s", $emp_id);
    $stmt_emp->execute();
    $emp_result = $stmt_emp->get_result();
    $emp_data = $emp_result->fetch_assoc();
    if (!$emp_data) {
        $errors[] = "Employee data not found. Please ensure your employee record and department assignments are correct.";
    } else {
        // Fetch leave balances if employee data is found
        $leave_balances_data = getLeaveBalances($emp_id); 
    }
    $stmt_emp->close();
} else {
    $errors[] = "Error preparing employee data query: " . $con->error;
}

// Get all leave types from policies
$leave_types = [];
if ($emp_data && isset($emp_data['gender'])) {
    $leave_query = "SELECT DISTINCT leave_type, gender_restriction, min_service_months, max_days, monthly_accrual, requires_certificate, is_paid 
                    FROM leave_policies 
                    WHERE (gender_restriction = 'all' OR gender_restriction = ?) 
                    ORDER BY leave_type";
    $stmt_leave_types = $con->prepare($leave_query);
    if ($stmt_leave_types) {
        $stmt_leave_types->bind_param("s", $emp_data['gender']);
        $stmt_leave_types->execute();
        $leave_types_result = $stmt_leave_types->get_result();
        $leave_types = $leave_types_result->fetch_all(MYSQLI_ASSOC);
        $stmt_leave_types->close();
    } else {
        $errors[] = "Error preparing leave types query: " . $con->error;
    }
} elseif (!$emp_data) {
    // Error already added for employee data not found
} else if ($emp_data && !isset($emp_data['gender'])){
    $errors[] = "Employee gender not found in your profile. Cannot fetch applicable leave policies.";
}

$employee_name_display = $emp_data['full_name'] ?? 'N/A';
$department_name_display = $emp_data['dept_name'] ?? 'N/A';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leave Balance</title>
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
        
        .balance-card {
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .balance-card:hover {
            transform: translateY(-5px);
        }
        
        .balance-card .card-header {
            border-radius: 10px 10px 0 0;
        }
        
        .balance-card .card-body {
            padding: 1.5rem;
        }
        
        .balance-value {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .progress-container {
            margin-top: 15px;
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
        <h2>Leave Balance</h2>

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
     
        <?php if ($emp_data): // Only show content if employee data was loaded ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Employee Name:</strong> <?php echo htmlspecialchars($employee_name_display); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Department:</strong> <?php echo htmlspecialchars($department_name_display); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Balance Cards -->
            <div class="row">
                <?php if (!empty($leave_balances_data)): ?>
                    <?php foreach ($leave_balances_data as $balance): ?>
                        <?php 
                            $percentage = ($balance['total_days'] > 0) ? 
                                round(($balance['used_days'] / $balance['total_days']) * 100) : 0;
                            
                            // Determine card color based on available days
                            $cardColor = 'primary';
                            if ($balance['available_days'] <= 2) {
                                $cardColor = 'danger';
                            } else if ($balance['available_days'] <= 5) {
                                $cardColor = 'warning';
                            } else if ($balance['available_days'] >= 10) {
                                $cardColor = 'success';
                            }
                        ?>
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-<?php echo $cardColor; ?> shadow h-100 py-2 balance-card">
                                <div class="card-header py-3 bg-<?php echo $cardColor; ?> text-white">
                                    <h6 class="m-0 font-weight-bold"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $balance['leave_type']))); ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?php echo $cardColor; ?> text-uppercase mb-1">
                                                Available Balance
                                            </div>
                                            <div class="balance-value text-gray-800"><?php echo htmlspecialchars($balance['available_days']); ?> days</div>
                                            <div class="progress-container">
                                                <div class="progress">
                                                    <div class="progress-bar bg-<?php echo $cardColor; ?>" role="progressbar" 
                                                        style="width: <?php echo $percentage; ?>%" 
                                                        aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-xs">
                                                    <span class="text-dark">Used: <?php echo htmlspecialchars($balance['used_days']); ?> days</span>
                                                    <span class="float-right">Total: <?php echo htmlspecialchars($balance['total_days']); ?> days</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">No leave balance information available.</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Leave Balance Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Balance Details</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($leave_balances_data)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="leaveBalanceTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Eligible Days</th>
                                        <th>Availed Days</th>
                                        <th>Available Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leave_balances_data as $balance): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $balance['leave_type']))); ?></td>
                                        <td><?php echo htmlspecialchars($balance['total_days']); ?></td>
                                        <td><?php echo htmlspecialchars($balance['used_days']); ?></td>
                                        <td><?php echo htmlspecialchars($balance['available_days']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No leave balance information available.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-4">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <a href="apply.php" class="btn btn-success">Apply for New Leave</a>
                <a href="leave_history.php" class="btn btn-info">View Leave History</a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Cannot display leave balance due to missing employee data. Please contact HR.</div>
        <?php endif; ?>
    </div>
</div>
    <?php include('../footer.php'); ?>
</div>
</div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
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
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
    $(document).ready(function() {
        $('#leaveBalanceTable').DataTable();
    });
    </script>
</body>
</html>
