<?php
include('session.php');
include('connection.php');
require __DIR__ . '/../vendor/autoload.php'; // Make sure you have PHPSpreadsheet installed
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if (isset($_POST["upload"])) {
    if ($_FILES["employee_data"]["name"] != '') {
        $allowed_extension = array('xls', 'xlsx');
        $file_array = explode(".", $_FILES["employee_data"]["name"]);
        $file_extension = end($file_array);

        if (in_array($file_extension, $allowed_extension)) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($_FILES["employee_data"]["tmp_name"]);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                // Read data from Excel using the correct method
                $first_name = $worksheet->getCell('A' . $row)->getValue();
                $last_name = $worksheet->getCell('B' . $row)->getValue();
                $full_name = $first_name . ' ' . $last_name;
                $user_name = strtolower(str_replace(' ', '', $first_name) . '.' . str_replace(' ', '', $last_name));
                $email = $worksheet->getCell('C' . $row)->getValue();
                $birthday = $worksheet->getCell('D' . $row)->getValue();
                $gender = $worksheet->getCell('E' . $row)->getValue();
                $maritalsts = $worksheet->getCell('F' . $row)->getValue();
                $blood_group = $worksheet->getCell('G' . $row)->getValue();
                $contact = $worksheet->getCell('H' . $row)->getValue();
                $address = $worksheet->getCell('I' . $row)->getValue();
                $country = $worksheet->getCell('J' . $row)->getValue();
                $degree = $worksheet->getCell('K' . $row)->getValue();
                $start_from = $worksheet->getCell('L' . $row)->getValue();
                $end_to = $worksheet->getCell('M' . $row)->getValue();
                $Institute = $worksheet->getCell('N' . $row)->getValue();
                // Add validation for Institute field since it cannot be null
                $Institute = !empty($Institute) ? $Institute : 'Not Specified';

                $status = $worksheet->getCell('O' . $row)->getValue();
                $doj = $worksheet->getCell('P' . $row)->getValue();
                $EmpLoc = $worksheet->getCell('Q' . $row)->getValue();
                $EmpDiv = $worksheet->getCell('R' . $row)->getValue();
                $EmpDiv = !empty($EmpDiv) ? $EmpDiv : 'Not Assigned';  // Add default value

                $EmpGrade = $worksheet->getCell('S' . $row)->getValue();
                $EmpGrade = !empty($EmpGrade) ? $EmpGrade : 'Not Assigned';  // Add default value
                $role = $worksheet->getCell('T' . $row)->getValue();
                $designation = $worksheet->getCell('U' . $row)->getValue();
                $department = $worksheet->getCell('V' . $row)->getValue();
                $emp_left_org = $worksheet->getCell('W' . $row)->getValue();
                $dol = $worksheet->getCell('X' . $row)->getValue();
                $EmpCostcenter = $worksheet->getCell('Y' . $row)->getValue();
                $MOLID = $worksheet->getCell('Z' . $row)->getValue();
                $bank_name = $worksheet->getCell('AA' . $row)->getValue();
                $account_no = $worksheet->getCell('AB' . $row)->getValue();
                $iban = $worksheet->getCell('AC' . $row)->getValue();
                $nominee = $worksheet->getCell('AD' . $row)->getValue();
                $visa_number = $worksheet->getCell('AE' . $row)->getValue();
                $visa_type = $worksheet->getCell('AF' . $row)->getValue();
                $visa_issue_date = $worksheet->getCell('AG' . $row)->getValue();
                $visa_expiry_date = $worksheet->getCell('AH' . $row)->getValue();
                $passport_number = $worksheet->getCell('AI' . $row)->getValue();
                $passport_type = $worksheet->getCell('AJ' . $row)->getValue();
                $passport_issue_date = $worksheet->getCell('AK' . $row)->getValue();
                $passport_expiry_date = $worksheet->getCell('AL' . $row)->getValue();
                $country_of_issue = $worksheet->getCell('AM' . $row)->getValue();
                $passport_issue_place = $worksheet->getCell('AN' . $row)->getValue();
                $is_field_staff = $worksheet->getCell('AO' . $row)->getValue();
                $labour_card_no = $worksheet->getCell('AP' . $row)->getValue();
                $labour_card_start_date = $worksheet->getCell('AQ' . $row)->getValue();
                $labour_card_end_date = $worksheet->getCell('AR' . $row)->getValue();
                $pregnancy_status = $worksheet->getCell('AS' . $row)->getValue();
                $due_date = $worksheet->getCell('AT' . $row)->getValue();
                $is_trainee = $worksheet->getCell('AU' . $row)->getValue();

                // Basic validation
                // Basic validation for required fields
                if (empty($first_name) || empty($email)) {
                    continue;
                }

                // Add default values for required fields
                $contact = !empty($contact) ? $contact : 'Not Provided';
                $address = !empty($address) ? $address : 'Not Provided';
                $degree = !empty($degree) ? $degree : 'Not Specified';
                $EmpLoc = !empty($EmpLoc) ? $EmpLoc : 'Not Assigned';
                $EmpDiv = !empty($EmpDiv) ? $EmpDiv : 'Not Assigned';
                $EmpGrade = !empty($EmpGrade) ? $EmpGrade : 'Not Assigned';
                $EmpCostcenter = !empty($EmpCostcenter) ? $EmpCostcenter : 'Not Assigned';
                $MOLID = !empty($MOLID) ? $MOLID : '0';
                $bank_name = !empty($bank_name) ? $bank_name : 'Not Provided';
                $account_no = !empty($account_no) ? $account_no : 'Not Provided';
                $iban = !empty($iban) ? $iban : 'Not Provided';
                $nominee = !empty($nominee) ? $nominee : 'Not Specified';
                $passport_type = !empty($passport_type) ? $passport_type : 'Not Specified';
                $passport_issue_place = !empty($passport_issue_place) ? $passport_issue_place : 'Not Specified';
                $is_field_staff = $worksheet->getCell('AO' . $row)->getValue();
                $labour_card_no = $worksheet->getCell('AP' . $row)->getValue();
                $labour_card_start_date = $worksheet->getCell('AQ' . $row)->getValue();
                $labour_card_end_date = $worksheet->getCell('AR' . $row)->getValue();
                $pregnancy_status = $worksheet->getCell('AS' . $row)->getValue();
                $due_date = $worksheet->getCell('AT' . $row)->getValue();
                $is_trainee = $worksheet->getCell('AU' . $row)->getValue();

                // Generate password
                $password = generateRandomPassword();
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Generate EID
                $sql_eid = "SELECT MAX(CAST(SUBSTRING(eid, 4) AS UNSIGNED)) AS max_eid FROM employees";
                $result_eid = mysqli_query($con, $sql_eid);
                $row_eid = mysqli_fetch_assoc($result_eid);
                $max_eid = $row_eid['max_eid'];
                $new_eid = 'CME' . str_pad(($max_eid + 1), 3, "0", STR_PAD_LEFT);

                // Get reporting manager based on department
                $reporting_manager = '';
                if (!empty($department)) {
                    $sql_manager = "SELECT eid FROM employees WHERE department_id = ? AND role = 'manager' LIMIT 1";
                    $stmt_manager = $con->prepare($sql_manager);
                    $stmt_manager->bind_param("i", intval($department)); // Changed from "s" to "i" for integer
                    $stmt_manager->execute();
                    $result_manager = $stmt_manager->get_result();
                    if ($row_manager = $result_manager->fetch_assoc()) {
                        $reporting_manager = $row_manager['eid'];
                    }
                }

                // Convert is_trainee to Y/N
                $is_trainee = strtolower($is_trainee);
                $is_trainee = ($is_trainee == 'yes' || $is_trainee == 'y' || $is_trainee == '1') ? 'Y' : 'N';

                // Format dates
                $birthday = !empty($birthday) ? date('Y-m-d', strtotime($birthday)) : null;
                $doj = !empty($doj) ? date('Y-m-d', strtotime($doj)) : null;
                $dol = !empty($dol) ? date('Y-m-d', strtotime($dol)) : null;
                $visa_issue_date = !empty($visa_issue_date) ? date('Y-m-d', strtotime($visa_issue_date)) : null;
                $visa_expiry_date = !empty($visa_expiry_date) ? date('Y-m-d', strtotime($visa_expiry_date)) : null;
                $passport_issue_date = !empty($passport_issue_date) ? date('Y-m-d', strtotime($passport_issue_date)) : null;
                $passport_expiry_date = !empty($passport_expiry_date) ? date('Y-m-d', strtotime($passport_expiry_date)) : null;
                $labour_card_start_date = !empty($labour_card_start_date) ? date('Y-m-d', strtotime($labour_card_start_date)) : null;
                $labour_card_end_date = !empty($labour_card_end_date) ? date('Y-m-d', strtotime($labour_card_end_date)) : null;
                $due_date = !empty($due_date) ? date('Y-m-d', strtotime($due_date)) : null;

                // Insert employee data
                $sql = "INSERT INTO employees (
                    eid, first_name, last_name, full_name, user_name, email, password, 
                    birthday, gender, maritalsts, blood_group, contact, address, country,
                    degree, start_from, end_to, Institute, status, doj, EmpLoc, EmpDiv,
                    EmpGrade, role, designation, department_id, reporting_manager, emp_left_org,
                    dol, EmpCostcenter, MOLID, bank_name, account_no, iban, nominee,
                    visa_number, visa_type, visa_issue_date, visa_expiry_date, 
                    passport_number, passport_type, passport_issue_date, passport_expiry_date,
                    country_of_issue, passport_issue_place, is_field_staff, labour_card_no,
                    labour_card_start_date, labour_card_end_date, pregnancy_status, due_date,
                    is_trainee
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?
                )";

                error_log("SQL Query: " . $sql);
                $stmt = $con->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $con->error);
                    continue;
                }

                $stmt->bind_param(
                    "ssssssssssssssssssssssssssssssssssssssssssssssssssss",
                    $new_eid,
                    $first_name,
                    $last_name,
                    $full_name,
                    $user_name,
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
                    $status,
                    $doj,
                    $EmpLoc,
                    $EmpDiv,
                    $EmpGrade,
                    $role,
                    $designation,
                    $department,
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
                    $is_field_staff,
                    $labour_card_no,
                    $labour_card_start_date,
                    $labour_card_end_date,
                    $pregnancy_status,
                    $due_date,
                    $is_trainee
                );

                if ($stmt->execute()) {
                    // Store credentials in session array for printing
                    if (!isset($_SESSION['new_employee_credentials'])) {
                        $_SESSION['new_employee_credentials'] = array();
                    }
                    $_SESSION['new_employee_credentials'][] = array(
                        'name' => $full_name,
                        'email' => $email,
                        'eid' => $new_eid,
                        'password' => $password
                    );
                } else {
                    error_log("Error inserting employee: " . $stmt->error);
                }
            }
            echo "<script>
                if(confirm('Data uploaded successfully! Would you like to view the credentials?')) {
                    window.location.href = '#credentials-section';
                }
            </script>";
        } else {
            echo "<script>alert('Invalid file format. Please upload Excel file.');</script>";
        }
    }
}

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Remove the sendWelcomeEmail function as it's no longer needed

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bulk Upload Employees</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Bulk Upload Employees</h1>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Upload Excel File</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <a href="templates/employee_template.xlsx" class="btn btn-success">
                                            <i class="fas fa-download"></i> Download Template
                                        </a>
                                    </div>
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label>Choose Excel File</label>
                                            <input type="file" name="employee_data" class="form-control"
                                                accept=".xlsx, .xls" required>
                                        </div>
                                        <button type="submit" name="upload" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Upload Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    @media print {
        #wrapper > *:not(#content-wrapper) {
            display: none !important;
        }
        .btn, .no-print {
            display: none !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background: none !important;
            border: none !important;
        }
    }
    </style>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>
                    </div>
                    
                    <?php if (isset($_SESSION['new_employee_credentials']) && !empty($_SESSION['new_employee_credentials'])) : ?>
                    <div class="row" id="credentials-section">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Employee Credentials</h6>
                                    <button onclick="window.print();" class="btn btn-sm btn-info">
                                        <i class="fas fa-print"></i> Print Credentials
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Employee ID</th>
                                                    <th>Password</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($_SESSION['new_employee_credentials'] as $credential) : ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($credential['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($credential['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($credential['eid']); ?></td>
                                                    <td><?php echo htmlspecialchars($credential['password']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    // Clear the credentials after displaying
                    unset($_SESSION['new_employee_credentials']);
                    endif; 
                    ?>
                </div>