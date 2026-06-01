<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../session.php');
include('../connection.php');
include('../../includes/email_functions.php');

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Debug: Log the current user's EID
error_log("HOD Panel - Current EID: " . $eid);

// Get the numeric ID of the current HOD
$hod_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $hod_numeric_id = $result->fetch_assoc()['id'];
    error_log("HOD Panel - HOD Numeric ID: " . $hod_numeric_id);
} else {
    error_log("HOD Panel - Error: Could not find numeric ID for EID: " . $eid);
    $_SESSION['error'] = "Error: Could not find your employee record. Please contact HR.";
    header("Location: ../index.php");
    exit();
}

// Get HOD's department
$dept_query = "SELECT department_id FROM employees WHERE eid = ?";
$stmt_dept = $con->prepare($dept_query);
$stmt_dept->bind_param("s", $eid);
$stmt_dept->execute();
$dept_result = $stmt_dept->get_result();
$hod_dept = $dept_result->fetch_assoc();

if ($hod_dept && $hod_dept['department_id']) {
    error_log("HOD Panel - HOD Department ID: " . $hod_dept['department_id']);
}

// Handle recommendation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action']; // 'recommend' or 'not_recommend'
    $remarks = $_POST['remarks'] ?? '';
    
    // Debug: Log the POST data
    error_log("HOD Panel - POST data: " . print_r($_POST, true));
    
    // Get leave details
    $query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email 
              FROM leaves l 
              LEFT JOIN employees e ON l.emp_id = e.eid 
              WHERE l.id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if ($leave) {
        $recommendation_status = ($action == 'recommend') ? 'Recommended' : 'Not Recommended';
        $action_date = date('Y-m-d H:i:s');
        
        // Update leave status
        $update_query = "UPDATE leaves SET 
                        recommender_status = ?, 
                        recommender_remarks = ?, 
                        recommender_action_date = ?, 
                        recommender_id = ?,
                        hod_status = ?,
                        hod_remarks = ?,
                        hod_action_date = ?,
                        hod_by = ?
                        WHERE id = ?";
        $stmt_update = $con->prepare($update_query);
        
        if ($stmt_update) {
            $bind_result = $stmt_update->bind_param("ssssssssi", $recommendation_status, $remarks, $action_date, $eid, 
                                    $recommendation_status, $remarks, $action_date, $eid, $leave_id);
            
            if ($bind_result && $stmt_update->execute()) {
                // Log success
                error_log("HOD Panel - Leave recommendation updated successfully for leave ID: " . $leave_id);
                
                // Try to send email notification (but don't fail if email fails)
                try {
                    // Prepare leave data for email
                    $leave_data = array(
                        'employee_name' => $leave['employee_name'],
                        'leave_type' => $leave['type_of_leave'],
                        'start_date' => $leave['start_date'],
                        'end_date' => $leave['end_date'],
                        'total_days' => $leave['total_days'],
                        'reason' => $leave['reason'],
                        'recommender_remarks' => $remarks
                    );
                    
                    // Get recommender name and email
                    $recommender_query = "SELECT full_name, email FROM employees WHERE eid = ?";
                    $stmt_recommender = $con->prepare($recommender_query);
                    $stmt_recommender->bind_param("s", $eid);
                    $stmt_recommender->execute();
                    $recommender_result = $stmt_recommender->get_result();
                    $recommender = $recommender_result->fetch_assoc();
                    $recommender_name = $recommender['full_name'] ?? 'HOD';
                    $recommender_email = $recommender['email'] ?? null;
                    
                    // Get approver email if exists
                    $approver_email = null;
                    if (!empty($leave['approver_id'])) {
                        $approver_email = getEmployeeEmail($con, $leave['approver_id']);
                    }
                    
                    // Get applicant email
                    $applicant_email = getEmployeeEmail($con, $leave['emp_id']);
                    
                    // Send email notification
                    sendLeaveRecommendationEmail($leave_data, $recommender_name, $recommendation_status, $applicant_email, $approver_email);
                    error_log("HOD Panel - Email notification sent successfully");
                } catch (Exception $e) {
                    error_log("HOD Panel - Email notification failed: " . $e->getMessage());
                    // Don't fail the whole process if email fails
                }
                
                $_SESSION['success'] = "Leave application " . strtolower($recommendation_status) . " successfully!";
            } else {
                error_log("HOD Panel - Error updating leave recommendation: " . $con->error);
                $_SESSION['error'] = "Error updating leave recommendation: " . $con->error;
            }
        } else {
            error_log("HOD Panel - Error preparing update statement: " . $con->error);
            $_SESSION['error'] = "Error preparing update statement: " . $con->error;
        }
    } else {
        error_log("HOD Panel - Leave application not found for ID: " . $leave_id);
        $_SESSION['error'] = "Leave application not found!";
    }
    
    // Redirect after action
    header("Location: pending_leaves.php");
    exit();
}

