<?php
include('session.php');
include('connection.php');

// Verify admin privileges again as a safeguard
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    error_log("Unauthorized access attempt to admin panel: " . ($_SESSION['email'] ?? 'no email set'));
    header("Location: ../login.php");
    exit();
}

// Get admin information
$admin_email = $_SESSION['email'];
$admin_role = $_SESSION['role'];

// Add these queries after your existing queries
// Fetch top 5 employees with maximum leaves
$query = "SELECT e.first_name, COUNT(l.id) as leave_count 
          FROM employees e 
          LEFT JOIN leaves l ON e.id = l.emp_id 
          WHERE l.status = 'approved' 
          AND YEAR(l.start_date) = YEAR(CURRENT_DATE)
          GROUP BY e.id, e.first_name 
          ORDER BY leave_count DESC 
          LIMIT 5";
$leave_result = mysqli_query($con, $query);
if (!$leave_result) {
    throw new Exception("Error in leave query: " . mysqli_error($con));
}
// Fetch employees on leave today
$query = "SELECT COUNT(*) as total_on_leave FROM leaves WHERE status='approved' AND DATE(start_date) <= CURDATE() AND DATE(end_date) >= CURDATE()";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_on_leave = $row['total_on_leave'];

// Fetch birthdays this month
$query = "SELECT COUNT(*) as birthdays_count FROM employees WHERE MONTH(birthday) = MONTH(CURRENT_DATE())";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$birthdays_count = $row['birthdays_count'];

// Fetch work anniversaries this month
$query = "SELECT COUNT(*) as anniversaries_count FROM employees WHERE MONTH(doj) = MONTH(CURRENT_DATE())";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$anniversaries_count = $row['anniversaries_count'];

// Fetch total departments
$query = "SELECT COUNT(*) as total_departments FROM departments";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_departments = $row['total_departments'];

// Fetch documents expiring in next 30 days

$expiring_docs = array();
// Check Visa Expiry
$query = "SELECT 
    'visa' as document_category,
    COUNT(*) as count,
    GROUP_CONCAT(DISTINCT country_of_issue) as nationalities
FROM employees
WHERE visa_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND emp_left_org = 0";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
if ($row) {
    $expiring_docs['visa'] = array(
        'count' => $row['count'],
        'nationalities' => $row['nationalities']
    );
}

// Check Passport Expiry
$query = "SELECT 
    'passport' as document_category,
    COUNT(*) as count,
    GROUP_CONCAT(DISTINCT country_of_issue) as nationalities
FROM employees
WHERE passport_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND emp_left_org = 0";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
if ($row) {
    $expiring_docs['passport'] = array(
        'count' => $row['count'],
        'nationalities' => $row['nationalities']
    );
}

// Check Labor Card Expiry
$query = "SELECT 
    COUNT(*) as count
FROM employees
WHERE labour_card_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND emp_left_org = 0";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
if ($row) {
    $expiring_docs['labour_card'] = array(
        'count' => $row['count']
    );
}



// Fetch absent employees (assuming you have an attendance table)
$query = "SELECT COUNT(*) as total_absent FROM attendance WHERE attendance_date = CURDATE() AND status = 'absent'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_absent = $row['total_absent'];

// Add this with your other queries
$deptQuery = "SELECT name, COUNT(*) as count FROM departments GROUP BY name";
$deptResult = mysqli_query($con, $deptQuery);


// Fetch total monthly costs from salary table
$query = "SELECT SUM(total_salary) AS total_monthly_costs FROM sal";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_monthly_costs = $row['total_monthly_costs'] ?? 0;

// Fetch total annual costs from salary table
$total_annual_costs = $total_monthly_costs * 12;

// Add these queries
$query = "SELECT e.first_name, e.last_name, AVG(p.points) as avg_performance 
          FROM employees e 
          LEFT JOIN project_assignments pa ON e.id = pa.employee_id
          LEFT JOIN projects p ON pa.project_id = p.p_id
          GROUP BY e.id 
          ORDER BY avg_performance DESC 
          LIMIT 5";

