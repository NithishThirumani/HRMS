<?php
include('session.php');
include('connection.php');

// Fetch only the visa and passport-related details
$query = "SELECT id, eid, full_name, visa_number, visa_type, visa_issue_date, visa_expiry_date, passport_number, passport_type, passport_issue_date, passport_expiry_date, country_of_issue, passport_issue_place FROM employees";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visa & Passport Details</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-stats {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(45deg, #4e73df, #2e59d9);
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        .table thead th {
            background: #4e73df;
            color: white;
            font-weight: 500;
            border: none;
            padding: 15px;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
            transition: all 0.2s;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons a {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .edit-btn {
            background: #4e73df;
        }

        .delete-btn {
            background: #e74a3b;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #1cc88a;
            color: white;
        }

        .status-expired {
            background: #e74a3b;
            color: white;
        }

        .nav-tabs {
            border-bottom: 2px solid #e3e6f0;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #858796;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 0;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: #4e73df;
            background: none;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #4e73df;
        }

        .tab-content {
            padding: 20px 0;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Visa & Passport Management</h1>
        </div>
        <div class="card-stats">
            <?php
            $total_records = mysqli_num_rows($result);
            $expired_visa_query = "SELECT COUNT(*) as count FROM employees WHERE visa_expiry_date < CURDATE()";
            $expired_visa_result = mysqli_query($con, $expired_visa_query);
            $expired_visa_count = mysqli_fetch_assoc($expired_visa_result)['count'];
            // Add passport statistics query
            $expired_passport_query = "SELECT COUNT(*) as count FROM employees WHERE passport_expiry_date < CURDATE()";
            $expired_passport_result = mysqli_query($con, $expired_passport_query);
            $expired_passport_count = mysqli_fetch_assoc($expired_passport_result)['count'];
            ?>
            <div class="stat-card">
                <div>
                    <h3><?php echo $total_records; ?></h3>
                    <p>Total Records</p>
                </div>
                <i class='bx bxs-folder' style="font-size: 2.5rem;"></i>
            </div>
            <div class="stat-card">
                <div>
                    <h3><?php echo $expired_visa_count; ?></h3>
                    <p>Expired Visas</p>
                </div>
                <i class='bx bxs-error' style="font-size: 2.5rem;"></i>
            </div>
            <div class="stat-card">
                <div>
                    <h3><?php echo $expired_passport_count; ?></h3>
                    <p>Expired Passports</p>
                </div>
                <i class='bx bxs-id-card' style="font-size: 2.5rem;"></i>
            </div>

        </div>

        <div class="dashboard-card">
            <div class="table-container">
                <ul class="nav nav-tabs" id="visaPassportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="visa-tab" data-bs-toggle="tab" href="#visa" role="tab">
                            <i class="fas fa-passport mr-2"></i>Visa Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="passport-tab" data-bs-toggle="tab" href="#passport" role="tab">
                            <i class="fas fa-id-card mr-2"></i>Passport Details
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="visaPassportContent">
                    <div class="tab-pane fade show active" id="visa" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>EID</th>
                                        <th>Full Name</th>
                                        <th>Visa Number</th>
                                        <th>Visa Type</th>
                                        <th>Issue Date</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['eid']; ?></td>
                                            <td><?php echo $row['full_name']; ?></td>
                                            <td><?php echo $row['visa_number']; ?></td>
                                            <td><?php echo $row['visa_type']; ?></td>
                                            <td><?php echo $row['visa_issue_date']; ?></td>
                                            <td>
                                                <span
                                                    class="status-badge <?php echo strtotime($row['visa_expiry_date']) < time() ? 'status-expired' : 'status-active'; ?>">
                                                    <?php echo $row['visa_expiry_date']; ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="edit_visapassport.php?id=<?php echo $row['id']; ?>"
                                                    class="edit-btn">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_visapassport.php?id=<?php echo $row['id']; ?>"
                                                    class="delete-btn" onclick="return confirm('Are you sure?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="passport" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>EID</th>
                                        <th>Full Name</th>
                                        <th>Passport Number</th>
                                        <th>Passport Type</th>
                                        <th>Issue Date</th>
                                        <th>Expiry Date</th>
                                        <th>Country</th>
                                        <th>Issue Place</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['eid']; ?></td>
                                            <td><?php echo $row['full_name']; ?></td>
                                            <td><?php echo $row['passport_number']; ?></td>
                                            <td><?php echo $row['passport_type']; ?></td>
                                            <td><?php echo $row['passport_issue_date']; ?></td>
                                            <td>
                                                <span
                                                    class="status-badge <?php echo strtotime($row['passport_expiry_date']) < time() ? 'status-expired' : 'status-active'; ?>">
                                                    <?php echo $row['passport_expiry_date']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['country_of_issue']; ?></td>
                                            <td><?php echo $row['passport_issue_place']; ?></td>
                                            <td class="action-buttons">
                                                <a href="edit_visapassport.php?id=<?php echo $row['id']; ?>"
                                                    class="edit-btn">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_visapassport.php?id=<?php echo $row['id']; ?>"
                                                    class="delete-btn" onclick="return confirm('Are you sure?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>










    </div>
    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
</body>

</html>