<?php
include('session.php');
include('connection.php');

if (!isset($_GET['id'])) {
    header("Location: onboarding_dashboard.php");
    exit();
}

$employee_id = mysqli_real_escape_string($con, $_GET['id']);

// Get employee details
$query = "SELECT e.*, d.name as department_name 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          WHERE e.id = '$employee_id'";
$result = mysqli_query($con, $query);
$employee = mysqli_fetch_assoc($result);

// Get document status
$doc_query = "SELECT * FROM document_status WHERE employee_id = '$employee_id'";
$doc_result = mysqli_query($con, $doc_query);
$doc_status = mysqli_fetch_assoc($doc_result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Document Status Details</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .status-card {
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .status-yes {
            background-color: #1cc88a;
            color: white;
        }
        .status-no {
            background-color: #e74a3b;
            color: white;
        }
        .status-na {
            background-color: #858796;
            color: white;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Document Status Details</h1>
            <a href="onboarding_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Dashboard
            </a>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['full_name']); ?></p>
                                <p><strong>Employee Code:</strong> <?php echo htmlspecialchars($employee['eid']); ?></p>
                                <p><strong>Designation:</strong> <?php echo htmlspecialchars($employee['designation']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department_name']); ?></p>
                                <p><strong>Date of Joining:</strong> <?php echo date('d M Y', strtotime($employee['doj'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge badge-<?php echo $employee['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($employee['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php
            $documents = [
                'biometric_login' => 'Biometric Login',
                'process_evaluation' => 'Process Evaluation',
                'offer_letter' => 'Offer Letter',
                'resume' => 'Resume',
                'kyc' => 'KYC',
                'policy_docs' => 'Policy Docs',
                'staff_id' => 'Staff ID',
                'deem_id_request' => 'Deem ID Request'
            ];

            foreach ($documents as $key => $title) {
                $status = isset($doc_status[$key]) ? $doc_status[$key] : 'No';
                $statusClass = $status == 'Yes' ? 'status-yes' : ($status == 'NA' ? 'status-na' : 'status-no');
            ?>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow status-card">
                    <div class="card-body">
                        <div class="text-center">
                            <h5 class="font-weight-bold text-primary mb-3"><?php echo $title; ?></h5>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $status; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <?php if (isset($doc_status['remarks']) && !empty($doc_status['remarks'])) { ?>
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Remarks</h6>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($doc_status['remarks'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php include('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>