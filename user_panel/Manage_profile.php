<?php include('session.php'); ?>
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
        .two-column-table tr {
            display: flex;
            width: 100%;
        }
        .two-column-table th, .two-column-table td {
            flex: 1;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <?php
        include('connection.php');
        $un = $_SESSION['user_name'];
        $query = "SELECT * FROM employees WHERE user_name = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $un);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result);
        
        if($row) {
        ?>
            <div class="profile-container">
                <div class="profile-header text-center mb-4">
                    <div class="profile-picture mb-3">
                        <img class="rounded-circle" src="../admin_panel/uploads/profile_pics/<?php echo $row['profile_pic']; ?>" alt="Profile Picture" onerror="this.src='img/undraw_profile.svg'"/>
                    </div>
                    <h2 class="h3 text-gray-800"><?php echo $row['4']; ?>'s Profile</h2>
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
                        <table class="profile-table two-column-table">
                            <tr>
                                <th>Employee ID</th>
                                <td><?php echo $row['eid']; ?></td>
                                <th>Full Name</th>
                                <td><?php echo $row['4']; ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo $row['6']; ?></td>
                                <th>Contact</th>
                                <td><?php echo $row['10']; ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?php echo $row['9']; ?></td>
                                <th>Date of Birth</th>
                                <td><?php echo $row['8']; ?></td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td colspan="3"><?php echo $row['11']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="education">
                        <table class="profile-table two-column-table">
                            <tr>
                                <th>Degree</th>
                                <td><?php echo $row['13']; ?></td>
                                <th>Institution</th>
                                <td><?php echo isset($row['institution']) ? $row['institution'] : 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <th>Completion Year</th>
                                <td><?php echo isset($row['completion_year']) ? $row['completion_year'] : 'Not Available'; ?></td>
                                <th>Major</th>
                                <td><?php echo isset($row['major']) ? $row['major'] : 'Not Available'; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="employment">
                        <table class="profile-table two-column-table">
                            <tr>
                                <th>Department</th>
                                <td><?php echo $row['12']; ?></td>
                                <th>Position</th>
                                <td><?php echo isset($row['position']) ? $row['position'] : 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <th>Join Date</th>
                                <td><?php echo isset($row['join_date']) ? $row['join_date'] : 'Not Available'; ?></td>
                                <th>Employee Status</th>
                                <td><?php echo isset($row['emp_status']) ? $row['emp_status'] : 'Not Available'; ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="documents">
                        <table class="profile-table two-column-table">
                            <tr>
                                <th>NID</th>
                                <td><?php echo $row['1']; ?></td>
                                <th>Passport</th>
                                <td><?php echo isset($row['passport']) ? $row['passport'] : 'Not Available'; ?></td>
                            </tr>
                            <tr>
                                <th>Other Documents</th>
                                <td colspan="3"><?php echo isset($row['documents']) ? $row['documents'] : 'Not Available'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="profile.php?edit=<?php echo $row['id']; ?>" class="btn btn-success">
                        <i class="fas fa-edit mr-2"></i>Update Profile
                    </a>
                </div>
            </div>
        <?php 
        } else {
            echo '<div class="alert alert-danger m-4">Profile not found.</div>';
        }
        ?>
    </div>

    <?php include('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>