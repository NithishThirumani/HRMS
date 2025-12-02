<?php  include('session.php'); 
include('connection.php');

// Fetch the currently logged-in HR user's profile
$un = $_SESSION['user_name'];
$q = "SELECT * FROM employees WHERE user_name='$un'";
$res = mysqli_query($con, $q);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    echo 'No profile data found.';
    exit;
}

$pic = preg_replace('#^uploads/profile_pics/#', '', $user['profile_pic'] ?? '');
$profile_pic_url = $pic ? "../admin_panel/uploads/profile_pics/" . htmlspecialchars($pic) : "https://ui-avatars.com/api/?name=" . urlencode($user['first_name'] ?? 'User');
$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>My Profile</title>

    <!-- Custom fonts for this template-->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <style>
        html, body { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%) !important; min-height: 100vh !important; }
        .profile-card {
            max-width: 500px !important;
            margin: 40px auto !important;
            background: #fff !important;
            border-radius: 30px !important;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15) !important;
            padding: 2.5rem 2rem 2rem 2rem !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            position: relative !important;
            z-index: 9999 !important;
            visibility: visible !important;
        }
        .profile-pic {
            width: 140px !important;
            height: 140px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 6px solid #a259f7 !important;
            margin-bottom: 1.5rem !important;
            box-shadow: 0 4px 16px rgba(162,89,247,0.15) !important;
        }
        .profile-name {
            font-size: 2rem !important;
            font-weight: 700 !important;
            color: #a259f7 !important;
            margin-bottom: 0.5rem !important;
            text-align: center !important;
        }
        .profile-role {
            font-size: 1.1rem !important;
            font-weight: 600 !important;
            color: #fff !important;
            background: linear-gradient(90deg, #a259f7 0%, #f5576c 100%) !important;
            border-radius: 20px !important;
            padding: 0.3rem 1.2rem !important;
            margin-bottom: 1.5rem !important;
            display: inline-block !important;
        }
        .profile-info {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr 2fr !important;
            gap: 0.7rem 1.2rem !important;
            margin-bottom: 1.5rem !important;
        }
        .profile-info-label {
            font-weight: 600 !important;
            color: #a259f7 !important;
            text-align: right !important;
        }
        .profile-info-value {
            color: #222 !important;
            font-weight: 500 !important;
            text-align: left !important;
        }
        .edit-btn {
            background: linear-gradient(90deg, #a259f7 0%, #f5576c 100%) !important;
            color: #fff !important;
            border: none !important;
            border-radius: 30px !important;
            padding: 0.7rem 2.2rem !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(162,89,247,0.15) !important;
            transition: background 0.2s !important;
            margin-top: 0.5rem !important;
        }
        .edit-btn:hover {
            background: linear-gradient(90deg, #f5576c 0%, #a259f7 100%) !important;
        }
        @media (max-width: 600px) {
            .profile-card { padding: 1.2rem 0.5rem !important; }
            .profile-info { grid-template-columns: 1fr !important; }
            .profile-info-label { text-align: left !important; }
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%) !important; min-height: 100vh !important;">
    <?php  include('sidebar.php'); ?>

    <?php  include('header.php'); ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <div class="profile-card" style="display: flex !important; visibility: visible !important; z-index: 9999 !important;">
            <?php if ($success) { echo '<div class="alert alert-success w-100 text-center">Profile updated successfully!</div>'; } ?>
            <img src="<?php echo $profile_pic_url; ?>" class="profile-pic" alt="Profile Picture">
            <div class="profile-name"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></div>
            <div class="profile-role"><?php echo strtoupper(htmlspecialchars($user['role'] ?? '')); ?></div>
            <div class="profile-info">
                <div class="profile-info-label">EID:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['eid'] ?? ''); ?></div>
                <div class="profile-info-label">Email:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                <div class="profile-info-label">Gender:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['gender'] ?? ''); ?></div>
                <div class="profile-info-label">Contact:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['contact'] ?? ''); ?></div>
                <div class="profile-info-label">Designation:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['designation'] ?? ''); ?></div>
                <div class="profile-info-label">Department:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['department_name'] ?? ''); ?></div>
                <div class="profile-info-label">Date of Birth:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['birthday'] ?? ''); ?></div>
                <div class="profile-info-label">Marital Status:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($user['maritalsts'] ?? ''); ?></div>
            </div>
            <a href="edit_profile.php" class="edit-btn"><i class="fas fa-edit"></i> Edit Profile</a>
        </div>

    </div>
    <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <?php
          include_once('footer.php');
          ?>

    </div>
    <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
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
                    <a class="btn btn-success"
                        href="../login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


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

</body>

</html>