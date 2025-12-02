<?php
include('session.php');

// Fetch unique, non-empty designations from the employees table
$designations_query = "SELECT DISTINCT designation FROM employees WHERE designation IS NOT NULL AND designation != '' ORDER BY designation";
$designations_result = mysqli_query($con, $designations_query);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Designations</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Manage Designations</h1>
        <p class="mb-4">Edit or remove existing job designations. New designations are added by assigning them to an employee.</p>
        
        <div class="alert alert-info">
            <strong>Note:</strong> This page lists all unique designations currently in use. To add a new designation, please assign it to an employee via the employee management page.
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Existing Designations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="designationTable">
                        <thead>
                            <tr>
                                <th>Designation Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($designations_result && mysqli_num_rows($designations_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($designations_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick='editDesignation(<?php echo json_encode($row["designation"]); ?>)'>
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick='deleteDesignation(<?php echo json_encode($row["designation"]); ?>)'>
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Designation Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Designation</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="old_name" id="old_name">
                        <div class="form-group">
                            <label>Designation Name</label>
                            <input type="text" class="form-control" name="new_name" id="new_name" required>
                        </div>
                         <p class="text-muted">Warning: Changing this name will update it for all employees with this designation.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once('footer.php'); ?>
    
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    
    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#designationTable').DataTable();

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'process_designation.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit',
                    success: function(response) {
                        if (response.trim() === 'success') {
                            location.reload();
                        } else {
                            alert('An error occurred: ' + response);
                        }
                    }
                });
            });
        });

        function editDesignation(name) {
            $('#old_name').val(name);
            $('#new_name').val(name);
            $('#editModal').modal('show');
        }

        function deleteDesignation(name) {
            if (confirm('Are you sure you want to delete the designation "' + name + '"? This will remove it from all employees who have it.')) {
                $.ajax({
                    url: 'process_designation.php',
                    type: 'POST',
                    data: { action: 'delete', name: name },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            location.reload();
                        } else {
                            alert('Error deleting designation: ' + response);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html> 