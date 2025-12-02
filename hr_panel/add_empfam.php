<?php
include('session.php');
include('connection.php');

// Fetch Employee Data
$employees_result = mysqli_query($con, "SELECT eid, full_name FROM employees");
$full_name = '';
$eid = '';

if (isset($_POST['eid'])) {
    $eid = $_POST['eid'];
    $employee_query = mysqli_query($con, "SELECT full_name FROM employees WHERE eid = '$eid'");
    $employee_row = mysqli_fetch_assoc($employee_query);
    $full_name = $employee_row['full_name'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['fm_name'])) {
    $eid = $_POST['eid'];
    $fm_name = $_POST['fm_name'];
    $fm_dob = $_POST['fm_dob'];
    $fm_nationality = $_POST['fm_nationality'];
    $fm_blood_group = $_POST['fm_blood_group'];
    $fm_gender = $_POST['fm_gender'];
    $fm_profession = $_POST['fm_profession'];
    $fm_relation = $_POST['fm_relation'];
    $anniversary_date = !empty($_POST['anniversary_date']) ? $_POST['anniversary_date'] : NULL;
    $phone_number = $_POST['phone_number'];
    $email = !empty($_POST['email']) ? $_POST['email'] : NULL;
    $is_emergency_contact = $_POST['is_emergency_contact'];
    $education = !empty($_POST['education']) ? $_POST['education'] : NULL;
    $occupation_status = !empty($_POST['occupation_status']) ? $_POST['occupation_status'] : NULL;
    $medical_condition = !empty($_POST['medical_condition']) ? $_POST['medical_condition'] : NULL;
    $health_insurance_no = !empty($_POST['health_insurance_no']) ? $_POST['health_insurance_no'] : NULL;
    $govt_id = !empty($_POST['govt_id']) ? $_POST['govt_id'] : NULL;
    $current_address = !empty($_POST['current_address']) ? $_POST['current_address'] : NULL;

    // Add to your SQL query
    $sql = "INSERT INTO employee_family (
        eid, fm_name, fm_dob, fm_nationality, fm_blood_group, 
        fm_gender, fm_profession, fm_relation, 
        anniversary_date, phone_number, email, 
        is_emergency_contact, education, occupation_status
    ) VALUES (
        '$eid', '$fm_name', '$fm_dob', '$fm_nationality', '$fm_blood_group', 
        '$fm_gender', '$fm_profession', '$fm_relation',
        '$anniversary_date', '$phone_number', '$email', 
        '$is_emergency_contact', '$education', '$occupation_status'
    )";
    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Family details added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
    }
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
    <title>Family Details</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/reg_emp.js"></script>

    <style>
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }

        .form-group {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            margin-bottom: 0.8rem;
        }

        .input-group1 {
            margin-bottom: 0.8rem;
        }

        .input--style-1 {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #3498db;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .input--style-1:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .card-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .title {
            margin-bottom: 1.5rem;
            color: #2980b9;
            text-align: center;
            font-size: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-1 {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background-color: #2ecc71;
            border-color: #27ae60;
            padding: 0.6rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background-color: #27ae60;
            border-color: #219a52;
            transform: translateY(-1px);
        }

        select.input--style-1 {
            background-color: white;
            cursor: pointer;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }

        .nav-tabs .nav-link {
            color: #2c3e50;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #2980b9;
            border-bottom: 2px solid #2980b9;
        }

        .tab-content {
            padding-top: 20px;
        }
    </style>




</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <form id="familyForm" action="add_empfam.php" method="POST">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>

                        <div class="card-body">
                            <h2 class="title">Family Member Details</h2>

                            <!-- Add Tab Navigation -->
                            <ul class="nav nav-tabs mb-4" id="familyTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic"
                                        role="tab">Basic Information</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="additional-tab" data-toggle="tab" href="#additional"
                                        role="tab">Additional Details</a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="familyTabContent">
                                <!-- Basic Information Tab -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="form-row">
                                        <!-- Keep existing employee selection and name fields -->
                                        <!-- Select Employee -->
                                        <div class="form-group">
                                            <label>Select Employee</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="eid" onchange="this.form.submit()"
                                                    required>
                                                    <option value="">Select Employee</option>
                                                    <?php while ($row = mysqli_fetch_assoc($employees_result)) { ?>
                                                        <option value="<?php echo $row['eid']; ?>" <?php if ($row['eid'] == $eid)
                                                               echo 'selected'; ?>>
                                                            <?php echo $row['eid']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>
                                        <!-- Display Full Name (Read Only) -->
                                        <div class="form-group">
                                            <label>Employee Full Name</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text"
                                                    value="<?php echo $full_name; ?>" readonly />
                                            </div>
                                        </div>
                                        <!-- Basic Family Member Details -->
                                        <div class="form-group">
                                            <label>Family Member Name</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" name="fm_name" required />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Relation</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="fm_relation" required>
                                                    <option value="">Select Relation</option>
                                                    <option value="Father">Father</option>
                                                    <option value="Mother">Mother</option>
                                                    <option value="Spouse">Spouse</option>
                                                    <option value="Son">Son</option>
                                                    <option value="Daughter">Daughter</option>
                                                    <option value="Brother">Brother</option>
                                                    <option value="Sister">Sister</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Date of Birth</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date" name="fm_dob" required />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Gender</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="fm_gender" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="tel" name="phone_number" required />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Emergency Contact</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="is_emergency_contact">
                                                    <option value="No">No</option>
                                                    <option value="Yes">Yes</option>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details Tab -->
                                <div class="tab-pane fade" id="additional" role="tabpanel">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Nationality</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" name="fm_nationality"
                                                    required />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Blood Group</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="fm_blood_group" required>
                                                    <option value="">Select Blood Group</option>
                                                    <option value="A+">A+</option>
                                                    <option value="A-">A-</option>
                                                    <option value="B+">B+</option>
                                                    <option value="B-">B-</option>
                                                    <option value="O+">O+</option>
                                                    <option value="O-">O-</option>
                                                    <option value="AB+">AB+</option>
                                                    <option value="AB-">AB-</option>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Anniversary Date</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date" name="anniversary_date" />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="email" name="email" />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Education/Qualification</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" name="education" />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Profession</label>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" name="fm_profession"
                                                    required />
                                                <span class="error-message"></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Occupation Status</label>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="occupation_status">
                                                    <option value="">Select Status</option>
                                                    <option value="Student">Student</option>
                                                    <option value="Working">Working</option>
                                                    <option value="Retired">Retired</option>
                                                    <option value="Homemaker">Homemaker</option>
                                                </select>
                                                <span class="error-message"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-t-20 p-2 text-center">
                                <button class="btn btn--radius btn-success" name="register"
                                    type="submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
        </form>

    </div>


    <?php
    include_once('footer.php');
    ?>

    </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="http://localhost/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/show_password1.js"></script>

    <!-- <script src="vendor/jquery/jquery.min.js"></script> -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <!-- <script src="vendor/datatables/jquery.dataTables.min.js"></script> -->
    <!-- <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script> -->
    <!-- <script src="js/demo/datatables-demo.js"></script> -->


</body>

</html>