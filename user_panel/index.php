<?php include('session.php'); ?>
<?php
// Get the logged-in user's username at the start
$user_name = $_SESSION['user_name'] ?? null;

// Check if username is set
if (!$user_name) {
    // Redirect to login if no username found
    header("Location: ../login.php");
    exit;
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

    <title>Employee Dashboard</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
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
            border-radius: 4px;
        }

        .quote-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            border-left: 4px solid var(--primary-color);
        }
    </style>

</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <!-- Page Heading -->

        <!-- Welcome Message -->
        <div class="alert alert-success mb-4">

            <?php
            // Get employee's full name
            $welcome_query = "SELECT e.full_name, e.reporting_manager, e.department_id,
            d.name as department_name,
            m.full_name as manager_name
             FROM employees e 
             JOIN emp_login el ON e.eid = el.emp_id 
             LEFT JOIN departments d ON e.department_id = d.id
              LEFT JOIN employees m ON e.reporting_manager = m.eid
                 WHERE el.user_name = '$user_name'";
            $welcome_result = mysqli_query($con, $welcome_query);
            $welcome_data = mysqli_fetch_assoc($welcome_result);
            $current_hour = date('H');
            $greeting = '';

            if ($current_hour < 12) {
                $greeting = 'Good Morning';
            } elseif ($current_hour < 17) {
                $greeting = 'Good Afternoon';
            } else {
                $greeting = 'Good Evening';
            }
            $name = ucwords(strtolower($welcome_data['full_name']));
            ?>
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="alert-heading"><?php echo $greeting; ?>, <?php echo $name; ?>!</h3>
                        <p class="mb-0">Welcome to your dashboard.</p>
                        <div class="mt-3">
                            <div class="mt-3">
                                <span class="badge bg-light text-primary p-2 me-2">
                                    Department:
                                    <i class="fas fa-building me-1"></i>
                                    <?php echo htmlspecialchars($welcome_data['department_name'] ?? 'Not Assigned'); ?>
                                </span>
                                <span class="badge bg-light text-primary p-2">
                                    Head of Department:
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?php echo htmlspecialchars($welcome_data['manager_name'] ?? 'Not Assigned'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="d-flex align-items-center justify-content-end mt-3">
                    <div class="small font-weight-bold text-white text-right">
                        <i class="fas fa-quote-left text-white mr-2"></i>
                        <?php
                        $thoughts = [
                            "Success is not final, failure is not fatal: it is the courage to continue that counts.",
                            "The only way to do great work is to love what you do.",
                            "Leadership is not about being the best. It's about making everyone else better.",
                            "Innovation distinguishes between a leader and a follower.",
                            "The future depends on what you do today.",
                            "Believe you can and you're halfway there.",
                            "Quality means doing it right when no one is looking.",
                            "Your attitude determines your direction.",
                            "Excellence is not a skill, it's an attitude.",
                            "The best way to predict the future is to create it."
                        ];

                        $dayOfYear = date('z');
                        $thoughtIndex = $dayOfYear % count($thoughts);
                        echo $thoughts[$thoughtIndex];
                        ?>
                        <i class="fas fa-quote-right text-success ml-2"></i>
                    </div>
                    <img src="img/thoughts.png" alt="Thought Icon" class="img-fluid" style="width: 30px; height: 30px;">
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Top Row - Employee Statistics -->
    <div class="row mb-4">
                <!-- Salary Summary Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Salary
                                        Summary</div>
                                    <?php
                                    $current_month = date('m');
                                    $previous_month = date('m', strtotime('-1 month'));
                                    $year = date('Y');

                                    $query = "SELECT 
                                        SUM(CASE WHEN MONTH(salary_date) = $current_month THEN total_salary ELSE 0 END) as current_salary,
                                        SUM(CASE WHEN MONTH(salary_date) = $previous_month THEN total_salary ELSE 0 END) as previous_salary
                                        FROM sal s 
                                        JOIN emp_login el ON s.emp_id = el.emp_id 
                                        WHERE el.user_name = '$user_name'
                                        AND YEAR(salary_date) = $year";

                                    $result = mysqli_query($con, $query);
                                    $salary_data = mysqli_fetch_assoc($result);

                                    // Handle null values for both current and previous salary
                                    $current_salary = ($salary_data['current_salary'] !== null) ? $salary_data['current_salary'] : 0;
                                    $previous_salary = ($salary_data['previous_salary'] !== null) ? $salary_data['previous_salary'] : 0;
                                    ?>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        Current Month: AED
                                        <?php echo number_format((float) $current_salary, 2); ?><br>
                                        Previous Month: AED
                                        <?php echo number_format((float) $previous_salary, 2); ?>
                                        
                                    </div>
                                </div>
                                <div class="text-center">
    <a href="view_payslip.php" title="View Salary Slips">
        <img src="img/money.png" alt="Salary Icon" class="img-fluid"
            style="width: 60px; height: 60px;">
    </a>
    <div style="font-size: 0.85rem; color: #4e73df; margin-top: 4px;">
        Click to view payslip
    </div>
</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly leave Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Leave Summary
                            </div>
                            <div class="row no-gutters align-items-center">
                                <?php
                                // Get the logged-in user's emp_id and details
                                $emp_id = intval($_SESSION['eid']);

                                // Get employee details including gender and joining date
                                $emp_query = "SELECT e.gender, e.doj 
                             FROM employees e 
                             JOIN emp_login el ON e.eid = el.emp_id 
                             WHERE el.emp_id = $emp_id";
                                $emp_result = mysqli_query($con, $emp_query);
                                $emp_data = mysqli_fetch_assoc($emp_result);

                                if ($emp_data) {
                                    $joining_date = new DateTime($emp_data['doj']);
                                    $gender = $emp_data['gender'];
                                } else {
                                    $joining_date = new DateTime(); // Set to current date if no data
                                    $gender = 'M'; // Set default gender
                                }

                                $today = new DateTime();
                                $service_months = ($today->format('Y') - $joining_date->format('Y')) * 12
                                    + ($today->format('m') - $joining_date->format('m'));

                                // Get total authorized leaves based on policies
                                $policy_query = "SELECT SUM(max_days) as total_leaves 
                               FROM leave_policies 
                               WHERE min_service_months <= $service_months 
                               AND (gender_restriction IS NULL OR gender_restriction = '$gender')";
                                $policy_result = mysqli_query($con, $policy_query);
                                $policy_data = mysqli_fetch_assoc($policy_result);

                                // Get availed leaves
                                $availed_query = "SELECT COUNT(*) as availed_leaves 
                                FROM leaves 
                                WHERE emp_id = $emp_id 
                                AND status = 'Approved' 
                                AND YEAR(start_date) = YEAR(CURRENT_DATE())";
                                $availed_result = mysqli_query($con, $availed_query);
                                $availed_data = mysqli_fetch_assoc($availed_result);

                                $authorized = $policy_data['total_leaves'] ?? 0;
                                $availed = $availed_data['availed_leaves'] ?? 0;
                                $balance = max(0, $authorized - $availed);
                                ?>
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Authorised
                                    </div>
                                    <p class="text-2xl font-bold"><?php echo $authorized; ?></p>
                                </div>
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Availed
                                    </div>
                                    <p class="text-2xl font-bold"><?php echo $availed; ?></p>
                                </div>
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Balance
                                    </div>
                                    <p class="text-2xl font-bold"><?php echo $balance; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Pending Signature Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <a href="documents/pending_signatures.php" style="text-decoration: none; color: inherit;">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Documents Pending Signature
                                        </div>
                                        <?php
                                        $emp_id = $_SESSION['eid'];
                                        $stmt = $con->prepare("SELECT COUNT(*) AS count 
                                FROM esign_documents ed
                                WHERE ed.status = 'pending'
                                AND ed.created_by = ?
                                AND ed.is_deleted = 0");
                                $stmt->bind_param("i", $emp_id);
                                        $stmt->execute();
                                        $count = $stmt->get_result()->fetch_assoc()['count'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $count ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

    <!-- Add a wrapper div with grey background -->
    <div style="background-color: #f8f9fc; padding: 1rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">

        <!-- Add a wrapper div with grey background -->
        <div style="background-color: #eaecf4; padding: 1.5rem; border-radius: 10px;">
            <!-- third Row - Additional Stats -->
            <div class="row mb-4">

                <!-- Due Projects Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="project_status.php" style="text-decoration: none; color: inherit;">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Due
                                            Projects
                                        </div>
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-auto">
                                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                    <?php
                                                    $due_projects = 0; // Initialize due projects variable
                                                    $query = "SELECT COUNT(*) AS due_projects FROM projects WHERE status = 'pending'";
                                                    $result = mysqli_query($con, $query);
                                                    if ($result && mysqli_num_rows($result) > 0) {
                                                        $row = mysqli_fetch_assoc($result);
                                                        $due_projects = $row['due_projects'];
                                                    }
                                                    echo $due_projects;
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: <?php echo ($due_projects * 10) . '%' ?>"
                                                        aria-valuenow="<?php echo $due_projects; ?>" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tasks fa-2x" style="color: #4e73df;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>



                <!-- Attendance Summary Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monthly
                                        Attendance</div>
                                    <?php
                                    // Get the logged-in user's emp_id
                                    $user_name = $_SESSION['user_name'];
                                    $emp_query = "SELECT emp_id FROM emp_login WHERE user_name = '$user_name'";
                                    $emp_result = mysqli_query($con, $emp_query);
                                    $emp_row = mysqli_fetch_assoc($emp_result);
                                    $emp_id = $emp_row['emp_id'];

                                    $month = date('m');
                                    $year = date('Y');

                                    $attendance_query = "SELECT 
                                        COUNT(CASE WHEN status = 'Present' THEN 1 END) as present_days,
                                        COUNT(CASE WHEN status = 'Absent' THEN 1 END) as absent_days,
                                        COUNT(CASE WHEN status = 'Half Day' THEN 1 END) as half_days
                                        FROM attendance 
                                        WHERE eid = '$emp_id' 
                                        AND MONTH(attendance_date) = '$month'
                                        AND YEAR(attendance_date) = '$year'";

                                    $attendance_result = mysqli_query($con, $attendance_query);
                                    $attendance = mysqli_fetch_assoc($attendance_result);

                                    // Calculate total working days
                                    $total_days = ($attendance['present_days'] ?? 0) + ($attendance['absent_days'] ?? 0) + ($attendance['half_days'] ?? 0);
                                    $attendance_percentage = $total_days > 0 ?
                                        round((($attendance['present_days'] ?? 0) + (($attendance['half_days'] ?? 0) * 0.5)) / $total_days * 100) : 0;
                                    ?>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                <?php echo $attendance_percentage; ?>%
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="progress progress-sm mr-2">
                                                <div class="progress-bar bg-info" role="progressbar"
                                                    style="width: <?php echo $attendance_percentage; ?>%"
                                                    aria-valuenow="<?php echo $attendance_percentage; ?>"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 small">
                                        Present: <?php echo $attendance['present_days'] ?? 0; ?> days<br>
                                        Half Day: <?php echo $attendance['half_days'] ?? 0; ?> days<br>
                                        Absent: <?php echo $attendance['absent_days'] ?? 0; ?> days
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="event.php" style="text-decoration: none; color: inherit;">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Events
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php
                                            $total_events = 0; // Initialize total events variable
                                            $query = "SELECT COUNT(*) AS total_events FROM events";
                                            $result = mysqli_query($con, $query);
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                $row = mysqli_fetch_assoc($result);
                                                $total_events = $row['total_events'];
                                            }
                                            echo $total_events;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Leave history
                            <img src="img/history.png" alt="Salary Icon" class="img-fluid"
                                style="width: 30px; height: 30px;">
                        </h6>

                    </div>
                    <div class="card-body">
                        <canvas id="leaveChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>


                <!-- Appraisals Card  -->


                <div class="col-xl-12 col-lg-12" id="appraisals">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">My Appraisals</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT a.*, ap.start_date, ap.end_date 
                FROM appraisal_assignments a 
                JOIN appraisal_periods ap ON a.period_id = ap.period_id 
                WHERE a.employee_id = ? 
                ORDER BY ap.start_date DESC";
                                        $stmt = $con->prepare($query);
                                        $stmt->bind_param("s", $_SESSION['eid']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td>Appraisal
                                                    <?php echo date('Y', strtotime($row['start_date'])); ?>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                                                <td><?php echo date('d M Y', strtotime($row['end_date'])); ?></td>
                                                <td>
                                                    <span
                                                        class="badge badge-<?php echo $row['status'] == 'Pending' ? 'warning' : ($row['status'] == 'Completed' ? 'success' : 'info'); ?>">
                                                        <?php echo $row['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] == 'Pending'): ?>
                                                        <a href="appraisal/fill_form.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-primary btn-sm">Fill Appraisal</a>
                                                    <?php else: ?>
                                                        <a href="appraisal/view_form.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-info btn-sm">View Details</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- Leave Chart -->

    <?php
    // Get logged-in user's emp_id and fetch leave data
    $emp_id = intval($_SESSION['eid']);
    $leave_counts = [
        'Approved' => 0,
        'Pending' => 0,
        'Rejected' => 0
    ];

    $query = "SELECT status, COUNT(*) AS count FROM leaves WHERE emp_id = $emp_id GROUP BY status";
    $result = mysqli_query($con, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $status = ucfirst(strtolower($row['status']));
        if (isset($leave_counts[$status])) {
            $leave_counts[$status] = min(30, $row['count']);
        }
    }

    // Fetch attendance and salary data
    $user_name = $_SESSION['user_name'];
    $month = date('m');
    $year = date('Y');

    $attendance_query = "SELECT 
            COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_days,
            COUNT(CASE WHEN a.status = 'Half Day' THEN 1 END) as half_days,
            COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absent_days
            FROM attendance a 
            JOIN emp_login el ON a.eid = el.emp_id 
            WHERE el.user_name = ? 
            AND MONTH(a.attendance_date) = ? 
            AND YEAR(a.attendance_date) = ?";

    $stmt = mysqli_prepare($con, $attendance_query);
    mysqli_stmt_bind_param($stmt, "sii", $user_name, $month, $year);
    mysqli_stmt_execute($stmt);
    $attendance_result = mysqli_stmt_get_result($stmt);
    $attendance_data = mysqli_fetch_assoc($attendance_result);

    // Fetch salary breakdown
    $salary_query = "SELECT base_salary, incentive, payable_salary, deductions, total_salary 
                FROM sal s 
                JOIN emp_login el ON s.emp_id = el.emp_id 
                WHERE el.user_name = ?";
    $stmt = mysqli_prepare($con, $salary_query);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    $salary_result = mysqli_stmt_get_result($stmt);
    $salary_data = mysqli_fetch_assoc($salary_result);

    // Convert leave data to JSON for JavaScript
    $leave_statuses = json_encode(array_keys($leave_counts));
    $leave_values = json_encode(array_values($leave_counts));
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Leave Chart
            const ctx = document.getElementById('leaveChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $leave_statuses; ?>,
                    datasets: [{
                        label: 'Number of Leaves',
                        data: <?php echo $leave_values; ?>,
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        borderColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 30
                        }
                    }
                }
            });

            // Salary Chart
            const salaryCtx = document.getElementById('salaryChart').getContext('2d');
            new Chart(salaryCtx, {
                type: 'doughnut',
                data: {
                    labels: ["Base Salary", "Incentive", "Deductions", "Net Payable"],
                    datasets: [{
                        data: [
                            <?php echo $salary_data['base_salary'] ?? 0; ?>,
                            <?php echo $salary_data['incentive'] ?? 0; ?>,
                            <?php echo $salary_data['deductions'] ?? 0; ?>,
                            <?php echo $salary_data['payable_salary'] ?? 0; ?>
                        ],
                        backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b', '#f6c23e'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#d52a1a', '#dda20a'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)"
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Salary Breakdown'
                        },
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>








    <?php include_once('footer.php'); ?>

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
                <div class="modal-body">Select "Logout" below if you are ready to end your current
                    session.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this form right after the logout modal -->
    <form id="logout-form" action="logout.php" method="POST" style="display: none;">
    </form>
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

</body>

</html>