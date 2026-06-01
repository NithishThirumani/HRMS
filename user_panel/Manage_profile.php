<?php
include('session.php');

$eid = $_SESSION['eid'] ?? '';
if ($eid === '') {
    header('Location: /emps/login.php');
    exit();
}

$query = "SELECT e.*, d.name AS department_name
          FROM employees e
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE e.eid = ?
          LIMIT 1";
$stmt = $con->prepare($query);
$stmt->bind_param('s', $eid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

function profile_pic_url(?string $pic): string
{
    if (empty($pic)) {
        return 'img/undraw_profile.svg';
    }
    if (preg_match('#^https?://#i', $pic)) {
        return $pic;
    }
    $pic = ltrim(str_replace('\\', '/', $pic), '/');
    if (strpos($pic, 'admin_panel/') === 0) {
        return '/emps/' . $pic;
    }
    if (strpos($pic, 'uploads/') === 0) {
        return '/emps/admin_panel/' . $pic;
    }
    return '/emps/admin_panel/uploads/profile_pics/' . basename($pic);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Profile</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .profile-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 1000px;
        }
        .nav-tabs .nav-link.active {
            background-color: #4e73df;
            color: #fff;
            border-color: #4e73df;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .profile-table th {
            background-color: #4e73df;
            color: white;
            width: 20%;
            font-size: 14px;
            font-weight: 500;
        }
        .profile-table td {
            width: 30%;
            font-size: 14px;
        }
        .profile-table td, .profile-table th {
            padding: 10px;
            border: 1px solid #e3e6f0;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <?php if ($row): ?>
            <div class="profile-container">
                <div class="profile-header text-center mb-4">
                    <div class="profile-picture mb-3">
                        <img class="rounded-circle" src="<?php echo htmlspecialchars(profile_pic_url($row['profile_pic'] ?? '')); ?>"
                             alt="Profile Picture" onerror="this.src='img/undraw_profile.svg'"/>
                    </div>
                    <h2 class="h3 text-gray-800"><?php echo htmlspecialchars($row['full_name']); ?>'s Profile</h2>
                </div>

                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#personal" role="tab">Personal Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#education" role="tab">Education</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#employment" role="tab">Employment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#documents" role="tab">Documents</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="personal">
                        <table class="profile-table">
                            <tr>
                                <th>Employee ID</th>
                                <td><?php echo htmlspecialchars($row['eid']); ?></td>
                                <th>Full Name</th>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <th>Contact</th>
                                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <th>Date of Birth</th>
                                <td><?php echo htmlspecialchars($row['birthday']); ?></td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td colspan="3"><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="education">
                        <table class="profile-table">
                            <tr>
                                <th>Degree</th>
                                <td><?php echo htmlspecialchars($row['degree'] ?? 'Not Available'); ?></td>
                                <th>Institution</th>
                                <td><?php echo htmlspecialchars($row['Institute'] ?? 'Not Available'); ?></td>
                            </tr>
                            <tr>
                                <th>From</th>
                                <td><?php echo htmlspecialchars($row['start_from'] ?? 'Not Available'); ?></td>
                                <th>To</th>
                                <td><?php echo htmlspecialchars($row['end_to'] ?? 'Not Available'); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="employment">
                        <table class="profile-table">
                            <tr>
                                <th>Department</th>
                                <td><?php echo htmlspecialchars($row['department_name'] ?? 'Not Available'); ?></td>
                                <th>Designation</th>
                                <td><?php echo htmlspecialchars($row['designation'] ?? 'Not Available'); ?></td>
                            </tr>
                            <tr>
                                <th>Date of Joining</th>
                                <td><?php echo htmlspecialchars($row['doj'] ?? 'Not Available'); ?></td>
                                <th>Status</th>
                                <td><?php echo htmlspecialchars($row['status'] ?? 'Not Available'); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="documents">
                        <table class="profile-table">
                            <tr>
                                <th>Passport No.</th>
                                <td><?php echo htmlspecialchars($row['passport_number'] ?? 'Not Available'); ?></td>
                                <th>Visa No.</th>
                                <td><?php echo htmlspecialchars($row['visa_number'] ?? 'Not Available'); ?></td>
                            </tr>
                            <tr>
                                <th>Labour Card</th>
                                <td colspan="3"><?php echo htmlspecialchars($row['labour_card_no'] ?? 'Not Available'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="profile.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-success">
                        <i class="fas fa-edit mr-2"></i>Update Profile
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger m-4">Profile not found for your account. Please contact HR.</div>
        <?php endif; ?>
    </div>

    <?php include('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