// Attendance Overview Data
$attendanceQuery = "SELECT 
    COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent,
    COUNT(CASE WHEN status = 'late' THEN 1 END) as late
FROM attendance 
WHERE attendance_date = CURDATE()";
$attendanceResult = mysqli_query($con, $attendanceQuery);
$attendanceData = mysqli_fetch_assoc($attendanceResult);

// Leave Distribution by Type
$leaveTypeQuery = "SELECT type_of_leave, COUNT(*) as count 
                   FROM leaves 
                   WHERE YEAR(start_date) = YEAR(CURRENT_DATE) 
                   GROUP BY type_of_leave";
$leaveTypeResult = mysqli_query($con, $leaveTypeQuery);

// Recent Timeline Events
$timelineQuery = "SELECT 
    'birthday' as type, first_name, birthday as date 
    FROM employees 
    WHERE MONTH(birthday) = MONTH(CURRENT_DATE)
    UNION ALL
    SELECT 
    'anniversary' as type, first_name, doj as date
    FROM employees 
    WHERE MONTH(doj) = MONTH(CURRENT_DATE)
    ORDER BY date ASC LIMIT 5";
$timelineResult = mysqli_query($con, $timelineQuery);

// Add these queries
$query = "SELECT COUNT(*) as training_due 
          FROM employees 
          WHERE DATEDIFF(CURRENT_DATE, last_training_date) > 365";

$query = "SELECT COUNT(*) as contract_renewal 
          FROM employees 
          WHERE contract_end_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)";

// Fetch total number of submitted projects
$query = "SELECT COUNT(*) AS total_submitted_projects FROM projects WHERE status = 'submitted'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_submitted_projects = $row['total_submitted_projects'] ?? 0;

// Calculate percentage of pending tasks
$pending_tasks_percentage = $total_submitted_projects * 20; // Each submitted project contributes 20%

// Fetch total number of pending leave requests
$query = "SELECT COUNT(*) AS total_pending_leave_requests FROM leaves WHERE status = 'pending'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_pending_leave_requests = $row['total_pending_leave_requests'] ?? 0;

// Data for Leave Trends Chart
$leaveQuery = "SELECT MONTH(start_date) as month, COUNT(*) as count 
               FROM leaves 
               WHERE YEAR(start_date) = YEAR(CURRENT_DATE) 
               GROUP BY MONTH(start_date)";
$leaveResult = mysqli_query($con, $leaveQuery);

$labels = [];
$data = [];
while ($row = mysqli_fetch_assoc($leaveResult)) {
    $labels[] = '"' . date("F", mktime(0, 0, 0, $row['month'], 1)) . '"';
    $data[] = $row['count'];
}

// Data for Department Distribution Chart
$deptQuery = "SELECT d.name, COUNT(e.id) as count 
              FROM departments d 
              LEFT JOIN employees e ON d.id = e.department_id 
              GROUP BY d.id";
$deptResult = mysqli_query($con, $deptQuery);

$deptLabels = [];
$deptData = [];
while ($row = mysqli_fetch_assoc($deptResult)) {
    $deptLabels[] = '"' . $row['name'] . '"';
    $deptData[] = $row['count'];
}

// Fetch total number of employees and trainee
$query = "SELECT 
    COUNT(*) as total_employees,
    SUM(CASE WHEN is_trainee = 1 THEN 1 ELSE 0 END) as trainee_count,
    SUM(CASE WHEN is_trainee = 0 OR is_trainee IS NULL THEN 1 ELSE 0 END) as regular_employees
FROM employees
WHERE role NOT IN ('admin', 'super_admin')
AND status = 'Active'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total_employees'];
$trainee_count = $row['trainee_count'];
$regular_employees = $row['regular_employees'];

// Fetch total number of tours
//$query = "SELECT COUNT(*) AS total_tours FROM tours";
//$result = mysqli_query($con, $query);
//$row = mysqli_fetch_assoc($result);
//$total_tours = $row['total_tours'] ?? 0;

