<?php
include('session.php');
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch family member details
    $query = "SELECT * FROM employee_family WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();
    
    if (!$family) {
        echo "No record found!";
        exit;
    }
} else {
    echo "Invalid Request!";
    exit;
}

// Update Data on Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $fm_name = $_POST['fm_name'];
    $fm_dob = $_POST['fm_dob'];
    $fm_nationality = $_POST['fm_nationality'];
    $fm_blood_group = $_POST['fm_blood_group'];
    $fm_gender = $_POST['fm_gender'];
    $fm_profession = $_POST['fm_profession'];
    $fm_relation = $_POST['fm_relation'];

    // Prepare the update query
    $updateQuery = "UPDATE employee_family SET 
                    fm_name=?, 
                    fm_dob=?, 
                    fm_nationality=?, 
                    fm_blood_group=?, 
                    fm_gender=?, 
                    fm_profession=?, 
                    fm_relation=? 
                    WHERE id=?";
    
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("sssssssi", 
                      $fm_name, 
                      $fm_dob, 
                      $fm_nationality, 
                      $fm_blood_group, 
                      $fm_gender, 
                      $fm_profession, 
                      $fm_relation, 
                      $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data updated successfully!'); window.location.href='view_family.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Family Details</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <form id="editForm" action="" method="POST">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h2 class="title">Edit Family Details</h2>

                            <input type="hidden" name="id" value="<?php echo $family['id']; ?>">

                            <div class="input-group1">
                                <label>Family Member Name</label>
                                <input class="input--style-1" type="text" name="fm_name" value="<?php echo $family['fm_name']; ?>" required />
                            </div>

                            <div class="input-group1">
                                <label>Date of Birth</label>
                                <input class="input--style-1" type="date" name="fm_dob" value="<?php echo $family['fm_dob']; ?>" required />
                            </div>

                            <div class="input-group1">
                                <label>Nationality</label>
                                <input class="input--style-1" type="text" name="fm_nationality" value="<?php echo $family['fm_nationality']; ?>" required />
                            </div>

                            <div class="input-group1">
                                <label>Blood Group</label>
                                <select class="input--style-1" name="fm_blood_group" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" <?php echo ($family['fm_blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo ($family['fm_blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo ($family['fm_blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo ($family['fm_blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                    <option value="O+" <?php echo ($family['fm_blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo ($family['fm_blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                    <option value="AB+" <?php echo ($family['fm_blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo ($family['fm_blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                </select>
                            </div>

                            <div class="input-group1">
                                <label>Gender</label>
                                <select class="input--style-1" name="fm_gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($family['fm_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($family['fm_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($family['fm_gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="input-group1">
                                <label>Profession</label>
                                <input class="input--style-1" type="text" name="fm_profession" value="<?php echo $family['fm_profession']; ?>" required />
                            </div>

                            <div class="input-group1">
                                <label>Relation</label>
                                <input class="input--style-1" type="text" name="fm_relation" value="<?php echo $family['fm_relation']; ?>" required />
                            </div>

                            <div class="p-t-20">
                                <button class="btn btn-success" type="submit">Update Family Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                        href="/emps/admin_panel/logout.php">Logout</a>
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
