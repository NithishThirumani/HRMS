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
    $degree = mysqli_real_escape_string($con, $_POST['degree']);
	$start_from = mysqli_real_escape_string($con, $_POST['start_from'] ?? '');
    $end_to = mysqli_real_escape_string($con, $_POST['end_to'] ?? '');
    $institute = mysqli_real_escape_string($con, $_POST['institute'] ?? '');
    $doj = mysqli_real_escape_string($con, $_POST['doj']);
    $emp_left_org = mysqli_real_escape_string($con, $_POST['emp_left_org']);
    $dol = isset($_POST['dol']) ? mysqli_real_escape_string($con, $_POST['dol']) : NULL;
    $reporting_manager = mysqli_real_escape_string($con, $_POST['reporting_manager']);
    $maritalsts = mysqli_real_escape_string($con, $_POST['maritalsts']);
    $EmpLoc = mysqli_real_escape_string($con, $_POST['EmpLoc']);
    $EmpDiv = mysqli_real_escape_string($con, $_POST['EmpDiv']);
    $EmpGrade = mysqli_real_escape_string($con, $_POST['EmpGrade']);
    $EmpCostcenter = mysqli_real_escape_string($con, $_POST['EmpCostcenter']);
    $MOLID = mysqli_real_escape_string($con, $_POST['MOLID']);
    $is_field_staff = mysqli_real_escape_string($con, $_POST['is_field_staff']);
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
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

 // Function to handle file upload securely
 function uploadFile($file, $uploadDir, $prefix) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return '';
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5242880) {
        return '';
    }

    $fileName = basename($file["name"]);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = $prefix . "_" . time() . "_" . uniqid() . "." . $fileExt;
    $targetFilePath = $uploadDir . $newFileName;

    // Allowed file types
    $allowedTypes = array("jpg", "jpeg", "png", "pdf");
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = array(
        'image/jpeg',
        'image/png',
        'application/pdf'
    );

    if (in_array($fileExt, $allowedTypes) && in_array($mimeType, $allowedMimes)) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            chmod($targetFilePath, 0644);
            return $newFileName;
        }
    }
    return '';
}
}

