<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
include('session.php');
require_once('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';


// Add this check after connection include
if (!$con) {
    die("<div class='alert alert-danger'>Database connection failed: " . mysqli_connect_error()."</div>");
}

// Fetch all employee details with proper joins and fields
$query = "SELECT 
    e.*,
    d.name as department_name,
    DATE_FORMAT(e.doj, '%Y-%m-%d') as doj,
    COALESCE(e.status, 'inactive') as status,
    CONCAT(e.first_name, ' ', e.last_name) as full_name
FROM employees e 
LEFT JOIN departments d ON e.department_id = d.id 
WHERE e.emp_left_org = 0
ORDER BY e.id DESC";
$result = mysqli_query($con, $query);

if (!$result) {
    die("<div class='alert alert-danger'>Query Error: " . mysqli_error($con) . "</div>");
}

// Debug output
if(mysqli_num_rows($result) === 0) {
    echo "<div class='alert alert-warning'>No employees found in the database.</div>";
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Employee Directory</title>

    <!-- Custom fonts and styles -->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/reg_emp.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <style>
        .employee-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }

        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .stats-card {
            border-left: 4px solid;
        }

        .tab-content {
            background: white;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link {
            border: none;
            color: #4e73df;
            font-weight: 600;
            padding: 15px 25px;
        }

        .nav-tabs .nav-link.active {
            color: #2e59d9;
            border-bottom: 3px solid #2e59d9;
            background: transparent;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .action-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
            z-index: 1;
        }

        .employee-card:hover .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            z-index: 1;
        }

        .document-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin: 2px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d !important;
        }

        hr {
            margin: 1rem 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .small {
            font-size: 0.875rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .mb-3 {
            margin-bottom: 1rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Employee Directory</h1>
            <div class="d-flex">
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#filterModal">
                    <i class="fas fa-filter fa-sm"></i> Filter
                </button>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown"
                        data-toggle="dropdown">
                        <i class="fas fa-download fa-sm"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" onclick="exportToExcel()">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </a>
                        <a class="dropdown-item" href="#" onclick="printCurrentView()">
                            <i class="fas fa-print mr-2"></i>Print
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-12">
                <div class="card stats-card shadow-sm">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 mr-3">
                                <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            <div class="flex-grow-1">
                                <div class="text-xs font-weight-bold text-primary text-uppercase">Employees</div>
                                    <?php
                                $total_query = "SELECT COUNT(*) as total FROM employees WHERE emp_left_org = 0 AND status = 'active'";
                                    $total_result = mysqli_query($con, $total_query);
                                $total = ($total_result) ? mysqli_fetch_assoc($total_result)['total'] : 0;
                                
                                $trainee_query = "SELECT COUNT(*) as total FROM employees WHERE is_trainee = 1 AND emp_left_org = 0 AND status = 'active'";
                                    $trainee_result = mysqli_query($con, $trainee_query);
                                $trainee_count = ($trainee_result) ? mysqli_fetch_assoc($trainee_result)['total'] : 0;

                                $permanent_count = $total - $trainee_count;
                                ?>
                                <div class="d-flex align-items-baseline">
                                    <div class="h4 mb-0 mr-2 font-weight-bold text-gray-800"><?php echo $total; ?></div>
                                    <div class="text-xs">
                                        <span class="text-primary mr-2">
                                            <i class="fas fa-user"></i> <?php echo $permanent_count; ?> Permanent
                                        </span>
                                        <span class="text-warning">
                                            <i class="fas fa-user-graduate"></i> <?php echo $trainee_count; ?> Trainees
                                        </span>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gender Distribution Card -->
            <div class="col-xl-4 col-md-12">
                <div class="card stats-card shadow-sm">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 mr-3">
                                <i class="fas fa-venus-mars fa-2x text-info"></i>
                                </div>
                            <div class="flex-grow-1">
                                <div class="text-xs font-weight-bold text-info text-uppercase">Gender Distribution</div>
                                <div class="row g-0">
                                    <div class="col-6 border-right pr-2">
                                    <?php
                                        $male_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Male' AND emp_left_org = 0 AND status = 'active' AND is_trainee = 0";
                                        $female_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Female' AND emp_left_org = 0 AND status = 'active' AND is_trainee = 0";

                                    $male_result = mysqli_query($con, $male_query);
                                    $female_result = mysqli_query($con, $female_query);

                                    $male_count = mysqli_fetch_assoc($male_result)['total'];
                                    $female_count = mysqli_fetch_assoc($female_result)['total'];
                                    ?>
                                        <div class="text-xs text-primary">Active</div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div><i class="fas fa-male"></i> Male</div>
                                            <div class="font-weight-bold"><?php echo $male_count; ?></div>
                                    </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><i class="fas fa-female"></i> Female</div>
                                            <div class="font-weight-bold"><?php echo $female_count; ?></div>
                                    </div>
                                </div>
                                    <div class="col-6 pl-2">
                                        <?php
                                        $trainee_male_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Male' AND emp_left_org = 0 AND status = 'active' AND is_trainee = 1";
                                        $trainee_female_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Female' AND emp_left_org = 0 AND status = 'active' AND is_trainee = 1";

                                        $trainee_male_result = mysqli_query($con, $trainee_male_query);
                                        $trainee_female_result = mysqli_query($con, $trainee_female_query);

                                        $trainee_male_count = mysqli_fetch_assoc($trainee_male_result)['total'];
                                        $trainee_female_count = mysqli_fetch_assoc($trainee_female_result)['total'];
                                        ?>
                                        <div class="text-xs text-warning">Trainees</div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div><i class="fas fa-male"></i> Male</div>
                                            <div class="font-weight-bold"><?php echo $trainee_male_count; ?></div>
                            </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><i class="fas fa-female"></i> Female</div>
                                            <div class="font-weight-bold"><?php echo $trainee_female_count; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Stats Card -->
            <div class="col-xl-4 col-md-12">
                <div class="card stats-card shadow-sm">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 mr-3">
                                <i class="fas fa-building fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-xs font-weight-bold text-warning text-uppercase">Departments</div>
                                <div class="row g-0">
                                    <div class="col-6 border-right pr-2">
                                        <div class="text-xs text-primary">Active</div>
                                    <?php
                                        $active_dept_query = "SELECT d.name as department, COUNT(*) as count 
                                    FROM employees e 
                                    JOIN departments d ON e.department_id = d.id 
                                        WHERE e.emp_left_org = 0 
                                        AND e.status = 'active' 
                                        AND e.is_trainee = 0
                                    GROUP BY d.id, d.name 
                                    ORDER BY count DESC 
                                    LIMIT 3";
                                        $active_dept_result = mysqli_query($con, $active_dept_query);
                                        while ($dept = mysqli_fetch_assoc($active_dept_result)) {
                                            echo "<div class='d-flex justify-content-between align-items-center mb-1'>
                                                    <div class='text-truncate mr-2'>" . htmlspecialchars($dept['department']) . "</div>
                                                    <div class='font-weight-bold'>" . $dept['count'] . "</div>
                                                </div>";
                                    }
                                    ?>
                                </div>
                                    <div class="col-6 pl-2">
                                        <div class="text-xs text-warning">Trainees</div>
                                        <?php
                                        $trainee_dept_query = "SELECT d.name as department, COUNT(*) as count 
                                        FROM employees e 
                                        JOIN departments d ON e.department_id = d.id 
                                        WHERE e.emp_left_org = 0 
                                        AND e.status = 'active' 
                                        AND e.is_trainee = 1
                                        GROUP BY d.id, d.name 
                                        ORDER BY count DESC 
                                        LIMIT 3";
                                        $trainee_dept_result = mysqli_query($con, $trainee_dept_query);
                                        while ($dept = mysqli_fetch_assoc($trainee_dept_result)) {
                                            echo "<div class='d-flex justify-content-between align-items-center mb-1'>
                                                    <div class='text-truncate mr-2'>" . htmlspecialchars($dept['department']) . "</div>
                                                    <div class='font-weight-bold'>" . $dept['count'] . "</div>
                                                </div>";
                                        }
                                        ?>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#grid-view">
                        <i class="fas fa-th-large mr-2"></i>Grid View
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#table-view">
                        <i class="fas fa-table mr-2"></i>Table View
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#department-view">
                        <i class="fas fa-sitemap mr-2"></i>Department View
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#trainee-view">
                        <i class="fas fa-user-graduate mr-2"></i>Trainee View
                    </a>
                </li>
            </ul>
        </div>
        <?php
// Fresh query for grid view
$grid_query = "SELECT 
    e.*,
    d.name as department_name,
    DATE_FORMAT(e.doj, '%Y-%m-%d') as doj,
    COALESCE(e.status, 'inactive') as status,
    CONCAT(e.first_name, ' ', e.last_name) as full_name
FROM employees e 
LEFT JOIN departments d ON e.department_id = d.id 
WHERE e.emp_left_org = 0
ORDER BY e.id DESC";
$grid_result = mysqli_query($con, $grid_query);
?>
        <div class="card-body">
            <div class="tab-content">
                <!-- Grid View -->
                <div class="tab-pane fade show active" id="grid-view">
                    <div class="row">
                        <?php 
                        if ($grid_result && mysqli_num_rows($grid_result) > 0) {
                            while ($row = mysqli_fetch_assoc($grid_result)) { ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card employee-card h-100">
                                        <div class="card-body position-relative">
                                            <div class="status-badge badge badge-<?php echo (!empty($row['status']) && $row['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                <?php echo !empty($row['status']) ? ucfirst($row['status']) : 'Inactive'; ?>
                                            </div>
                                            <div class="action-buttons">
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="../generate_id_card.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            </div>
                                            <div class="text-center mb-3">
                                                <img src="<?php echo !empty($row['profile_pic']) ? 'uploads/profile_pics/' . basename($row['profile_pic']) : 'uploads/profile_pics/default.jpg'; ?>"
                                                    class="profile-img mb-3" alt="Profile Picture">
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($row['full_name'] ?? ''); ?>
                                                </h5>
                                                <p class="text-muted small mb-2">
                                                    <?php echo htmlspecialchars($row['designation'] ?? ''); ?>
                                                </p>
                                                <div class="badge badge-primary mb-2">
                                                    <?php echo htmlspecialchars($row['department_name'] ?? 'No Department'); ?>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="text-left small">
                                                <p class="mb-2"><i class="fas fa-id-card mr-2"></i>EID: <?php echo htmlspecialchars($row['eid'] ?? ''); ?></p>
                                                <p class="mb-2"><i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($row['email'] ?? ''); ?></p>
                                                <p class="mb-2"><i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($row['contact'] ?? ''); ?></p>
                                                <p class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($row['EmpLoc'] ?? ''); ?></p>
                                                <p class="mb-2"><i class="fas fa-calendar-alt mr-2"></i>Joined: <?php 
                                                    if (!empty($row['doj']) && $row['doj'] != '0000-00-00') {
                                                        $join_date = new DateTime($row['doj']);
                                                        echo $join_date->format('d M Y');
                                                    } else {
                                                        echo 'Not specified';
                                                    }
                                                ?></p>
                                                <?php if (!empty($row['is_trainee']) && $row['is_trainee'] == 1) { ?>
                                                    <div class="badge badge-warning mt-2">
                                                        <i class="fas fa-user-graduate mr-1"></i>Trainee
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No employees found in the database.
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Table View -->
                <?php
                $table_query = "SELECT 
                    e.*,
                    d.name as department_name,
                    DATE_FORMAT(e.doj, '%Y-%m-%d') as doj,
                    COALESCE(e.status, 'inactive') as status,
                    CONCAT(e.first_name, ' ', e.last_name) as full_name
                FROM employees e 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE e.emp_left_org = 0
                ORDER BY e.id DESC";
                $table_result = mysqli_query($con, $table_query);
                ?>
                <div class="tab-pane fade" id="table-view">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="employeeTable">
                            <thead>
                                <tr>
                                    <th>EID</th>
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Contact Info</th>
                                    <th>Documents</th>
                                    <th>Employment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($table_result && mysqli_num_rows($table_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($table_result)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['eid'] ?? ''); ?></td>
                                        <td class="text-center">
                                            <img src="<?php echo !empty($row['profile_pic']) ? 'uploads/profile_pics/' . basename($row['profile_pic']) : 'uploads/profile_pics/default.jpg'; ?>"
                                                class="rounded-circle" style="width: 50px; height: 50px;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['designation'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['department_name'] ?? 'No Department'); ?></td>
                                        <td>
                                            <i class="fas fa-envelope mr-1"></i>
                                            <?php echo htmlspecialchars($row['email'] ?? ''); ?><br>
                                            <i class="fas fa-phone mr-1"></i>
                                            <?php echo htmlspecialchars($row['contact'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php if (isset($row['visa_doc']) && $row['visa_doc']): ?>
                                                <a href="<?php echo 'uploads/documents/' . basename($row['visa_doc']); ?>"
                                                    class="btn btn-sm btn-outline-info mr-1" target="_blank">
                                                    <i class="fas fa-passport"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (isset($row['passport_doc']) && $row['passport_doc']): ?>
                                                <a href="<?php echo 'uploads/documents/' . basename($row['passport_doc']); ?>"
                                                    class="btn btn-sm btn-outline-info" target="_blank">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>DOJ:</strong> <?php echo htmlspecialchars($row['doj'] ?? ''); ?><br>
                                            <strong>Location:</strong> <?php echo htmlspecialchars($row['EmpLoc'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo (!empty($row['status']) && $row['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                <?php echo !empty($row['status']) ? ucfirst($row['status']) : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No employees found in the database.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Department View -->
                <div class="tab-pane fade" id="department-view">
                    <?php
                    $dept_query = "SELECT d.*, COUNT(e.id) as emp_count 
                   FROM departments d 
                   LEFT JOIN employees e ON d.id = e.department_id 
                   GROUP BY d.id";
                    $dept_result = mysqli_query($con, $dept_query);
                    while ($dept = mysqli_fetch_assoc($dept_result)) {
                        $department_id = $dept['id'];
                        $emp_query = "SELECT e.*, d.name as department_name 
                     FROM employees e 
                     LEFT JOIN departments d ON e.department_id = d.id 
                     WHERE e.department_id = $department_id";
                        $emp_result = mysqli_query($con, $emp_query);
                        ?>
                        <div class="department-section mb-4">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-building mr-2"></i><?php echo htmlspecialchars($dept['name']); ?>
                                <span class="badge badge-primary ml-2"><?php echo $dept['emp_count']; ?></span>
                            </h4>
                            <div class="row">
                                <?php while ($emp = mysqli_fetch_assoc($emp_result)) { ?>
                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <img src="<?php echo !empty($emp['profile_pic']) ? 'uploads/profile_pics/' . basename($emp['profile_pic']) : 'uploads/profile_pics/default.jpg'; ?>"
                                                    class="rounded-circle mb-3" style="width: 80px; height: 80px;">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($emp['full_name'] ?? ''); ?></h6>
                                                <p class="text-muted small">
                                                    <?php echo htmlspecialchars($emp['designation'] ?? ''); ?>
                                                </p>
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails(<?php echo $emp['id']; ?>)">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>


                <!-- Trainee View -->
                <div class="tab-pane fade" id="trainee-view">
                    <div class="row">
                        <?php
                        $trainee_view_query = "SELECT e.*, d.name as department_name 
                                FROM employees e 
                                LEFT JOIN departments d ON e.department_id = d.id 
                                WHERE e.is_trainee = 1 AND e.emp_left_org = 0 AND e.status = 'active'";
                        $trainee_view_result = mysqli_query($con, $trainee_view_query);

                        if (mysqli_num_rows($trainee_view_result) > 0) {
                            while ($trainee = mysqli_fetch_assoc($trainee_view_result)) { ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-warning text-white">
                                            <i class="fas fa-user-graduate mr-2"></i>Trainee
                                        </div>
                                        <div class="card-body text-center">
                                            <img src="<?php echo !empty($trainee['profile_pic']) ? 'uploads/profile_pics/' . basename($trainee['profile_pic']) : 'uploads/profile_pics/default.jpg'; ?>"
                                                class="rounded-circle mb-3" style="width: 80px; height: 80px;">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($trainee['full_name'] ?? ''); ?></h6>
                                            <p class="text-muted small mb-2">
                                                <?php echo htmlspecialchars($trainee['designation'] ?? ''); ?>
                                            </p>
                                            <div class="badge badge-warning mb-3">
                                                <?php echo htmlspecialchars($trainee['department_name'] ?? 'No Department'); ?>
                                            </div>
                                            <div class="text-left small">
                                                <p><i class="fas fa-calendar-alt mr-2"></i>Joined:
                                                    <?php echo date('d M Y', strtotime($trainee['doj'])); ?>
                                                </p>
                                                <p><i
                                                        class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($trainee['email'] ?? ''); ?>
                                                </p>
                                                <p><i
                                                        class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($trainee['contact'] ?? ''); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <div class="col-12 text-center mt-4">
                                <p class="text-muted">No trainee employees found.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Employees</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="form-group">
                            <label>Department</label>
                            <select class="form-control select2" name="department_id">
                                <option value="">All Departments</option>
                                <?php
                                $dept_query = "SELECT * FROM departments ORDER BY name";
                                $dept_result = mysqli_query($con, $dept_query);
                                while ($dept = mysqli_fetch_assoc($dept_result)) {
                                    echo '<option value="' . $dept['id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control select2" name="status">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" name="location">
                                <option value="">All Locations</option>
                                <?php
                                $loc_query = "SELECT DISTINCT EmpLoc FROM employees WHERE EmpLoc IS NOT NULL";
                                $loc_result = mysqli_query($con, $loc_query);
                                while ($loc = mysqli_fetch_assoc($loc_result)) {
                                    echo '<option value="' . htmlspecialchars($loc['EmpLoc']) . '">'
                                        . htmlspecialchars($loc['EmpLoc']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    include_once('footer.php');
    ?>

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
                    <a class="btn btn-success" href="https://communik.san-solutions.in/login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include your existing scripts -->
    <script src="js/show_password1.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Add your custom scripts -->
    <script>
        $(document).ready(function () {
            // Initialize DataTable - single initialization
            $('#employeeTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search employees..."
                }
            });

            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#filterModal')
            });
        });

        $(document).ready(function () {
            // Initialize DataTable and Select2 code remains the same...

            // Updated viewDetails function
            window.viewDetails = function (employeeId) {
                // Show loading state
                $('#employeeDetailsModal').modal('show');
                $('#employeeDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>');

                $.ajax({
                    url: 'get_employee_details.php',
                    type: 'POST',
                    data: { id: employeeId },
                    success: function (response) {
                        $('#employeeDetailsContent').html(response);
                    },
                    error: function (xhr, status, error) {
                        $('#employeeDetailsContent').html('<div class="alert alert-danger">Error loading employee details: ' + error + '</div>');
                        console.error('Ajax Error:', error);
                    }
                });
            }
        });
    </script>
    <script>
        function applyFilters() {
            var formData = $('#filterForm').serialize();
            $.ajax({
                url: 'filter_employees.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#grid-view .row').html(response);
                    $('#filterModal').modal('hide');
                },
                error: function (xhr, status, error) {
                    alert('Error applying filters: ' + error);
                }
            });
        }

        function exportToExcel() {
            window.location.href = 'export_employees.php?format=excel';
        }

        function exportToPDF() {
            window.location.href = 'export_employees.php?format=pdf';
        }

        function printCurrentView() {
            window.print();
        }
    </script>



    <!-- Employee Details Modal -->
    <!-- Place this right before </body> -->
    <div class="modal fade" id="employeeDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="employeeDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>
