<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/includes/hrms_paths.php';

if (!isset($_GET['id'])) {
    die("Employee ID not provided.");
}

$id = $_GET['id'];
$query = "SELECT * FROM employees WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employee = mysqli_fetch_assoc($result);

if (!$employee) {
    die("Employee not found.");
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Employee Profile</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <!-- Existing head content -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #2E8B57, #20B2AA);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
        }

        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
        }

        .edit-pic-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #2E8B57;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .card-stats {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card-stats:hover {
            transform: translateY(-5px);
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            padding: 15px 25px;
            margin: 5px;
            transition: all 0.3s;
        }

        .nav-pills .nav-link.active {
            background: #2E8B57;
            color: white;
            box-shadow: 0 4px 10px rgba(46, 139, 87, 0.3);
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .floating-save {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .document-preview {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            padding: 15px;
            border-left: 2px solid #2E8B57;
            margin-left: 20px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 20px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #2E8B57;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="profile-pic-container">

                        <?php $profileImgUrl = hrms_employee_upload_url($employee['profile_pic'] ?? null); ?>
                        <img src="<?= htmlspecialchars($profileImgUrl) ?>" class="profile-pic"
                            id="profilePic"
                            data-original-src="<?= htmlspecialchars($profileImgUrl) ?>"
                            alt="Profile Picture">
                        <div class="edit-pic-overlay" onclick="$('#profilePicInput').click();">
                            <i class="fas fa-camera"></i>
                        </div>
                        <input type="file" id="profilePicInput" name="profile_pic" hidden accept="image/*">
                    </div>
                </div>
                <div class="col">
                    <h2><?php echo htmlspecialchars($employee['first_name'] ?? '') . ' ' . htmlspecialchars($employee['last_name'] ?? ''); ?>
                    </h2>
                    <p class="mb-1"><?php echo htmlspecialchars($employee['designation'] ?? ''); ?></p>
                    <span class="status-badge bg-<?php echo $employee['status'] == 'Active' ? 'success' : 'danger'; ?>">
                        <?php echo $employee['status'] ?? ''; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Quick Stats Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card-stats">
                    <h6>Visa Expiry</h6>
                    <p class="h4">
                        <?php
                        if (!empty($employee['visa_expiry_date'])) {
                            echo date('d M Y', strtotime($employee['visa_expiry_date']));
                        } else {
                            echo 'Not Set';
                        }
                        ?>
                    </p>
                    <small class="text-muted">Days remaining:
                        <?php
                        if (!empty($employee['visa_expiry_date'])) {
                            echo ceil((strtotime($employee['visa_expiry_date']) - time()) / 86400);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </small>
                </div>
            </div>
            <!-- Add more stat cards -->
        </div>

        <form id="employeeForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <a class="nav-link active" data-toggle="pill" href="#personal" role="tab">
                            <i class="fas fa-user mr-2"></i> Personal Information
                        </a>
                        <a class="nav-link" data-toggle="pill" href="#employment" role="tab">
                            <i class="fas fa-briefcase mr-2"></i> Employment Details
                        </a>
                        <a class="nav-link" data-toggle="pill" href="#documents" role="tab">
                            <i class="fas fa-file-alt mr-2"></i> Documents & Visas
                        </a>
                        <a class="nav-link" data-toggle="pill" href="#education" role="tab">
                            <i class="fas fa-graduation-cap mr-2"></i> Education
                        </a>
                        <a class="nav-link" data-toggle="pill" href="#bank" role="tab">
                            <i class="fas fa-university mr-2"></i> Bank Details
                        </a>
                    </div>
                </div>

                <!-- Replace the tab-content section with these complete tabs -->
                <div class="col-md-9">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="personal">
                            <div class="form-section">
                                <h5 class="mb-4">Personal Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" class="form-control" name="first_name"
                                                value="<?php echo htmlspecialchars($employee['first_name'] ?? ''); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" class="form-control" name="last_name"
                                                value="<?php echo htmlspecialchars($employee['last_name'] ?? ''); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="tel" class="form-control" name="contact"
                                                value="<?php echo htmlspecialchars($employee['contact'] ?? ''); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Birth</label>
                                            <input type="date" class="form-control" name="birthday"
                                                value="<?php echo $employee['birthday']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <select class="form-control" name="gender">
                                                <option value="Male" <?php echo $employee['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo $employee['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Blood Group</label>
                                            <select class="form-control" name="blood_group">
                                                <?php
                                                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                                                foreach ($blood_groups as $bg) {
                                                    echo "<option value='$bg'" . ($employee['blood_group'] == $bg ? ' selected' : '') . ">$bg</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Marital Status</label>
                                            <select class="form-control" name="maritalsts">
                                                <option value="Single" <?php echo $employee['maritalsts'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                                <option value="Married" <?php echo $employee['maritalsts'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" name="address"
                                                rows="3"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="employment">
                            <div class="form-section">
                                <h5 class="mb-4">Employment Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Employee ID</label>
                                            <input type="text" class="form-control"
                                                value="<?php echo htmlspecialchars($employee['eid'] ?? ''); ?>"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Department</label>
                                            <select class="form-control" name="department_id">
                                                <option value="">Select Department</option>
                                                <?php
                                                $dept_query = "SELECT id, name FROM departments ORDER BY name";
                                                $dept_result = mysqli_query($con, $dept_query);
                                                while ($dept = mysqli_fetch_assoc($dept_result)) {
                                                    echo "<option value='" . $dept['id'] . "'" .
                                                        ($employee['department_id'] == $dept['id'] ? ' selected' : '') . ">" .
                                                        htmlspecialchars($dept['name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">

                                            <label>Designation</label>
                                            <select class="form-control" name="designation">
                                                <option value="">Select Designation</option>
                                                <?php
                                                $designations = array(
                                                    'Managing Director',
                                                    'Sales Director',
                                                    'Sales Team Leader',
                                                    'Asst Sales Manager',
                                                    'Digital Sales',
                                                    'Operations Manager',
                                                    'Asst. Operation',
                                                    'Relationship officer',
                                                    'HR Manager',
                                                    'Office Asst'
                                                );
                                                foreach ($designations as $designation) {
                                                    echo "<option value='" . htmlspecialchars($designation) . "'" .
                                                        ($employee['designation'] == $designation ? ' selected' : '') . ">" .
                                                        htmlspecialchars($designation) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Joining</label>
                                            <input type="date" class="form-control" name="doj"
                                                value="<?php echo $employee['doj']; ?>">
                                        </div>
                                    </div>
                                    <!-- Add Reporting Manager field -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Reporting Manager <br><span style="color: purple">(change only if
                                                    Employee Department or Manager is Changed)</span></label>
                                            <select class="form-control" name="reporting_manager">
                                                <option value="">Select Reporting Manager</option>
                                                <?php
                                                $manager_query = "SELECT id, first_name, last_name FROM employees WHERE designation IN ('Managing Director', 'Sales Director', 'Sales Team Leader', 'Operations Manager', 'HR Manager') ORDER BY first_name";
                                                $manager_result = mysqli_query($con, $manager_query);
                                                while ($manager = mysqli_fetch_assoc($manager_result)) {
                                                    echo "<option value='" . $manager['id'] . "'" .
                                                        ($employee['reporting_manager'] == $manager['id'] ? ' selected' : '') . ">" .
                                                        htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="documents">
                            <div class="form-section">
                                <h5 class="mb-4">Labor Card Details</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Labor Card Number</label>
                                            <input type="text" class="form-control" name="labour_card_no"
                                                value="<?php echo htmlspecialchars($employee['labour_card_no'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" class="form-control" name="labour_card_start_date"
                                                value="<?php echo $employee['labour_card_start_date']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" name="labour_card_end_date"
                                                value="<?php echo $employee['labour_card_end_date']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-4 mt-4">Visa Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visa Number</label>
                                            <input type="text" class="form-control" name="visa_number"
                                                value="<?php echo htmlspecialchars($employee['visa_number'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visa Type</label>
                                            <select class="form-control" name="visa_type">
                                                <option value="">Select Visa Type</option>
                                                <option value="Employer Sponsored Visa" <?php echo ($employee['visa_type'] == 'Employer Sponsored Visa') ? 'selected' : ''; ?>>Employer Sponsored Visa</option>
                                                <option value="Family Visa" <?php echo ($employee['visa_type'] == 'Family Visa') ? 'selected' : ''; ?>>Family Visa</option>
                                                <option value="Spouse Visa" <?php echo ($employee['visa_type'] == 'Spouse Visa') ? 'selected' : ''; ?>>Spouse Visa</option>
                                                <option value="Freelance Visa" <?php echo ($employee['visa_type'] == 'Freelance Visa') ? 'selected' : ''; ?>>
                                                    Freelance Visa</option>
                                                <option value="Emp Visa Change Status" <?php echo ($employee['visa_type'] == 'Emp Visa Change Status') ? 'selected' : ''; ?>>Emp Change</option>
                                                <option value="Employement Visa" <?php echo ($employee['visa_type'] == 'Employement Visa') ? 'selected' : ''; ?>>
                                                    Employement Visa</option>
                                                <option value="Entry Permit Visa" <?php echo ($employee['visa_type'] == 'Entry Permit Visa') ? 'selected' : ''; ?>>
                                                    Entry Permit Visa</option>
                                                <option value="Job Search Visa" <?php echo ($employee['visa_type'] == 'Job Search Visa') ? 'selected' : ''; ?>>Job Search Visa</option>
                                                <option value="Tourist" <?php echo ($employee['visa_type'] == 'Tourist') ? 'selected' : ''; ?>>Tourist</option>
                                                <option value="Business" <?php echo ($employee['visa_type'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                                                <option value="Work" <?php echo ($employee['visa_type'] == 'Work') ? 'selected' : ''; ?>>Work</option>
                                                <option value="Student" <?php echo ($employee['visa_type'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                                                <option value="Other" <?php echo ($employee['visa_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visa Issue Date</label>
                                            <input type="date" class="form-control" name="visa_issue_date"
                                                value="<?php echo $employee['visa_issue_date']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visa Expiry Date</label>
                                            <input type="date" class="form-control" name="visa_expiry_date"
                                                value="<?php echo $employee['visa_expiry_date']; ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label>Visa Document</label>
                                            <input type="file" class="form-control doc-upload" name="visa_doc"
                                                accept=".pdf,image/*">
                                            <div class="document-preview">
                                                <?php if (!empty($employee['visa_doc'])): ?>
                                                    <div class="text-center">
                                                        <?php if (pathinfo($employee['visa_doc'], PATHINFO_EXTENSION) == 'pdf'): ?>
                                                            <i class="far fa-file-pdf fa-3x text-danger"></i>
                                                        <?php else: ?>
                                                            <img src="<?php echo 'uploads/documents/' . basename($employee['visa_doc']); ?>"
                                                                class="img-fluid rounded" style="max-height: 200px">
                                                        <?php endif; ?>
                                                        <p class="mt-2"><?php echo basename($employee['visa_doc']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>




                                <h5 class="mb-4 mt-4">Passport Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Passport Number</label>
                                            <input type="text" class="form-control" name="passport_number"
                                                value="<?php echo htmlspecialchars($employee['passport_number'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Passport Type</label>
                                            <select class="form-control" name="passport_type">
                                                <?php
                                                $passport_types = ['Regular', 'Official', 'Diplomatic', 'Emergency'];
                                                foreach ($passport_types as $type) {
                                                    echo "<option value='$type'" . ($employee['passport_type'] == $type ? ' selected' : '') . ">$type</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Issue Date</label>
                                            <input type="date" class="form-control" name="passport_issue_date"
                                                value="<?php echo $employee['passport_issue_date']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Expiry Date</label>
                                            <input type="date" class="form-control" name="passport_expiry_date"
                                                value="<?php echo $employee['passport_expiry_date']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Country of Issue</label>
                                            <input type="text" class="form-control" name="country_of_issue"
                                                value="<?php echo htmlspecialchars($employee['country_of_issue'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Place of Issue</label>
                                            <input type="text" class="form-control" name="passport_issue_place"
                                                value="<?php echo htmlspecialchars($employee['passport_issue_place'] ?? ''); ?>">
                                        </div>
                                    </div>


                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label>Passport Document</label>
                                            <input type="file" class="form-control doc-upload" name="passport_doc"
                                                accept=".pdf,image/*">
                                            <div class="document-preview">
                                                <?php if (!empty($employee['passport_doc'])): ?>
                                                    <div class="text-center">
                                                        <?php if (pathinfo($employee['passport_doc'], PATHINFO_EXTENSION) == 'pdf'): ?>
                                                            <i class="far fa-file-pdf fa-3x text-danger"></i>
                                                        <?php else: ?>
                                                            <img src="<?php echo 'uploads/documents/' . basename($employee['passport_doc']); ?>"
                                                                class="img-fluid rounded" style="max-height: 200px">
                                                        <?php endif; ?>
                                                        <p class="mt-2"><?php echo basename($employee['passport_doc']); ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </div>





                                </div>
                            </div>





                        </div>

                        <div class="tab-pane fade" id="education">
                            <div class="form-section">
                                <h5 class="mb-4">Education Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Degree</label>
                                            <input type="text" class="form-control" name="degree"
                                                value="<?php echo htmlspecialchars($employee['degree'] ?? ''); ?>">

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Institute</label>
                                            <input type="text" class="form-control" name="Institute"
                                                value="<?php echo htmlspecialchars($employee['Institute'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" class="form-control" name="start_from"
                                                value="<?php echo $employee['start_from'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" class="form-control" name="end_to"
                                                value="<?php echo $employee['end_to'] ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="bank">
                            <div class="form-section">
                                <h5 class="mb-4">Bank Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bank Name</label>
                                            <input type="text" class="form-control" name="bank_name"
                                                value="<?php echo htmlspecialchars($employee['bank_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Account Number</label>
                                            <input type="text" class="form-control" name="account_no"
                                                value="<?php echo htmlspecialchars($employee['account_no'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>IBAN</label>
                                            <input type="text" class="form-control" name="iban"
                                                value="<?php echo htmlspecialchars($employee['iban'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nominee</label>
                                            <input type="text" class="form-control" name="nominee"
                                                value="<?php echo htmlspecialchars($employee['nominee'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <button type="submit" class="btn btn-success btn-lg floating-save"
            onclick="$('#employeeForm').submit(); return false;">
            <i class="fas fa-save mr-2"></i> Save Changes
        </button>
    </div>


    <?php
    include_once('footer.php');
    ?>
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
                    <a class="btn btn-success" href="../login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Existing scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2();

            // Single form submission handler
            $('#employeeForm').on('submit', function (e) {
                e.preventDefault();

                // Show loading state
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Prepare form data
                let formData = new FormData(this);

                // AJAX submission
                $.ajax({
                    url: 'update_employee.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json', // Explicitly specify JSON dataType
                    success: function (response) {
                        try {
                            let data = response;
                            // Parse if string
                            if (typeof response === 'string') {
                                try {
                                    data = JSON.parse(response);
                                } catch (e) {
                                    throw new Error('Invalid server response');
                                }
                            }

                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message || 'Changes saved successfully',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'Update failed');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: error.message || 'Failed to update employee. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', error);
                        let errorMessage = 'Failed to save changes. Please try again.';

                        // Try to get more specific error message
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // Use default error message
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });



            //profile pic

            $('#profilePicInput').change(function () {
                const file = this.files[0];
                const originalSrc = $('#profilePic').data('original-src');

                if (!file) {
                    $(this).val('');
                    return;
                }

                Swal.fire({
                    title: 'Update Profile Picture?',
                    text: 'This will replace your current profile photo',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2E8B57',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reader = new FileReader();

                        Swal.fire({
                            title: 'Uploading...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading()
                        });

                        reader.onload = (e) => {
                            $('#profilePic').attr('src', e.target.result);

                            const formData = new FormData();
                            formData.append('profile_pic', file);
                            formData.append('id', <?php echo $id; ?>);

                            $.ajax({
                                url: 'update_profile_pic.php',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: (response) => {
                                    Swal.close();
                                    try {
                                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                                        if (data.status === 'success') {
                                            const newSrc = `uploads/profile_pics/${data.new_path}?t=${new Date().getTime()}`;
                                            $('#profilePic').attr('src', newSrc).data('original-src', newSrc);
                                            Swal.fire('Updated!', 'Profile picture saved successfully', 'success');
                                        } else {
                                            throw new Error(data.message || 'Upload failed');
                                        }
                                    } catch (e) {
                                        $('#profilePic').attr('src', originalSrc);
                                        Swal.fire('Error!', e.message, 'error');
                                    }
                                },
                                error: () => {
                                    Swal.close();
                                    $('#profilePic').attr('src', originalSrc);
                                    Swal.fire('Error!', 'Failed to connect to server', 'error');
                                }
                            });
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $(this).val(''); // Reset file input
                        $('#profilePic').attr('src', originalSrc);
                    }
                });
            });





        });




    </script>

</body>

</html>