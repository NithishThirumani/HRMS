<?php
include('session.php');
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

if (isset($_POST['submit'])) {
    $emp_id = $_SESSION['user_id'];
    $des = mysqli_real_escape_string($con, $_POST['des']);
    $tl = mysqli_real_escape_string($con, $_POST['tl']);
    $sd = $_POST['sd'];
    $ed = $_POST['ed'];
    // Check for leave overlap
    $overlap_check = $con->prepare("SELECT * FROM leaves WHERE emp_id = ? 
AND ((start_date BETWEEN ? AND ?) 
OR (end_date BETWEEN ? AND ?)
OR (start_date <= ? AND end_date >= ?))
AND status != 'Rejected'");
    $overlap_check->bind_param("issssss", $emp_id, $sd, $ed, $sd, $ed, $sd, $ed);
    $overlap_check->execute();
    $overlap_result = $overlap_check->get_result();

    if ($overlap_result->num_rows > 0) {
        echo "<script>alert('You already have an approved or pending leave during this period!'); window.history.back();</script>";
        exit();
    }


    $status = "pending HOD Approval";
    $start_date = new DateTime($sd);
    $end_date = new DateTime($ed);
    $interval = $start_date->diff($end_date);
    $td = $interval->days + 1;
    // Handle file upload for medical certificate
    $doctor_cert = '';
    if (isset($_FILES['doctor_cert']) && $_FILES['doctor_cert']['size'] > 0) {
        $target_dir = "uploads/certificates/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["doctor_cert"]["name"], PATHINFO_EXTENSION);
        $new_filename = $emp_id . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["doctor_cert"]["tmp_name"], $target_file)) {
            $doctor_cert = $new_filename;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            exit();
        }
    }
    if ($td <= 0) {
        echo "<script>alert('End date must be after start date. Please select valid dates.'); window.history.back();</script>";
        exit();
    }

    $hierarchy_query = "SELECT 
    e.id, e.full_name, e.role, e.designation, e.reporting_manager, 
    e.department_id, d.name as department_name,
    rm.id as manager_id, rm.full_name as manager_name, rm.role as manager_role,
    rm.reporting_manager as next_level_manager
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN employees rm ON e.reporting_manager = rm.id
    WHERE e.id = ?";
    // Fetch employee details
    $stmt = $con->prepare($hierarchy_query);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    // Determine approval chain
    $approval_chain = [];
    $current_manager = $employee['reporting_manager'];

    while ($current_manager) {
        $manager_query = "SELECT id, full_name, role, reporting_manager 
                       FROM employees WHERE id = ?";
        $stmt = $con->prepare($manager_query);
        $stmt->bind_param("i", $current_manager);
        $stmt->execute();
        $manager_result = $stmt->get_result();
        $manager = $manager_result->fetch_assoc();

        if ($manager) {
            $approval_chain[] = [
                'id' => $manager['id'],
                'name' => $manager['full_name'],
                'role' => $manager['role']
            ];
            $current_manager = $manager['reporting_manager'];
        } else {
            break;
        }
    }
    // Determine initial approver
    $initial_approver = null;
    foreach ($approval_chain as $approver) {
        if (in_array($approver['role'], ['Team Lead', 'Manager', 'HOD'])) {
            $initial_approver = $approver;
            break;
        }
    }

    if (!$initial_approver) {
        // If no direct approver found, route to HR
        $hr_query = "SELECT id, full_name FROM employees WHERE role = 'HR' LIMIT 1";
        $hr_result = mysqli_query($con, $hr_query);
        if ($hr_result && mysqli_num_rows($hr_result) > 0) {
            $hr = mysqli_fetch_assoc($hr_result);
            $initial_approver = [
                'id' => $hr['id'],
                'name' => $hr['full_name'],
                'role' => 'HR'
            ];
        }
    }
    if ($initial_approver) {
        // Determine the initial status based on the reporting hierarchy
        $status = '';
        if ($initial_approver['role'] == 'Team Lead') {
            $status = "Pending Team Lead Approval";
        } elseif ($initial_approver['role'] == 'Manager') {
            $status = "Pending Manager Approval";
        } elseif ($initial_approver['role'] == 'HOD') {
            $status = "Pending HOD Approval";
        } else {
            $status = "Pending HR Approval";
        }

        // Insert leave request with complete approval chain information
        $stmt = $con->prepare("INSERT INTO leaves (
            emp_id, user_name, reason, type_of_leave, 
            start_date, end_date, total_days, 
            status, hod_id, hod_name, 
            doctor_cert, applied_at,
            hod_status, hr_status, admin_status,
            tl_status, manager_status
        ) VALUES (
            ?, ?, ?, ?, 
            ?, ?, ?, 
            ?, ?, ?, 
            ?, NOW(),
            'Pending', 'Pending', 'Pending',
            'Pending', 'Pending'
        )");

        $stmt->bind_param(
            "isssssissss",
            $emp_id,
            $employee['full_name'],
            $des,
            $tl,
            $sd,
            $ed,
            $td,
            $status,
            $initial_approver['id'],
            $initial_approver['name'],
            $doctor_cert
        );

        if ($stmt->execute()) {
            $leave_id = $stmt->insert_id;
            echo "<script>alert('Leave request submitted successfully!');</script>";
            echo "<script>window.location.href='leaves.php';</script>";
        } else {
            echo "<script>alert('Error submitting leave request. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No approver found in the system. Please contact HR.');</script>";
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
    <title>Request for Leave</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <link href="vendor/select2/select2.min.css" rel="stylesheet">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">


</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Leave Request</h1>
        </div>
        <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 rounded-lg">
                        <div class="card-header bg-gradient-primary py-3">
                            <h4 class="text-white font-weight-light mb-0">Apply For Leave</h4>
                        </div>

                        <?php
                        $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();

                        // Get employee details including gender and join date
                        $emp_id = $_SESSION['user_id'];
                        $query = "SELECT e.*, d.name as department_name, DATEDIFF(CURRENT_DATE, doj)/30 as months_employed, gender 
                                  FROM employees e
                                  LEFT JOIN departments d ON e.department_id = d.id
                                  WHERE e.id = '$emp_id'";
                        $result = mysqli_query($con, $query);
                        $employee = mysqli_fetch_assoc($result);

                        // Get current approver information
                        $approver_name = 'N/A';
                        $approver_type = 'N/A';

                        // Check HOD first
                        $hod_check = $con->prepare("SELECT full_name FROM employees WHERE department_id = ? AND role = 'HOD' LIMIT 1");
                        $hod_check->bind_param("i", $employee['department_id']);
                        $hod_check->execute();
                        $hod_result = $hod_check->get_result();

                        if ($hod_result->num_rows > 0) {
                            $hod_row = $hod_result->fetch_assoc();
                            $approver_name = $hod_row['full_name'];
                            $approver_type = 'HOD';
                        } else if (!empty($employee['reporting_manager'])) {
                            // Fallback to reporting manager
                            $rm_check = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
                            $rm_check->bind_param("i", $employee['reporting_manager']);
                            $rm_check->execute();
                            $rm_result = $rm_check->get_result();

                            if ($rm_result->num_rows > 0) {
                                $rm_row = $rm_result->fetch_assoc();
                                $approver_name = $rm_row['full_name'];
                                $approver_type = 'Reporting Manager';
                            }
                        }

                        // Debug employee details
                        echo "<!-- Employee details: " . print_r($employee, true) . " -->";

                        // Set default months_employed if it's NULL
                        $months_employed = isset($employee['months_employed']) ? $employee['months_employed'] : 0;
                        $gender = isset($employee['gender']) ? $employee['gender'] : 'all';

                        // Debug values
                        echo "<!-- Months employed: $months_employed, Gender: $gender -->";

                        // Get available leave types based on eligibility
                        $leave_types_query = "SELECT lp.*, 
    COALESCE(elb.balance, lp.max_days) as current_balance
    FROM leave_policies lp
    LEFT JOIN employee_leave_balance elb ON 
        elb.emp_id = '$emp_id' AND 
        elb.leave_type = lp.leave_type AND 
        elb.year = YEAR(CURRENT_DATE)
    WHERE 
       lp.min_service_months <= $months_employed
        AND (lp.gender_restriction = 'all' 
             OR lp.gender_restriction = '$gender')";

                        $leave_types = mysqli_query($con, $leave_types_query);
                        ?>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Employee Name</label>
                                        <input class="form-control form-control-solid py-2" type="text" name="nm"
                                            value="<?php echo ucwords(strtolower($row['full_name'])); ?>" readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Department</label>
                                        <input class="form-control form-control-solid py-2" type="text"
                                            value="<?php echo htmlspecialchars($employee['department_name']); ?>"
                                            readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Approver</label>
                                        <input class="form-control form-control-solid py-2" type="text"
                                            value="<?php echo htmlspecialchars("$approver_type: $approver_name"); ?>"
                                            readonly />
                                    </div>

                                    <div class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Type of Leave</label>
                                        <select class="form-control form-control-solid py-2" id="tl" name="tl" required>
                                            <option value="">Select Leave Type</option>
                                            <?php

                                            echo "<!-- Query: $leave_types_query -->";
                                            // Reset the result pointer
                                            if ($leave_types && mysqli_num_rows($leave_types) > 0) {
                                                mysqli_data_seek($leave_types, 0);
                                                while ($type = mysqli_fetch_assoc($leave_types)) {
                                                    // Debug the leave type
                                                    echo "<!-- Leave type: " . print_r($type, true) . " -->"; ?>
                                                    <option value="<?php echo $type['leave_type']; ?>"
                                                        data-requires-cert="<?php echo $type['requires_certificate']; ?>"
                                                        data-balance="<?php echo $type['current_balance']; ?>"
                                                        data-max-days="<?php echo $type['max_days']; ?>"
                                                        data-is-paid="<?php echo $type['is_paid']; ?>">
                                                        <?php echo $type['leave_type']; ?>
                                                        (Balance: <?php echo $type['current_balance']; ?> days)
                                                    </option>
                                                <?php }
                                            } else {
                                                echo "<!-- No leave types found or query error: " . mysqli_error($con) . " -->";
                                            } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Reason for Leave</label>
                                        <textarea class="form-control form-control-solid py-2" name="des" rows="4"
                                            placeholder="Please provide detailed reason" required></textarea>
                                    </div>

                                </div>
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="date-inputs-container">
                                        <div class="date-input-group">
                                            <label class="small text-dark font-weight-bold mb-2">Start Date</label>
                                            <input class="form-control form-control-solid py-2" type="date" id="sd"
                                                name="sd" min="<?php echo date('Y-m-d'); ?>" required />
                                        </div>

                                        <div class="date-input-group">
                                            <label class="small text-dark font-weight-bold mb-2">End Date</label>
                                            <input class="form-control form-control-solid py-2" type="date" id="ed"
                                                name="ed" required />
                                        </div>
                                    </div>

                                    <div id="certificate_upload" style="display:none;" class="form-group">
                                        <label class="small text-dark font-weight-bold mb-2">Medical
                                            Certificate</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="doctor_cert"
                                                id="doctor_cert">
                                            <label class="custom-file-label" for="doctor_cert">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Accepted formats: PDF, JPG, PNG</small>
                                    </div>
                                </div>


                                <!-- Leave Summary Card -->
                                <div id="leave_summary" style="display: none;" class="mt-4">
                                    <div class="card bg-light border-left-primary">
                                        <div class="card-body">
                                            <h5 class="text-primary font-weight-bold">Leave Summary</h5>
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Days
                                                        Requested</div>
                                                    <div id="days_requested"
                                                        class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                        Available Balance</div>
                                                    <div id="available_balance"
                                                        class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                        Remaining Balance</div>
                                                    <div id="remaining_balance"
                                                        class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                                <!-- Submit Button -->
                                <div class="col-12">
                                    <div class="text-center mt-4">
                                        <button class="btn btn-primary btn-lg px-5 shadow-sm" name="submit"
                                            type="submit" onclick="return validateForm()">
                                            <i class="fas fa-paper-plane mr-2"></i>Submit Request
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>




    <script>
        function validateDates() {
            let startDate = new Date(document.getElementById('sd').value);
            let endDate = new Date(document.getElementById('ed').value);
            if (endDate < startDate) {
                alert('End date must be after start date. Please select valid dates.');
                return false;
            }
            return true;
        }

        function calculateDays() {
            let startDate = new Date(document.getElementById('sd').value);
            let endDate = new Date(document.getElementById('ed').value);

            if (startDate && endDate && endDate >= startDate) {
                // Add 1 to include both start and end days
                return Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            }
            return 0;
        }
        function updateLeaveSummary() {
            if (document.getElementById('sd').value && document.getElementById('ed').value) {
                const days = calculateDays();
                const selectedOption = document.getElementById('tl').options[document.getElementById('tl').selectedIndex];

                if (selectedOption.value) {
                    // Add AJAX call to get current balance
                    $.ajax({
                        url: 'get_leave_balance.php',
                        type: 'POST',
                        data: {
                            emp_id: <?php echo $_SESSION['user_id']; ?>,
                            leave_type: selectedOption.value
                        },
                        success: function (response) {
                            const balance = parseFloat(response);
                            const remaining = balance - days;

                            document.getElementById('days_requested').textContent = days;
                            document.getElementById('available_balance').textContent = balance;
                            document.getElementById('remaining_balance').textContent = remaining >= 0 ? remaining : 'Insufficient';
                            document.getElementById('leave_summary').style.display = 'block';

                            const remainingElement = document.getElementById('remaining_balance');
                            if (remaining < 0) {
                                remainingElement.style.color = 'red';
                            } else if (remaining < 3) {
                                remainingElement.style.color = 'orange';
                            } else {
                                remainingElement.style.color = 'green';
                            }
                        }
                    });
                }
            }
        }

        function validateForm() {
            // Validate dates
            if (!validateDates()) {
                return false;
            }

            // Check if certificate is required but not uploaded
            const selectedOption = document.getElementById('tl').options[document.getElementById('tl').selectedIndex];
            if (!selectedOption.value) {
                alert('Please select a leave type.');
                return false;
            }

            const requiresCert = selectedOption.getAttribute('data-requires-cert') === '1';
            const maxDays = parseFloat(selectedOption.getAttribute('data-max-days'));
            const balance = parseFloat(selectedOption.getAttribute('data-balance'));

            // Calculate selected days
            const days = calculateDays();

            if (requiresCert && document.getElementById('doctor_cert').files.length === 0) {
                alert('Please upload a medical certificate for this type of leave.');
                return false;
            }

            if (days > balance) {
                alert('You do not have enough leave balance. Available: ' + balance + ' days, Requested: ' + days + ' days');
                return false;
            }

            if (days > maxDays) {
                alert('Maximum allowed days for this leave type is ' + maxDays + '. You requested ' + days + ' days.');
                return false;
            }

            return true;
        }

        document.getElementById('tl').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const requiresCert = selectedOption.getAttribute('data-requires-cert') === '1';

            document.getElementById('certificate_upload').style.display =
                requiresCert ? 'block' : 'none';

            updateLeaveSummary();
        });

        document.getElementById('sd').addEventListener('change', function () {
            console.log('Start date changed');
            updateLeaveSummary();
        });

        document.getElementById('ed').addEventListener('change', function () {
            console.log('End date changed');
            updateLeaveSummary();
        });

        // Initialize the summary if dates are already selected
        if (document.getElementById('sd').value && document.getElementById('ed').value) {
            updateLeaveSummary();
        }
    </script>

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
                    <a class="btn btn-success" href="/emps/user_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>


    <?php include_once('footer.php'); ?>

</body>

</html>