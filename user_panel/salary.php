<?php include('session.php');
 ?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Salary</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
</head>

<body id="page-top">

    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>
    <!-- Begin Page Content -->
    <div class="container-fluid"><br>
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Salary</h6>
            </div>
			<div class="export-buttons">
    <a href="export_excel.php" class="btn btn-success">Excel</a>
    <a href="export_csv.php" class="btn btn-primary">CSV</a>
    <a href="export_pdf.php" class="btn btn-danger">PDF</a>
    <button onclick="printTable()" class="btn btn-info">Print</button>
</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Emp ID</th>
                                <th>Employee Name</th>
                                  <th>Base Salary</th>
                                <th>Bonus</th>
                                <th>Total Salary</th>
                            </tr>
                        </thead>
                        <tbody>		
<?php

// Check if user is logged in
$user_name = $_SESSION['username'] ?? null; // Ensure it matches session key

// Check if username is set
if (!$user_name) {
    echo "No username found in session.";
    exit;
}

// Secure the query against SQL injection
$user_name = mysqli_real_escape_string($con, $user_name);

// Fetch emp_id and salary details
$query = "SELECT el.user_name, el.emp_id, s.base_salary, s.bonus, s.total_salary, e.full_name
          FROM emp_login el
          JOIN employees e ON el.emp_id = e.eid
          JOIN salary s ON e.eid = s.emp_id
          WHERE el.user_name = '$user_name'";

$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Display data
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['emp_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>AED " . number_format($row['base_salary'], 2) . "</td>";
        echo "<td>AED " . number_format($row['bonus'], 2) . "</td>";
        echo "<td>AED " . number_format($row['total_salary'], 2) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No salary record found for user: " . htmlspecialchars($user_name) . "</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <!-- /.container-fluid -->
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
                        href="<?php echo htmlspecialchars(hrms_user_panel_url('logout.php')); ?>">Logout</a>
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