// Usage remains the same
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
		
    }// Insert into employees table
		
		// In the POST handling section, modify the SQL insert statement:
		$sql_insert_employee = "INSERT INTO employees 
		    (eid, first_name, last_name, full_name, user_name, email, password, contact, department, birthday, gender, address, country, degree, 
		    start_from, end_to, institute, doj, emp_left_org, dol, reporting_manager, maritalsts, EmpLoc, EmpDiv, EmpGrade, EmpCostcenter, MOLID, is_field_staff, visa_number, visa_type, visa_issue_date, visa_expiry_date, 
		    passport_number, passport_type, passport_issue_date, passport_expiry_date, 
		    country_of_issue, passport_issue_place, profile_pic, visa_doc, passport_doc, token) 
		VALUES 
		    ('$new_eid', '$first_name', '$last_name', '$full_name', '$username', '$email', '$hashed_password', '$contact', '$department', '$birthday', '$gender', '$address',
		     '$country', '$degree', '$start_from', '$end_to', '$institute', '$doj','$emp_left_org', '$dol', '$reporting_manager', '$maritalsts', '$EmpLoc', '$EmpDiv', '$EmpGrade', '$EmpCostcenter', '$MOLID', '$is_field_staff', '$visa_number', '$visa_type', '$visa_issue_date', '$visa_expiry_date',
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

$insert_login_query = "INSERT INTO emp_login (user_name, password) VALUES ('$username', '$password')";
$ress= mysqli_query($con, $insert_login_query);

$last_emp_id_query = "SELECT MAX(id) AS last_emp_id FROM employees";
$res = mysqli_query($con, $last_emp_id_query);

$row = mysqli_fetch_assoc($res);
$last_emp_id = $row['last_emp_id'];
$new_emp_id = $last_emp_id;

// Uncomment and use the salary value from the form
$salary = isset($_POST['sal']) ? mysqli_real_escape_string($con, $_POST['sal']) : 0;
$insert_salary_query = "INSERT INTO sal (`emp_id`,`base_salary`) VALUES ('$new_emp_id', '$salary')";
$resss = mysqli_query($con, $insert_salary_query);
   
                $mail = new PHPMailer();

               try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Replace with your email
    $mail->Password = 'your-app-specific-password'; // Use App-specific password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    
    $mail->setFrom('your-email@gmail.com', 'Your Company Name');
    $mail->addAddress($email, $first_name);
    $mail->isHTML(true);
    $mail->Subject = 'Account Verification';
    $mail->Body = 'Congratulations! ' . $first_name . ', your account has been created successfully.<br>Your Username: ' . $username . 
                 '<br><a href="' . $websiteUrl . '/verify_account.php?em=' . urlencode($email) . '&token=' . $token . '">Click here to verify your account</a>';
    
    $mail->send();
} catch (Exception $e) {
    error_log("Email sending failed: " . $mail->ErrorInfo);
    // Continue with registration even if email fails
}
}   

	
$countries = array(
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
    "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
    "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia",
    "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)", "Costa Rica",
    "Croatia", "Cuba", "Cyprus", "Czechia", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt",
    "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon",
    "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
    "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel",
    "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos",
    "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi",
    "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova",
    "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar (Burma)", "Namibia", "Nauru", "Nepal", "Netherlands",
    "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau",
    "Palestine State", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania",
    "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal",
    "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea",
    "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan",
    "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu",
    "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela",
    "Vietnam", "Yemen", "Zambia", "Zimbabwe"
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
    
    <!-- Custom styles for tabs -->
    <style>
        .nav-tabs .nav-item .nav-link {
            color: #5a5c69;
            font-weight: 600;
        }
        .nav-tabs .nav-item .nav-link.active {
            color: #2E8B57;
            border-color: #2E8B57 #dee2e6 #fff;
        }
        .tab-content {
            padding: 20px;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }
        .section-title {
            background-color: #20B2AA;
            color: white;
            padding: 8px 15px;
            margin-bottom: 15px;
        }
    </style>

</head>

<body id="page-top">
    <?php  include('sidebar.php'); ?>

    <?php  include('header.php'); ?>

    <div class="container-fluid">
        
        <form id="registrationForm" action="add_emp.php" method="POST" enctype="multipart/form-data">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">			   
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h2 class="title" style="color: #2E8B57;">Register New Employee</h2>
                            
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs" id="employeeFormTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="true">Personal Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">Education</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="employment-tab" data-toggle="tab" href="#employment" role="tab" aria-controls="employment" aria-selected="false">Employment</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="visa-tab" data-toggle="tab" href="#visa" role="tab" aria-controls="visa" aria-selected="false">Visa & Passport</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">Documents</a>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="employeeFormTabContent">
                                
                                <!-- Personal Details Tab -->
                                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                    <div class="section-title">Personal Details</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>First Name</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="First Name" name="fn" />
                                                        <span id="fn_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Last Name</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Last Name" name="ln" />
                                                        <span id="ln_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="field-column">
                                                    <div>
                                                        <p for="em3">Email Address</p>
                                                    </div>
                                                    <div>
                                                        <input type="email" class="input--style-1" name="em" id="em3" class="demo-input-box"
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
                                                        <p for="ps1">Password</p>
                                                    </div>
                                                    <div>
                                                        <input type="password" class="input--style-1" name="ps" id="ps1" class="demo-input-box"
                                                            placeholder="Create a Password" autocomplete="new-password">
                                                        <span id="ps_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="field-column">
                                                    <div>
                                                        <p for="cp1">Confirm Password</p>
                                                    </div>
                                                    <div>
                                                        <input type="password" class="input--style-1" name="cp" id="cp1" class="demo-input-box"
                                                            placeholder="Enter Confirm Password" autocomplete="new-password">
                                                        <span id="cp_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                                <input type="text" name="token" value="<?php echo uniqid().uniqid(); ?>" id="token1" name="token" hidden>
                                            </td>
                                            <td>
                                                <input type="checkbox" onclick="myFunction()"> Show Password <br><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p>DOB(DD-MM-YYYY)</p>
                                                        <div class="input-group1">
                                                            <input class="input--style-1" type="date" placeholder="BIRTHDATE" name="birthday" />
                                                            <span id="bd_err" class="error1 p-1"></span>
                                                        </div>
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
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Phone Number</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="number" placeholder="Contact Number" name="pn" />
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
                                                        <textarea class="input--style-1" type="text" placeholder="Address" name="address"></textarea>
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
                                                                <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <span id="country" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="col-6">
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
                                
                                <!-- Education Tab -->
                                <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                    <div class="section-title">Educational Details</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <div class="field-column">
                                                    <div>
                                                        <p for="degree">Qualification</p>
                                                    </div>
                                                    <div>
                                                        <input class="input--style-1" type="text" placeholder="Qualification" name="degree" />
                                                        <span id="degree_err" class="error-msg"></span><br><br>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Degree Start Date</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Start From" name="start_from" />
                                                        <span id="start_from_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Degree End Date</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="End To" name="end_to" />
                                                        <span id="end_to_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Institute</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Institute Name" name="institute" />
                                                        <span id="institute_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Employment Tab -->
                                <div class="tab-pane fade" id="employment" role="tabpanel" aria-labelledby="employment-tab">
                                    <div class="section-title">Employment Details</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Date of Joining</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Date of Joining" name="doj" required />
                                                        <span id="doj_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Department</p>
                                                    <div class="input-group1">
                                                        <select class="input--style-1" name="department" required>
                                                            <option value="">Select Department</option>
                                                            <option value="HR">Human Resources</option>
                                                            <option value="IT">Information Technology</option>
                                                            <option value="Finance">Finance</option>
                                                            <option value="Marketing">Marketing</option>
                                                            <option value="Operations">Operations</option>
                                                            <option value="Sales">Sales</option>
                                                        </select>
                                                        <span id="department_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Reporting Manager</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Reporting Manager" name="reporting_manager" />
                                                        <span id="reporting_manager_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Employee Location</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Employee Location" name="EmpLoc" />
                                                        <span id="emplc_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Employee Division</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Employee Division" name="EmpDiv" />
                                                        <span id="empdiv_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Employee Grade</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Employee Grade" name="EmpGrade" />
                                                        <span id="empgrade_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Cost Center</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Cost Center" name="EmpCostcenter" />
                                                        <span id="costcenter_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>MOL ID</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="MOL ID" name="MOLID" />
                                                        <span id="molid_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Base Salary</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="number" placeholder="Base Salary" name="sal" />
                                                        <span id="sal_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Is Field Staff?</p>
                                                    <div class="input-group1">
                                                        <select class="input--style-1" name="is_field_staff" id="is_field_staff">
                                                            <option value="No">No</option>
                                                            <option value="Yes">Yes</option>
                                                        </select>
                                                        <span id="is_field_staff_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Has Employee Left Organization?</p>
                                                    <div class="input-group1">
                                                        <select class="input--style-1" name="emp_left_org" id="emp_left_org">
                                                            <option value="No">No</option>
                                                            <option value="Yes">Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                            <td id="dol_field" style="display:none;">
                                                <div>
                                                    <p>Date of Leaving</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Date of Leaving" name="dol" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Visa & Passport Tab -->
                                <div class="tab-pane fade" id="visa" role="tabpanel" aria-labelledby="visa-tab">
                                    <div class="section-title">Visa Details</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Visa Number</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Visa Number" name="visa_number" />
                                                        <span id="visa_number_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Visa Type</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Visa Type" name="visa_type" />
                                                        <span id="visa_type_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Visa Issue Date</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Visa Issue Date" name="visa_issue_date" />
                                                        <span id="visa_issue_date_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Visa Expiry Date</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Visa Expiry Date" name="visa_expiry_date" />
                                                        <span id="visa_expiry_date_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div class="section-title mt-4">Passport Details</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Passport Number</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Passport Number" name="passport_number" />
                                                        <span id="passport_number_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Passport Type</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Passport Type" name="passport_type" />
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
                                                        <input class="input--style-1" type="date" placeholder="Passport Issue Date" name="passport_issue_date" />
                                                        <span id="passport_issue_date_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Passport Expiry Date</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="date" placeholder="Passport Expiry Date" name="passport_expiry_date" />
                                                        <span id="passport_expiry_date_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div>
                                                    <p>Country of Issue</p>
                                                    <div class="input-group1">
                                                        <select class="input--style-1" name="country_of_issue">
                                                            <option value="">Select Country</option>
                                                            <?php foreach ($countries as $country) { ?>
                                                                <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <span id="country_of_issue_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p>Passport Issue Place</p>
                                                    <div class="input-group1">
                                                        <input class="input--style-1" type="text" placeholder="Passport Issue Place" name="passport_issue_place" />
                                                        <span id="passport_issue_place_err" class="error1 p-1"></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Documents Tab -->
                                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                    <div class="section-title">Upload Documents</div>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                                           
                                            <div class="document-icon">
                                                <i class="fas fa-user-circle fa-3x"></i>
                                            </div>
                                            <div class="document-info">
                                                <h5>Profile Picture</h5>
                                                <p class="file-restrictions">Allowed: JPG, PNG, JPEG | Max: 2MB</p>
                                                <div class="custom-file-upload">
                                                    <input type="file" name="profile_pic" id="profile_pic" accept="image/jpeg,image/png,image/jpg" />
                                                    <label for="profile_pic"><i class="fas fa-upload"></i> Choose File</label>
                                                </div>
                                                <div class="selected-file" id="profile_pic_name">No file chosen</div>
                                                <span id="profile_pic_err" class="error1 p-1"></span>
                                            </div>
                                            </td>
                                            </tr>
                                            <tr>
                                            <td>
                                        
                                            <div class="document-icon">
                                                <i class="fas fa-id-card fa-3x"></i>
                                            </div>
                                            <div class="document-info">
                                                <h5>Visa Document</h5>
                                                <p class="file-restrictions">Allowed: PDF, JPG, PNG | Max: 5MB</p>
                                                <div class="custom-file-upload">
                                                    <input type="file" name="visa_doc" id="visa_doc" accept=".pdf,.jpg,.jpeg,.png" />
                                                    <label for="visa_doc"><i class="fas fa-upload"></i> Choose File</label>
                                                </div>
                                                <div class="selected-file" id="visa_doc_name">No file chosen</div>
                                                <span id="visa_doc_err" class="error1 p-1"></span>
                                            </div>
                                            </td>
                                            </tr>
                                            <tr>
                                            <td>
                                        <div class="document-card">
                                            <div class="document-icon">
                                                <i class="fas fa-passport fa-3x"></i>
                                            </div>
                                            <div class="document-info">
                                                <h5>Passport Document</h5>
                                                <p class="file-restrictions">Allowed: PDF, JPG, PNG | Max: 5MB</p>
                                                <div class="custom-file-upload">
                                                    <input type="file" name="passport_doc" id="passport_doc" accept=".pdf,.jpg,.jpeg,.png" />
                                                    <label for="passport_doc"><i class="fas fa-upload"></i> Choose File</label>
                                                </div>
                                                <div class="selected-file" id="passport_doc_name">No file chosen</div>
                                                <span id="passport_doc_err" class="error1 p-1"></span>
                                            </div>
                                        </div>
                                    </div>                             
                            </td>
                                        </tr>
                                    </table>
                                  
                            <div class="p-t-20 text-center">
                                <!-- Replace single register button with tab-specific buttons -->
                                <button class="btn btn--radius btn--green tab-button" id="personal-save-btn" type="button">Save & Continue</button>
                                <button class="btn btn--radius btn--green tab-button" id="education-save-btn" type="button" style="display:none;">Save & Continue</button>
                                <button class="btn btn--radius btn--green tab-button" id="employment-save-btn" type="button" style="display:none;">Save & Continue</button>
                                <button class="btn btn--radius btn--green tab-button" id="visa-save-btn" type="button" style="display:none;">Save & Continue</button>
                                <button class="btn btn--radius btn--green tab-button" id="documents-register-btn" type="submit" name="register" style="display:none;">Register</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    
    <script>
        // Show/hide password function
        function myFunction() {
            var x = document.getElementById("ps1");
            var y = document.getElementById("cp1");
            if (x.type === "password") {
                x.type = "text";
                y.type = "text";
            } else {
                x.type = "password";
                y.type = "password";
            }
        }
        
        // Show/hide date of leaving field based on employee left organization selection
        $(document).ready(function() {
            // Make the form more compact with CSS
            $(".card-body").css({
                "padding": "15px"
            });
            
            $(".table td").css({
                "padding": "5px"
            });
            
            $(".input--style-1").css({
                "padding": "8px 12px",
                "margin-bottom": "5px"
            });
            
            $(".section-title").css({
                "margin-bottom": "10px",
                "font-size": "16px"
            });
            
            // Tab navigation and button display logic
            $('.nav-link').on('click', function() {
                // Hide all tab buttons
                $('.tab-button').hide();
                
                // Show the button for the active tab
                var activeTab = $(this).attr('href').substring(1);
                if(activeTab === 'documents') {
                    $('#documents-register-btn').show();
                } else {
                    $('#' + activeTab + '-save-btn').show();
                }
            });
            // Also add this to ensure the documents-register-btn shows when documents tab is active
            if($('#documents-tab').hasClass('active')) {
                $('.tab-button').hide();
                $('#documents-register-btn').show();
            }
            // Save and continue functionality
            $('#personal-save-btn').click(function() {
                // Validate personal tab fields
                if(validatePersonalTab()) {
                    $('#education-tab').tab('show');
                }
            });
            
            $('#education-save-btn').click(function() {
                // Validate education tab fields
                if(validateEducationTab()) {
                    $('#employment-tab').tab('show');
                }
            });
            
            $('#employment-save-btn').click(function() {
                // Validate employment tab fields
                if(validateEmploymentTab()) {
                    $('#visa-tab').tab('show');
                }
            });
            
            $('#visa-save-btn').click(function() {
                // Validate visa tab fields
                if(validateVisaTab()) {
                    $('#documents-tab').tab('show');
                }

                // Make sure the register button is visible when documents tab is clicked
                $('#documents-tab').click(function() {
                    $('.tab-button').hide();
                    $('#documents-register-btn').show();
                });


            });
            
            // Validation functions for each tab
            function validatePersonalTab() {
                // Basic validation for required fields
                var isValid = true;
                
                if($('input[name="fn"]').val() === '') {
                    $('#fn_err').text('First name is required');
                    isValid = false;
                } else {
                    $('#fn_err').text('');
                }
                
                if($('input[name="ln"]').val() === '') {
                    $('#ln_err').text('Last name is required');
                    isValid = false;
                } else {
                    $('#ln_err').text('');
                }
                
                // Email validation
                var email = $('input[name="em"]').val();
                if(email === '') {
                    $('#em_err').text('Email is required');
                    isValid = false;
                } else if(!isValidEmail(email)) {
                    $('#em_err').text('Please enter a valid email');
                    isValid = false;
                } else {
                    $('#em_err').text('');
                }
                
                // Password validation
                var password = $('input[name="ps"]').val();
                var confirmPassword = $('input[name="cp"]').val();
                
                if(password === '') {
                    $('#ps_err').text('Password is required');
                    isValid = false;
                } else if(password.length < 6) {
                    $('#ps_err').text('Password must be at least 6 characters');
                    isValid = false;
                } else {
                    $('#ps_err').text('');
                }
                
                if(confirmPassword === '') {
                    $('#cp_err').text('Confirm password is required');
                    isValid = false;
                } else if(password !== confirmPassword) {
                    $('#cp_err').text('Passwords do not match');
                    isValid = false;
                } else {
                    $('#cp_err').text('');
                }
                
                return isValid;
            }
            
            function validateEducationTab() {
                // Education tab validation (optional fields)
                return true;
            }
            
            function validateEmploymentTab() {
                // Basic validation for required employment fields
                var isValid = true;
                
                if($('input[name="doj"]').val() === '') {
                    $('#doj_err').text('Date of joining is required');
                    isValid = false;
                } else {
                    $('#doj_err').text('');
                }
                
                if($('select[name="department"]').val() === '') {
                    $('#department_err').text('Department is required');
                    isValid = false;
                } else {
                    $('#department_err').text('');
                }
                
                return isValid;
            }
            
            function validateVisaTab() {
                // Visa tab validation (optional fields)
                return true;
            }
            
            function isValidEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }
            
            $('#emp_left_org').change(function() {
                if ($(this).val() === 'Yes') {
                    $('#dol_field').show();
                } else {
                    $('#dol_field').hide();
                }
            });
</script>
</body>
</html>