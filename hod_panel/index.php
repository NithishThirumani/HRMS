<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'HOD') {
    header('Location: ../login.php');
    exit();
}
include('session.php');
include('connection.php');

// Get department ID from session with error handling
if (!isset($_SESSION['department_id'])) {
    // Redirect to login if department_id is not set
    header("Location: /emps/login.php");
    exit();
}
$department_id = $_SESSION['department_id'];

// Fetch employees count in department
$query = "SELECT COUNT(*) AS total_employees 
          FROM employees 
          WHERE department_id = '$department_id'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total_employees'] ?? 0;


$leave_result = mysqli_query($con, $query);
// Fetch employees on leave today
$query = "SELECT COUNT(*) as total_on_leave FROM leaves WHERE status='approved' AND DATE(start_date) <= CURDATE() AND DATE(end_date) >= CURDATE()";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_on_leave = $row['total_on_leave'];


// Fetch employees count in specific department
$dept_query = "SELECT COUNT(*) AS dept_employees 
               FROM employees 
               WHERE department_id = '$department_id'";
$dept_result = mysqli_query($con, $dept_query);
$dept_row = mysqli_fetch_assoc($dept_result);
$dept_employees = $dept_row['dept_employees'] ?? 0;

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

// Fetch visa expiring in next 30 days
$query = "SELECT COUNT(*) as visa_expiring FROM employees WHERE visa_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$visa_expiring = $row['visa_expiring'];

// Fetch labor cards expiring in next 30 days
$query = "SELECT COUNT(*) as labor_cards_expiring FROM employees WHERE labour_card_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$labor_cards_expiring = $row['labor_cards_expiring'];

// Fetch absent employees (assuming you have an attendance table)
$query = "SELECT COUNT(*) as total_absent FROM attendance WHERE attendance_date = CURDATE() AND status = 'absent'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_absent = $row['total_absent'];

// Add this with your other queries
$deptQuery = "SELECT name, COUNT(*) as count FROM departments GROUP BY name";
$deptResult = mysqli_query($con, $deptQuery);


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
$deptQuery = "SELECT 
    d.name as department_name, 
    COUNT(e.id) as count,
    CONCAT(h.first_name, ' ', h.last_name) as hod_name
FROM departments d 
LEFT JOIN employees e ON d.id = e.department_id 
LEFT JOIN employees h ON d.id = h.department_id AND h.role = 'HOD'
GROUP BY d.id";
$deptResult = mysqli_query($con, $deptQuery);

$deptLabels = [];
$deptData = [];
while ($row = mysqli_fetch_assoc($deptResult)) {
    $deptLabels[] = '"' . $row['department_name'] . ' (HOD: ' . ($row['hod_name'] ?? 'Not Assigned') . ')"';
    $deptData[] = $row['count'];
}
// Fetch total number of employees
$query = "SELECT COUNT(*) AS total_employees FROM employees";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total_employees'] ?? 0;

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
        // If deletion is successful, redirect back to the same page
        echo "<script>alert('Record deleted successfully!');</script>";
        echo "<script>window.location.href='https://communik.san-solutions.in/admin_panel/index.php';</script>";
    } else {
        // If deletion fails, display an error message
        echo "<script>alert('Error deleting record: " . mysqli_error($con) . "');</script>";
        echo "<script>window.location.href='https://communik.san-solutions.in/admin_panel/index.php';</script>";
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
    <title>HOD Dashboard</title>
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
                <h1 class="h3 mb-0 text-gray-800">Welcome, <?php echo ucfirst(strtolower($_SESSION['username'])); ?>!
                </h1>
                <p class="text-muted">Head of Department: <?php echo $_SESSION['department_name']; ?></p>
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
                <div class="modern-card stat-card bg-gradient-primary animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small mb-1">Total Employees in Org</h6>
                            <h3 class="text-white mb-0"><?php echo $total_employees; ?></h3>
                            <div class="progress mt-2" style="height: 3px;">
                                <div class="progress-bar bg-white" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="icon-box rounded-circle bg-white-10 p-3">
                            <i class="typcn typcn-group-outline text-white icon-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Employees -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="modern-card stat-card bg-gradient-primary animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 small mb-1">Employees in
                                <?php echo $_SESSION['department_name']; ?>
                            </h6>
                            <h3 class="text-white mb-0"><?php echo $dept_employees; ?></h3>
                            <div class="progress mt-2" style="height: 3px;">
                                <div class="progress-bar bg-white" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="icon-box rounded-circle bg-white-10 p-3">
                            <i class="typcn typcn-group-outline text-white icon-xl"></i>
                        </div>
                    </div>
                </div>
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
                                    style="width: <?php echo ($total_on_leave / $total_employees) * 100; ?>%"></div>
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


        </div>

        <!-- Analytics Section -->
        <div class="row mb-4">
            <!-- Critical Alerts Section -->
            <div class="col-xl-4">
                <div class="modern-card h-100 animate__animated animate__fadeIn">
                    <div class="card-header bg-transparent border-0 py-2">
                        <h6 class="text-primary mb-0">Critical Alerts</h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="alert-card mb-2 bg-warning-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-warning p-2 mr-2">
                                    <i class="typcn typcn-warning text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-medium">Visa Expiring</h6>
                                        <span class="badge badge-warning"><?php echo $visa_expiring; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert-card mb-2 bg-info-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-info p-2 mr-2">
                                    <i class="typcn typcn-credit-card text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-medium">Labor Cards</h6>
                                        <span class="badge badge-info"><?php echo $labor_cards_expiring; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert-card bg-success-light rounded p-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-box rounded-circle bg-success p-2 mr-2">
                                    <i class="typcn typcn-gift text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-medium">Birthdays</h6>
                                        <span class="badge badge-success"><?php echo $birthdays_count; ?></span>
                                    </div>
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
        <div class="card modern-card mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold text-primary mb-0">Active Projects</h6>
                <a href="add_project.php" class="btn btn-primary btn-sm">Add New Project</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Project Lead</th>
                                <th>Project Name</th>
                                <th>Points</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td class='align-middle'>" . $row['leader_name'] . "</td>";
                                echo "<td class='align-middle'>" . $row['p_name'] . "</td>";
                                echo "<td class='align-middle'>" . $row['points'] . "</td>";
                                echo "<td class='align-middle'>
                                            <a href='assign_marks.php?edit=" . $row['p_id'] . "' class='btn btn-success btn-sm mr-2'><i class='fas fa-edit'></i></a>
                                            <a href='index.php?del=" . $row['p_id'] . "' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                                          </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <?php
    include_once('footer.php');
    ?>
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
                    <a class="btn btn-success" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="dashboardtemplate/vendors/chart.js/Chart.min.js"></script>

    <!-- Update chart configurations -->
    <script>
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

        // Add this CSS to make charts more modern
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

    </script>

    <!-- PolluxUI Core JS -->
    <script src="dashboardtemplate/vendors/js/vendor.bundle.base.js"></script>
    <script src="dashboardtemplate/js/off-canvas.js"></script>
    <script src="dashboardtemplate/js/hoverable-collapse.js"></script>
    <script src="dashboardtemplate/js/template.js"></script>
    <script src="dashboardtemplate/js/settings.js"></script>
    <script src="dashboardtemplate/js/todolist.js"></script>

    <!-- Bootstrap core JavaScript-->
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
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
</body>

</html>