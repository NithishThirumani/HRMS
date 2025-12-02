<?php
session_start();
include('connection.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $emp_id = mysqli_real_escape_string($con, $_POST['employee_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $termination_date = date('Y-m-d H:i:s');

    if ($status === 'inactive') {
        $update_sql = "UPDATE employees SET status = '$status', emp_left_org = 1, dol = '$termination_date' WHERE id = '$emp_id'";
    } else {
        $update_sql = "UPDATE employees SET status = '$status', emp_left_org = 0, dol = NULL WHERE id = '$emp_id'";
    }

    if (mysqli_query($con, $update_sql)) {
        $_SESSION['message'] = "Employee status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating status: " . mysqli_error($con);
    }
    header("Location: manage_employee_status.php");
    exit();
}

// Handle trainee upgrade
if (isset($_POST['upgrade_trainee'])) {
    $emp_id = mysqli_real_escape_string($con, $_POST['employee_id']);
    $completion_date = date('Y-m-d H:i:s');
    
    $update_sql = "UPDATE employees 
                   SET is_trainee = 0, 
                       status = 'active'
                   WHERE id = '$emp_id'";

    if (mysqli_query($con, $update_sql)) {
        $_SESSION['message'] = "Trainee successfully upgraded to permanent employee!";
    } else {
        $_SESSION['error'] = "Error upgrading trainee: " . mysqli_error($con);
    }
    header("Location: manage_employee_status.php");
    exit();
}

// Fetch employees with counts
$count_sql = "SELECT 
    SUM(CASE WHEN (emp_left_org = 0 AND status = 'active' AND (is_trainee = 0 OR is_trainee IS NULL)) THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN (emp_left_org = 1 OR status = 'inactive') THEN 1 ELSE 0 END) as inactive_count,
    SUM(CASE WHEN (emp_left_org = 0 AND status = 'active' AND is_trainee = 1) THEN 1 ELSE 0 END) as trainee_count
FROM employees
WHERE role NOT IN ('admin', 'super_admin')";
$count_result = mysqli_query($con, $count_sql);
$counts = mysqli_fetch_assoc($count_result);

// Fetch employees
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql = "SELECT 
    e.id,
    e.eid,
    e.full_name,
    e.designation,
    e.is_trainee,
    d.name as department_name,
    CASE 
        WHEN e.emp_left_org = 1 OR e.status = 'inactive' THEN 'inactive'
        ELSE 'active'
    END as current_status
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id 
    WHERE e.role NOT IN ('admin', 'super_admin')";

if ($status_filter !== 'all') {
    if ($status_filter === 'active') {
        $sql .= " AND e.emp_left_org = 0 AND e.status = 'active' AND (e.is_trainee = 0 OR e.is_trainee IS NULL)";
    } else if ($status_filter === 'trainee') {
        $sql .= " AND e.emp_left_org = 0 AND e.status = 'active' AND e.is_trainee = 1";
    } else {
        $sql .= " AND (e.emp_left_org = 1 OR e.status = 'inactive')";
    }
}

$sql .= " ORDER BY e.emp_left_org ASC, e.is_trainee DESC, e.full_name ASC";

