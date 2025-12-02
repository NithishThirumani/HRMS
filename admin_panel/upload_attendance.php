<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Attendance - Admin Panel</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upload Attendance Data</h6>
            </div>
            <div class="card-body">
                <!-- Upload Form -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="process_attendance_upload.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Upload Excel/CSV File</label>
                                <input type="file" class="form-control" name="attendance_file" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="alert alert-info">
                                <h6>File Format Requirements:</h6>
                                <p>Please ensure your Excel/CSV file contains the following columns:</p>
                                <ul>
                                    <li>Full Name</li>
                                    <li>Employee Code</li>
                                    <li>Visa Under</li>
                                    <li>Manager/TL</li>
                                    <li>Status</li>
                                    <li>Designation</li>
                                    <li>Client Team</li>
                                    <li>Mobile No</li>
                                    <li>Email</li>
                                    <li>Date of Joining</li>
                                    <li>Offer Letter Issued</li>
                                    <li>Payroll Start Date</li>
                                    <li>Total Absent Days</li>
                                    <li>Late Entries</li>
                                    <li>Sick Leave</li>
                                    <li>Approved Leave</li>
                                    <li>Half Days</li>
                                    <li>Annual Leave</li>
                                    <li>On-Time Entries</li>
                                    <li>Payable Days</li>
                                </ul>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Attendance Data</button>
                        </form>
                    </div>
                </div>

                <!-- Sample Template Download -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Download Template</h5>
                                <p class="card-text">Download the sample Excel template for attendance upload.</p>
                                <a href="templates/attendance_template.xlsx" class="btn btn-success">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
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