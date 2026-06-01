<?php
include('session.php');
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require('PHPMailer/PHPMailer.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) { // Changed to 'register'

    $uploadDir = "uploads/"; // Folder to store uploaded files
    // Retrieve form data
    $first_name = mysqli_real_escape_string($con, $_POST['fn']);
    $last_name = mysqli_real_escape_string($con, $_POST['ln']);
    $email = mysqli_real_escape_string($con, $_POST['em']);
    $password = mysqli_real_escape_string($con, $_POST['ps']);
    $contact = mysqli_real_escape_string($con, $_POST['pn']);
    $department = isset($_POST['department']) ? mysqli_real_escape_string($con, $_POST['department']) : '';
    $full_name = $first_name . " " . $last_name;
    $username = explode("@", $email)[0];
    $birthday = mysqli_real_escape_string($con, $_POST['birthday']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $degree = mysqli_real_escape_string($con, $_POST['degree']);
    $start_from = mysqli_real_escape_string($con, $_POST['start_from'] ?? '');
    $end_to = mysqli_real_escape_string($con, $_POST['end_to'] ?? '');
    $Institute = mysqli_real_escape_string($con, $_POST['Institute'] ?? '');
    $doj = mysqli_real_escape_string($con, $_POST['doj']);
    $emp_left_org = mysqli_real_escape_string($con, $_POST['emp_left_org']);
    $dol = isset($_POST['dol']) ? mysqli_real_escape_string($con, $_POST['dol']) : NULL;
    $reporting_manager = mysqli_real_escape_string($con, $_POST['reporting_manager']);
    $maritalsts = mysqli_real_escape_string($con, $_POST['maritalsts']);
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $EmpLoc = mysqli_real_escape_string($con, $_POST['EmpLoc']);
    $EmpDiv = mysqli_real_escape_string($con, $_POST['EmpDiv']);
    $EmpGrade = mysqli_real_escape_string($con, $_POST['EmpGrade']);
    $EmpCostcenter = mysqli_real_escape_string($con, $_POST['EmpCostcenter']);
    $MOLID = mysqli_real_escape_string($con, $_POST['MOLID']);
    $visa_number = mysqli_real_escape_string($con, $_POST['visa_number'] ?? '');
    $visa_type = mysqli_real_escape_string($con, $_POST['visa_type'] ?? '');
    $visa_issue_date = mysqli_real_escape_string($con, $_POST['visa_issue_date'] ?? '');
    $visa_expiry_date = mysqli_real_escape_string($con, $_POST['visa_expiry_date'] ?? '');
    $passport_number = mysqli_real_escape_string($con, $_POST['passport_number'] ?? '');
    $passport_type = mysqli_real_escape_string($con, $_POST['passport_type'] ?? '');
    $passport_issue_date = mysqli_real_escape_string($con, $_POST['passport_issue_date'] ?? '');
    $passport_expiry_date = mysqli_real_escape_string($con, $_POST['passport_expiry_date'] ?? '');
    $country_of_issue = mysqli_real_escape_string($con, $_POST['country_of_issue'] ?? '');
    $passport_issue_place = mysqli_real_escape_string($con, $_POST['passport_issue_place'] ?? '');
    $token = mysqli_real_escape_string($con, $_POST['token']);
    $bank_name = mysqli_real_escape_string($con, $_POST['bank_name'] ?? '');
    $account_no = mysqli_real_escape_string($con, $_POST['account_no'] ?? '');
    $iban = mysqli_real_escape_string($con, $_POST['iban'] ?? '');
    $nominee = mysqli_real_escape_string($con, $_POST['nominee'] ?? '');
    $role = mysqli_real_escape_string($con, $_POST['role'] ?? 'user');
    $is_field_staff = mysqli_real_escape_string($con, $_POST['is_field_staff'] ?? '0');

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Function to handle file upload securely
    function uploadFile($file, $uploadDir, $prefix)
    {
        $fileName = basename($file["name"]);
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = $prefix . "_" . time() . "." . $fileExt; // Unique file name
        $targetFilePath = $uploadDir . $newFileName;

        // Allowed file types
        $allowedTypes = array("jpg", "jpeg", "png", "pdf");

        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return $newFileName;
            }
        }
        return false;
    }

    $profilePic = uploadFile($_FILES["profile_pic"], $uploadDir, "profile");
    $visaDoc = uploadFile($_FILES["visa_doc"], $uploadDir, "visa");
    $passportDoc = uploadFile($_FILES["passport_doc"], $uploadDir, "passport");


    // Check if email already exists
    $sql_check_email = "SELECT * FROM employees WHERE email = '$email'";
    $result_check_email = mysqli_query($con, $sql_check_email);

    if (mysqli_num_rows($result_check_email) > 0) {
        echo "<script>alert('Error: User with this email already exists');</script>";
        echo "<script>window.location.replace('$_SERVER[PHP_SELF]');</script>";
        exit();
    } else {
        // Insert into employees table
        $sql_eid = "SELECT MAX(CAST(SUBSTRING(eid, 2) AS UNSIGNED)) AS max_eid FROM employees";
        $result_eid = mysqli_query($con, $sql_eid);
        $row_eid = mysqli_fetch_assoc($result_eid);
        $max_eid = $row_eid['max_eid'];
        $new_eid = 'U' . str_pad(($max_eid + 1), 2, "0", STR_PAD_LEFT);

        // Insert into employees table

        $sql_insert_employee = "INSERT INTO employees 
    (eid, first_name, last_name, full_name, user_name, email, password, contact, department, birthday, gender, address, degree, start_from, end_to, Institute,
    doj, emp_left_org, dol, reporting_manager, maritalsts, blood_group, EmpLoc, EmpDiv, EmpGrade, EmpCostcenter, MOLID, bank_name, account_no, iban, nominee, role, is_field_staff,visa_number, visa_type, visa_issue_date, visa_expiry_date, 
                passport_number, passport_type, passport_issue_date, passport_expiry_date, 
                country_of_issue, passport_issue_place, profile_pic, visa_doc, passport_doc, token) 
VALUES 
    ('$new_eid', '$first_name', '$last_name', '$full_name', '$username', '$email', '$password', '$contact', '$department', '$birthday', '$gender', '$address',
     '$degree', '$start_from','$end_to','$Institute','$doj','$emp_left_org', '$dol', '$reporting_manager', '$maritalsts', '$blood_group', '$EmpLoc', '$EmpDiv', '$EmpGrade', '$EmpCostcenter', '$MOLID', '$bank_name', '$account_no', '$iban', '$nominee', '$role', '$is_field_staff','$visa_number', '$visa_type', '$visa_issue_date', '$visa_expiry_date',
                '$passport_number', '$passport_type', '$passport_issue_date', '$passport_expiry_date',
                '$country_of_issue', '$passport_issue_place','$profilePic', '$visaDoc', '$passportDoc','$token')";

        // $insert_employee_query = "INSERT INTO emp_login (user_name, password) VALUES ('$username', '$password')";
// if (mysqli_query($con, $insert_employee_query)) {

        if (mysqli_query($con, $sql_insert_employee)) {

            $last_emp_id_query1 = "SELECT MAX(id) AS last_emp_id FROM employees";
            $res1 = mysqli_query($con, $last_emp_id_query1);

            $row1 = mysqli_fetch_assoc($res1);
            $last_emp_id1 = $row1['last_emp_id'];
            $new_emp_id1 = $last_emp_id1;



            // After successful employee insertion
            $emp_sql = "INSERT INTO emp_login (emp_id, user_name, password, status) 
                VALUES (?, ?, ?, 'active')";
            $emp_stmt = $con->prepare($emp_sql);
            $emp_stmt->bind_param("sss", $new_eid, $username, $password);
            $emp_stmt->execute();


            $last_emp_id_query = "SELECT MAX(id) AS last_emp_id FROM employees";
            $res = mysqli_query($con, $last_emp_id_query);

            $row = mysqli_fetch_assoc($res);
            $last_emp_id = $row['last_emp_id'];
            $new_emp_id = $last_emp_id;


            $mail = new PHPMailer();

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'youremail@gmail.com';
                $mail->Password = 'yourpassword';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;
                $mail->setFrom('youremail@gmail.com', 'Your Name');
                $mail->addAddress($email, $first_name);
                $mail->isHTML(true);
                $mail->Subject = 'Account Verification';
                $mail->Body = 'Congratulations! ' . $first_name . ', your account has been created successfully.<br>Your Username: ' . $username . '<br><a href="http://yourdomain.com/verify_account.php?em=' . $email . '&token=' . $token . '">Click here to verify your account</a>';
                $mail->send();
                echo "<script>alert('Registration successful');</script>";
            } catch (Exception $e) {
                echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error in registration: " . mysqli_error($con);
        }
    }
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
                                                            <input type="radio" name="gender" value="Female">
                                                            <span class="checkmark"></span>
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
                                            <select class="input--style-1" name="blood_group" required>
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
                                                        name="start_from" />
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
                                        <th colspan="3" style="background-color: #20B2AA; text-align: left;">Employement
                                            Details
                                        </th>
                                    </tr>

                                    <tr>
                                        <td>

                                            <div class="col-8">
                                                <p>Date of Joining</p>
                                                <div class="input-group1">
                                                    <input class="input--style-1" type="date" name="doj" />
                                                    <span id="doj_err" class="error1 p-1"></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                <p>Reporting Manager</p>
                                                <select name="department" class="input--style-1" id="department"
                                                    class="demo-input-box">
                                                    <option value="">Select Department</option>
                                                    <option value="IT">IT</option>
                                                    <option value="HR">HR</option>
                                                    <option value="Finance">Finance</option>
                                                    <option value="Marketing">Marketing</option>
                                                    <option value="Operations">Operations</option>
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
                                        <p>Role</p>
                                        <div class="input-group1">
                                            <select class="input--style-1" name="role">
                                                <option value="">Select Role</option>
                                                <option value="user">User</option>
                                                <option value="HOD">HOD</option>
                                            </select>
                                            <span id="role_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>

                                    <div class="col-6">
                                        <p>Employee Location</p>
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
                                        <p>Field Staff</p>
                                        <div class="input-group1">
                                            <select class="input--style-1" name="is_field_staff">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                            <span id="field_staff_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                            <tr>
                                <td>
                                    <!-- Employee Division (EmpDiv) -->
                                    <div class="col-6">
                                        <p>Employee Division</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpDiv"
                                                placeholder="Enter Emp Division" />
                                            <span id="div_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- Employee Grade (EmpGrade) -->
                                    <div class="col-8">
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
                                    <div class="col-8">
                                        <p>Emp Cost Center</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="EmpCostcenter"
                                                placeholder="Enter Emp Cost Center" />
                                            <span id="cost_err" class="error1 p-1"></span>
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
                                    <div class="col-8" id="dol_container" style="display: none;">
                                        <p>Date of Leaving</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="date" name="dol" />
                                            <span id="dol_err" class="error1 p-1"></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- MOL ID (MOLID) -->
                                    <div class="col-8">
                                        <p>MOL ID</p>
                                        <div class="input-group1">
                                            <input class="input--style-1" type="text" name="MOLID"
                                                placeholder="Enter MOL ID" />
                                            <span id="mol_err" class="error1 p-1"></span>
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
                                            <p>Account Number</p>
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
                                                    name="visa_number" required />
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
                                                <input class="input--style-1" type="date" placeholder="Visa Issue Date"
                                                    name="visa_issue_date" required />
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
                                                    name="visa_expiry_date" required />
                                                <span id="visa_expiry_date_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p>Passport Number</p>
                                            <div class="input-group1">
                                                <input class="input--style-1" type="text" placeholder="Passport Number"
                                                    name="passport_number" required />
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
                                                    placeholder="Passport Expiry Date" name="passport_expiry_date"
                                                    required />
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
                                            accept=".jpg,.jpeg,.png" required>
                                        <small style="color: gray;">(Allowed: JPG, JPEG, PNG)</small>
                                    </td>



                                    <td><label for="visa_doc">Visa Document:</label>

                                        <input type="file" class="form-control" name="visa_doc" id="visa_doc"
                                            accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small style="color: gray;">(Allowed: JPG, JPEG, PNG, PDF)</small>
                                    </td>
                                    <td><label for="passport_doc">Passport Document:</label>

                                        <input type="file" class="form-control" name="passport_doc" id="passport_doc"
                                            accept=".jpg,.jpeg,.png,.pdf" required>
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
            'IT': ['IT Manager', 'Technical Lead', 'Project Manager'],
            'HR': ['HR Manager', 'HR Director', 'HR Executive'],
            'Finance': ['Finance Manager', 'Financial Controller', 'CFO'],
            'Marketing': ['Marketing Manager', 'Marketing Director', 'Brand Manager'],
            'Operations': ['Operations Manager', 'Operations Director', 'COO']
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


</body>

</html>