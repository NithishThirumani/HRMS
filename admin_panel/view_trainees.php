<?php
include('connection.php');
include('session.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Trainees</title>
    <!-- Custom fonts and styles -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Trainees List</h1>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <a href="add_trainee.php" class="btn btn-primary">Add New Trainee</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Trainee ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Joining Date</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT t.*, d.name as department_name 
                                                 FROM trainees t 
                                                 LEFT JOIN departments d ON t.department_id = d.id 
                                                 ORDER BY t.created_at DESC";
                                        $result = mysqli_query($mysqli, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['trainee_id'] . "</td>";
                                            echo "<td>" . $row['full_name'] . "</td>";
                                            echo "<td>" . $row['email'] . "</td>";
                                            echo "<td>" . $row['department_name'] . "</td>";
                                            echo "<td>" . $row['designation'] . "</td>";
                                            echo "<td>" . date('d-m-Y', strtotime($row['date_of_joining'])) . "</td>";
                                            echo "<td>" . $row['training_duration'] . " months</td>";
                                            echo "<td>" . ucfirst($row['status']) . "</td>";
                                            echo "<td>
                                                    <a href='view_trainee_details.php?id=" . $row['trainee_id'] . "' class='btn btn-info btn-sm'>View</a>
                                                    <a href='edit_trainee.php?id=" . $row['trainee_id'] . "' class='btn btn-primary btn-sm'>Edit</a>
                                                    <button onclick='updateStatus(\"" . $row['trainee_id'] . "\")' class='btn btn-warning btn-sm'>Update Status</button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Trainee Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" id="traineeId" name="traineeId">
                        <div class="form-group">
                            <label>New Status</label>
                            <select class="form-control" id="newStatus" name="newStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveStatus()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });

        function updateStatus(traineeId) {
            $('#traineeId').val(traineeId);
            $('#statusModal').modal('show');
        }

        function saveStatus() {
            const traineeId = $('#traineeId').val();
            const newStatus = $('#newStatus').val();

            $.ajax({
                url: 'update_trainee_status.php',
                type: 'POST',
                data: {
                    traineeId: traineeId,
                    status: newStatus
                },
                success: function(response) {
                    alert('Status updated successfully');
                    location.reload();
                },
                error: function() {
                    alert('Error updating status');
                }
            });
        }
    </script>
</body>
</html> 