$result = mysqli_query($con, $sql);
$all_employees = array();
while ($row = mysqli_fetch_assoc($result)) {
    $all_employees[] = $row;
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

    <title>Manage Employee Status</title>

    <!-- Load CSS files -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        /* Status-specific styles */
        .status-active {
            color: #1cc88a;
            font-weight: 600;
            background-color: rgba(28, 200, 138, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #1cc88a;
        }

        .status-inactive {
            color: #e74a3b;
            font-weight: 600;
            background-color: rgba(231, 74, 59, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #e74a3b;
        }

        .employee-row-inactive {
            background-color: rgba(231, 74, 59, 0.05);
            opacity: 0.8;
        }

        .employee-row-inactive:hover {
            background-color: rgba(231, 74, 59, 0.1);
        }

        .status-count {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .filter-card {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .filter-card .row {
            align-items: center;
        }

        .filter-buttons .btn {
            margin-right: 0.5rem;
            padding: 0.375rem 1rem;
        }

        .filter-buttons .btn.active {
            transform: translateY(1px);
        }

        .status-trainee {
            color: #f6c23e;
            font-weight: 600;
            background-color: rgba(246, 194, 62, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #f6c23e;
        }

        .employee-row-trainee {
            background-color: rgba(246, 194, 62, 0.05);
        }

        .employee-row-trainee:hover {
            background-color: rgba(246, 194, 62, 0.1);
        }

    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manage Employee Status</h1>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']); 
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']); 
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Filter and Count Section -->
        <div class="filter-card">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="mr-4 mb-2 mb-md-0">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Employees</div>
                            <div class="status-count text-success"><?php echo $counts['active_count']; ?></div>
                        </div>
                        <div class="mr-4 mb-2 mb-md-0">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Trainees</div>
                            <div class="status-count text-warning"><?php echo $counts['trainee_count']; ?></div>
                        </div>
                        <div class="mb-2 mb-md-0">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inactive Employees</div>
                            <div class="status-count text-danger"><?php echo $counts['inactive_count']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="filter-buttons text-md-right mt-3 mt-md-0">
                        <a href="?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary active' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <a href="?status=active" class="btn <?php echo $status_filter === 'active' ? 'btn-success active' : 'btn-outline-success'; ?>">
                            Active
                        </a>
                        <a href="?status=trainee" class="btn <?php echo $status_filter === 'trainee' ? 'btn-warning active' : 'btn-outline-warning'; ?>">
                            Trainees
                        </a>
                        <a href="?status=inactive" class="btn <?php echo $status_filter === 'inactive' ? 'btn-danger active' : 'btn-outline-danger'; ?>">
                            Inactive
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Employee Status Management</h6>
            </div>
            <div class="card-body">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="employeeTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>EID</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Current Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $row_count = 0;
                                foreach ($all_employees as $employee): 
                                    $row_count++;
                                    $row_class = '';
                                    if ($employee['current_status'] === 'inactive') {
                                        $row_class = 'employee-row-inactive';
                                    } elseif ($employee['is_trainee'] == 1) {
                                        $row_class = 'employee-row-trainee';
                                    }
                                ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td><?php echo htmlspecialchars($employee['eid']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($employee['full_name']); ?>
                                            <?php if ($employee['is_trainee'] == 1): ?>
                                                <span class="status-trainee ml-2">Trainee</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['department_name'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($employee['designation'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="status-<?php echo $employee['current_status']; ?>">
                                                <?php echo ucfirst($employee['current_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($employee['is_trainee'] == 1 && $employee['current_status'] === 'active'): ?>
                                                <button class="btn btn-sm btn-warning mb-1"
                                                        data-toggle="modal" data-target="#upgradeModal<?php echo $employee['id']; ?>">
                                                    <i class="fas fa-user-graduate"></i> Upgrade to Employee
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm <?php echo $employee['current_status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>"
                                                    data-toggle="modal" data-target="#statusModal<?php echo $employee['id']; ?>">
                                                <?php echo $employee['current_status'] === 'active' ? 'Terminate' : 'Reactivate'; ?>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $employee['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <?php echo $employee['current_status'] === 'active' ? 'Terminate Employee' : 'Reactivate Employee'; ?>
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to 
                                                            <?php echo $employee['current_status'] === 'active' ? 'terminate' : 'reactivate'; ?>
                                                            <strong><?php echo htmlspecialchars($employee['full_name']); ?></strong>?
                                                        </p>
                                                        <?php if ($employee['current_status'] === 'active'): ?>
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                This will mark the employee as inactive and set today as their last working day.
                                                            </div>
                                                        <?php endif; ?>
                                                        <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                        <input type="hidden" name="status" 
                                                               value="<?php echo $employee['current_status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_status" 
                                                                class="btn <?php echo $employee['current_status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>">
                                                            Confirm
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Trainee Upgrade Modal -->
                                    <?php if ($employee['is_trainee'] == 1 && $employee['current_status'] === 'active'): ?>
                                    <div class="modal fade" id="upgradeModal<?php echo $employee['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-user-graduate text-warning"></i>
                                                        Upgrade Trainee to Permanent Employee
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            You are about to upgrade <strong><?php echo htmlspecialchars($employee['full_name']); ?></strong> from trainee to permanent employee status.
                                                        </div>
                                                        <p>This action will:</p>
                                                        <ul>
                                                            <li>Change the employee status from trainee to permanent</li>
                                                            <li>Maintain their active status in the system</li>
                                                        </ul>
                                                        <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="upgrade_trainee" class="btn btn-warning">
                                                            <i class="fas fa-user-graduate"></i> Confirm Upgrade
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Debug row count -->
                        <div class="debug-info mt-3">
                            <p>Rows displayed: <?php echo $row_count; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php if (!$result): ?>
                            Database error occurred while fetching employees.
                        <?php else: ?>
                            No employees found in the system.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <!-- Load JavaScript files -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#employeeTable').DataTable({
                "order": [[1, "asc"]],
                "pageLength": 25
            });
            }

            // Move all modals to the end of the body
            $('.modal').appendTo('body');

            // Prevent nested event propagation
            $('.modal-content').on('click', function(e) {
                e.stopPropagation();
            });

            // Ensure modals close properly
            $('.modal').on('hidden.bs.modal', function() {
                if($('.modal:visible').length) {
                    $('body').addClass('modal-open');
                }
            });
        });
    </script>
</body>

</html>