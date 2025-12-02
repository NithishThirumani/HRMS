<?php
include('session.php');
include('connection.php');

// Add safe_html function to handle null values
function safe_html($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Check if 'id' is passed in the URL
if (!isset($_GET['id'])) {
    die("Employee ID not provided.");
}

$id = $_GET['id'];

// Fetch employee details
$query = "SELECT e.id, e.eid, e.first_name, e.last_name, e.email, e.birthday, e.maritalsts, e.contact, e.address, e.country, 
    e.degree, e.start_from, e.end_to, e.Institute, e.status, e.doj, e.EmpLoc, e.EmpDiv, e.EmpGrade, e.role, e.department_id, 
    d.name AS department, e.reporting_manager, e.emp_left_org, e.dol, e.EmpCostcenter, e.MOLID, e.bank_name, e.account_no, e.iban, e.nominee, 
    e.blood_group, e.gender, e.is_field_staff, e.visa_number, e.visa_type, e.visa_issue_date, e.visa_expiry_date, 
    e.passport_number, e.passport_type, e.passport_issue_date, e.passport_expiry_date, e.country_of_issue, 
    e.passport_issue_place, e.profile_pic, e.visa_doc, e.passport_doc 
FROM employees e 
LEFT JOIN departments d ON e.department_id = d.id 
WHERE e.id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employee = mysqli_fetch_assoc($result);

if (!$employee) {
    die("Employee not found.");
}

// Cast all values to strings to prevent null values
foreach ($employee as $key => $value) {
    $employee[$key] = $value === null ? '' : (string)$value;
}

// Handle form submission for updating employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update_query = "UPDATE employees SET 
        first_name=?, last_name=?, email=?, birthday=?, maritalsts=?, contact=?, 
        address=?, country=?, degree=?, start_from=?, end_to=?, Institute=?, 
        status=?, doj=?, EmpLoc=?, EmpDiv=?, EmpGrade=?, role=?, department_id=?, 
        reporting_manager=?, emp_left_org=?, dol=?, EmpCostcenter=?, MOLID=?,
        bank_name=?, account_no=?, iban=?, nominee=?, is_field_staff=?,
        visa_number=?, visa_type=?, visa_issue_date=?, visa_expiry_date=?,
        passport_number=?, passport_type=?, passport_issue_date=?, passport_expiry_date=?,
        country_of_issue=?, passport_issue_place=?
        WHERE id=?";

    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssssssssssssssssssssssssssssssi",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['birthday'],
        $_POST['maritalsts'],
        $_POST['contact'],
        $_POST['address'],
        $_POST['country'],
        $_POST['degree'],
        $_POST['start_from'],
        $_POST['end_to'],
        $_POST['Institute'],
        $_POST['status'],
        $_POST['doj'],
        $_POST['EmpLoc'],
        $_POST['EmpDiv'],
        $_POST['EmpGrade'],
        $_POST['role'],
        $_POST['department_id'],
        $_POST['reporting_manager'],
        $_POST['emp_left_org'],
        $_POST['dol'],
        $_POST['EmpCostcenter'],
        $_POST['MOLID'],
        $_POST['bank_name'],
        $_POST['account_no'],
        $_POST['iban'],
        $_POST['nominee'],
        $_POST['is_field_staff'],
        $_POST['visa_number'],
        $_POST['visa_type'],
        $_POST['visa_issue_date'],
        $_POST['visa_expiry_date'],
        $_POST['passport_number'],
        $_POST['passport_type'],
        $_POST['passport_issue_date'],
        $_POST['passport_expiry_date'],
        $_POST['country_of_issue'],
        $_POST['passport_issue_place'],
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        // Handle file uploads if files were submitted
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
            // Handle resume upload
            $resume_path = "uploads/resumes/" . $id . "_" . basename($_FILES['resume']['name']);
            move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path);

            // Update resume path in database
            $update_resume = "UPDATE employees SET resume=? WHERE id=?";
            $stmt = mysqli_prepare($con, $update_resume);
            mysqli_stmt_bind_param($stmt, "si", $resume_path, $id);
            mysqli_stmt_execute($stmt);
        }

        if (isset($_FILES['passport']) && $_FILES['passport']['error'] == 0) {
            // Handle passport upload
            $passport_path = "uploads/passports/" . $id . "_" . basename($_FILES['passport']['name']);
            move_uploaded_file($_FILES['passport']['tmp_name'], $passport_path);

            // Update passport path in database
            $update_passport = "UPDATE employees SET passport_doc=? WHERE id=?";
            $stmt = mysqli_prepare($con, $update_passport);
            mysqli_stmt_bind_param($stmt, "si", $passport_path, $id);
            mysqli_stmt_execute($stmt);
        }

        echo "<script>alert('Employee details updated successfully!'); window.location.href='view_emp.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Employee</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">

    <style>
        .nav-tabs {
            border-bottom: 2px solid #2E8B57;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 10px 10px 0;
            border-radius: 5px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #555;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 5px 5px 0 0;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(46, 139, 87, 0.1);
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background: #2E8B57;
            border: none;
        }

        .tab-content {
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .card-heading {
            background: #2E8B57;
            color: #fff;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }

        .input-group1 {
            margin-bottom: 20px;
        }

        .input-group1 label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .input--style-1 {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .input--style-1:focus {
            border-color: #2E8B57;
            box-shadow: 0 0 0 0.2rem rgba(46, 139, 87, 0.25);
        }

        .table th {
            background: #2E8B57;
            color: #fff;
            padding: 12px;
        }

        .btn-success {
            background: #2E8B57;
            border: none;
            padding: 10px 30px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: #246B47;
            transform: translateY(-2px);
        }

        .section-title {
            color: #2E8B57;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2E8B57;
        }
    </style>
    <style>
        .floating-save-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .floating-save-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .table {
            margin-bottom: 0;
        }

        .table td {
            padding: 8px;
            vertical-align: middle;
        }

        .input-group1 {
            margin-bottom: 10px;
            /* Reduced from 20px */
        }

        .input-group1 label {
            display: block;
            margin-bottom: 4px;
            /* Reduced from 8px */
            font-size: 0.9rem;
            color: #495057;
            font-weight: 500;
        }

        .input--style-1 {
            height: 35px;
            /* Reduced height */
            padding: 6px 10px;
            font-size: 0.9rem;
        }

        textarea.input--style-1 {
            height: auto;
            min-height: 60px;
        }

        .tab-content {
            padding: 15px;
            /* Reduced from 30px */
        }

        .table th {
            padding: 10px;
            /* Reduced padding */
            background: #2E8B57;
            color: white;
            font-size: 1rem;
        }

        /* Adjust spacing between tabs */
        .nav-tabs {
            margin-bottom: 15px;
            /* Reduced from 30px */
        }

        .nav-tabs .nav-link {
            padding: 8px 15px;
            /* Reduced padding */
        }

        /* Make form elements full width */
        .input--style-1 {
            width: 100%;
            max-width: 100%;
        }

        /* Adjust table layout */
        .table td {
            width: 33.33%;
            /* Equal width columns */
        }
    </style>





</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <form id="editForm" action="" method="POST">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h3>Edit Employee Details</h3>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="employeeTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal"
                                    role="tab">
                                    <i class="fas fa-user mr-2"></i>Personal Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab">
                                    <i class="fas fa-graduation-cap mr-2"></i>Education
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employment-tab" data-toggle="tab" href="#employment" role="tab">
                                    <i class="fas fa-briefcase mr-2"></i>Employment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents" role="tab">
                                    <i class="fas fa-file-alt mr-2"></i>Documents & Visa
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank" role="tab">
                                    <i class="fas fa-university mr-2"></i>Bank Details
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="employeeTabContent">
                            <!-- Personal Information Tab -->
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3" style="background-color: #20B2AA; text-align: left;">Personal
                                            Details</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>EID:</label>
                                                <input class="input--style-1" type="text" name="eid"
                                                    value="<?php echo safe_html($employee['eid'] ?? ''); ?>"
                                                    readonly />
                                                <input class="input--style-1" type="hidden" name="id"
                                                    value="<?php echo safe_html($employee['eid'] ?? ''); ?>">
                                                <span id="eid_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>First Name:</label>
                                                <input class="input--style-1" type="text" name="first_name"
                                                    value="<?php echo safe_html($employee['first_name'] ?? ''); ?>"
                                                    required>
                                                <span id="fn_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Last Name:</label>
                                                <input class="input--style-1" type="text" name="last_name"
                                                    value="<?php echo safe_html($employee['last_name'] ?? ''); ?>"
                                                    required>
                                                <span id="ln_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Email:</label>
                                                <input class="input--style-1" type="email" name="email"
                                                    value="<?php echo safe_html($employee['email'] ?? ''); ?>"
                                                    required>
                                                <span id="email_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Gender:</label>
                                                <select class="input--style-1" name="gender">
                                                    <option value="Male" <?php echo ($employee['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option value="Female" <?php echo ($employee['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Blood Group:</label>
                                                <select class="input--style-1" name="blood_group">
                                                    <option value="">Select Blood Group</option>
                                                    <option value="A+" <?php echo ($employee['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                                    <option value="A-" <?php echo ($employee['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                                    <option value="B+" <?php echo ($employee['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                                    <option value="B-" <?php echo ($employee['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                                    <option value="O+" <?php echo ($employee['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                                    <option value="O-" <?php echo ($employee['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                                    <option value="AB+" <?php echo ($employee['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                                    <option value="AB-" <?php echo ($employee['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Birthday:</label>
                                                <input class="input--style-1" type="date" name="birthday"
                                                    value="<?php echo safe_html($employee['birthday'] ?? ''); ?>">
                                                <span id="birthday_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Marital Status:</label>
                                                <select class="input--style-1" name="maritalsts">
                                                    <option value="Single" <?php echo ($employee['maritalsts'] == 'Single') ? 'selected' : ''; ?>>Single
                                                    </option>
                                                    <option value="Married" <?php echo ($employee['maritalsts'] == 'Married') ? 'selected' : ''; ?>>
                                                        Married</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Contact:</label>
                                                <input class="input--style-1" type="text" name="contact"
                                                    value="<?php echo safe_html($employee['contact']); ?>">
                                                <span id="contact_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <div class="input-group1">
                                                <label>Address:</label>
                                                <textarea class="input--style-1" name="address"><?php echo safe_html($employee['address']); ?></textarea>
                                                <span id="address_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Country:</label>
                                                <input class="input--style-1" type="text" name="country"
                                                    value="<?php echo safe_html($employee['country']); ?>">
                                                <span id="country_err" class="error1 p-1"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>


                            <!-- Education Tab -->
                            <div class="tab-pane fade" id="education" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3">Educational Details</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Degree:</label>
                                                <input class="input--style-1" type="text" name="degree"
                                                    value="<?php echo safe_html($employee['degree']); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Institute:</label>
                                                <input class="input--style-1" type="text" name="Institute"
                                                    value="<?php echo safe_html($employee['Institute']); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Start From:</label>
                                                <input class="input--style-1" type="date" name="start_from"
                                                    value="<?php echo safe_html($employee['start_from']); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>End To:</label>
                                                <input class="input--style-1" type="date" name="end_to"
                                                    value="<?php echo safe_html($employee['end_to']); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>


                            <!-- Employment Tab -->
                            <div class="tab-pane fade" id="employment" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3">Employment Details</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Status:</label>
                                                <select class="input--style-1" name="status">
                                                    <option value="Active" <?php echo ($employee['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="Inactive" <?php echo ($employee['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive
                                                    </option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Date of Joining:</label>
                                                <input class="input--style-1" type="date" name="doj"
                                                    value="<?php echo safe_html($employee['doj'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Employee Location:</label>
                                                <input class="input--style-1" type="text" name="EmpLoc"
                                                    value="<?php echo safe_html($employee['EmpLoc'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Division:</label>
                                                <input class="input--style-1" type="text" name="EmpDiv"
                                                    value="<?php echo safe_html($employee['EmpDiv'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Grade:</label>
                                                <input class="input--style-1" type="text" name="EmpGrade"
                                                    value="<?php echo safe_html($employee['EmpGrade'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Role:</label>
                                                <input class="input--style-1" type="text" name="role"
                                                    value="<?php echo safe_html($employee['role'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Department:</label>
                                                <input class="input--style-1" type="text" name="department"
                                                    value="<?php echo safe_html($employee['department'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Reporting Manager:</label>
                                                <input class="input--style-1" type="text" name="reporting_manager"
                                                    value="<?php echo safe_html($employee['reporting_manager'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Cost Center:</label>
                                                <input class="input--style-1" type="text" name="EmpCostcenter"
                                                    value="<?php echo safe_html($employee['EmpCostcenter'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>MOL ID:</label>
                                                <input class="input--style-1" type="text" name="MOLID"
                                                    value="<?php echo safe_html($employee['MOLID'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Left Organization:</label>
                                                <select class="input--style-1" name="emp_left_org">
                                                    <option value="No" <?php echo ($employee['emp_left_org'] == 'No') ? 'selected' : ''; ?>>No</option>
                                                    <option value="Yes" <?php echo ($employee['emp_left_org'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Date of Leaving:</label>
                                                <input class="input--style-1" type="date" name="dol"
                                                    value="<?php echo safe_html($employee['dol'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Field Staff:</label>
                                                <select class="input--style-1" name="is_field_staff">
                                                    <option value="0" <?php echo ($employee['is_field_staff'] == '0') ? 'selected' : ''; ?>>No</option>
                                                    <option value="1" <?php echo ($employee['is_field_staff'] == '1') ? 'selected' : ''; ?>>Yes</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <!-- Documents & Visa Tab -->
                            <div class="tab-pane fade" id="documents" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3">Documents & Visa Details</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Visa Number:</label>
                                                <input class="input--style-1" type="text" name="visa_number"
                                                    value="<?php echo safe_html($employee['visa_number'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Visa Type:</label>
                                                <input class="input--style-1" type="text" name="visa_type"
                                                    value="<?php echo safe_html($employee['visa_type'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Visa Issue Date:</label>
                                                <input class="input--style-1" type="date" name="visa_issue_date"
                                                    value="<?php echo safe_html($employee['visa_issue_date'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Visa Expiry Date:</label>
                                                <input class="input--style-1" type="date" name="visa_expiry_date"
                                                    value="<?php echo safe_html($employee['visa_expiry_date'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Number:</label>
                                                <input class="input--style-1" type="text" name="passport_number"
                                                    value="<?php echo safe_html($employee['passport_number'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Type:</label>
                                                <input class="input--style-1" type="text" name="passport_type"
                                                    value="<?php echo safe_html($employee['passport_type'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Issue Date:</label>
                                                <input class="input--style-1" type="date" name="passport_issue_date"
                                                    value="<?php echo safe_html($employee['passport_issue_date'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Expiry Date:</label>
                                                <input class="input--style-1" type="date" name="passport_expiry_date"
                                                    value="<?php echo safe_html($employee['passport_expiry_date'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Country of Issue:</label>
                                                <input class="input--style-1" type="text" name="country_of_issue"
                                                    value="<?php echo safe_html($employee['country_of_issue'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Issue Place:</label>
                                                <input class="input--style-1" type="text" name="passport_issue_place"
                                                    value="<?php echo safe_html($employee['passport_issue_place'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Profile Pic:</label>
                                                <input class="input--style-1" type="file" name="profile">
                                                <?php if (!empty($employee['profile_pic'])): ?>
                                                    <small> <?php echo basename($employee['passport_doc']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Visa Doc:</label>
                                                <input class="input--style-1" type="file" name="visa"
                                                    accept=".pdf,.doc,.docx">
                                                <?php if (!empty($employee['visa_doc'])): ?>
                                                    <small>
                                                        <?php echo basename($employee['visa_doc']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Passport Document:</label>
                                                <input class="input--style-1" type="file" name="passport"
                                                    accept=".pdf,.doc,.docx">
                                                <?php if (!empty($employee['passport_doc'])): ?>
                                                    <small>
                                                        <?php echo basename($employee['passport_doc']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>



                            <!-- Bank Details Tab -->
                            <div class="tab-pane fade" id="bank" role="tabpanel">
                                <table class="table">
                                    <tr>
                                        <th colspan="3">Bank Information</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>Bank Name:</label>
                                                <input class="input--style-1" type="text" name="bank_name"
                                                    value="<?php echo safe_html($employee['bank_name'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Account Number:</label>
                                                <input class="input--style-1" type="text" name="account_no"
                                                    value="<?php echo safe_html($employee['account_no'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="input-group1">
                                                <label>IBAN:</label>
                                                <input class="input--style-1" type="text" name="iban"
                                                    value="<?php echo safe_html($employee['iban'] ?? ''); ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group1">
                                                <label>Nominee:</label>
                                                <input class="input--style-1" type="text" name="nominee"
                                                    value="<?php echo safe_html($employee['nominee'] ?? ''); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <!-- Add this before closing form tag -->
                            <button type="submit" class="btn btn-success floating-save-btn">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>





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

                            <!-- Page level plugins -->
                            <script src="vendor/datatables/jquery.dataTables.min.js"></script>
                            <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

                            <!-- Page level custom scripts -->
                            <script src="js/demo/datatables-demo.js"></script>
                        </div>
                    </div>
                </div>
                <script>
                    $(document).ready(function () {
                        // Form validation
                        $('#editForm').on('submit', function (e) {
                            e.preventDefault();

                            // Add loading state to button
                            $('.floating-save-btn').html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);

                            // Submit form
                            this.submit();
                        });

                        // Tab navigation feedback
                        $('.nav-link').on('click', function () {
                            // Smooth scroll to top of form
                            $('html, body').animate({
                                scrollTop: $("#employeeTab").offset().top - 20
                            }, 300);
                        });
                    });
                </script>

</body>

</html>