<?php
include('session.php');
include('connection.php');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Onboarding Dashboard</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <!-- Add jQuery UI -->
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .onboarding-progress {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #e3e6f0;
        }

        .table thead th {
            background-color: #4e73df;
            color: white;
            border-color: #4e73df;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }

        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .table-responsive {
            padding: 1rem;
        }

        #onboardingTable {
            margin-bottom: 0;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Onboarding Dashboard</h1>
            <button class="btn btn-primary" onclick="exportOnboardingData()">
                <i class="fas fa-download fa-sm"></i> Export Report
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="onboardingTable">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Employee Code</th>
                                <th>Visa Under</th>
                                <th>Manager/TL Name</th>
                                <th>Status</th>
                                <th>Designation</th>
                                <th>Client Team</th>
                                <th>Date of Joining</th>
                                <th>Documents Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT e.*, d.name as department_name 
                                     FROM employees e 
                                     LEFT JOIN departments d ON e.department_id = d.id 
                                     ORDER BY e.doj DESC";
                            $result = mysqli_query($con, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['eid'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['visa_type'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['reporting_manager'] ?? ''); ?></td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($row['status'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['designation'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['department_name'] ?? ''); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['doj'] ?? '')); ?></td>
                                    <td>
                                        <a href="view_document_status.php?id=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-info">
                                            View Status
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="updateOnboardingStatus(<?php echo $row['id']; ?>)">
                                            Update
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

    <!-- Document Status Modal -->
    <div class="modal fade" id="documentStatusModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Status</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="documentStatusForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biometric Login</label>
                                    <select class="form-control" name="biometric_login">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Process Evaluation</label>
                                    <select class="form-control" name="process_evaluation">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Offer Letter</label>
                                    <select class="form-control" name="offer_letter">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Resume</label>
                                    <select class="form-control" name="resume">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>KYC</label>
                                    <select class="form-control" name="kyc">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Policy Docs</label>
                                    <select class="form-control" name="policy_docs">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Staff ID</label>
                                    <select class="form-control" name="staff_id">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Deem ID Request</label>
                                    <select class="form-control" name="deem_id_request">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveDocumentStatus()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script>
        $(document).ready(function () {
            $('#onboardingTable').DataTable({
                responsive: true,
                order: [[7, 'desc']], // Sort by DOJ
                pageLength: 25
            });
        });

        function viewDocumentStatus(employeeId) {
            $.ajax({
                url: 'get_document_status.php',
                type: 'POST',
                data: { employee_id: employeeId },
                success: function (response) {
                    var data = JSON.parse(response);
                    $('#documentStatusModal').modal('show');
                    // Populate form with response data
                    for (var key in data) {
                        $('[name="' + key + '"]').val(data[key]);
                    }
                }
            });
        }

        function updateOnboardingStatus(employeeId) {
            $('#documentStatusModal').modal('show');
            $('#documentStatusForm')[0].reset();
            $('#documentStatusForm').data('employeeId', employeeId);
        }

        function saveDocumentStatus() {
            var employeeId = $('#documentStatusForm').data('employeeId');
            var formData = $('#documentStatusForm').serialize();
            formData += '&employee_id=' + employeeId;

            $.ajax({
                url: 'update_document_status.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#documentStatusModal').modal('hide');
                    location.reload();
                }
            });
        }

        function exportOnboardingData() {
            window.location.href = 'export_onboarding.php';
        }
    </script>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>