<?php
include('../session.php');
include('includes/leave_functions.php');

// Get recommender's numeric ID
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$result = $stmt->get_result();
$recommender = $result->fetch_assoc();
$recommender_id = $recommender['id'];

// Get pending leaves count
$query = "SELECT COUNT(*) as pending_count 
          FROM leaves l
          JOIN employees e ON l.emp_id = e.eid
          JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
          WHERE lh.recommender_id = ? AND l.status = 'Pending'";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $recommender_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['pending_count'];

// Get recommended leaves count
$query = "SELECT COUNT(*) as recommended_count 
          FROM leaves l
          JOIN employees e ON l.emp_id = e.eid
          JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
          WHERE lh.recommender_id = ? AND l.status = 'Recommended'";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $recommender_id);
$stmt->execute();
$recommended_count = $stmt->get_result()->fetch_assoc()['recommended_count'];

// Get rejected leaves count
$query = "SELECT COUNT(*) as rejected_count 
          FROM leaves l
          JOIN employees e ON l.emp_id = e.eid
          JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
          WHERE lh.recommender_id = ? AND l.status = 'Rejected'";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $recommender_id);
$stmt->execute();
$rejected_count = $stmt->get_result()->fetch_assoc()['rejected_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Recommender Dashboard</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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
                <?php include('../header.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Recommender Dashboard</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Pending Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending Leaves</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="pending_leaves.php" class="card-footer text-warning">
                                    <span class="float-left">View Details</span>
                                    <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                </a>
                            </div>
                        </div>

                        <!-- Recommended Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Recommended Leaves</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $recommended_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="recommended_leaves.php" class="card-footer text-success">
                                    <span class="float-left">View Details</span>
                                    <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                </a>
                            </div>
                        </div>

                        <!-- Rejected Leaves Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Rejected Leaves</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rejected_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="rejected_leaves.php" class="card-footer text-danger">
                                    <span class="float-left">View Details</span>
                                    <span class="float-right"><i class="fa fa-arrow-circle-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include('../footer.php'); ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

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