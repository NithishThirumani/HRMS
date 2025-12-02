<?php
include('session.php');

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Leave Settings</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="img/favicon.png" rel="icon">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
        }

        .badge {
            font-size: 0.85rem;
        }

        .card-header {
            border-bottom: 2px solid #e3e6f0;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>


</head>


<body id="page-top">
    <?php include('sidebar.php'); ?>

    <?php include('header.php'); ?>

    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Leave Settings</h1>
        <p class="mb-4">Manage and configure leave policies for your organization.</p>

        <div class="row">
            <!-- Leave Policies Card -->
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center"
                        style="background-color: #f8f9fc;">
                        <h6 class="m-0 font-weight-bold text-primary">Leave Policies</h6>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPolicyModal">
                            <i class="fas fa-plus"></i> Add Policy
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="policyTable">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Max Days/Year</th>
                                        <th>Monthly Accrual</th>
                                        <th>Min. Service (Months)</th>
                                        <th>Requires Certificate</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM leave_policies ORDER BY leave_type";
                                    $result = mysqli_query($con, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['leave_type']; ?></td>
                                            <td><?php echo $row['max_days']; ?></td>
                                            <td><?php echo $row['monthly_accrual']; ?></td>
                                            <td><?php echo $row['min_service_months']; ?></td>
                                            <td><?php echo $row['requires_certificate'] ? 'Yes' : 'No'; ?></td>
                                            <td>
                                                <span
                                                    class="badge badge-<?php echo (isset($row['is_active']) && $row['is_active']) ? 'success' : 'danger'; ?>">
                                                    <?php echo (isset($row['is_active']) && $row['is_active']) ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-info btn-sm"
                                                    onclick="editPolicy(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-<?php echo (isset($row['is_active']) && $row['is_active']) ? 'danger' : 'success'; ?> btn-sm"
                                                    onclick="togglePolicy(<?php echo $row['id']; ?>, <?php echo (isset($row['is_active']) ? $row['is_active'] : 1); ?>)">
                                                    <i
                                                        class="fas fa-<?php echo (isset($row['is_active']) && $row['is_active']) ? 'times' : 'check'; ?>"></i>
                                                </button>
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

    <!-- Add/Edit Policy Modal -->
    <div class="modal fade" id="addPolicyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Leave Policy</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="policyForm">
                    <div class="modal-body">
                        <input type="hidden" name="policy_id" id="policy_id">
                        <div class="form-group">
                            <label>Leave Type</label>
                            <input type="text" class="form-control" name="leave_type" required>
                        </div>
                        <div class="form-group">
                            <label>Maximum Days Per Year</label>
                            <input type="number" class="form-control" name="max_days" required>
                        </div>
                        <div class="form-group">
                            <label>Monthly Accrual Rate</label>
                            <input type="number" step="0.5" class="form-control" name="monthly_accrual" required>
                        </div>
                        <div class="form-group">
                            <label>Minimum Service (Months)</label>
                            <input type="number" class="form-control" name="min_service_months" required>
                        </div>
                        <div class="form-group">
                            <label>Requires Medical Certificate</label>
                            <select class="form-control" name="requires_certificate">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#policyTable').DataTable();

            $('#policyForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'process_leave_policy.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Error saving policy');
                        }
                    }
                });
            });
        });

        function editPolicy(data) {
            $('#modalTitle').text('Edit Leave Policy');
            $('#policy_id').val(data.id);
            $('input[name="leave_type"]').val(data.leave_type);
            $('input[name="max_days"]').val(data.max_days);
            $('input[name="monthly_accrual"]').val(data.monthly_accrual);
            $('input[name="min_service_months"]').val(data.min_service_months);
            $('select[name="requires_certificate"]').val(data.requires_certificate);
            $('#addPolicyModal').modal('show');
        }

        function togglePolicy(id, currentStatus) {
            if (confirm('Are you sure you want to ' + (currentStatus ? 'deactivate' : 'activate') + ' this policy?')) {
                $.ajax({
                    url: 'process_leave_policy.php',
                    type: 'POST',
                    data: {
                        action: 'toggle',
                        policy_id: id,
                        status: currentStatus ? 0 : 1
                    },
                    success: function (response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Error updating policy status');
                        }
                    }
                });
            }
        }
    </script>

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
                    <a class="btn btn-success" href="https:/emps/admin_panel/logout.php">Logout</a>
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