// Simple query to fetch pending leaves for HOD
// Shows leaves where HOD needs to take action (recommend or approve)
// Excludes leaves that have already been acted upon by this HOD
$query = "SELECT DISTINCT l.*, e.full_name as employee_name, e.email as employee_email,
          d.name as dept_name, e.department_id,
          CASE 
            WHEN (l.recommender_id IS NULL OR l.recommender_id = 0) THEN 'Recommend'
            WHEN l.recommender_id = ? AND (l.approver_id IS NULL OR l.approver_id = 0) THEN 'Approve'
            ELSE 'Review'
          END as action_required
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.status = 'Pending' 
          AND (
              l.hod_id = ? 
              OR EXISTS (
                  SELECT 1 FROM leave_hierarchy lh 
                  WHERE lh.employee_id = e.id 
                  AND (lh.recommender_id = ? OR lh.approver_id = ?)
              )
          )
          AND (
              -- For recommendation: HOD hasn't recommended yet
              ((l.recommender_id IS NULL OR l.recommender_id = 0) AND EXISTS (
                  SELECT 1 FROM leave_hierarchy lh 
                  WHERE lh.employee_id = e.id 
                  AND lh.recommender_id = ? 
                  AND lh.type = 'recommender'
              ))
              OR
              -- For approval: HOD has recommended but hasn't approved yet
              (l.recommender_id = ? AND (l.approver_id IS NULL OR l.approver_id = 0) AND EXISTS (
                  SELECT 1 FROM leave_hierarchy lh 
                  WHERE lh.employee_id = e.id 
                  AND lh.approver_id = ? 
                  AND lh.type = 'approver'
              ))
              OR
              -- For direct HOD assignment
              (l.hod_id = ? AND (l.recommender_id IS NULL OR l.recommender_id = 0))
          )
          ORDER BY l.applied_at DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("iiiiiiii", $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id);
$stmt->execute();
$result = $stmt->get_result();

// Log the number of results for monitoring
error_log("HOD Panel - Number of pending leaves found: " . $result->num_rows);

// Debug: Log current HOD info
error_log("HOD Panel - Current HOD ID: " . $hod_numeric_id);
error_log("HOD Panel - Current HOD EID: " . $_SESSION['eid']);