// Fetch total number of events
//$query = "SELECT COUNT(*) AS total_events FROM events";
//$result = mysqli_query($con, $query);
//$row = mysqli_fetch_assoc($result);
//$total_events = $row['total_events'] ?? 0;
$query = "SELECT 
    departments.name AS department_name,
    COUNT(employees.id) AS employee_count 
    FROM employees 
    INNER JOIN departments ON employees.department_id = departments.id 
    GROUP BY departments.name";

$query = "SELECT * FROM projects";
$result = mysqli_query($con, $query);

// Check if 'del' parameter is set in the URL
if (isset($_GET['del'])) {
    // Get the project ID to delete
    $project_id = $_GET['del'];

    // Query to delete the project from the database
    $delete_query = "DELETE FROM projects WHERE p_id='$project_id'";

    // Execute the delete query
    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Record deleted successfully!');</script>";
        echo "<script>window.location.href=window.location.pathname;</script>";
    } else {
        echo "<script>alert('Error deleting record: " . mysqli_error($con) . "');</script>";
        echo "<script>window.location.href=window.location.pathname;</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Admin Dashboard</title>
    <link href="img/favicon.png" rel="icon">
    <!-- PolluxUI CSS -->
    <link rel="stylesheet" href="dashboardtemplate/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="dashboardtemplate/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="dashboardtemplate/css/vertical-layout-light/style.css">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <!-- Add jQuery UI -->
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        .modern-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.3s ease;
            transform: translateZ(0);
            margin-bottom: 1rem;
        }

        .bg-purple-light {
            background: rgba(156, 39, 176, 0.1);
        }

        .bg-purple {
            background: #9c27b0;
        }

        .badge-purple {
            background-color: #9c27b0;
            color: white;
        }

        .modern-card:hover {
            transform: translateY(-5px) translateZ(10px);
            box-shadow: 0 12px 30px 0 rgba(31, 38, 135, 0.25);
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            color: #fff;
            height: 110px;
            padding: 12px;
        }

        /* Update the card body padding */
        .card-body.py-2 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        /* Update the card header */
        .card-header {
            padding: 8px 15px !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #da8cff, #9a55ff);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #84d9d2, #07cdae);
        }

        .bg-gradient-warning {
            background: linear-gradient(45deg, #ffd86f, #fc7a5a);
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #90caf9, #047edf);
        }


        .bg-white-10 {
            background: rgba(255, 255, 255, 0.1);
        }

        .icon-xl {
            font-size: 2rem;
        }

        .alert-card {
            transition: all 0.2s ease;
            padding: 8px !important;
            margin-bottom: 8px !important;
        }

        .alert-card:last-child {
            margin-bottom: 0 !important;
        }

        .alert-card:hover {
            transform: translateX(5px);
        }

        .bg-warning-light {
            background: rgba(255, 193, 7, 0.1);
        }

        .bg-info-light {
            background: rgba(23, 162, 184, 0.1);
        }

        .bg-success-light {
            background: rgba(40, 167, 69, 0.1);
        }

        .btn-light-outline {
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            background: transparent;
            padding: 0.2rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-light-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .badge {
            padding: 0.3em 0.5em;
            font-size: 10px;
            font-weight: 600;
        }

        .icon-box {
            width: 28px;
            height: 28px;
            padding: 4px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .font-weight-medium {
            font-weight: 600;
            font-size: 12px;
        }


        .chart-container {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            padding: 12px;
            height: 150px !important;
            margin: 0;
            transition: all 0.3s ease;
        }

        .chart-container.maximized {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80vw;
            height: 80vh !important;
            z-index: 1000;
            background: white;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <!-- Begin Page Content -->
    <!-- After your PHP queries and before the closing body tag -->

    <div class="container-scroller">
        <!-- Navbar -->

        <div class="d-sm-flex align-items-center justify-content-between mb-4 fade-in">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Welcome, <?php 
                    // Get admin name from database using email
                    $admin_email = $_SESSION['email'];
                    $stmt = mysqli_prepare($con, "SELECT user_name FROM admin WHERE email = ?");
                    mysqli_stmt_bind_param($stmt, "s", $admin_email);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $admin = mysqli_fetch_assoc($result);
                    echo ucfirst($admin['user_name'] ?? 'Admin');
                ?>!</h1>
                <p class="text-muted">Your dashboard overview</p>
            </div>
            <div class="d-flex">
                <a href="export_dashboard.php" class="btn btn-primary btn-icon-split mr-2">
                    <span class="icon text-white-50"><i class="fas fa-download"></i></span>
                    <span class="text">Export Report</span>
                </a>
                <a href="#" class="btn btn-success btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-sync"></i></span>
                    <span class="text">Refresh</span>
                </a>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row mb-3">
            <!-- Total Employees -->
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="view_emp1.php" class="text-decoration-none">
                    <div class="modern-card stat-card bg-gradient-primary animate__animated animate__fadeIn">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Active Employees</h6>
                                <h3 class="text-white mb-0"><?php echo $total_employees; ?></h3>
                                <div class="d-flex text-white-50 small mt-2">
                                    <span class="mr-3">Regular: <?php echo $regular_employees; ?></span>
                                    <span>Trainee: <?php echo $trainee_count; ?></span>
                                </div>
                                <div class="progress mt-2" style="height: 3px;">
                                    <div class="progress-bar bg-white"
                                        style="width: <?php echo $total_employees > 0 ? ($regular_employees / $total_employees) * 100 : 0; ?>%">
                                    </div>
                                    <div class="progress-bar bg-warning"
                                        style="width: <?php echo $total_employees > 0 ? ($trainee_count / $total_employees) * 100 : 0; ?>%">
                                    </div>
                                </div>
                            </div>
                            <div class="icon-box rounded-circle bg-white-10 p-3">
                                <i class="typcn typcn-group-outline text-white icon-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>




            <!-- On Leave Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="modern-card stat-card bg-gradient-success animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small mb-1">On Leave Today</h6>
                            <h3 class="text-white mb-0"><?php echo $total_on_leave; ?></h3>
                            <div class="progress mt-2" style="height: 3px;">
                                <div class="progress-bar bg-white"
                                    style="width: <?php echo $total_employees > 0 ? ($total_on_leave / $total_employees) * 100 : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="icon-box rounded-circle bg-white-10 p-3">
                            <i class="typcn typcn-calendar text-white icon-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Leaves Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="modern-card stat-card bg-gradient-warning animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small mb-1">Pending Leaves</h6>
                            <h3 class="text-white mb-0"><?php echo $total_pending_leave_requests; ?></h3>
                            <a href="leaves.php" class="btn btn-sm btn-light-outline mt-2">Review</a>
                        </div>
                        <div class="icon-box rounded-circle bg-white-10 p-3">
                            <i class="typcn typcn-time text-white icon-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Cost Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="modern-card stat-card bg-gradient-info animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small mb-1">Monthly Cost</h6>
                            <h3 class="text-white mb-0">AED <?php echo number_format($total_monthly_costs, 0); ?></h3>
                            <div class="text-white-50 small mt-2">Annual:
                                <?php echo number_format($total_annual_costs, 2); ?>
                            </div>
                        </div>
                        <div class="icon-box rounded-circle bg-white-10 p-3">
                            <i class="typcn typcn-chart-line text-white icon-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Statistics Row -->
        <div class="row mb-4">
            <!-- Active Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Employees
                                 </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $active_query = "SELECT COUNT(*) as count FROM employees 
                                                   WHERE status = 'Active' 
                                                   AND role NOT IN ('admin', 'super_admin')
                                                   AND (is_trainee = 0 OR is_trainee IS NULL)";
                                    $active_result = mysqli_query($con, $active_query);
                                    $active_count = mysqli_fetch_assoc($active_result)['count'];
                                    echo $active_count;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permanent Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Permanent Employees
                                    
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $permanent_query = "SELECT COUNT(*) as count FROM employees 
                                                      WHERE status = 'Active' 
                                                      AND (is_trainee = 0 OR is_trainee IS NULL)
                                                      AND role NOT IN ('admin', 'super_admin')";
                                    $permanent_result = mysqli_query($con, $permanent_query);
                                    $permanent_count = mysqli_fetch_assoc($permanent_result)['count'];
                                    echo $permanent_count;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inactive Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Inactive Employees
                                   
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $inactive_query = "SELECT COUNT(*) as count FROM employees 
                                                     WHERE status = 'Inactive'
                                                     AND role NOT IN ('admin', 'super_admin')";
                                    $inactive_result = mysqli_query($con, $inactive_query);
                                    $inactive_count = mysqli_fetch_assoc($inactive_result)['count'];
                                    echo $inactive_count;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Employees Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Employees
                                   
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $total_query = "SELECT COUNT(*) as count FROM employees 
                                                  WHERE role NOT IN ('admin', 'super_admin')
                                                  AND status = 'Active'";
                                    $total_result = mysqli_query($con, $total_query);
                                    $total_count = mysqli_fetch_assoc($total_result)['count'];
                                    echo $total_count;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Employee Statistics Row -->

        <!-- Analytics Section -->
        <div class="row mb-4">
            <!-- Critical Alerts Section -->
            <div class="col-xl-4">
                <div class="modern-card h-100 animate__animated animate__fadeIn">
                    <div class="card-header bg-transparent border-0 py-2">
                        <h6 class="text-primary mb-0">Critical Alerts</h6>
                    </div>
                    <div class="card-body py-2">
                        <!-- Visa Alert -->
                        <a href="visapassport_view.php" class="text-decoration-none">
                        <div class="alert-card mb-2 bg-warning-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-warning p-2 mr-2">
                                    <i class="typcn typcn-warning text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Visa Expiring</span>
                                        <span
                                            class="badge badge-warning"><?php echo isset($expiring_docs['visa']) ? $expiring_docs['visa']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                    <?php if (isset($expiring_docs['visa'])): ?>
                                        <small class="d-block text-muted">Nationalities:
                                            <?php echo $expiring_docs['visa']['nationalities']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        </a>

                        <!-- Passport Alert -->
                        <a href="visapassport_view.php" class="text-decoration-none">
                        <div class="alert-card mb-2 bg-info-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-info p-2 mr-2">
                                    <i class="typcn typcn-document text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Passport Expiring</span>
                                        <span
                                            class="badge badge-info"><?php echo isset($expiring_docs['passport']) ? $expiring_docs['passport']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                    <?php if (isset($expiring_docs['passport'])): ?>
                                        <small class="d-block text-muted">Nationalities:
                                            <?php echo $expiring_docs['passport']['nationalities']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
</a>
                        <!-- Emirates ID Alert -->
                        <div class="alert-card mb-2 bg-success-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-success p-2 mr-2">
                                    <i class="typcn typcn-id-badge text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Emirates ID Expiring</span>
                                        <span
                                            class="badge badge-success"><?php echo isset($expiring_docs['emirates_id']) ? $expiring_docs['emirates_id']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                </div>
                            </div>
                        </div>

                        <!-- Labor Card Alert -->
                        <div class="alert-card mb-2 bg-primary-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-primary p-2 mr-2">
                                    <i class="typcn typcn-business-card text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Labor Card Expiring</span>
                                        <span
                                            class="badge badge-primary"><?php echo isset($expiring_docs['labour_card']) ? $expiring_docs['labour_card']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                </div>
                            </div>
                        </div>

                        <!-- Labor Offer Letter Alert -->
                        <div class="alert-card mb-2 bg-danger-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-danger p-2 mr-2">
                                    <i class="typcn typcn-document-text text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Labor Offer Letter Expiring</span>
                                        <span
                                            class="badge badge-danger"><?php echo isset($expiring_docs['labour_offer']) ? $expiring_docs['labour_offer']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Insurance Alert -->
                        <div class="alert-card mb-2 bg-purple-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-purple p-2 mr-2">
                                    <i class="typcn typcn-heart-outline text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-medium">Medical Insurance Expiring</span>
                                        <span
                                            class="badge badge-purple"><?php echo isset($expiring_docs['medical_insurance']) ? $expiring_docs['medical_insurance']['count'] : 0; ?></span>
                                    </div>
                                    <small class="text-muted">Expires in next 30 days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Trends Chart -->
            <div class="col-xl-4">
                <div class="card modern-card h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="font-weight-bold text-primary mb-0">Leave Trends</h6>
                    </div>
                    <div class="chart-container position-relative">
                        <button class="btn btn-sm btn-light maximize-btn">
                            <i class="fas fa-expand"></i>
                        </button>
                        <canvas id="leaveChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Department Distribution Chart -->
            <div class="col-xl-4">
                <div class="card modern-card h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="font-weight-bold text-primary mb-0">Department Distribution</h6>
                    </div>
                    <div class="chart-container position-relative">
                        <button class="btn btn-sm btn-light maximize-btn">
                            <i class="fas fa-expand"></i>
                        </button>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mb-4">
            <div class="col-xl-4">
                <div class="card modern-card h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="font-weight-bold text-primary mb-0">Attendance Overview</h6>
                    </div>
                    <div class="chart-container position-relative">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card modern-card h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="font-weight-bold text-primary mb-0">Leave Distribution</h6>
                    </div>
                    <div class="chart-container position-relative">
                        <canvas id="leaveTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card modern-card h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="font-weight-bold text-primary mb-0">Recent Events</h6>
                    </div>
                    <div class="timeline-container p-3" style="max-height: 250px; overflow-y: auto;">
                        <?php while ($event = mysqli_fetch_assoc($timelineResult)): ?>
                            <div class="d-flex mb-3">
                                <div
                                    class="icon-box rounded-circle <?php echo $event['type'] == 'birthday' ? 'bg-warning' : 'bg-info'; ?> p-2 mr-3">
                                    <i
                                        class="typcn <?php echo $event['type'] == 'birthday' ? 'typcn-gift' : 'typcn-calendar'; ?> text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 font-weight-medium"><?php echo $event['first_name']; ?></h6>
                                    <p class="text-muted small mb-0">
                                        <?php echo $event['type'] == 'birthday' ? 'Birthday' : 'Work Anniversary'; ?> on
                                        <?php echo date('M d', strtotime($event['date'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Table -->
        



        <?php
        include_once('footer.php');
        ?>
        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>
</div>
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
                <a class="btn btn-success" href="../login.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts -->
<script src="js/sb-admin-2.min.js"></script>

<!-- Chart.js -->
<script src="dashboardtemplate/vendors/chart.js/Chart.min.js"></script>


<!-- Logout handler -->


<!-- Chart configurations -->
<script>
    // Chart configurations
    var leaveChartConfig = {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', $labels); ?>],
            datasets: [{
                label: 'Leave Trends',
                data: [<?php echo implode(',', $data); ?>],
                borderColor: 'rgba(147, 104, 255, 0.8)',
                backgroundColor: 'rgba(147, 104, 255, 0.15)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(147, 104, 255, 1)',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBorderWidth: 3,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(147, 104, 255, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    bodyFont: {
                        size: 12
                    },
                    borderColor: 'rgba(147, 104, 255, 0.3)',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(200, 200, 200, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#666'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#666'
                    }
                }
            }
        }
    };

    var departmentConfig = {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', $deptLabels); ?>],
            datasets: [{
                data: [<?php echo implode(',', $deptData); ?>],
                backgroundColor: [
                    'rgba(147, 104, 255, 0.8)',
                    'rgba(71, 195, 179, 0.8)',
                    'rgba(255, 181, 71, 0.8)',
                    'rgba(95, 150, 255, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 10,
                        padding: 15,
                        font: {
                            size: 11
                        },
                        color: '#666'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    bodyFont: {
                        size: 12
                    },
                    borderColor: 'rgba(147, 104, 255, 0.3)',
                    borderWidth: 1,
                    padding: 10
                }
            }
        }
    };

    // Add modern chart styles
    document.head.insertAdjacentHTML('beforeend', `
    <style>
        .chart-container {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px;
            height: 250px;
            border: 1px solid rgba(147, 104, 255, 0.1);
        }
        
        .chart-container:hover {
            box-shadow: 0 8px 32px rgba(147, 104, 255, 0.1);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .maximize-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }

        .maximize-btn:hover {
            opacity: 1;
        }
    </style>
    `);

    // Debug information
    console.log("DOM Content Loaded");

    // Initialize charts with a slight delay to ensure DOM elements are ready
    window.addEventListener('load', function () {
        console.log("Window loaded");
        setTimeout(function () {
            try {
                console.log("Initializing charts");

                // Check if chart elements exist
                console.log("Chart elements:", {
                    leaveChart: document.getElementById('leaveChart'),
                    departmentChart: document.getElementById('departmentChart'),
                    attendanceChart: document.getElementById('attendanceChart'),
                    leaveTypeChart: document.getElementById('leaveTypeChart')
                });

                // Initialize Leave Trends Chart
                if (document.getElementById('leaveChart')) {
                    var leaveCtx = document.getElementById('leaveChart').getContext('2d');
                    new Chart(leaveCtx, leaveChartConfig);
                    console.log("Leave chart initialized");
                }

                // Initialize Department Distribution Chart
                if (document.getElementById('departmentChart')) {
                    var deptCtx = document.getElementById('departmentChart').getContext('2d');
                    new Chart(deptCtx, departmentConfig);
                    console.log("Department chart initialized");
                }

                // Initialize Attendance Chart
                if (document.getElementById('attendanceChart')) {
                    var attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
                    new Chart(attendanceCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Present', 'Absent', 'Late'],
                            datasets: [{
                                data: [
                                    <?php echo $attendanceData['present'] ?? 0; ?>,
                                    <?php echo $attendanceData['absent'] ?? 0; ?>,
                                    <?php echo $attendanceData['late'] ?? 0; ?>
                                ],
                                backgroundColor: ['#47C3B3', '#FF6B6B', '#FFB547']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%'
                        }
                    });
                    console.log("Attendance chart initialized");
                }

                // Initialize Leave Type Chart with error handling
                if (document.getElementById('leaveTypeChart')) {
                    var leaveTypeCtx = document.getElementById('leaveTypeChart').getContext('2d');

                    // Prepare data with error handling
                    var leaveTypeLabels = [];
                    var leaveTypeData = [];

                    <?php
                    if (isset($leaveTypeResult) && $leaveTypeResult) {
                        mysqli_data_seek($leaveTypeResult, 0);
                        while ($row = mysqli_fetch_assoc($leaveTypeResult)) {
                            echo "leaveTypeLabels.push('" . $row['type_of_leave'] . "');";
                            echo "leaveTypeData.push(" . $row['count'] . ");";
                        }
                    }
                    ?>

                    new Chart(leaveTypeCtx, {
                        type: 'bar',
                        data: {
                            labels: leaveTypeLabels,
                            datasets: [{
                                data: leaveTypeData,
                                backgroundColor: '#9368FF'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                    console.log("Leave type chart initialized");
                }

                // Add maximize functionality
                document.querySelectorAll('.maximize-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const container = this.closest('.chart-container');
                        container.classList.toggle('maximized');
                        if (container.classList.contains('maximized')) {
                            container.style.height = '500px';
                            this.innerHTML = '<i class="fas fa-compress"></i>';
                        } else {
                            container.style.height = '250px';
                            this.innerHTML = '<i class="fas fa-expand"></i>';
                        }
                    });
                });

            } catch (e) {
                console.error("Error initializing charts:", e);
            }
        }, 500); // 500ms delay to ensure DOM is fully loaded
    });
</script>



<!-- DataTables -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/demo/datatables-demo.js"></script>
</body>

</html>