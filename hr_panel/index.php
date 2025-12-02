<?php
include('session.php');
include('connection.php');

// Get user data from session
$email = $_SESSION['email'];
$role = $_SESSION['role'];
$eid = $_SESSION['eid'];

// Get employee details including full name
$emp_query = "SELECT e.*, el.status as login_status 
             FROM employees e 
             JOIN emp_login el ON e.eid = el.emp_id 
             WHERE e.eid = ? AND el.status = 'Active'";
$stmt = $con->prepare($emp_query);
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Comprehensive Statistics Queries
$stats = [];

// Basic Employee Counts
$stats['total_employees'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees"))['count'];
$stats['active_employees'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE status = 'Active' AND role NOT IN ('super_admin', 'admin')"))['count'];
$stats['inactive_employees'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE status = 'Inactive'"))['count'];
$stats['permanent_employees'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE status = 'Active' AND (is_trainee = 0 OR is_trainee IS NULL) AND role NOT IN ('super_admin', 'admin')"))['count'];
$stats['trainees'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE status = 'Active' AND is_trainee = 1 AND role NOT IN ('super_admin', 'admin')"))['count'];

// Visa Statistics
$stats['visa_active'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE visa_expiry_date > CURDATE() AND visa_expiry_date IS NOT NULL"))['count'];
$stats['visa_expired'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE visa_expiry_date < CURDATE() AND visa_expiry_date IS NOT NULL"))['count'];
$stats['visa_expiring_30'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE visa_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND visa_expiry_date IS NOT NULL"))['count'];

// Passport Statistics
$stats['passport_active'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE passport_expiry_date > CURDATE() AND passport_expiry_date IS NOT NULL"))['count'];
$stats['passport_expired'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE passport_expiry_date < CURDATE() AND passport_expiry_date IS NOT NULL"))['count'];
$stats['passport_expiring_30'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE passport_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND passport_expiry_date IS NOT NULL"))['count'];

// Labour Card Statistics
$stats['labour_card_active'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE labour_card_end_date > CURDATE() AND labour_card_end_date IS NOT NULL"))['count'];
$stats['labour_card_expired'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE labour_card_end_date < CURDATE() AND labour_card_end_date IS NOT NULL"))['count'];
$stats['labour_card_expiring_30'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE labour_card_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND labour_card_end_date IS NOT NULL"))['count'];

// Gender Distribution
$stats['male_count'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE gender = 'Male' AND status = 'Active' AND role NOT IN ('super_admin', 'admin')"))['count'];
$stats['female_count'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM employees WHERE gender = 'Female' AND status = 'Active' AND role NOT IN ('super_admin', 'admin')"))['count'];

// Department Count
$stats['departments'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM departments"))['count'];

// Leave Statistics
$stats['pending_leaves'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM leaves WHERE hr_status IS NULL AND status = 'approved'"))['count'];
$stats['approved_leaves_month'] = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM leaves WHERE MONTH(start_date) = MONTH(CURRENT_DATE) AND YEAR(start_date) = YEAR(CURRENT_DATE) AND hr_status = 'approved'"))['count'];

// Salary Statistics
$salary_stats = mysqli_fetch_assoc(mysqli_query($con, "SELECT COALESCE(ROUND(AVG(NULLIF(total_salary, 0)), 2), 0.00) as avg_salary, COALESCE(ROUND(SUM(NULLIF(incentive, 0)), 2), 0.00) as total_incentive FROM sal WHERE MONTH(salary_date) = MONTH(CURRENT_DATE) AND YEAR(salary_date) = YEAR(CURRENT_DATE)"));
$stats['avg_salary'] = $salary_stats['avg_salary'];
$stats['total_incentive'] = $salary_stats['total_incentive'];

// Pending Documents
$hr_id = $user_data['id'];
$pending_query = "SELECT COUNT(*) as count FROM esign_workflow w JOIN esign_documents d ON w.document_id = d.id WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $hr_id);
$stmt->execute();
$stats['pending_documents'] = $stmt->get_result()->fetch_assoc()['count'];

// Recent Activities
$activities_query = "SELECT * FROM activity_log ORDER BY date DESC LIMIT 10";
$activities_result = mysqli_query($con, $activities_query);

// Recent Leave Requests
$leave_query = "SELECT l.*, e.full_name FROM leaves l JOIN employees e ON l.emp_id = e.id ORDER BY l.applied_at DESC LIMIT 5";
$leave_result = mysqli_query($con, $leave_query);

// Department Distribution for Chart
$dept_query = "SELECT d.name as department_name, COUNT(e.eid) as count FROM departments d LEFT JOIN employees e ON d.id = e.department_id AND e.status='active' AND e.role NOT IN ('super_admin', 'admin') GROUP BY d.id, d.name";
$dept_result = mysqli_query($con, $dept_query);
$dept_labels = [];
$dept_data = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $dept_labels[] = $row['department_name'];
    $dept_data[] = $row['count'];
}

// Age Distribution for Chart
$age_query = "SELECT CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 25 THEN '18-25' WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 35 THEN '26-35' WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 45 THEN '36-45' ELSE '45+' END as age_group, COUNT(*) as count FROM employees WHERE status='active' AND role NOT IN ('super_admin', 'admin') GROUP BY age_group";
$age_result = mysqli_query($con, $age_query);
$age_labels = [];
$age_data = [];
while ($row = mysqli_fetch_assoc($age_result)) {
    $age_labels[] = $row['age_group'];
    $age_data[] = $row['count'];
}

// Celebrations
$today = date('Y-m-d');
$month = date('m');
$birthday_query = "SELECT first_name, last_name, birthday FROM employees WHERE MONTH(birthday) = $month AND emp_left_org = 'no' ORDER BY DAY(birthday)";
$birthday_result = mysqli_query($con, $birthday_query);
$anniversary_query = "SELECT first_name, last_name, doj FROM employees WHERE MONTH(doj) = $month AND emp_left_org = 'no' ORDER BY DAY(doj)";
$anniversary_result = mysqli_query($con, $anniversary_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HR Dashboard - Communik Marketing</title>
    
    <link href="img/favicon.ico" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            margin: 20px;
            padding: 30px;
        }

        .welcome-header {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        .welcome-header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 300;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-header p {
            color: #34495e;
            margin: 10px 0 0 0;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        /* Enhanced background for status cards */
        .stat-card.primary,
        .stat-card.info,
        .stat-card.warning {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(15px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.success::before { background: var(--success-gradient); }
        .stat-card.warning::before { background: var(--warning-gradient); }
        .stat-card.danger::before { background: var(--danger-gradient); }
        .stat-card.info::before { background: var(--info-gradient); }
        .stat-card.dark::before { background: var(--dark-gradient); }

        /* Special styling for status cards to ensure text visibility */
        .stat-card.primary .stat-title,
        .stat-card.primary .stat-value,
        .stat-card.primary .stat-description,
        .stat-card.info .stat-title,
        .stat-card.info .stat-value,
        .stat-card.info .stat-description,
        .stat-card.warning .stat-title,
        .stat-card.warning .stat-value,
        .stat-card.warning .stat-description {
            color: #2c3e50 !important;
        }

        .stat-card.primary small,
        .stat-card.info small,
        .stat-card.warning small {
            color: #34495e !important;
            font-weight: 500;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            color: #2c3e50;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
        }

        .stat-value {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-description {
            color: #34495e;
            font-size: 0.9rem;
            margin: 0;
        }

        .chart-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        .chart-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .table-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        .table-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .table-responsive {
            background: rgba(255,255,255,0.3);
            border-radius: 15px;
            overflow: hidden;
        }

        .table {
            margin: 0;
            color: #2c3e50;
        }

        .table thead th {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px;
        }

        .table tbody td {
            border: none;
            padding: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            color: #34495e;
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.2);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-approved { background: rgba(76, 175, 80, 0.2); color: #2e7d32; }
        .status-pending { background: rgba(255, 193, 7, 0.2); color: #f57c00; }
        .status-rejected { background: rgba(244, 67, 54, 0.2); color: #d32f2f; }

        .celebration-card {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        .celebration-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .celebration-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .celebration-section h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .celebration-item {
            background: rgba(255,255,255,0.3);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .celebration-content {
                grid-template-columns: 1fr;
            }
            
            .welcome-header h1 {
                font-size: 2rem;
            }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1>🎉 Welcome back, <?php echo ucwords(htmlspecialchars($user_data['full_name'])); ?>!</h1>
            <p>Here's your comprehensive HR dashboard overview for today</p>
        </div>

        <!-- Main Statistics Grid -->
        <div class="stats-grid">
            <!-- Total Employees -->
            <div class="stat-card primary">
                <div class="stat-header">
                    <h3 class="stat-title">Total Employees</h3>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
                <p class="stat-description">Complete workforce count</p>
            </div>

            <!-- Active Employees -->
            <div class="stat-card success">
                <div class="stat-header">
                    <h3 class="stat-title">Active Employees</h3>
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['active_employees']; ?></div>
                <p class="stat-description">Currently working staff</p>
            </div>

            <!-- Permanent Employees -->
            <div class="stat-card info">
                <div class="stat-header">
                    <h3 class="stat-title">Permanent Staff</h3>
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['permanent_employees']; ?></div>
                <p class="stat-description">Full-time employees</p>
            </div>

            <!-- Trainees -->
            <div class="stat-card warning">
                <div class="stat-header">
                    <h3 class="stat-title">Trainees</h3>
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['trainees']; ?></div>
                <p class="stat-description">Training program participants</p>
            </div>

            <!-- Pending Documents -->
            <div class="stat-card danger">
                <div class="stat-header">
                    <h3 class="stat-title">Pending Documents</h3>
                    <div class="stat-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['pending_documents']; ?></div>
                <p class="stat-description">Awaiting your approval</p>
            </div>

            <!-- Pending Leave Requests -->
            <div class="stat-card warning">
                <div class="stat-header">
                    <h3 class="stat-title">Pending Leaves</h3>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['pending_leaves']; ?></div>
                <p class="stat-description">Leave requests to review</p>
            </div>

            <!-- Average Salary -->
            <div class="stat-card success">
                <div class="stat-header">
                    <h3 class="stat-title">Avg. Monthly Salary</h3>
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['avg_salary'], 0); ?></div>
                <p class="stat-description">Current month average</p>
            </div>

            <!-- Total Incentives -->
            <div class="stat-card info">
                <div class="stat-header">
                    <h3 class="stat-title">Total Incentives</h3>
                    <div class="stat-icon">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_incentive'], 0); ?></div>
                <p class="stat-description">This month's incentives</p>
            </div>
        </div>

        <!-- Visa & Passport Status Section -->
        <div class="stats-grid">
            <!-- Visa Status -->
            <div class="stat-card primary">
                <div class="stat-header">
                    <h3 class="stat-title">Visa Status</h3>
                    <div class="stat-icon">
                        <i class="fas fa-passport"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['visa_active']; ?></div>
                <p class="stat-description">Active visas</p>
                <div style="margin-top: 15px;">
                    <small style="color: rgba(255,255,255,0.7);">
                        ⚠️ <?php echo $stats['visa_expired']; ?> expired | 
                        🔔 <?php echo $stats['visa_expiring_30']; ?> expiring in 30 days
                    </small>
                </div>
            </div>

            <!-- Passport Status -->
            <div class="stat-card info">
                <div class="stat-header">
                    <h3 class="stat-title">Passport Status</h3>
                    <div class="stat-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['passport_active']; ?></div>
                <p class="stat-description">Valid passports</p>
                <div style="margin-top: 15px;">
                    <small style="color: rgba(255,255,255,0.7);">
                        ⚠️ <?php echo $stats['passport_expired']; ?> expired | 
                        🔔 <?php echo $stats['passport_expiring_30']; ?> expiring in 30 days
                    </small>
                </div>
            </div>

            <!-- Labour Card Status -->
            <div class="stat-card warning">
                <div class="stat-header">
                    <h3 class="stat-title">Labour Cards</h3>
                    <div class="stat-icon">
                        <i class="fas fa-id-badge"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['labour_card_active']; ?></div>
                <p class="stat-description">Active labour cards</p>
                <div style="margin-top: 15px;">
                    <small style="color: rgba(255,255,255,0.7);">
                        ⚠️ <?php echo $stats['labour_card_expired']; ?> expired | 
                        🔔 <?php echo $stats['labour_card_expiring_30']; ?> expiring in 30 days
                    </small>
                </div>
            </div>

            <!-- Gender Distribution -->
            <div class="stat-card dark">
                <div class="stat-header">
                    <h3 class="stat-title">Gender Ratio</h3>
                    <div class="stat-icon">
                        <i class="fas fa-venus-mars"></i>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="stat-value"><?php echo $stats['male_count']; ?></div>
                        <p class="stat-description">Male</p>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo $stats['female_count']; ?></div>
                        <p class="stat-description">Female</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Celebrations Section -->
        <div class="celebration-card">
            <h3 class="celebration-title">🎉 This Month's Celebrations</h3>
            <div class="celebration-content">
                <div class="celebration-section">
                    <h6>🎂 Birthdays</h6>
                    <?php while ($bday = mysqli_fetch_assoc($birthday_result)) { ?>
                        <div class="celebration-item">
                            <?php echo $bday['first_name'] . ' ' . $bday['last_name']; ?> - 
                            <?php echo date('d', strtotime($bday['birthday'])); ?>th
                        </div>
                    <?php } ?>
                </div>
                <div class="celebration-section">
                    <h6>🌟 Work Anniversaries</h6>
                    <?php while ($anniv = mysqli_fetch_assoc($anniversary_result)) { ?>
                        <div class="celebration-item">
                            <?php echo $anniv['first_name'] . ' ' . $anniv['last_name']; ?> - 
                            <?php echo date('Y') - date('Y', strtotime($anniv['doj'])); ?> years
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="chart-section">
                    <h3 class="chart-title">Department Distribution</h3>
                    <div class="chart-container">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6">
                <div class="chart-section">
                    <h3 class="chart-title">Age Distribution</h3>
                    <div class="chart-container">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities Table -->
        <div class="table-section">
            <h3 class="table-title">Recent Activities</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($activities_result)) { ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['employee']; ?></td>
                                <td><?php echo $row['activity']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Leave Requests Table -->
        <div class="table-section">
            <h3 class="table-title">Recent Leave Requests</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($leave_result)) { 
                            $status_class = '';
                            switch ($row['hr_status']) {
                                case 'approved': $status_class = 'status-approved'; break;
                                case 'rejected': $status_class = 'status-rejected'; break;
                                default: $status_class = 'status-pending';
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['type_of_leave']; ?></td>
                                <td><?php echo date('d M', strtotime($row['start_date'])) . ' - ' . date('d M', strtotime($row['end_date'])); ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $row['hr_status'] ? ucfirst($row['hr_status']) : 'Pending'; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($row['applied_at'])); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart Scripts -->
    <script>
        // Department Distribution Chart
        var deptCtx = document.getElementById('departmentChart').getContext('2d');
        var departmentChart = new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($dept_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dept_data); ?>,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe',
                        '#43e97b', '#38f9d7', '#fa709a', '#fee140', '#a8edea', '#fed6e3'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#2c3e50',
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                weight: '600'
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Age Distribution Chart
        var ageCtx = document.getElementById('ageChart').getContext('2d');
        var ageChart = new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($age_labels); ?>,
                datasets: [{
                    label: 'Number of Employees',
                    data: <?php echo json_encode($age_data); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#2c3e50' },
                        grid: { color: 'rgba(0,0,0,0.1)' }
                    },
                    x: {
                        ticks: { color: '#2c3e50' },
                        grid: { color: 'rgba(0,0,0,0.1)' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#2c3e50' }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Add floating animation to stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('floating');
        });
    </script>

    <?php include('footer.php'); ?>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
</body>
</html> 