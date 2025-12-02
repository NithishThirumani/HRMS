<?php
include('connection.php');
include('session.php');

if (!isset($_GET['id'])) {
    header("Location: view_trainees.php");
    exit();
}

$trainee_id = mysqli_real_escape_string($mysqli, $_GET['id']);

// Fetch trainee details with department name
$query = "SELECT t.*, d.name as department_name 
          FROM trainees t 
          LEFT JOIN departments d ON t.department_id = d.id 
          WHERE t.trainee_id = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $trainee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_trainees.php");
    exit();
}

$trainee = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Trainee Details - <?php echo $trainee['trainee_id']; ?></title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .detail-card {
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .section-title {
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .detail-label {
            font-weight: bold;
            color: #4e73df;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-active { background-color: #1cc88a; color: white; }
        .status-inactive { background-color: #e74a3b; color: white; }
        .status-completed { background-color: #f6c23e; color: white; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Trainee Details</h1>
                        <div>
                            <a href="edit_trainee.php?id=<?php echo $trainee_id; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit fa-sm"></i> Edit Details
                            </a>
                            <a href="view_trainees.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left fa-sm"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Basic Information Card -->
                    <div class="card shadow mb-4 detail-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary section-title">
                                <i class="fas fa-user mr-2"></i>Basic Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Trainee ID:</span>
                                    <div><?php echo $trainee['trainee_id']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Full Name:</span>
                                    <div><?php echo $trainee['full_name']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Email:</span>
                                    <div><?php echo $trainee['email']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Status:</span>
                                    <div>
                                        <span class="status-badge status-<?php echo $trainee['status']; ?>">
                                            <?php echo ucfirst($trainee['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Details Card -->
                    <div class="card shadow mb-4 detail-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary section-title">
                                <i class="fas fa-address-card mr-2"></i>Personal Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Date of Birth:</span>
                                    <div><?php echo date('d-m-Y', strtotime($trainee['birthday'])); ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Gender:</span>
                                    <div><?php echo $trainee['gender']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Marital Status:</span>
                                    <div><?php echo $trainee['marital_status']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Blood Group:</span>
                                    <div><?php echo $trainee['blood_group'] ?: 'Not Specified'; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Contact:</span>
                                    <div><?php echo $trainee['contact']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Country:</span>
                                    <div><?php echo $trainee['country']; ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <span class="detail-label">Address:</span>
                                    <div><?php echo $trainee['address']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Education Details Card -->
                    <div class="card shadow mb-4 detail-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary section-title">
                                <i class="fas fa-graduation-cap mr-2"></i>Education Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Degree/Course:</span>
                                    <div><?php echo $trainee['degree']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Institute:</span>
                                    <div><?php echo $trainee['institute']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Start Date:</span>
                                    <div><?php echo $trainee['education_start'] ? date('d-m-Y', strtotime($trainee['education_start'])) : 'Not Specified'; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">End Date:</span>
                                    <div><?php echo $trainee['education_end'] ? date('d-m-Y', strtotime($trainee['education_end'])) : 'Not Specified'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Training Details Card -->
                    <div class="card shadow mb-4 detail-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary section-title">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>Training Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Department:</span>
                                    <div><?php echo $trainee['department_name']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Designation:</span>
                                    <div><?php echo $trainee['designation']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Location:</span>
                                    <div><?php echo $trainee['location']; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Joining Date:</span>
                                    <div><?php echo date('d-m-Y', strtotime($trainee['date_of_joining'])); ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Training Duration:</span>
                                    <div><?php echo $trainee['training_duration']; ?> months</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Reporting Manager:</span>
                                    <div><?php echo $trainee['reporting_manager']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visa Details Card -->
                    <div class="card shadow mb-4 detail-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary section-title">
                                <i class="fas fa-passport mr-2"></i>Visa Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Visa Number:</span>
                                    <div><?php echo $trainee['visa_number'] ?: 'Not Available'; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Visa Type:</span>
                                    <div><?php echo $trainee['visa_type'] ?: 'Not Available'; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Issue Date:</span>
                                    <div><?php echo $trainee['visa_issue_date'] ? date('d-m-Y', strtotime($trainee['visa_issue_date'])) : 'Not Available'; ?></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <span class="detail-label">Expiry Date:</span>
                                    <div><?php echo $trainee['visa_expiry_date'] ? date('d-m-Y', strtotime($trainee['visa_expiry_date'])) : 'Not Available'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html> 