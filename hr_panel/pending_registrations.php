<?php
include('session.php');




// Handle approval action
if (isset($_POST['approve'])) {
    $emp_id = mysqli_real_escape_string($con, $_POST['emp_id']);

    // First get the eid from employees table
    $get_eid = "SELECT eid FROM employees WHERE id = ?";
    $stmt = $con->prepare($get_eid);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $eid = $row['eid'];

    // Update employee status
    $update_emp = "UPDATE employees SET status = 'active' WHERE id = ?";
    $stmt = $con->prepare($update_emp);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();

    // Update login status using eid
    $update_login = "UPDATE emp_login SET status = 'active' WHERE emp_id = ?";
    $stmt = $con->prepare($update_login);
    $stmt->bind_param("s", $eid);  // Using eid instead of id
    $stmt->execute();

    echo "<script>alert('Employee approved successfully!');</script>";
}

// Fetch pending registrations
$query = "SELECT * FROM employees WHERE status = 'pending' ORDER BY id DESC";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Pending Registrations</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>

    <?php include('header.php'); ?>
    <div class="container mt-5">
        <h2>Pending Employee Registrations</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>NID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Contact</th>
                            <th>Degree</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['eid']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                <td><?php echo htmlspecialchars($row['degree']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="emp_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View
                                        Details</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No pending registrations found.</div>
        <?php endif; ?>
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
                    <a class="btn btn-success" href="http://localhost/emps/admin_panel/logout.php">Logout</a>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>