<?php
// NOTE: The trainees table should be created in your database using a migration or SQL script, not in this file.
// Remove any CREATE TABLE statements from here for best practices.
include('connection.php');
include('session.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Fetch departments for dropdown
$departments_query = "SELECT id, name FROM departments";
$departments_result = mysqli_query($con, $departments_query);
$departments = [];
while ($row = mysqli_fetch_assoc($departments_result)) {
    $departments[] = $row;
}

// Fetch department heads for reporting manager dropdown
$heads_query = "SELECT id, head_name FROM department_heads";
$heads_result = mysqli_query($con, $heads_query);
$heads = [];
while ($row = mysqli_fetch_assoc($heads_result)) {
    $heads[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Basic Information
    $first_name = mysqli_real_escape_string($con, $_POST['fn']);
    $last_name = mysqli_real_escape_string($con, $_POST['ln']);
    $full_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_POST['em']);
    
    // Personal Details
    $birthday = mysqli_real_escape_string($con, $_POST['birthday']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $marital_status = mysqli_real_escape_string($con, $_POST['maritalsts']);
    $blood_group = mysqli_real_escape_string($con, $_POST['blood_group']);
    $phone = mysqli_real_escape_string($con, $_POST['pn']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $country = mysqli_real_escape_string($con, $_POST['country']);

    // Education Details
    $degree = mysqli_real_escape_string($con, $_POST['degree']);
    $education_start = !empty($_POST['start_from']) ? mysqli_real_escape_string($con, $_POST['start_from']) : NULL;
    $education_end = !empty($_POST['end_to']) ? mysqli_real_escape_string($con, $_POST['end_to']) : NULL;
    $institute = mysqli_real_escape_string($con, $_POST['Institute'] ?? '');

    // Employment Details
    $status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'active';
    if (empty($_POST['doj'])) {
        echo "<script>alert('Date of Joining is required');</script>";
        echo "<script>window.location.replace('$_SERVER[PHP_SELF]');</script>";
        exit();
    }
    $date_of_joining = mysqli_real_escape_string($con, $_POST['doj']);
    $location = mysqli_real_escape_string($con, $_POST['EmpLoc']);
    $department_id = mysqli_real_escape_string($con, $_POST['department']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']);
    $reporting_manager = mysqli_real_escape_string($con, $_POST['reporting_manager']);
    $training_duration = mysqli_real_escape_string($con, $_POST['training_duration']);

    // Visa Details
    $visa_number = !empty($_POST['visa_number']) ? mysqli_real_escape_string($con, $_POST['visa_number']) : NULL;
    $visa_type = !empty($_POST['visa_type']) ? mysqli_real_escape_string($con, $_POST['visa_type']) : NULL;
    $visa_issue_date = !empty($_POST['visa_issue_date']) ? mysqli_real_escape_string($con, $_POST['visa_issue_date']) : NULL;
    $visa_expiry_date = !empty($_POST['visa_expiry_date']) ? mysqli_real_escape_string($con, $_POST['visa_expiry_date']) : NULL;

    // Generate Trainee ID
    $sql_tid = "SELECT MAX(CAST(SUBSTRING(trainee_id, 4) AS UNSIGNED)) AS max_tid FROM trainees WHERE trainee_id IS NOT NULL";
    $result_tid = mysqli_query($con, $sql_tid);
    $row_tid = mysqli_fetch_assoc($result_tid);
    $max_tid = $row_tid['max_tid'] ?? 0;
    $new_tid = 'TRN' . str_pad(($max_tid + 1), 3, "0", STR_PAD_LEFT);

    // Prepare integer variables for bind_param
    $department_id_int = (int)$department_id;
    $reporting_manager_int = (int)$reporting_manager;

    // Insert into trainees table
    $sql_insert_trainee = "INSERT INTO trainees (
        trainee_id, first_name, last_name, full_name, email, birthday, gender, marital_status, blood_group, phone, address, country, degree, education_start, education_end, institute, date_of_joining, location, department_id, designation, reporting_manager, training_duration, visa_number, visa_type, visa_issue_date, visa_expiry_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $con->prepare($sql_insert_trainee)) {
        $stmt->bind_param(
            "sssssssssssssssssssissssss",
            $new_tid, $first_name, $last_name, $full_name, $email, $birthday, $gender, $marital_status, $blood_group, $phone, $address, $country, $degree, $education_start, $education_end, $institute, $date_of_joining, $location, $department_id_int, $designation, $reporting_manager_int, $training_duration, $visa_number, $visa_type, $visa_issue_date, $visa_expiry_date
        );
        if ($stmt->execute()) {
            echo "<script>alert('Trainee added successfully!');</script>";
            echo "<script>window.location.replace('view_trainees.php');</script>";
        } else {
            echo "<script>alert('Error adding trainee: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $con->error . "');</script>";
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
                                <div class="row">
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
                                <div class="row mt-4">
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
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Phone*</label>
                                            <input type="text" name="pn" class="form-control" required>
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
                                <div class="row mt-4">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Degree/Course*</label>
                                            <input type="text" name="degree" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Education Start</label>
                                            <input type="date" name="start_from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Education End</label>
                                            <input type="date" name="end_to" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Institute</label>
                                            <input type="text" name="Institute" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
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
                                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
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
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Reporting Manager*</label>
                                            <select name="reporting_manager" class="form-control" required>
                                                <option value="">Select Reporting Manager</option>
                                                <?php foreach ($heads as $head): ?>
                                                    <option value="<?php echo $head['id']; ?>"><?php echo htmlspecialchars($head['head_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Training Duration</label>
                                            <input type="text" name="training_duration" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Visa Number</label>
                                            <input type="text" name="visa_number" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12 text-center">
                                        <button type="submit" name="register" class="btn btn-primary">Add Trainee</button>
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