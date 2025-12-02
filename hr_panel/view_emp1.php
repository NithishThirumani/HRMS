<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';

// FOOLPROOF NULL-SAFE HTML ESCAPE
function safe_html($value) {
    if ($value === null || $value === '') {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Format date for display
function format_date($date) {
    if (!$date) return 'N/A';
    return date('Y-m-d', strtotime($date));
}

// Debug connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch employees with proper joins and conditions - REMOVED restrictive conditions
$query = "SELECT 
    e.id, 
    e.eid, 
    e.first_name,
    e.last_name, 
    e.full_name, 
    e.email, 
    e.birthday, 
    e.maritalsts, 
    e.contact, 
    e.address, 
    e.country,
    e.degree, 
    e.start_from, 
    e.end_to, 
    e.Institute,
    e.status,
    e.doj,
    e.EmpLoc,
    e.EmpDiv,
    e.EmpGrade,
    d.name AS department,
    e.designation,
    e.reporting_manager,
    e.emp_left_org,
    e.profile_pic
FROM 
    employees e
LEFT JOIN 
    departments d ON e.department_id = d.id
ORDER BY 
    e.full_name ASC";

// Debug query
echo "<!-- SQL Query: " . htmlspecialchars($query) . " -->";

$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

$employee_count = mysqli_num_rows($result);
echo "<!-- Debug: Found $employee_count employees -->";
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Employee Management - Communik Marketing</title>
    
    <!-- Favicon -->
    <link href="img/favicon.png" rel="icon">
    
    <!-- Custom fonts and styles -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(to right, var(--primary-color), #224abe);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem 1.25rem;
        }

        .card-header h6 {
            font-weight: 600;
            margin: 0;
        }

        .nav-tabs {
            border: none;
            margin-bottom: -1px;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 30px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--secondary-color);
            transition: all 0.3s;
            margin-right: 0.5rem;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(78, 115, 223, 0.1);
        }

        .nav-tabs .nav-link.active {
            color: white;
            background: linear-gradient(to right, var(--primary-color), #224abe);
        }

        .filter-card {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .select2-container--bootstrap4 .select2-selection {
            border-radius: 10px;
            padding: 0.375rem 0.75rem;
            height: calc(1.5em + 0.75rem + 2px);
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #d1d3e2;
        }

        .btn {
            border-radius: 10px;
            padding: 0.375rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-export {
            color: white;
            padding: 0.5rem 1.5rem;
            margin-right: 0.5rem;
        }

        .btn-export i {
            margin-right: 0.5rem;
        }

        .table {
            color: #5a5c69;
        }

        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5em 1em;
            border-radius: 30px;
            font-weight: 500;
        }

        /* Status badge styles */
        .badge-success {
            background-color: var(--success-color) !important;
            color: white !important;
        }

        .badge-danger {
            background-color: var(--danger-color) !important;
            color: white !important;
        }

        /* Status badge styles */
        .badge-success {
            background-color: var(--success-color);
            color: white;
        }

        .badge-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            margin: 0 2px;
        }

        .dataTables_info {
            color: var(--secondary-color);
        }

        .page-item.active .page-link {
            background: linear-gradient(to right, var(--primary-color), #224abe);
            border-color: var(--primary-color);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .btn-export {
                margin-bottom: 0.5rem;
                width: 100%;
            }

            .filter-card {
                margin-bottom: 1rem;
            }
        }

        /* Custom Styles */
        .filter-card {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            margin: 0 2px;
        }

        .avatar img {
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .badge {
            padding: 0.5em 1em;
            font-size: 85%;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .nav-tabs .nav-link {
            padding: 1rem 1.5rem;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #4e73df;
            border-color: #4e73df #4e73df #fff;
        }

        .table-responsive {
            margin: 0;
        }

        #personalTable_wrapper,
        #educationTable_wrapper,
        #employmentTable_wrapper {
            padding: 0;
        }

        .dataTables_filter input {
            margin-left: 0.5em;
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            padding: 0.375rem 0.75rem;
        }

        .dataTables_length select {
            border-radius: 4px;
            border: 1px solid #d1d3e2;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        }
    </style>
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Employee Management 
                
            </h1>
            <div class="btn-group">
                <button class="btn btn-success btn-export" id="exportExcel">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn btn-danger btn-export" id="exportPDF">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-info btn-export" id="printTable">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card shadow">
            <!-- Card Header with Tabs -->
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">
                            <i class="fas fa-user mr-2"></i>Personal Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab">
                            <i class="fas fa-graduation-cap mr-2"></i>Education
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="employment-tab" data-toggle="tab" href="#employment" role="tab">
                            <i class="fas fa-briefcase mr-2"></i>Employment
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- Filters Section -->
                <div class="filter-card mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="departmentFilter">Department</label>
                            <select class="form-control select2" id="departmentFilter">
                                <option value="">All Departments</option>
                                <?php
                                $dept_query = "SELECT DISTINCT d.name FROM departments d 
                                             INNER JOIN employees e ON d.id = e.department_id 
                                             WHERE d.name IS NOT NULL 
                                             ORDER BY d.name";
                                $dept_result = mysqli_query($con, $dept_query);
                                while ($dept = mysqli_fetch_assoc($dept_result)) {
                                    echo '<option value="' . safe_html($dept['name']) . '">' . safe_html($dept['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="statusFilter">Status</label>
                            <select class="form-control select2" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="locationFilter">Location</label>
                            <select class="form-control select2" id="locationFilter">
                                <option value="">All Locations</option>
                                <?php
                                $loc_query = "SELECT DISTINCT EmpLoc FROM employees WHERE EmpLoc IS NOT NULL ORDER BY EmpLoc";
                                $loc_result = mysqli_query($con, $loc_query);
                                while ($loc = mysqli_fetch_assoc($loc_result)) {
                                    echo '<option value="' . safe_html($loc['EmpLoc']) . '">' . safe_html($loc['EmpLoc']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-primary w-100" id="resetFilters">
                                <i class="fas fa-sync-alt mr-2"></i>Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="employeeTabContent">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="personalTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>EID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo safe_html($row['eid']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar mr-2">
                                                        <?php 
                                                        $profile_pic = $row['profile_pic'] ?? '';
                                                        if ($profile_pic) {
                                                            if (strpos($profile_pic, 'http') === 0 || strpos($profile_pic, '/admin_panel/') === 0) {
                                                                // use as-is
                                                            } else {
                                                                $profile_pic = ltrim($profile_pic, '/');
                                                                $profile_pic = preg_replace('#^(uploads/profile_pics/)?#', '', $profile_pic);
                                                                $profile_pic = '/admin_panel/uploads/profile_pics/' . $profile_pic;
                                                            }
                                                        } else {
                                                            $profile_pic = '/img/undraw_profile.svg';
                                                        }
                                                        echo '<img src="' . safe_html($profile_pic) . '" class="rounded-circle" width="30">';
                                                        ?>
                                                    </div>
                                                    <?php echo safe_html($row['full_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo safe_html($row['email']); ?></td>
                                            <td><?php echo safe_html($row['contact']); ?></td>
                                            <td><?php echo safe_html($row['EmpLoc']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo (strtolower($row['status']) == 'active') ? 'success' : 'danger'; ?>">
                                                    <?php echo safe_html($row['status']); ?>
                                                </span>
                                            </td>
                                            <td class="action-btns">
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_emp.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this employee?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Education Tab -->
                    <div class="tab-pane fade" id="education" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="educationTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>EID</th>
                                        <th>Full Name</th>
                                        <th>Degree</th>
                                        <th>Institute</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo safe_html($row['eid']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar mr-2">
                                                        <?php 
                                                        $profile_pic = $row['profile_pic'] ?? '';
                                                        if ($profile_pic) {
                                                            if (strpos($profile_pic, 'http') === 0 || strpos($profile_pic, '/admin_panel/') === 0) {
                                                                // use as-is
                                                            } else {
                                                                $profile_pic = ltrim($profile_pic, '/');
                                                                $profile_pic = preg_replace('#^(uploads/profile_pics/)?#', '', $profile_pic);
                                                                $profile_pic = '/admin_panel/uploads/profile_pics/' . $profile_pic;
                                                            }
                                                        } else {
                                                            $profile_pic = '/img/undraw_profile.svg';
                                                        }
                                                        echo '<img src="' . safe_html($profile_pic) . '" class="rounded-circle" width="30">';
                                                        ?>
                                                    </div>
                                                    <?php echo safe_html($row['full_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo safe_html($row['degree']); ?></td>
                                            <td><?php echo safe_html($row['Institute']); ?></td>
                                            <td>
                                                <?php 
                                                $start = format_date($row['start_from']);
                                                $end = format_date($row['end_to']);
                                                echo $start === 'N/A' || $end === 'N/A' ? 'N/A' : "$start - $end";
                                                ?>
                                            </td>
                                            <td class="action-btns">
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_emp.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this employee?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Employment Tab -->
                    <div class="tab-pane fade" id="employment" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="employmentTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>EID</th>
                                        <th>Full Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Location</th>
                                        <th>Division</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo safe_html($row['eid']); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar mr-2">
                                                        <?php 
                                                        $profile_pic = $row['profile_pic'] ?? '';
                                                        if ($profile_pic) {
                                                            if (strpos($profile_pic, 'http') === 0 || strpos($profile_pic, '/admin_panel/') === 0) {
                                                                // use as-is
                                                            } else {
                                                                $profile_pic = ltrim($profile_pic, '/');
                                                                $profile_pic = preg_replace('#^(uploads/profile_pics/)?#', '', $profile_pic);
                                                                $profile_pic = '/admin_panel/uploads/profile_pics/' . $profile_pic;
                                                            }
                                                        } else {
                                                            $profile_pic = '/img/undraw_profile.svg';
                                                        }
                                                        echo '<img src="' . safe_html($profile_pic) . '" class="rounded-circle" width="30">';
                                                        ?>
                                                    </div>
                                                    <?php echo safe_html($row['full_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo safe_html($row['department']); ?></td>
                                            <td><?php echo safe_html($row['designation']); ?></td>
                                            <td><?php echo safe_html($row['EmpLoc']); ?></td>
                                            <td><?php echo safe_html($row['EmpDiv']); ?></td>
                                            <td><?php echo safe_html($row['EmpGrade']); ?></td>
                                            <td class="action-btns">
                                                <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_emp.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this employee?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Main Content -->

    <?php include('footer.php'); ?>

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

    <!-- Page level plugins -->
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom scripts -->
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        // Initialize DataTables
        var tables = ['#personalTable', '#educationTable', '#employmentTable'].map(function(tableId) {
            return $(tableId).DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records..."
                }
            });
        });

        // Filter functionality
        $('#departmentFilter, #statusFilter, #locationFilter').on('change', function() {
            var department = $('#departmentFilter').val();
            var status = $('#statusFilter').val();
            var location = $('#locationFilter').val();

            tables.forEach(function(table) {
                table.columns().every(function() {
                    var column = this;
                    var columnText = $(column.header()).text().toLowerCase();

                    if (columnText.includes('department') && department) {
                        table.column(column.index()).search(department);
                    }
                    if (columnText.includes('status') && status) {
                        table.column(column.index()).search(status);
                    }
                    if (columnText.includes('location') && location) {
                        table.column(column.index()).search(location);
                    }
                });
                table.draw();
            });
        });

        // Reset filters
        $('#resetFilters').on('click', function() {
            $('.select2').val(null).trigger('change');
            tables.forEach(function(table) {
                table.search('').columns().search('').draw();
            });
        });

        // Export functionality
        $('#exportExcel').on('click', function() {
            window.location.href = 'export_employees.php?format=excel';
        });

        $('#exportPDF').on('click', function() {
            window.location.href = 'export_employees.php?format=pdf';
        });

        $('#printTable').on('click', function() {
            window.print();
        });

        // Loading overlay
        $(document).ajaxStart(function() {
            $('.loading-overlay').fadeIn();
        }).ajaxStop(function() {
            $('.loading-overlay').fadeOut();
        });
    });
    </script>

</body>

</html>