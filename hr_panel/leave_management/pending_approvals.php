<?php
// Include the main session file first
include('../session.php');

// Include other required files
include('../connection.php');
include('includes/leave_functions.php');

// Check if user is logged in (session.php should handle this)
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch pending approvals for the HR user
$pending_approvals_query = "SELECT l.*, e.full_name FROM leaves l JOIN employees e ON l.emp_id = e.eid WHERE l.status = 'pending' AND l.approver_id = ?";
$stmt = $con->prepare($pending_approvals_query);
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$pending_approvals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pending Approvals</title>
    
    <!-- Custom fonts -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles -->
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
                        <h1 class="h3 mb-0 text-gray-800">Pending Approvals</h1>
                    </div>

                    <!-- Pending Approvals Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Pending Leave Approvals</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Reason</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_approvals as $approval): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($approval['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $approval['type_of_leave']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($approval['start_date']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($approval['end_date']))); ?></td>
                                            <td><?php echo htmlspecialchars($approval['total_days']); ?></td>
                                            <td><?php echo htmlspecialchars($approval['reason']); ?></td>
                                            <td>
                                                <a href="approve_leave.php?id=<?php echo $approval['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                                <a href="reject_leave.php?id=<?php echo $approval['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
</body>
</html> 