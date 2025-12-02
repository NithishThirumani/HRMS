<?php
// Include the main session file first
include('../session.php');

// Include other required files
include('../connection.php');
include('includes/leave_functions.php');

// Check if user is logged in (session.php should handle this)
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch employee record by EID
$stmt = $con->prepare("SELECT id, eid, full_name, department_id, doj, gender FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$employee) {
    echo "<div class='alert alert-danger'>Employee record not found. Please contact HR.</div>";
    exit;
}
$employee_id = $employee['id'];

// Fetch department name
$department = ['name' => 'N/A'];
if (!empty($employee['department_id'])) {
    $stmt = $con->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->bind_param("i", $employee['department_id']);
    $stmt->execute();
    $dept_result = $stmt->get_result();
    $department = $dept_result->fetch_assoc() ?: ['name' => 'N/A'];
    $stmt->close();
}

// Calculate leave balances and available days
$leave_balances_data = getLeaveBalances($employee['eid']);
$total_available_days = 0;
foreach ($leave_balances_data as $row) {
    if (!empty($row['is_eligible']) && !empty($row['available_days'])) {
        $total_available_days += $row['available_days'];
    }
}

// Get recent and all leaves for this employee (using numeric ID)
$recent_leaves = getRecentLeavesByEmployeeId($employee_id, 5);
$all_leaves = [];
$stmt = $con->prepare("SELECT type_of_leave, start_date, end_date, total_days, status, recommender_name, approver_name FROM leaves WHERE emp_id = ? ORDER BY applied_at DESC");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $all_leaves[] = $row;
}
$stmt->close();

// Get current/pending leave
$current_leave = null;
$stmt = $con->prepare("SELECT *, recommender_name, approver_name FROM leaves WHERE emp_id = ? AND status = 'Pending' ORDER BY applied_at DESC LIMIT 1");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$current_leave = $result->fetch_assoc();
$stmt->close();

// Get leave balance summary (annual leave)
$leave_balance = getLeaveBalance($employee['eid']);

// Get pending leaves count
$pending_leaves = getPendingLeaves($employee['eid']);
$pending_count = count($pending_leaves);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leave Management Dashboard</title>
    
    <!-- Custom fonts -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include('../topbar.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Success Message Display -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Error Message Display -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Welcome Section -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Leave Management Dashboard</h1>
                    </div>

                    <!-- User Info Card -->
                    <div class="row mb-4">
                        <div class="col-xl-12">
                            <div class="card shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Welcome, <?php echo htmlspecialchars($employee['full_name']); ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Department: <?php echo htmlspecialchars($department['name']); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Balance Cards -->
                    <div class="row mb-4">
                        <!-- Total Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Annual Leaves
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $leave_balance['total']; ?> Days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Used Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Used Leaves
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $leave_balance['used']; ?> Days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remaining Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Remaining Leaves
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $leave_balance['remaining']; ?> Days
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-minus fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Leave Balance Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Available Leave Days</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_available_days; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending Requests</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Balance Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Leave Balances</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Annual Entitlement</th>
                                            <th>Available Days (as per DOJ)</th>
                                            <th>Eligibility Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leave_balances_data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                            <td><?php echo $row['total_days']; ?></td>
                                            <td><?php echo $row['available_days']; ?></td>
                                            <td>
                                                <?php
                                                if ($row['eligibility_date'] === 'Already Eligible') {
                                                    echo '<span class="text-success">Already Eligible</span>';
                                                } else {
                                                    echo '<span class="text-danger">' . $row['eligibility_date'] . '</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Leave Applications -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Leave Applications</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Recommender</th>
                                            <th>Approver</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_leaves as $leave): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($leave['type_of_leave'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('d M Y', strtotime($leave['start_date'] ?? '')); ?></td>
                                            <td><?php echo date('d M Y', strtotime($leave['end_date'] ?? '')); ?></td>
                                            <td><?php echo $leave['total_days'] ?? 'N/A'; ?></td>
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
                                                <span class="badge badge-<?php echo getStatusBadgeClass($leave['status'] ?? 'Pending'); ?>">
                                                    <?php echo $leave['status'] ?? 'N/A'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- All Leave Applications -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">All Leave Applications</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Recommender</th>
                                            <th>Approver</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($all_leaves) > 0): ?>
                                            <?php foreach ($all_leaves as $leave): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leave['type_of_leave'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d M Y', strtotime($leave['start_date'] ?? '')); ?></td>
                                                <td><?php echo date('d M Y', strtotime($leave['end_date'] ?? '')); ?></td>
                                                <td><?php echo $leave['total_days'] ?? 'N/A'; ?></td>
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
                                                    <span class="badge badge-<?php echo getStatusBadgeClass($leave['status'] ?? 'Pending'); ?>">
                                                        <?php echo $leave['status'] ?? 'N/A'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center">No leave applications found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Current Leave Application Status -->
                    <?php if ($current_leave): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Current Leave Application Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($current_leave['type_of_leave'] ?? 'N/A'); ?></p>
                                    <p><strong>Dates:</strong> <?php echo date('d M Y', strtotime($current_leave['start_date'] ?? '')); ?> to <?php echo date('d M Y', strtotime($current_leave['end_date'] ?? '')); ?></p>
                                    <p><strong>Total Days:</strong> <?php echo $current_leave['total_days'] ?? 'N/A'; ?></p>
                                    <p><strong>Status:</strong> <span class="badge badge-warning"><?php echo $current_leave['status'] ?? 'N/A'; ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Recommender:</strong> 
                                        <?php if (!empty($current_leave['recommender_name'])): ?>
                                            <span class="text-info"><?php echo htmlspecialchars($current_leave['recommender_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Approver:</strong> 
                                        <?php if (!empty($current_leave['approver_name'])): ?>
                                            <span class="text-success"><?php echo htmlspecialchars($current_leave['approver_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Current Stage:</strong> <?php echo htmlspecialchars($current_leave['current_approver_role'] ?? 'N/A'); ?></p>
                                    <?php if (!empty($current_leave['next_approver_role'])): ?>
                                        <p><strong>Next Approver:</strong> <?php echo htmlspecialchars($current_leave['next_approver_role']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($current_leave['hod_status'])): ?>
                                <p><strong>HOD Status:</strong> <?php echo htmlspecialchars($current_leave['hod_status']); ?><?php if (!empty($current_leave['hod_remarks'])): ?> (<?php echo htmlspecialchars($current_leave['hod_remarks']); ?>)<?php endif; ?></p>
                            <?php endif; ?>
                            <?php if (!empty($current_leave['hr_status'])): ?>
                                <p><strong>HR Status:</strong> <?php echo htmlspecialchars($current_leave['hr_status']); ?><?php if (!empty($current_leave['hr_remarks'])): ?> (<?php echo htmlspecialchars($current_leave['hr_remarks']); ?>)<?php endif; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- End of Main Content -->
            </div>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html>