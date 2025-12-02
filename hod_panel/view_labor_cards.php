<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Fetch labor card details
$query = "SELECT e.id, e.eid, e.full_name, d.name as department, e.designation, e.MOLID, 
          e.labour_card_no, e.labour_card_start_date, e.labour_card_end_date, 
          e.status, e.profile_pic 
          FROM employees e
          LEFT JOIN departments d ON e.department_id = d.id 
          WHERE e.MOLID IS NOT NULL";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Labor Card Details</title>

    <!-- Custom fonts and styles -->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .labor-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }

        .labor-card:hover {
            transform: translateY(-5px);
        }

        .expiry-warning {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Labor Card Management</h1>
            <div class="d-flex">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <!-- Total Labor Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Labor Cards
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo mysqli_num_rows($result); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-id-card fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expiring Within
                                    30 Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $expiring_query = "SELECT COUNT(*) as count FROM employees 
                  WHERE labour_card_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                                    $expiring_result = mysqli_query($con, $expiring_query);
                                    echo mysqli_fetch_assoc($expiring_result)['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expired Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired Cards
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $expired_query = "SELECT COUNT(*) as count FROM employees 
                WHERE labour_card_end_date < CURDATE()";
                                    $expired_result = mysqli_query($con, $expired_query);
                                    echo mysqli_fetch_assoc($expired_result)['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valid Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Valid Cards</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $valid_query = "SELECT COUNT(*) as count FROM employees 
              WHERE labour_card_end_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                                    $valid_result = mysqli_query($con, $valid_query);
                                    echo mysqli_fetch_assoc($valid_result)['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Labor Cards Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Labor Card Details</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="laborTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>EID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>MOL ID</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Check if labour_card_end_date is null before passing to strtotime
                                $expiry_date = !empty($row['labour_card_end_date']) ? strtotime($row['labour_card_end_date']) : false;
                                $today = strtotime('today');
                                $days_remaining = $expiry_date ? ceil(($expiry_date - $today) / (60 * 60 * 24)) : -999;

                                if ($days_remaining < 0) {
                                    $status_class = 'danger';
                                    $status_text = 'Expired';
                                } elseif ($days_remaining <= 30) {
                                    $status_class = 'warning';
                                    $status_text = 'Expiring Soon';
                                } else {
                                    $status_class = 'success';
                                    $status_text = 'Valid';
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['eid'] ?? ''); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/<?php echo $row['profile_pic'] ?: 'default.jpg'; ?>"
                                                class="rounded-circle mr-2" style="width: 40px; height: 40px;">
                                            <div>
                                                <?php echo htmlspecialchars($row['full_name'] ?? ''); ?><br>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($row['designation'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['department'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['MOLID'] ?? ''); ?></td>
                                    <td>
                                        <?php echo !empty($row['labour_card_end_date']) ? date('d-m-Y', strtotime($row['labour_card_end_date'])) : 'Not Set'; ?>
                                        <br>
                                        <small class="text-<?php echo $status_class; ?>">
                                            <?php
                                            if (!$expiry_date) {
                                                echo "No expiry date";
                                            } elseif ($days_remaining < 0) {
                                                echo "Expired " . abs($days_remaining) . " days ago";
                                            } else {
                                                echo $days_remaining . " days remaining";
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick="viewDetails(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit_emp1.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
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

    <!-- Include Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    // Add this modal HTML before the closing </div> of container-fluid
    ?>
    <!-- Employee Details Modal -->
    <div class="modal fade" id="employeeDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Labor Card Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="employeeDetailsContent">
                </div>
            </div>
        </div>
    </div>

    <!-- Update the viewDetails function in your JavaScript -->
    <script>
        function viewDetails(employeeId) {
            $.ajax({
                url: 'get_labor_details.php',
                type: 'POST',
                data: { id: employeeId },
                success: function (response) {
                    $('#employeeDetailsContent').html(response);
                    $('#employeeDetailsModal').modal('show');
                },
                error: function (xhr, status, error) {
                    alert('Error loading employee details: ' + error);
                }
            });
        }
    </script>

    function exportToExcel() {
    window.location.href = 'export_labor_cards.php?format=excel';
    }

    function exportToPDF() {
    window.location.href = 'export_labor_cards.php?format=pdf';
    }
    </script>

</body>

</html>