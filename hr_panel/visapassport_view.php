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
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-heading {
            background: #4e73df;
            padding: 1rem 2rem;
            border-radius: 8px 8px 0 0;
        }

        h2 {
            color: #2e3d5c;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        table {
            font-size: 14px;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin: 1rem 0;
        }

        th,
        td {
            font-size: 13px;
            border: 1px solid #e3e6f0;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4e73df;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        tr:hover {
            background-color: #eaecf4;
        }

        .readonly-field {
            background-color: #f8f9fc;
            color: #5a5c69;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .table {
            margin-top: 0;
            margin-bottom: 0;
        }

        .table thead th {
            vertical-align: middle;
            border-bottom: 2px solid #e3e6f0;
            background-color: #4e73df;
            color: white;
            font-weight: 500;
            padding: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .export-buttons {
            margin-bottom: 0.5rem;

        }

        .export-buttons .btn {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .export-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
        }

        .action-links a {
            color: #4e73df;
            text-decoration: none;
            margin-right: 10px;
            transition: color 0.3s ease;
        }

        .action-links a:hover {
            color: #2e59d9;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <form id="editForm" action="" method="POST">

                <div class="card-header py-3">
                    <h2 class="m-0 font-weight-bold text-primary">Visa & Passport Details</h2>
                </div>
                <table class="table">


                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <?php
                                // Fetch column names dynamically
                                $fields = mysqli_fetch_fields($result);
                                foreach ($fields as $field) {
                                    if ($field->name !== 'id') { // Hide the primary key 'id'
                                        echo "<th>" . ucfirst(str_replace('_', ' ', $field->name)) . "</th>";
                                    }
                                }
                                ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <?php foreach ($row as $key => $value) {
                                    if ($key !== 'id') { // Hide 'id' column
                                        ?>
                                        <td class="<?php echo ($key == 'eid') ? 'readonly-field' : ''; ?>">
                                            <?php echo htmlspecialchars($value); ?>
                                        </td>
                                    <?php }
                                } ?>
                                <td class="action-links">
                                    <a href="edit_visapassport.php?id=<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a> |
                                    <a href="delete_visapassport.php?id=<?php echo $row['id']; ?>"
                                        onclick="return confirm('Are you sure?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>




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
</body>

</html>