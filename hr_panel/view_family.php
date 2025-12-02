<?php
include('session.php');
include('connection.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Family Details</title>

    <!-- Custom fonts for this template-->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
</head>

<body id="page-top">
    <?php  include('sidebar.php'); ?>

    <?php  include('header.php'); ?>

    <!-- /.container-fluid -->

    <!-- Begin Page Content -->
	<script>
    function printTable() {
        var printContent = document.querySelector('.table-responsive').innerHTML;
        var originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        
        document.body.innerHTML = originalContent;
    }
</script>

    <div class="container-fluid">

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Employee Family Details</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
<div class="export-buttons">
    <a href="export_excel.php" class="btn btn-success">Excel</a>
    <a href="export_csv.php" class="btn btn-primary">CSV</a>
    <a href="export_pdf.php" class="btn btn-danger">PDF</a>
    <button onclick="printTable()" class="btn btn-info">Print</button>
</div>

                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                             
                                <th>Employee Name</th>
                                <th>Family Member Name</th>
                                <th>Date of Birth</th>
                                <th>Nationality</th>
                                <th>Blood Group</th>
                                <th>Gender</th>
                                <th>Profession</th>
                                <th>Relation</th>
                                <th>Action</th>                               
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
              // Fetching data from both tables (Family and employees) 
              $sql = "SELECT e.full_name, f.id, f.fm_name, f.fm_dob, f.fm_nationality, f.fm_blood_group, f.fm_gender, f.fm_profession, f.fm_relation
                                    FROM employee_family f
                                    JOIN employees e ON f.eid = e.eid
                                    ORDER BY e.full_name";
                            $result = mysqli_query($con, $sql);

                            $current_employee = '';
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Check if the employee name is different from the previous row
                                if ($current_employee != $row['full_name']) {
                                    $current_employee = $row['full_name'];
                                    echo "<tr><td><b>{$row['full_name']}</b></td>";
                                } else {
                                    echo "<tr><td></td>";
                                }
                                echo "<td>{$row['fm_name']}</td>
                                      <td>{$row['fm_dob']}</td>
                                      <td>{$row['fm_nationality']}</td>
                                      <td>{$row['fm_blood_group']}</td>
                                      <td>{$row['fm_gender']}</td>
                                      <td>{$row['fm_profession']}</td>
                                      <td>{$row['fm_relation']}</td>
                                      <td>
                                        <a href='edit_family.php?id={$row['id']}' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a>
                                      </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    </div>


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
                        href="http://localhost/emps/admin_panel/logout.php">Logout</a>
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
	<script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>

</body>

</html>
