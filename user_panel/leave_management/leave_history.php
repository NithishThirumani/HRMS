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
    }
    $stmt_emp->close();
} else {
    $errors[] = "Error preparing employee data query: " . $con->error;
}

// Get leave history for the employee using HR panel logic but filtered for this user
$leave_history = [];
if ($emp_data) {
    $query = "SELECT l.*, 
                     COALESCE(e.full_name, l.user_name) as full_name, 
                     COALESCE(e.eid, l.emp_id) as eid, 
                     d.name as dept_name,
                     lh_recommender.recommender_id,
                     lh_approver.approver_id
              FROM leaves l
              LEFT JOIN employees e ON l.emp_id = e.id
              LEFT JOIN departments d ON e.department_id = d.id
              LEFT JOIN leave_hierarchy lh_recommender ON l.emp_id = lh_recommender.employee_id AND lh_recommender.type = 'recommender'
              LEFT JOIN leave_hierarchy lh_approver ON l.emp_id = lh_approver.employee_id AND lh_approver.type = 'approver'
              WHERE l.emp_id = ?
              ORDER BY l.applied_at DESC
              LIMIT 100";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $emp_data['id']); // Use numeric employee ID
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Get recommender and approver names
        $recommender_name = '';
        $approver_name = '';
        
        if (!empty($row['recommender_id'])) {
            $rec_query = "SELECT full_name FROM employees WHERE id = ?";
            $rec_stmt = $con->prepare($rec_query);
            $rec_stmt->bind_param("i", $row['recommender_id']);
            $rec_stmt->execute();
            $rec_result = $rec_stmt->get_result();
            if ($rec_row = $rec_result->fetch_assoc()) {
                $recommender_name = $rec_row['full_name'];
            }
            $rec_stmt->close();
        }
        
        if (!empty($row['approver_id'])) {
            $app_query = "SELECT full_name FROM employees WHERE id = ?";
            $app_stmt = $con->prepare($app_query);
            $app_stmt->bind_param("i", $row['approver_id']);
            $app_stmt->execute();
            $app_result = $app_stmt->get_result();
            if ($app_row = $app_result->fetch_assoc()) {
                $approver_name = $app_row['full_name'];
            }
            $app_stmt->close();
        }
        
        // Add the names to the row
        $row['recommender_name'] = $recommender_name;
        $row['approver_name'] = $approver_name;
        
        $leave_history[] = $row;
    }
    $stmt->close();
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
    <title>Leave History</title>
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
        <h2>Leave History</h2>

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

            <!-- Leave History Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Leave History</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($leave_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="leaveHistoryTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Reason</th>
                                        <th>Recommender</th>
                                        <th>Approver</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leave_history as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $leave['type_of_leave']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['start_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['end_date']))); ?></td>
                                        <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                        <td>
                                            <?php if (!empty($leave['recommender_name'])): ?>
                                                <span class="text-info"><?php echo htmlspecialchars($leave['recommender_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($leave['approver_name'])): ?>
                                                <span class="text-success"><?php echo htmlspecialchars($leave['approver_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo getStatusBadgeClass($leave['status']); ?>">
                                                <?php echo htmlspecialchars($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($leave['applied_at']))); ?></td>
                                        <td>
                                            <a href="../../hr_panel/leave_management/leave_slip.php?leave_id=<?php echo $leave['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf"></i> Download Leave Slip
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No leave history found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-4">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <a href="apply.php" class="btn btn-success">Apply for New Leave</a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Cannot display leave history due to missing employee data. Please contact HR.</div>
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
                    <a class="btn btn-success" href="../logout.php">Logout</a>
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
        $('#leaveHistoryTable').DataTable({
            "order": [[6, "desc"]] // Sort by applied date (column 6) in descending order
        });
    });
    </script>
</body>
</html>
