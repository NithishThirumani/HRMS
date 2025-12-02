<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';
// Fetch all employee details
$query = "SELECT e.*, d.name as department_name, d.id as department_id 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE e.department_id = '{$_SESSION['department_id']}'";
$result = mysqli_query($con, $query);


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
            transition: transform 0.2s;
            margin-bottom: 20px;
        }

        .employee-card:hover {
            transform: translateY(-5px);
        }

        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
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
        }

        .employee-card:hover .action-buttons {
            display: block;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .document-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin: 2px;
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
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Employees
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $total_query = "SELECT COUNT(*) as total FROM employees";
                                    $total_result = mysqli_query($con, $total_query);
                                    $total = mysqli_fetch_assoc($total_result)['total'];
                                    echo $total;
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


            <!-- Add more statistics cards here -->

            <!-- Male Employees Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card border-left-info h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Male Employees</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $male_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Male'";
                                    $male_result = mysqli_query($con, $male_query);
                                    echo mysqli_fetch_assoc($male_result)['total'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-male fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Female Employees Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Female Employees
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $female_query = "SELECT COUNT(*) as total FROM employees WHERE gender = 'Female'";
                                    $female_result = mysqli_query($con, $female_query);
                                    echo mysqli_fetch_assoc($female_result)['total'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-female fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Stats Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Departments</div>
                                <div class="small">
                                    <?php
                                    // Update department stats query
                                    $dept_stats_query = "SELECT d.name as department, COUNT(*) as count 
                                    FROM employees e 
                                    JOIN departments d ON e.department_id = d.id 
                                    GROUP BY d.id, d.name 
                                    ORDER BY count DESC 
                                    LIMIT 3";
                                    $dept_stats_result = mysqli_query($con, $dept_stats_query);
                                    while ($dept = mysqli_fetch_assoc($dept_stats_result)) {
                                        echo "<div class='mb-1'>" . htmlspecialchars($dept['department']) .
                                            ": <span class='font-weight-bold'>" . $dept['count'] . "</span></div>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
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
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- Grid View -->
                <div class="tab-pane fade show active" id="grid-view">
                    <div class="row">
                        <?php mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card employee-card">
                                    <div
                                        class="status-badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </div>
                                    <div class="action-buttons">


                                        <button class="btn btn-sm btn-info"
                                            onclick="viewDetails(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <a href="../generate_id_card.php?id=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-id-card"></i>
                                        </a>


                                    </div>
                                    <div class="card-body text-center">
                                        <img src="<?php echo !empty($row['profile_pic']) ? '/emps/admin_panel/uploads/profile_pics/' . basename($row['profile_pic']) : 'uploads/profile_pics/default.jpg'; ?>"
                                            class="profile-img mb-3" alt="Profile Picture">
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['full_name'] ?? ''); ?>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            <?php echo htmlspecialchars($row['designation'] ?? ''); ?>
                                        </p>
                                        <div class="badge badge-primary mb-2">
                                            <?php echo htmlspecialchars($row['department_name'] ?? 'No Department'); ?>
                                        </div>
                                        <hr>
                                        <div class="text-left small">
                                            <p><i
                                                    class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($row['email'] ?? ''); ?>
                                            </p>
                                            <p><i
                                                    class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($row['contact'] ?? ''); ?>
                                            </p>
                                            <p><i
                                                    class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($row['EmpLoc'] ?? ''); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Table View -->
                <!-- Table View -->
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
                                <?php mysqli_data_seek($result, 0);
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['eid'] ?? ''); ?></td>
                                        <td class="text-center">
                                            <img src="<?php echo !empty($row['profile_pic']) ? '/emps/admin_panel/uploads/profile_pics/' . basename($row['profile_pic']) : '/emps/admin_panel/uploads/profile_pics/default.jpg'; ?>"
                                                class="rounded-circle" style="width: 50px; height: 50px;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></strong><br>
                                            <small
                                                class="text-muted"><?php echo htmlspecialchars($row['designation'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['department_name'] ?? 'No Department', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope mr-1"></i>
                                            <?php echo htmlspecialchars($row['email'] ?? ''); ?><br>
                                            <i class="fas fa-phone mr-1"></i>
                                            <?php echo htmlspecialchars($row['contact'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <?php if ($row['visa_doc']): ?>
                                                <a href="<?php echo 'uploads/documents/' . basename($row['visa_doc']); ?>"
                                                    class="btn btn-sm btn-outline-info mr-1" target="_blank">
                                                    <i class="fas fa-passport"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($row['passport_doc']): ?>
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
                                            <span
                                                class="badge badge-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info"
                                                    onclick="viewDetails(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                            </div>
                                        </td>
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
                                                <img src="<?php echo !empty($emp['profile_pic']) ? '/emps/admin_panel/uploads/profile_pics/' . basename($emp['profile_pic']) : '/emps/admin_panel/uploads/profile_pics/default.jpg'; ?>"
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
                    <a class="btn btn-success" href="https:/emps/admin_panel/logout.php">Logout</a>
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