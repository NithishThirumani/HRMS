<?php
include('../session.php');
include('../connection.php');
include('includes/leave_functions.php');

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Get the numeric ID of the current HOD
$hod_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $hod_numeric_id = $result->fetch_assoc()['id'];
}

// Get counts for dashboard
$recommendation_count = 0;
$approval_count = 0;

if ($hod_numeric_id) {
    // Count pending recommendations
    $stmt = $con->prepare("
        SELECT COUNT(*) as count FROM leaves l 
        LEFT JOIN employees e ON l.emp_id = e.eid 
        WHERE l.status = 'Pending' 
        AND (l.recommender_id IS NULL OR l.recommender_id = 0)
        AND (
            l.hod_id = ? 
            OR EXISTS (
                SELECT 1 FROM leave_hierarchy lh 
                WHERE lh.employee_id = e.id 
                AND lh.recommender_id = ? 
                AND lh.type = 'recommender'
            )
        )
    ");
    $stmt->bind_param("ii", $hod_numeric_id, $hod_numeric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recommendation_count = $result->fetch_assoc()['count'];

    // Count pending approvals
    $stmt = $con->prepare("
        SELECT COUNT(*) as count FROM leaves l 
        LEFT JOIN employees e ON l.emp_id = e.eid 
        WHERE l.status = 'Recommended' 
        AND l.recommender_id != 0
        AND (l.approver_id IS NULL OR l.approver_id = 0)
        AND (
            l.hod_id = ? 
            OR EXISTS (
                SELECT 1 FROM leave_hierarchy lh 
                WHERE lh.employee_id = e.id 
                AND lh.approver_id = ? 
                AND lh.type = 'approver'
            )
        )
    ");
    $stmt->bind_param("ii", $hod_numeric_id, $hod_numeric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $approval_count = $result->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leave Workflow - HOD Panel</title>
    <link href="img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        .workflow-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .workflow-card:hover {
            transform: translateY(-5px);
        }
        .workflow-step {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
        }
        .step-number {
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('../sidebar.php'); ?>
  
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
    <?php include('../header.php'); ?>
    
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Leave Workflow</h1>
        </div>

        <!-- Workflow Explanation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">How the Leave Workflow Works</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="workflow-step">
                                    <div class="step-number">1</div>
                                    <h5>Recommendation Phase</h5>
                                    <p>Review leave requests and decide whether to recommend them for approval or not recommend them.</p>
                                    <ul>
                                        <li>View pending leave requests</li>
                                        <li>Review employee details and leave information</li>
                                        <li>Add optional remarks</li>
                                        <li>Recommend or Not Recommend</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="workflow-step">
                                    <div class="step-number">2</div>
                                    <h5>Approval Phase</h5>
                                    <p>Review recommended leaves and make final approval or rejection decisions.</p>
                                    <ul>
                                        <li>View recommended leave requests</li>
                                        <li>See recommender remarks</li>
                                        <li>Add final remarks</li>
                                        <li>Approve or Reject</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row">
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 workflow-card" onclick="window.location.href='hod_recommendation.php'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Recommendation
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $recommendation_count; ?> Pending
                                </div>
                                <div class="text-xs text-gray-600 mt-2">
                                    Review and recommend leave requests
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 workflow-card" onclick="window.location.href='hod_approval.php'">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Approval
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $approval_count; ?> Pending
                                </div>
                                <div class="text-xs text-gray-600 mt-2">
                                    Approve or reject recommended leaves
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

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="hod_recommendation.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-thumbs-up"></i> Go to Recommendation
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="hod_approval.php" class="btn btn-success btn-block">
                                    <i class="fas fa-check-circle"></i> Go to Approval
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="leave_history.php" class="btn btn-info btn-block">
                                    <i class="fas fa-history"></i> View History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php include('../footer.php'); ?>
</div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

</body>
</html> 