// Show debug info only if debug parameter is set
if (isset($_GET['debug'])) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<h4>🔍 CURRENT STATE DEBUG:</h4>";
    echo "<p><strong>Current EID:</strong> " . ($_SESSION['eid'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>HOD Numeric ID:</strong> " . ($hod_numeric_id ?? 'NOT SET') . "</p>";
    echo "<p><strong>Department ID:</strong> " . ($hod_dept['department_id'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>Number of Pending Leaves:</strong> " . $result->num_rows . "</p>";
    echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
    echo "<p><strong>POST Data:</strong> " . (empty($_POST) ? 'EMPTY' : 'PRESENT') . "</p>";
    
    // Debug: Show some sample leave data
    if ($result->num_rows > 0) {
        echo "<h5>Sample Leave Data:</h5>";
        $sample_leaves = [];
        $counter = 0;
        while ($row = $result->fetch_assoc() && $counter < 3) {
            $sample_leaves[] = $row;
            $counter++;
        }
        foreach ($sample_leaves as $leave) {
            echo "<p><strong>Leave ID:</strong> " . $leave['id'] . 
                 " | <strong>Status:</strong> " . $leave['status'] . 
                 " | <strong>Recommender ID:</strong> " . ($leave['recommender_id'] ?? 'NULL') . 
                 " | <strong>Approver ID:</strong> " . ($leave['approver_id'] ?? 'NULL') . 
                 " | <strong>Action Required:</strong> " . $leave['action_required'] . "</p>";
        }
        // Reset result pointer
        $result->data_seek(0);
    }
    
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pending Leave Requests - HOD Panel</title>
    <link href="img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet"> 
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">     
    <link rel="stylesheet" href="../css/custom.css">
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/search.js"></script>
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
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .table th {
            background-color: #f8f9fc;
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
        }

        .btn-action {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('../header.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username'] ?? 'HOD'; ?></span>
                                <img class="img-profile rounded-circle" src="../img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Pending Leave Requests</h1>
                        <a href="../index.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
                        </a>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Pending Leaves Card -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Pending Leave Applications</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Actions:</div>
                                            <a class="dropdown-item" href="#" onclick="refreshTable()">
                                                <i class="fas fa-sync-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                                Refresh
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="pendingLeavesTable" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Leave Type</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Total Days</th>
                                                        <th>Reason</th>
                                                        <th>Action Required</th>
                                                        <th>Remarks</th>
                                                        <th>Applied On</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3">
                                                                        <span class="text-white font-weight-bold">
                                                                            <?php echo strtoupper(substr($row['employee_name'], 0, 1)); ?>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <div class="font-weight-bold"><?php echo $row['employee_name']; ?></div>
                                                                        <small class="text-muted"><?php echo $row['emp_id']; ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info"><?php echo $row['type_of_leave']; ?></span>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($row['end_date'])); ?></td>
                                                            <td>
                                                                <span class="badge badge-secondary"><?php echo $row['total_days']; ?> day(s)</span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                                        onclick="viewReason('<?php echo addslashes($row['reason']); ?>')">
                                                                    <i class="fas fa-eye"></i> View Reason
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <?php if ($row['action_required'] == 'Recommend'): ?>
                                                                    <span class="badge badge-warning">Recommend</span>
                                                                <?php elseif ($row['action_required'] == 'Approve'): ?>
                                                                    <span class="badge badge-success">Approve</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-info">Review</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($row['recommender_remarks'])): ?>
                                                                    <span class="text-muted"><?php echo htmlspecialchars($row['recommender_remarks']); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo date('M d, Y H:i', strtotime($row['applied_at'])); ?></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <?php if ($row['action_required'] == 'Recommend'): ?>
                                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                                onclick="recommendLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-check"></i> Recommend
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                                onclick="notRecommendLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-times"></i> Not Recommend
                                                                        </button>
                                                                    <?php elseif ($row['action_required'] == 'Approve'): ?>
                                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                                onclick="approveLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-check"></i> Approve
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                                onclick="rejectLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-times"></i> Reject
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                                onclick="recommendLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-check"></i> Recommend
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                                onclick="notRecommendLeave(<?php echo $row['id']; ?>)">
                                                                            <i class="fas fa-times"></i> Not Recommend
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <button type="button" class="btn btn-sm btn-info" 
                                                                            onclick="viewDetails(<?php echo $row['id']; ?>)">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                            <h5 class="text-gray-500">No Pending Leave Requests</h5>
                                            <p class="text-gray-400">There are currently no leave applications waiting for your recommendation.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
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
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendation Modal -->
    <div class="modal fade" id="recommendationModal" tabindex="-1" role="dialog" aria-labelledby="recommendationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Recommend Leave Application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="recommendationForm" method="POST">
                    <div class="modal-body">
                        <p id="modalMessage">Are you sure you want to recommend this leave application?</p>
                        <input type="hidden" id="leaveId" name="leave_id">
                        <input type="hidden" id="actionType" name="action">
                        <div class="form-group">
                            <label for="remarks">Remarks (Optional):</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks or comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn">
                            <span id="submitText">Recommend</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Leave Details Modal -->
    <div class="modal fade" id="viewLeaveModal" tabindex="-1" role="dialog" aria-labelledby="viewLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLeaveModalLabel">Leave Application Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewLeaveModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading leave details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Datepicker JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#pendingLeavesTable').DataTable({
                "order": [[6, "desc"]], // Sort by applied date descending
                "pageLength": 10,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "emptyTable": "No pending leave requests found",
                    "zeroRecords": "No matching records found"
                }
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top',
                html: true
            });
        });

        function recommendLeave(leaveId) {
            console.log('🔵 recommendLeave called with leaveId:', leaveId);
            
            // Validate input
            if (!leaveId || leaveId <= 0) {
                console.error('❌ Invalid leaveId:', leaveId);
                alert('Error: Invalid leave ID');
                return;
            }
            
            // Set form values
            $('#leaveId').val(leaveId);
            $('#actionType').val('recommend');
            $('#modalTitle').text('Recommend Leave Application');
            $('#modalMessage').text('Are you sure you want to recommend this leave application?');
            $('#submitBtn').removeClass('btn-warning').addClass('btn-success');
            $('#submitText').text('Recommend');
            
            console.log('📝 Form values set:');
            console.log('- leaveId:', $('#leaveId').val());
            console.log('- actionType:', $('#actionType').val());
            console.log('- modalTitle:', $('#modalTitle').text());
            
            // Show modal
            console.log('🚀 About to show modal');
            try {
                $('#recommendationModal').modal('show');
                console.log('✅ Modal show command executed successfully');
            } catch (error) {
                console.error('❌ Error showing modal:', error);
                alert('❌ Error opening modal: ' + error.message);
            }
        }

        function notRecommendLeave(leaveId) {
            console.log('🔴 notRecommendLeave called with leaveId:', leaveId);
            
            // Validate input
            if (!leaveId || leaveId <= 0) {
                console.error('❌ Invalid leaveId:', leaveId);
                alert('Error: Invalid leave ID');
                return;
            }
            
            // Set form values
            $('#leaveId').val(leaveId);
            $('#actionType').val('reject');
            $('#modalTitle').text('Reject Leave Application');
            $('#modalMessage').text('Are you sure you want to reject this leave application? Please provide a reason.');
            $('#submitBtn').removeClass('btn-success').addClass('btn-warning');
            $('#submitText').text('Reject');
            
            console.log('📝 Form values set:');
            console.log('- leaveId:', $('#leaveId').val());
            console.log('- actionType:', $('#actionType').val());
            console.log('- modalTitle:', $('#modalTitle').text());
            
            // Show modal
            console.log('🚀 About to show modal');
            try {
                $('#recommendationModal').modal('show');
                console.log('✅ Modal show command executed successfully');
            } catch (error) {
                console.error('❌ Error showing modal:', error);
                alert('❌ Error opening modal: ' + error.message);
            }
        }

        function approveLeave(leaveId) {
            console.log('✅ approveLeave called with leaveId:', leaveId);
            
            // Validate input
            if (!leaveId || leaveId <= 0) {
                console.error('❌ Invalid leaveId:', leaveId);
                alert('Error: Invalid leave ID');
                return;
            }
            
            // Set form values
            $('#leaveId').val(leaveId);
            $('#actionType').val('approve');
            $('#modalTitle').text('Approve Leave Application');
            $('#modalMessage').text('Are you sure you want to approve this leave application?');
            $('#submitBtn').removeClass('btn-warning btn-danger').addClass('btn-success');
            $('#submitText').text('Approve');
            
            console.log('📝 Form values set:');
            console.log('- leaveId:', $('#leaveId').val());
            console.log('- actionType:', $('#actionType').val());
            console.log('- modalTitle:', $('#modalTitle').text());
            
            // Show modal
            console.log('🚀 About to show modal');
            try {
                $('#recommendationModal').modal('show');
                console.log('✅ Modal show command executed successfully');
            } catch (error) {
                console.error('❌ Error showing modal:', error);
                alert('❌ Error opening modal: ' + error.message);
            }
        }

        function rejectLeave(leaveId) {
            console.log('❌ rejectLeave called with leaveId:', leaveId);
            
            // Validate input
            if (!leaveId || leaveId <= 0) {
                console.error('❌ Invalid leaveId:', leaveId);
                alert('Error: Invalid leave ID');
                return;
            }
            
            // Set form values
            $('#leaveId').val(leaveId);
            $('#actionType').val('reject');
            $('#modalTitle').text('Reject Leave Application');
            $('#modalMessage').text('Are you sure you want to reject this leave application? Please provide a reason.');
            $('#submitBtn').removeClass('btn-success btn-warning').addClass('btn-danger');
            $('#submitText').text('Reject');
            
            console.log('📝 Form values set:');
            console.log('- leaveId:', $('#leaveId').val());
            console.log('- actionType:', $('#actionType').val());
            console.log('- modalTitle:', $('#modalTitle').text());
            
            // Show modal
            console.log('🚀 About to show modal');
            try {
                $('#recommendationModal').modal('show');
                console.log('✅ Modal show command executed successfully');
            } catch (error) {
                console.error('❌ Error showing modal:', error);
                alert('❌ Error opening modal: ' + error.message);
            }
        }

        function viewDetails(leaveId) {
            console.log('👁️ viewDetails called with leaveId:', leaveId);
            
            // Validate input
            if (!leaveId || leaveId <= 0) {
                console.error('❌ Invalid leaveId:', leaveId);
                alert('Error: Invalid leave ID');
                return;
            }
            
            // Show loading in modal
            $('#viewLeaveModalBody').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading leave details...</p>
                </div>
            `);
            
            // Show modal
            console.log('🚀 About to show view modal');
            try {
                $('#viewLeaveModal').modal('show');
                console.log('✅ View modal show command executed successfully');
            } catch (error) {
                console.error('❌ Error showing view modal:', error);
                alert('❌ Error opening view modal: ' + error.message);
            }
            
            // Load leave details via AJAX
            $.ajax({
                url: 'get_leave_details.php',
                type: 'GET',
                data: { leave_id: leaveId },
                success: function(response) {
                    console.log('✅ AJAX response received');
                    $('#viewLeaveModalBody').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX error:', error);
                    $('#viewLeaveModalBody').html(`
                        <div class="alert alert-danger">
                            <h6>Error Loading Leave Details</h6>
                            <p>Failed to load leave details. Please try again.</p>
                            <small>Error: ${error}</small>
                        </div>
                    `);
                }
            });
        }

        function viewReason(reason) {
            console.log('📝 viewReason called with reason:', reason);
            alert('Leave Reason:\n\n' + reason);
        }

        function refreshTable() {
            console.log('🔄 refreshTable called');
            location.reload();
        }

        // Form submission handler
        $('#recommendationForm').on('submit', function(e) {
            e.preventDefault();
            
            const actionType = $('#actionType').val();
            const remarks = $('#remarks').val().trim();
            
            // Validate remarks for rejection
            if (actionType === 'reject' && remarks === '') {
                alert('Please provide a reason for rejection.');
                $('#remarks').focus();
                return false;
            }
            
            // Submit the form
            console.log('📤 Submitting form with action:', actionType);
            this.submit();
        });
    </script>
</body>
</html> 