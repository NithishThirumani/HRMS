<?php
include('connection.php');
include('session.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Fetch departments for dropdown
$dept_query = "SELECT id, name FROM departments WHERE 1 ORDER BY name ASC";
$dept_result = mysqli_query($mysqli, $dept_query);

if (!$dept_result) {
    die("Error fetching departments: " . mysqli_error($mysqli));
}

$departments = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Basic Information
    $first_name = mysqli_real_escape_string($mysqli, $_POST['fn']);
    $last_name = mysqli_real_escape_string($mysqli, $_POST['ln']);
    $full_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($mysqli, $_POST['em']);
    
    // Personal Details
    $birthday = mysqli_real_escape_string($mysqli, $_POST['birthday']);
    $gender = mysqli_real_escape_string($mysqli, $_POST['gender']);
    $maritalsts = mysqli_real_escape_string($mysqli, $_POST['maritalsts']);
    $blood_group = mysqli_real_escape_string($mysqli, $_POST['blood_group']);
    $contact = mysqli_real_escape_string($mysqli, $_POST['pn']);
    $address = mysqli_real_escape_string($mysqli, $_POST['address']);
    $country = mysqli_real_escape_string($mysqli, $_POST['country']);

    // Education Details
    $degree = mysqli_real_escape_string($mysqli, $_POST['degree']);
    $start_from = !empty($_POST['start_from']) ? mysqli_real_escape_string($mysqli, $_POST['start_from']) : NULL;
    $end_to = !empty($_POST['end_to']) ? mysqli_real_escape_string($mysqli, $_POST['end_to']) : NULL;
    $Institute = mysqli_real_escape_string($mysqli, $_POST['Institute'] ?? '');

    // Employment Details
    $status = 'active';
    if (empty($_POST['doj'])) {
        echo "<script>alert('Date of Joining is required');</script>";
        echo "<script>window.location.replace('$_SERVER[PHP_SELF]');</script>";
        exit();
    }
    $doj = mysqli_real_escape_string($mysqli, $_POST['doj']);
    $EmpLoc = mysqli_real_escape_string($mysqli, $_POST['EmpLoc']);
    $department_id = mysqli_real_escape_string($mysqli, $_POST['department']);
    $designation = mysqli_real_escape_string($mysqli, $_POST['designation']);
    $reporting_manager = mysqli_real_escape_string($mysqli, $_POST['reporting_manager']);
    $training_duration = mysqli_real_escape_string($mysqli, $_POST['training_duration']);

    // Visa Details
    $visa_number = !empty($_POST['visa_number']) ? mysqli_real_escape_string($mysqli, $_POST['visa_number']) : NULL;
    $visa_type = !empty($_POST['visa_type']) ? mysqli_real_escape_string($mysqli, $_POST['visa_type']) : NULL;
    $visa_issue_date = !empty($_POST['visa_issue_date']) ? mysqli_real_escape_string($mysqli, $_POST['visa_issue_date']) : NULL;
    $visa_expiry_date = !empty($_POST['visa_expiry_date']) ? mysqli_real_escape_string($mysqli, $_POST['visa_expiry_date']) : NULL;

    // Generate Trainee ID
    $sql_tid = "SELECT MAX(CAST(SUBSTRING(trainee_id, 4) AS UNSIGNED)) AS max_tid FROM trainees";
    $result_tid = mysqli_query($mysqli, $sql_tid);
    $row_tid = mysqli_fetch_assoc($result_tid);
    $max_tid = $row_tid['max_tid'];
    $new_tid = 'TRN' . str_pad(($max_tid + 1), 3, "0", STR_PAD_LEFT);

    // Insert into trainees table
    $sql_insert_trainee = "INSERT INTO trainees (
        trainee_id, first_name, last_name, full_name, email, 
        birthday, gender, marital_status, blood_group, contact, 
        address, country, degree, education_start, education_end, 
        institute, date_of_joining, location, department_id, 
        designation, reporting_manager, training_duration,
        visa_number, visa_type, visa_issue_date, visa_expiry_date,
        status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    if ($stmt = $mysqli->prepare($sql_insert_trainee)) {
        $stmt->bind_param(
            "ssssssssssssssssssisssssss",
            $new_tid, $first_name, $last_name, $full_name, $email,
            $birthday, $gender, $maritalsts, $blood_group, $contact,
            $address, $country, $degree, $start_from, $end_to,
            $Institute, $doj, $EmpLoc, $department_id,
            $designation, $reporting_manager, $training_duration,
            $visa_number, $visa_type, $visa_issue_date, $visa_expiry_date,
            $status
        );

        if ($stmt->execute()) {
            echo "<script>alert('Trainee added successfully!');</script>";
            echo "<script>window.location.replace('view_trainees.php');</script>";
        } else {
            echo "<script>alert('Error adding trainee: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $mysqli->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add Trainee</title>
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
                    <h1 class="h3 mb-2 text-gray-800">Add New Trainee</h1>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <!-- Basic Information -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="mb-3">Basic Information</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>First Name*</label>
                                            <input type="text" name="fn" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Last Name*</label>
                                            <input type="text" name="ln" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Email*</label>
                                            <input type="email" name="em" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Details -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4 class="mb-3">Personal Details</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date of Birth*</label>
                                            <input type="date" name="birthday" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Gender*</label>
                                            <select name="gender" class="form-control" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Marital Status*</label>
                                            <select name="maritalsts" class="form-control" required>
                                                <option value="">Select Status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Blood Group</label>
                                            <select name="blood_group" class="form-control">
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
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Contact Number*</label>
                                            <input type="tel" name="pn" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Address*</label>
                                            <textarea name="address" class="form-control" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Country*</label>
                                            <input type="text" name="country" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Education Details -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4 class="mb-3">Education Details</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Degree/Course*</label>
                                            <input type="text" name="degree" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" name="start_from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" name="end_to" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Institute*</label>
                                            <input type="text" name="Institute" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Training Details -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4 class="mb-3">Training Details</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date of Joining*</label>
                                            <input type="date" name="doj" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Location*</label>
                                            <input type="text" name="EmpLoc" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Department*</label>
                                            <select name="department" class="form-control" required>
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Designation*</label>
                                            <input type="text" name="designation" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-group">
                                            <label>Training Duration (in months)*</label>
                                            <input type="number" name="training_duration" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-group">
                                            <label>Reporting Manager*</label>
                                            <input type="text" name="reporting_manager" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visa Details -->
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4 class="mb-3">Visa Details</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Visa Number</label>
                                            <input type="text" name="visa_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Visa Type</label>
                                            <input type="text" name="visa_type" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Visa Issue Date</label>
                                            <input type="date" name="visa_issue_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Visa Expiry Date</label>
                                            <input type="date" name="visa_expiry_date" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button type="submit" name="register" class="btn btn-primary">Add Trainee</button>
                                        <a href="view_trainees.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
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