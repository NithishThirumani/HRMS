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

// Fetch employee data
$emp_query = "SELECT eid, full_name, department_id, doj, gender FROM employees WHERE eid = ?";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

$doj = $emp['doj'];
$gender = strtolower(trim($emp['gender']));
$today = new DateTime();
$doj_date = new DateTime($doj);

// Fetch all leave policies
$policies = [];
$res = $con->query("SELECT * FROM leave_policies");
while ($row = $res->fetch_assoc()) {
    $policies[] = $row;
}

$leave_balances_data = [];
$total_available_days = 0;
$shown_types = [];

foreach ($policies as $policy) {
    $leave_type = $policy['leave_type'];
    if (in_array($leave_type, $shown_types)) continue; // Skip duplicates

    $max_days = isset($policy['max_days']) ? (int)$policy['max_days'] : 0;
    $min_service_months = isset($policy['min_service_months']) ? (int)$policy['min_service_months'] : 0;
    $gender_restriction = isset($policy['gender_restriction']) ? strtolower(trim($policy['gender_restriction'])) : 'all';
    $monthly_accrual = isset($policy['monthly_accrual']) ? (float)$policy['monthly_accrual'] : 0.0;

    // Gender check
    if ($gender_restriction !== 'all' && $gender_restriction !== '' && $gender_restriction !== null) {
        if ($gender_restriction !== strtolower($gender)) continue;
    }

    // DOJ eligibility check
    $eligible_date = clone $doj_date;
    $eligible_date->modify("+{$min_service_months} months");
    $is_eligible = ($today >= $eligible_date);

    // Pro-rata calculation (if eligible and monthly_accrual > 0)
    $interval = $doj_date->diff($today);
    $months_worked = ($interval->y * 12) + $interval->m;
    $pro_rata = $max_days;
    if ($monthly_accrual > 0 && $months_worked < 12) {
        $pro_rata = min($max_days, round($monthly_accrual * $months_worked));
    }

    // Only show if max_days > 0
    if ($max_days <= 0) continue;

    $leave_balances_data[] = [
        'leave_type' => $leave_type,
        'annual_entitlement' => $max_days,
        'available_days' => $is_eligible ? $pro_rata : 0,
        'eligibility_date' => $is_eligible ? 'Already Eligible' : $eligible_date->format('d-m-Y')
    ];

    // Only sum if eligible
    if ($is_eligible) {
        $total_available_days += $pro_rata;
    }

    $shown_types[] = $leave_type;
}

// Get user's leave data
$emp_id = $_SESSION['eid'];
$leave_balances = getLeaveBalances($emp_id);
$pending_leaves = getPendingLeaves($emp_id);
$recent_leaves = getRecentLeaves($emp_id, 5);

// Count pending leaves
$pending_count = count($pending_leaves);

// Get employee's information
$stmt = $con->prepare("SELECT id, full_name, department_id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// Get department name
$stmt = $con->prepare("SELECT name FROM departments WHERE id = ?");
$stmt->bind_param("i", $employee['department_id']);
$stmt->execute();
$dept_result = $stmt->get_result();
$department = $dept_result->fetch_assoc();

// Get leave balance
$leave_balance = getLeaveBalance($_SESSION['eid']);

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
                                            <td><?php echo $row['annual_entitlement']; ?></td>
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
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_leaves as $leave): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($leave['type_of_leave']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($leave['start_date'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($leave['end_date'])); ?></td>
                                            <td><?php echo $leave['total_days']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo getStatusBadgeClass($leave['status']); ?>">
                                                    <?php echo $leave['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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