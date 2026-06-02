<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/includes/employee_registration_helpers.php';
error_reporting(E_ALL);
ini_set('display_errors', 'On');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require('PHPMailer/PHPMailer.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) { // Changed to 'register'

    $uploadDirs = [
        'profile_pic' => "uploads/profile_pics",
        'visa_doc' => "uploads/documents",
        'passport_doc' => "uploads/documents"
    ];
    // Retrieve form data
    // Basic Information
    $first_name = mysqli_real_escape_string($con, $_POST['fn']);
    $last_name = mysqli_real_escape_string($con, $_POST['ln']);
    $full_name = $first_name . " " . $last_name;
    $email = hrms_normalize_email($_POST['em'] ?? '');
    $visa_number_precheck = trim($_POST['visa_number'] ?? '');
    $passport_number_precheck = trim($_POST['passport_number'] ?? '');
    $precheck_error = hrms_registration_precheck($con, $email, $visa_number_precheck, $passport_number_precheck);
    if ($precheck_error !== null) {
        hrms_registration_fail($precheck_error);
    }
    $password = mysqli_real_escape_string($con, $_POST['ps']);
    $username = explode('@', $email)[0];


    // Personal Details
    $birthday = mysqli_real_escape_string($con, $_POST['birthday']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $maritalsts = mysqli_real_escape_string($con, $_POST['maritalsts']);
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $contact = mysqli_real_escape_string($con, $_POST['pn']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $country = mysqli_real_escape_string($con, $_POST['country']);

    // Education Details
    $degree = mysqli_real_escape_string($con, $_POST['degree']);
    $start_from = !empty($_POST['start_from']) ? mysqli_real_escape_string($con, $_POST['start_from']) : NULL;
    $end_to = !empty($_POST['end_to']) ? mysqli_real_escape_string($con, $_POST['end_to']) : NULL;
    $Institute = mysqli_real_escape_string($con, $_POST['Institute'] ?? '');

    // Employment Details
    $status = 'active'; // Default status
    // Validate required date fields
    if (empty($_POST['doj'])) {
        echo "<script>alert('Date of Joining is required');</script>";
        echo "<script>window.location.replace('$_SERVER[PHP_SELF]');</script>";
        exit();
    }
    $doj = mysqli_real_escape_string($con, $_POST['doj']);
    $EmpLoc = mysqli_real_escape_string($con, $_POST['EmpLoc']);
    $EmpDiv = mysqli_real_escape_string($con, $_POST['EmpDiv']);
    $EmpGrade = mysqli_real_escape_string($con, $_POST['EmpGrade']);
    $role = mysqli_real_escape_string($con, $_POST['role'] ?? 'user');
    $department = mysqli_real_escape_string($con, $_POST['department']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']);
    $reporting_manager = mysqli_real_escape_string($con, $_POST['reporting_manager']);
    $emp_left_org = mysqli_real_escape_string($con, $_POST['emp_left_org']);
    $dol = isset($_POST['dol']) ? mysqli_real_escape_string($con, $_POST['dol']) : NULL;
    $EmpCostcenter = mysqli_real_escape_string($con, $_POST['EmpCostcenter']);
    $MOLID = mysqli_real_escape_string($con, $_POST['MOLID']);

    // Banking Details
    $bank_name = mysqli_real_escape_string($con, $_POST['bank_name'] ?? '');
    $account_no = mysqli_real_escape_string($con, $_POST['account_no'] ?? '');
    $iban = mysqli_real_escape_string($con, $_POST['iban'] ?? '');
    $nominee = mysqli_real_escape_string($con, $_POST['nominee'] ?? '');

    // Visa Details
    $visa_number = mysqli_real_escape_string($con, $_POST['visa_number'] ?? '');
    $visa_type = mysqli_real_escape_string($con, $_POST['visa_type'] ?? '');
    $visa_issue_date = mysqli_real_escape_string($con, $_POST['visa_issue_date'] ?? '');
    $visa_expiry_date = mysqli_real_escape_string($con, $_POST['visa_expiry_date'] ?? '');

    // Passport Details
    $passport_number = mysqli_real_escape_string($con, $_POST['passport_number'] ?? '');
    $passport_type = mysqli_real_escape_string($con, $_POST['passport_type'] ?? '');
    $passport_issue_date = mysqli_real_escape_string($con, $_POST['passport_issue_date'] ?? '');
    $passport_expiry_date = mysqli_real_escape_string($con, $_POST['passport_expiry_date'] ?? '');
    $country_of_issue = mysqli_real_escape_string($con, $_POST['country_of_issue'] ?? '');
    $passport_issue_place = mysqli_real_escape_string($con, $_POST['passport_issue_place'] ?? '');

    // Additional Fields  
    $is_field_staff = mysqli_real_escape_string($con, $_POST['is_field_staff'] ?? '0');

    // Labour Card Details
    $labour_card_no = mysqli_real_escape_string($con, $_POST['labour_card_no'] ?? '');
    $labour_card_start_date = mysqli_real_escape_string($con, $_POST['labour_card_start_date'] ?? '');
    $labour_card_end_date = mysqli_real_escape_string($con, $_POST['labour_card_end_date'] ?? '');

    // Pregnancy Details (for female employees)
    $pregnancy_status = ($gender === 'Female') ? mysqli_real_escape_string($con, $_POST['pregnancy_status'] ?? NULL) : NULL;
    $due_date = ($gender === 'Female' && isset($_POST['due_date'])) ? mysqli_real_escape_string($con, $_POST['due_date']) : NULL;

    // Security and System Fields
    $token = mysqli_real_escape_string($con, $_POST['token']);
    $created_at = date('Y-m-d H:i:s');



    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Function to handle file upload securely
    function uploadFile($file, $uploadDir, $prefix)
    {
        $fileName = basename($file["name"]);
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = $prefix . "_" . time() . "." . $fileExt; // Unique file name
        $targetFilePath = $uploadDir . "/" . $newFileName;

        // Allowed file types
        $allowedTypes = array("jpg", "jpeg", "png", "pdf");

        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return $uploadDir . "/" . $newFileName;
            }
        }
        return false;
    }

    // Handle file uploads
    $profilePic = uploadFile($_FILES["profile_pic"], $uploadDirs['profile_pic'], "profile");
    $visaDoc = uploadFile($_FILES["visa_doc"], $uploadDirs['visa_doc'], "visa");
    $passportDoc = uploadFile($_FILES["passport_doc"], $uploadDirs['passport_doc'], "passport");


    $new_eid = hrms_generate_next_eid($con);

$dol = null; // Set default value to null for new employees


// Before the SQL insert, add validation for visa dates
if (empty($_POST['visa_issue_date'])) {
    $visa_issue_date = null;
} else {
    $visa_issue_date = $_POST['visa_issue_date'];
}

if (empty($_POST['visa_expiry_date'])) {
    $visa_expiry_date = null;
} else {
    $visa_expiry_date = $_POST['visa_expiry_date'];
}


// Before the SQL insert, passport add this validation
if (empty($_POST['passport_issue_date'])) {
    $passport_issue_date = null;
} else {
    $passport_issue_date = $_POST['passport_issue_date'];
}

if (empty($_POST['passport_expiry_date'])) {
    $passport_expiry_date = null;
} else {
    $passport_expiry_date = $_POST['passport_expiry_date'];
}


// Before the SQL insert, add this validation
if (empty($_POST['labour_card_start_date'])) {
    $labour_card_start_date = null;
} else {
    $labour_card_start_date = $_POST['labour_card_start_date'];
}

if (empty($_POST['labour_card_end_date'])) {
    $labour_card_end_date = null;
} else {
    $labour_card_end_date = $_POST['labour_card_end_date'];
}

        // Insert into employees table


        $sql_insert_employee = "INSERT INTO employees (
            eid, first_name, last_name, full_name, user_name, email, password, 
            birthday, gender, maritalsts, blood_group, contact, address, country,
            degree, start_from, end_to, Institute, status, doj, EmpLoc, EmpDiv, 
            EmpGrade, role, department, designation, reporting_manager, emp_left_org, 
            dol, EmpCostcenter, MOLID, bank_name, account_no, iban, nominee,
            visa_number, visa_type, visa_issue_date, visa_expiry_date,
            passport_number, passport_type, passport_issue_date, passport_expiry_date,
            country_of_issue, passport_issue_place, profile_pic, visa_doc, passport_doc,
            is_field_staff, labour_card_no, labour_card_start_date, labour_card_end_date, 
            pregnancy_status, due_date, token, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )";

        if ($stmt = $con->prepare($sql_insert_employee)) {
            $stmt->bind_param(
                "ssssssssssssssssssssssssssssssssssssssssssssssssssssss",
                $new_eid,
                $first_name,
                $last_name,
                $full_name,
                $username,
                $email,
                $hashed_password,
                $birthday,
                $gender,
                $maritalsts,
                $blood_group,
                $contact,
                $address,
                $country,
                $degree,
                $start_from,
                $end_to,
                $Institute,
                $doj,
                $EmpLoc,
                $EmpDiv,
                $EmpGrade,
                $role,
                $department,
                $designation,
                $reporting_manager,
                $emp_left_org,
                $dol,
                $EmpCostcenter,
                $MOLID,
                $bank_name,
                $account_no,
                $iban,
                $nominee,
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
                $profilePic,
                $visaDoc,
                $passportDoc,
                $is_field_staff,
                $labour_card_no,
                $labour_card_start_date,
                $labour_card_end_date,
                $pregnancy_status,
                $due_date,
                $token
            );
            
            
            mysqli_begin_transaction($con);
            $saved = false;

            if (!$stmt->execute()) {
                mysqli_rollback($con);
                hrms_registration_fail(hrms_registration_duplicate_message($con, $stmt->errno, $stmt->error));
            }

            $emp_sql = "INSERT INTO emp_login (emp_id, user_name, email, password, status) 
                VALUES (?, ?, ?, ?, 'active')";
            $emp_stmt = $con->prepare($emp_sql);
            if (!$emp_stmt || !$emp_stmt->bind_param('ssss', $new_eid, $username, $email, $hashed_password) || !$emp_stmt->execute()) {
                $err = $emp_stmt ? $emp_stmt->error : $con->error;
                $errno = $emp_stmt ? $emp_stmt->errno : $con->errno;
                mysqli_rollback($con);
                hrms_registration_fail(hrms_registration_duplicate_message($con, $errno, $err));
            }
            $emp_stmt->close();

            mysqli_commit($con);
            $saved = true;

            if ($saved) {
                hrms_send_registration_welcome_email($con, $email, $first_name, $username, $token);
                echo "<script>alert('Registration successful'); window.location.replace('" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "');</script>";
                exit();
            }
        } else {
            echo "Error in preparing statement: " . $con->error;
        }



        // $insert_employee_query = "INSERT INTO emp_login (user_name, password) VALUES ('$username', '$password')";
// if (mysqli_query($con, $insert_employee_query)) {

}


