<?php
include('session.php');
include('connection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM employees WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $employee = mysqli_fetch_assoc($result);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $visa_number = $_POST['visa_number'];
    $visa_type = $_POST['visa_type'];
    $visa_issue_date = $_POST['visa_issue_date'];
    $visa_expiry_date = $_POST['visa_expiry_date'];
    $passport_number = $_POST['passport_number'];
    $passport_type = $_POST['passport_type'];
    $passport_issue_date = $_POST['passport_issue_date'];
    $passport_expiry_date = $_POST['passport_expiry_date'];
    $country_of_issue = $_POST['country_of_issue'];
    $passport_issue_place = $_POST['passport_issue_place'];

    $update_query = "UPDATE employees SET 
        visa_number=?, visa_type=?, visa_issue_date=?, visa_expiry_date=?,
        passport_number=?, passport_type=?, passport_issue_date=?, passport_expiry_date=?,
        country_of_issue=?, passport_issue_place=? WHERE id=?";

    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssi",
        $visa_number,
        $visa_type,
        $visa_issue_date,
        $visa_expiry_date,
        $passport_number,
        $passport_type,
        $passport_issue_date,
        $passport_expiry_date,
        $country_of_issue,
        $passport_issue_place,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: visapassport_view.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Visa & Passport Details</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <!-- Include your existing CSS links -->

    <style>
        .edit-form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 20px 0;
        }

        .form-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4e73df;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 12px;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-submit {
            background: #4e73df;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #858796;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
            margin-right: 10px;
        }

        .btn-cancel:hover {
            background: #717384;
            transform: translateY(-2px);
        }

        .edit-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .edit-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            background: #4e73df;
            color: white;
            padding: 20px;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        .form-control {
            height: 45px;
            border-radius: 6px;
            border: 1px solid #e3e6f0;
            padding: 0.375rem 0.75rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        .section-divider {
            border-top: 1px solid #e3e6f0;
            margin: 1.5rem 0;
            padding-top: 1.5rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #4e73df;
            border: none;
        }

        .btn-light {
            background: #f8f9fc;
            color: #5a5c69;
            border: 1px solid #e3e6f0;
        }

        .readonly-field {
            background-color: #f8f9fc;
        }

        .section-header {
            background: #f8f9fc;
            padding: 15px 20px;
            border-left: 4px solid #4e73df;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        .section-title {
            color: #4e73df;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="edit-container">
            <div class="edit-card">
                <div class="card-header">
                    Edit Visa & Passport Details
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">

                        <!-- Basic Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Employee ID</label>
                                    <input type="text" class="form-control readonly-field"
                                        value="<?php echo $employee['eid']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" class="form-control readonly-field"
                                        value="<?php echo $employee['full_name']; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Visa Details -->
                        <div class="section-divider"></div>
                        <div class="section-header">
                            <h5 class="section-title"><i class="fas fa-visa me-2"></i>Visa Information</h5>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Visa Number</label>
                                    <input type="text" name="visa_number" class="form-control"
                                        value="<?php echo $employee['visa_number']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Visa Type</label>
                                    <select name="visa_type" class="form-control">
                                        <option value="">Select Visa Type</option>
                                        <option value="Employer Sponsored Visa" <?php echo ($employee['visa_type'] == 'Employer Sponsored Visa') ? 'selected' : ''; ?>>
                                            Employer Sponsored Visa</option>
                                        <option value="Family Visa" <?php echo ($employee['visa_type'] == 'Family Visa') ? 'selected' : ''; ?>>Family Visa</option>
                                        <option value="Spouse Visa" <?php echo ($employee['visa_type'] == 'Spouse Visa') ? 'selected' : ''; ?>>Spouse Visa</option>
                                        <option value="Freelance Visa" <?php echo ($employee['visa_type'] == 'Freelance Visa') ? 'selected' : ''; ?>>Freelance Visa</option>
                                        <option value="Emp Change" <?php echo ($employee['visa_type'] == 'Emp Change') ? 'selected' : ''; ?>>Emp Change</option>
                                        <option value="Employement Visa" <?php echo ($employee['visa_type'] == 'Employement Visa') ? 'selected' : ''; ?>>
                                            Employement Visa</option>
                                        <option value="Entry Permit Visa" <?php echo ($employee['visa_type'] == 'Entry Permit Visa') ? 'selected' : ''; ?>>Entry Permit Visa</option>
                                        <option value="Job Search Visa" <?php echo ($employee['visa_type'] == 'Job Search Visa') ? 'selected' : ''; ?>>Job Search Visa</option>
                                        <option value="Tourist" <?php echo ($employee['visa_type'] == 'Tourist') ? 'selected' : ''; ?>>Tourist</option>
                                        <option value="Business" <?php echo ($employee['visa_type'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                                        <option value="Work" <?php echo ($employee['visa_type'] == 'Work') ? 'selected' : ''; ?>>Work</option>
                                        <option value="Student" <?php echo ($employee['visa_type'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                                        <option value="Other" <?php echo ($employee['visa_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" name="visa_issue_date" class="form-control"
                                        value="<?php echo $employee['visa_issue_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="visa_expiry_date" class="form-control"
                                        value="<?php echo $employee['visa_expiry_date']; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Passport Details -->
                        <div class="section-divider"></div>
                        <div class="section-header">
                            <h5 class="section-title"><i class="fas fa-passport me-2"></i>Passport Information</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Passport Number</label>
                                    <input type="text" name="passport_number" class="form-control"
                                        value="<?php echo $employee['passport_number']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Passport Type</label>
                                    <select name="passport_type" class="form-control">
                                        <option value="">Select Passport Type</option>
                                        <option value="Ordinary" <?php echo ($employee['passport_type'] == 'Ordinary') ? 'selected' : ''; ?>>Ordinary</option>
                                        <option value="Diplomatic" <?php echo ($employee['passport_type'] == 'Diplomatic') ? 'selected' : ''; ?>>Diplomatic</option>
                                        <option value="Official" <?php echo ($employee['passport_type'] == 'Official') ? 'selected' : ''; ?>>Official</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Country of Issue</label>
                                    <input type="text" name="country_of_issue" class="form-control"
                                        value="<?php echo $employee['country_of_issue']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" name="passport_issue_date" class="form-control"
                                        value="<?php echo $employee['passport_issue_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="passport_expiry_date" class="form-control"
                                        value="<?php echo $employee['passport_expiry_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Issue Place</label>
                                    <input type="text" name="passport_issue_place" class="form-control"
                                        value="<?php echo $employee['passport_issue_place']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="visapassport_view.php" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php
    include_once('footer.php');
    ?>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
</body>

</html>