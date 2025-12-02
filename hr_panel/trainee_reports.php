<?php

include('connection.php');
include('session.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Trainee Reports</title>
    <!-- Custom fonts and styles -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Trainee Reports</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Download Reports</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Report Type</th>
                                            <th>Description</th>
                                            <th>Included Information</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Excel Report (.xls)</td>
                                            <td>Complete trainee data in spreadsheet format</td>
                                            <td>
                                                <ul class="mb-0">
                                                    <li>Personal Information</li>
                                                    <li>Contact Details</li>
                                                    <li>Department & Designation</li>
                                                    <li>Training Details</li>
                                                    <li>Educational Background</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <a href="trainee_excel_export.php" class="btn btn-success btn-sm">
                                                    <i class="fas fa-download fa-sm"></i> Download Excel
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PDF Report (.pdf)</td>
                                            <td>Formatted report with essential trainee information</td>
                                            <td>
                                                <ul class="mb-0">
                                                    <li>Basic Information</li>
                                                    <li>Department Details</li>
                                                    <li>Training Status</li>
                                                    <li>Contact Information</li>
                                                    <li>Current Location</li>
                                                </ul>
                                            </td>
                                            <td>
                                                <a href="trainee_pdf_export.php" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-download fa-sm"></i> Download PDF
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i> Reports are generated in real-time with current data from the database.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('footer.php'); ?>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html> 