$countries = array(
    "Afghanistan",
    "Albania",
    "Algeria",
    "Andorra",
    "Angola",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
    "Bangladesh",
    "Barbados",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bhutan",
    "Bolivia",
    "Bosnia and Herzegovina",
    "Botswana",
    "Brazil",
    "Brunei",
    "Bulgaria",
    "Burkina Faso",
    "Burundi",
    "Cabo Verde",
    "Cambodia",
    "Cameroon",
    "Canada",
    "Central African Republic",
    "Chad",
    "Chile",
    "China",
    "Colombia",
    "Comoros",
    "Congo (Congo-Brazzaville)",
    "Costa Rica",
    "Croatia",
    "Cuba",
    "Cyprus",
    "Czechia",
    "Denmark",
    "Djibouti",
    "Dominica",
    "Dominican Republic",
    "Ecuador",
    "Egypt",
    "El Salvador",
    "Equatorial Guinea",
    "Eritrea",
    "Estonia",
    "Eswatini",
    "Ethiopia",
    "Fiji",
    "Finland",
    "France",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Greece",
    "Grenada",
    "Guatemala",
    "Guinea",
    "Guinea-Bissau",
    "Guyana",
    "Haiti",
    "Honduras",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran",
    "Iraq",
    "Ireland",
    "Israel",
    "Italy",
    "Jamaica",
    "Japan",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Kiribati",
    "Kuwait",
    "Kyrgyzstan",
    "Laos",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Liechtenstein",
    "Lithuania",
    "Luxembourg",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Maldives",
    "Mali",
    "Malta",
    "Marshall Islands",
    "Mauritania",
    "Mauritius",
    "Mexico",
    "Micronesia",
    "Moldova",
    "Monaco",
    "Mongolia",
    "Montenegro",
    "Morocco",
    "Mozambique",
    "Myanmar (Burma)",
    "Namibia",
    "Nauru",
    "Nepal",
    "Netherlands",
    "New Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "North Korea",
    "North Macedonia",
    "Norway",
    "Oman",
    "Pakistan",
    "Palau",
    "Palestine State",
    "Panama",
    "Papua New Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Poland",
    "Portugal",
    "Qatar",
    "Romania",
    "Russia",
    "Rwanda",
    "Saint Kitts and Nevis",
    "Saint Lucia",
    "Saint Vincent and the Grenadines",
    "Samoa",
    "San Marino",
    "Sao Tome and Principe",
    "Saudi Arabia",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leone",
    "Singapore",
    "Slovakia",
    "Slovenia",
    "Solomon Islands",
    "Somalia",
    "South Africa",
    "South Korea",
    "South Sudan",
    "Spain",
    "Sri Lanka",
    "Sudan",
    "Suriname",
    "Sweden",
    "Switzerland",
    "Syria",
    "Taiwan",
    "Tajikistan",
    "Tanzania",
    "Thailand",
    "Timor-Leste",
    "Togo",
    "Tonga",
    "Trinidad and Tobago",
    "Tunisia",
    "Turkey",
    "Turkmenistan",
    "Tuvalu",
    "Uganda",
    "Ukraine",
    "United Arab Emirates",
    "United Kingdom",
    "United States",
    "Uruguay",
    "Uzbekistan",
    "Vanuatu",
    "Vatican City",
    "Venezuela",
    "Vietnam",
    "Yemen",
    "Zambia",
    "Zimbabwe"
);

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Add Employees</title>

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
        .nav-tabs {
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            color: #2E8B57;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #2E8B57;
            border-color: #2E8B57;
        }

        .tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>

    <?php include('header.php'); ?>

    <div class="container-fluid">

        <form id="registrationForm" action="add_emp.php" method="POST" enctype="multipart/form-data">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h2 class="title" style="color: #2E8B57;">Register New Employee</h2>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" id="employeeTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal"
                                        role="tab">Personal Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="education-tab" data-toggle="tab" href="#education"
                                        role="tab">Education</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="employment-tab" data-toggle="tab" href="#employment"
                                        role="tab">Employment</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank" role="tab">Bank
                                        Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents"
                                        role="tab">Documents</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="employeeTabContent">
                                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                    <table class="table">
                                        <tr>
                                            <th colspan="3" style="background-color: #20B2AA; text-align: left;">
                                                Personal Details
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>First Name</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text"
                                                            placeholder="First Name" name="fn" />
                                                        <span id="fn_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>


                                            <td>
                                                <div>
                                                    <p>Last Name</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text"
                                                            placeholder="Last Name" name="ln" />
                                                        <span id="ln_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="field-column">
                                                    <div>
                                                        <p for="em3">
                                                            Email Address
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <input type="email" class="input--style-1" name="em" id="em3"
                                                            class="demo-input-box"
                                                            placeholder="Enter your Email Address" autocomplete="off">
                                                        <span id="em_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="field-column">

                                                    <div>
                                                        <p for="ps1">
                                                            Password
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <input type="password" class="input--style-1" name="ps" id="ps1"
                                                            class="demo-input-box" placeholder="Create a Password"
                                                            autocomplete="new-password">
                                                        <span id="ps_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>

                                                <div class="field-column">
                                                    <div>
                                                        <p for="cp1">
                                                            Confirm Password
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <input type="password" class="input--style-1" name="cp" id="cp1"
                                                            class="demo-input-box" placeholder="Enter Confirm Password"
                                                            autocomplete="new-password">
                                                        <span id="cp_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                                <input type="text" name="token"
                                                    value="<?php echo uniqid() . uniqid(); ?>" id="token1" name="token"
                                                    hidden>
                                            </td>

                                            <td>
                                                <input type="checkbox" onclick="myFunction()"> Show Password <br><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="row">
                                                    <div class="col-8">
                                                        <p>DOB(DD-MM-YYYY)</p>
                                                        <div class="input-group1">
                                                            <input class="input--style-1" type="date"
                                                                placeholder="BIRTHDATE" name="birthday" />
                                                            <span id="bd_err" class="error1 p-1"></span>
                                                        </div>
                                                    </div>
                                            </td>
                                            <td>
                                                <div class="col-6">
                                                    <p>Gender</p>
                                                    <div class="input-group1">
                                                        <label class="radio-container">Male
                                                            <input type="radio" name="gender" value="Male" checked>
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="radio-container">Female
                                                            <input type="radio" name="gender" value="Female"
                                                                id="female_gender">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                </div>
                                </td>
                                <!-- New Pregnancy Status Section (Initially Hidden) -->
                                <td id="pregnancy_details" style="display: none;">
                                    <div class="col-10">
                                        <p>Pregnancy Status</p>
                                        <div class="input-group1">
                                            <select class="input--style-1" name="pregnancy_status"
                                                id="pregnancy_status">
                                                <option value="">Select Status</option>
                                                <option value="Not Pregnant">Not Pregnant</option>
                                                <option value="Pregnant">Pregnant</option>
                                                <option value="Maternity Leave">On Maternity Leave</option>
                                            </select>
                                            <span id="pregnancy_status_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                    <div id="due_date_container" style="display: none;">
                                        <div class="col-10">
                                            <p>Expected Due Date</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date" name="due_date">
                                                <span id="due_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p>Phone Number</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="number" placeholder="Contact Number"
                                                name="pn" />
                                            <span id="pn_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <div>
                                            <p>Full Address</p>
                                            <div class="input-group1">
                                                <textarea class="input--style-1" type="text" placeholder="Address"
                                                    name="address"></textarea>
                                                <span id="ad_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Country</p>
                                            <div class="input-group1">

                                                <select class="input--style-1" name="country">
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($countries as $country) { ?>
                                                        <option value="<?php echo $country; ?>"><?php echo $country; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <span id="country" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>

                                    <td>
                                        <!-- Marital Status (maritalsts) -->
                                        <div class="col-10">
                                            <p>Marital Status</p>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="maritalsts">
                                                    <option value="">Select Marital Status</option>
                                                    <option value="Single">Single</option>
                                                    <option value="Married">Married</option>
                                                    <option value="Divorced">Divorced</option>
                                                    <option value="Widowed">Widowed</option>
                                                </select>
                                                <span id="ms_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group1">
                                            <label>Blood Group</label>
                                            <select class="input--style-1" name="blood_group">
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
                                    </td>
                                </tr>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="education" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3" style="background-color: #20B2AA; text-align: left;">Educational
                                            Details
                                        </th>
                                    </tr>

                                    <tr>

                                        <td>
                                            <div class="field-column">
                                                <div>
                                                    <p for="degree">
                                                        Qualification
                                                    </p>
                                                </div>
                                                <div>

                                                    <input class="input--style-1" type="text"
                                                        placeholder="Qualification" name="degree" />
                                                    <span id="degree_err" class="error-msg"></span><br><br>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <p>Degree Start Date</p>
                                                <div class="input-group1">
                                                    <input class="input--style-1" type="date" placeholder="Start From"
                                                        name="start_from" min="1950-01-01" />
                                                    <span id="start_from_err" class="error1 p-1"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <p>Degree End Date</p>
                                                <div class="input-group1">
                                                    <input class="input--style-1" type="date" placeholder="End To"
                                                        name="end_to" />
                                                    <span id="end_to_err" class="error1 p-1"></span>
                                                </div>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <div>
                                                <p>Institute</p>
                                                <div class="input-group1">
                                                    <input class="input--style-1" type="text" placeholder="Institute"
                                                        name="Institute" />
                                                    <span id="institute_err" class="error1 p-1"></span>
                                                </div>
                                            </div>
                                        </td>

                                    </tr>

                                </table>
                            </div>
                            <!-- Employment section change -->
                            <div class="tab-pane fade" id="employment" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="4" style="background-color: #20B2AA; text-align: left;">Employement
                                            Details
                                        </th>
                                    </tr>

                                    <tr>


                                        <td>

                                            <div class="col-9">
    <p>Date of Joining<span style="color: red;">*</span></p>
    <div class="input-group1">
        <input class="input--style-1" type="date" name="doj" required />
        <span id="doj_err" class="error1 p-1"></span>
    </div>
</div>
                                        </td>
                                        <td>
                                            <div>
                                                <p>Designation<span style="color: red;">*</span></p>
                                                <select name="designation" class="input--style-1" id="designation">
                                                    <option value="">Select Designation</option>
                                                    <option value="Managing Director">Managing Director</option>
                                                    <option value="Sales Director">Sales Director</option>
                                                    <option value="Sales Team Leader">Sales Team Leader</option>
                                                    <option value="Asst Sales Manager">Asst Sales Manager</option>
                                                    <option value="Digital Sales">Digital Sales</option>
                                                    <option value="Operations Manager">Operations Manager</option>
                                                    <option value="Asst. Operation">Asst. Operation</option>
                                                    <option value="Relationship officer">Relationship officer</option>
                                                    <option value="HR Manager">HR Manager</option>
                                                    <option value="Office Asst">Office Asst</option>
                                                </select>
                                                <span id="designation_err" class="error-msg"></span><br><br </div>
                                        </td>
                                        <td>
                                           <div>
                                                <p>Department
                                                <span style="color: red;">*</span></p>
                                                <select name="department" class="input--style-1" id="department"
                                                    class="demo-input-box" required>
                                                    <option value="">Select Department</option>
                                                    <option value="Operations">Operations</option>
                                                    <option value="Sales">Sales</option>
                                                    <option value="HR">HR</option>
                                                </select>
                                                <span id="department_err" class="error-msg"></span><br><br>
                                            </div>
                            </div>
                            </td>

                            <td>
                                <!-- Reporting Manager -->
                                <div class="col-8">
                                    <p>Reporting Manager</p>
                                    <div class="input-group1">
                                        <select class="input--style-1" name="reporting_manager" id="reporting_manager">
                                            <option value="">Select Reporting Manager</option>
                                        </select>
                                        <span id="rm_err" class="error1 p-1"></span>
                                    </div>
                                </div>
                            </td>

                            </tr>


                            <tr>
                                <td>
                                    <!-- Role Selection -->
                                    <div class="col-8">
                                        <p>Role<span style="color: red;">*</span></p>
                                        
                                        <div class="input-group1">
                                            <select class="input--style-1" name="role" required>
                                                <option value="">Select Role</option>
                                                <option value="user">Employee</option>
                                                <option value="HOD">HOD</option>
                                                <option value="HR">HR</option>
                                            </select>
                                            <span id="role_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>

                                    <div class="col-8">
                                        <p>Emp Location</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpLoc"
                                                placeholder="Enter Emp Location" />
                                            <span id="loc_err" class="error1 p-1"></span>
                                        </div>
                                    </div>

                                </td>
                                <td>
                                    <!-- Field Staff -->
                                    <div class="col-8">
                                        <p>Field Staff <span style="color: red;">*</span></p>
                                        <div class="input-group1">
                                            <select class="input--style-1" name="is_field_staff">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                            <span id="field_staff_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>

                                    <div class="col-8">
                                        <p>Employee Division</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpDiv"
                                                placeholder="Enter Emp Division" />
                                            <span id="div_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Employee Division (EmpDiv) -->
                            <tr>

                                <td>
                                    <!-- Employee Grade (EmpGrade) -->
                                    <div class="col-10">
                                        <p>Employee Grade</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpGrade"
                                                placeholder="Enter Emp Grade" />
                                            <span id="grade_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <!-- Employee Cost Center (EmpCostcenter) -->
                                    <div class="col-10">
                                        <p>Emp Cost Center</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpCostcenter"
                                                placeholder="Enter Emp Cost Center" />
                                            <span id="cost_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- MOL ID (MOLID) -->
                                    <div class="col-10">
                                        <p>MOL ID</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="MOLID"
                                                placeholder="Enter MOL ID" />
                                            <span id="mol_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="col-10">
                                        <p>Labour Card Number</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="labour_card_no"
                                                placeholder="Enter Labour Card Number" />
                                            <span id="labour_card_no_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <!-- Employee Left Organization (EmpLeftOrg) -->
                                    <div class="col-6">
                                        <p>Emp Left Org</p>
                                        <div class="input-group1">
                                            <select class="input--style-1" name="emp_left_org">
                                                <option value="">Status</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                            <span id="elo_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- Date of Leaving (DOL) -->
                                    <div class="col-9" id="dol_container" style="display: none;">
                                        <p>Date of Leaving</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="date" name="dol" />
                                            <span id="dol_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="col-10">
    <p>Labour Card Start Date</p>
    <div class="input-group1">
        <input class="input--style-1" type="date" name="labour_card_start_date" />
        <span id="labour_card_start_date_err" class="error1 p-1"></span>
    </div>
</div>
                                </td>
                                <td>
                                    <div class="col-10">
    <p>Labour Card End Date</p>
    <div class="input-group1">
        <input class="input--style-1" type="date" name="labour_card_end_date" />
        <span id="labour_card_end_date_err" class="error1 p-1"></span>
    </div>
</div>
                                </td>
                            </tr>
                            </table>
                        </div>
                        <!-- BANK SECTION -->

                        <div class="tab-pane fade" id="bank" role="tabpanel">
                            <table class="table">
                                <tr>
                                    <th colspan="3" style="background-color: #20B2AA; text-align: left;">
                                        Bank Details
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <p>Bank Name</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Bank Name"
                                                    name="bank_name" />
                                                <span id="bank_name_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Account Number<span style="color: red;">*</span></p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Account Number"
                                                    name="account_no" />
                                                <span id="account_no_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>IBAN</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="IBAN Number"
                                                    name="iban" />
                                                <span id="iban_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div>
                                            <p>Nominee</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Nominee Name"
                                                    name="nominee" />
                                                <span id="nominee_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>



                        <!-- VISA-PASSPORT SECTION -->
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <table class="table">
                                <tr>
                                    <th colspan="3" style="background-color: #20B2AA; text-align: left;">Visa &
                                        Passport
                                        Details
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <p>Visa Number</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Visa Number"
                                                    name="visa_number" />
                                                <span id="visa_number_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Visa Type</p>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="visa_type">
                                                    <option value="" selected disabled>Select Visa Type</option>

                                                    <option value="Employer Sponsored Visa">Employer Sponsored Visa
                                                    </option>
                                                    <option value="Family Visa">Family Visa</option>
                                                    <option value="Spouse Visa">Spouse Visa</option>
                                                    <option value="Freelance Visa">Freelance Visa</option>
                                                    <option value="Emp Visa Change Status">Emp Change</option>
                                                    <option value="Employement Visa">Employement Visa</option>
                                                    <option value="Entry Permit Visa">Entry Permit Visa</option>
                                                    <option value="Job Search Visa">Job Search Visa</option>
                                                    <option value="Tourist">Tourist</option>
                                                    <option value="Business">Business</option>
                                                    <option value="Work">Work</option>
                                                    <option value="Student">Student</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                                <span id="visa_type_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Visa Issue Date</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date" name="visa_issue_date">
    <span id="visa_issue_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div>
                                            <p>Visa Expiry Date</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date" placeholder="Visa Expiry Date"
                                                    name="visa_expiry_date" />
                                                <span id="visa_expiry_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Passport Number</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Passport Number"
                                                    name="passport_number" />
                                                <span id="passport_number_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Passport Type</p>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="passport_type">
                                                    <option value="" selected disabled>Select Passport Type</option>
                                                    <option value="Ordinary">Ordinary</option>
                                                    <option value="Diplomatic">Diplomatic</option>
                                                    <option value="Official">Official</option>
                                                </select>
                                                <span id="passport_type_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div>
                                            <p>Passport Issue Date</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date"
                                                    placeholder="Passport Issue Date" name="passport_issue_date" />
                                                <span id="passport_issue_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Passport Expiry Date</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="date"
                                                    placeholder="Passport Expiry Date" name="passport_expiry_date" />
                                                <span id="passport_expiry_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Country of Issue</p>
                                            <div class="input-group1">
                                                <select class="input--style-1" name="country_of_issue">
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($countries as $country) { ?>
                                                        <option value="<?php echo $country; ?>"><?php echo $country; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <span id="country_of_issue_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div>
                                            <p>Passport Issue Place</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text"
                                                    placeholder="Passport Issue Place" name="passport_issue_place" />
                                                <span id="passport_issue_place_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Files upload section -->
                                <tr>
                                    <th colspan="3" style="background-color: #20B2AA; text-align: left;">File
                                        Uploads</th>
                                </tr>
                                <tr>
                                    <td><label for="profile_pic">Profile Picture:</label>

                                        <input type="file" class="form-control" name="profile_pic" id="profile_pic"
                                            accept=".jpg,.jpeg,.png">
                                        <small style="color: gray;">(Allowed: JPG, JPEG, PNG)</small>
                                    </td>



                                    <td><label for="visa_doc">Visa Document:</label>

                                        <input type="file" class="form-control" name="visa_doc" id="visa_doc"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                        <small style="color: gray;">(Allowed: JPG, JPEG, PNG, PDF)</small>
                                    </td>
                                    <td><label for="passport_doc">Passport Document:</label>

                                        <input type="file" class="form-control" name="passport_doc" id="passport_doc"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                        <small style="color: gray;">(Allowed: JPG, JPEG, PNG, PDF)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="p-t-20 p-2">
                                            <button class="btn btn--radius btn-success" name="register"
                                                type="submit">Submit</button>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>



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
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
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

    <script>
        document.querySelector('select[name="emp_left_org"]').addEventListener('change', function () {
            const dolContainer = document.getElementById('dol_container');
            if (this.value === 'Yes') {
                dolContainer.style.display = 'block';
            } else {
                dolContainer.style.display = 'none';
                document.querySelector('input[name="dol"]').value = ''; // Clear the date when hidden
            }
        });
    </script>

    <script>
        const managersByDepartment = {
            'Operations': ['Managing Director', 'Operations Manager', 'Operations Director', 'Operations Executive'],
            'Sales': ['Managing Director', 'Sales Manager', 'Sales Director', 'Sales Executive'],
            'HR': ['Managing Director', 'HR Manager', 'HR Director', 'HR Executive']
        };

        document.getElementById('department').addEventListener('change', function () {
            const reportingManager = document.getElementById('reporting_manager');
            reportingManager.innerHTML = '<option value="">Select Reporting Manager</option>';

            const selectedDepartment = this.value;
            if (selectedDepartment && managersByDepartment[selectedDepartment]) {
                managersByDepartment[selectedDepartment].forEach(manager => {
                    const option = document.createElement('option');
                    option.value = manager;
                    option.textContent = manager;
                    reportingManager.appendChild(option);
                });
            }
        });
    </script>
    <script>
        // Pregnancy status handling
        document.getElementById('female_gender').addEventListener('change', function () {
            document.getElementById('pregnancy_details').style.display = this.checked ? 'block' : 'none';
        });

        document.querySelector('input[name="gender"][value="Male"]').addEventListener('change', function () {
            document.getElementById('pregnancy_details').style.display = 'none';
            document.getElementById('pregnancy_status').value = '';
            document.querySelector('input[name="due_date"]').value = '';
        });

        document.getElementById('pregnancy_status').addEventListener('change', function () {
            const dueDateContainer = document.getElementById('due_date_container');
            dueDateContainer.style.display = (this.value === 'Pregnant') ? 'block' : 'none';
            if (this.value !== 'Pregnant') {
                document.querySelector('input[name="due_date"]').value = '';
            }
        });
    </script>
</body>

</html>