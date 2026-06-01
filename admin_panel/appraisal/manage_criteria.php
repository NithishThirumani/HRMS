<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/bootstrap_session.php';
require_once __DIR__ . '/../../connection.php';
require_once __DIR__ . '/../../classes/AppraisalCriteria.php';

$criteria = new AppraisalCriteria();
$allCriteria = $criteria->getAllCriteria(false);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Manage Appraisal Criteria</title>

    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/reg_emp.js"></script>

    <!-- JavaScript Files -->
    <script src="../js/jquery-3.6.4.min.js"></script>
    <script src="../js/search.js"></script>


    <style>
        .text-seagreen {
            color: #20B2AA !important;
            background: linear-gradient(to bottom, #2cdad5, #20B2AA, #187f7b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .text-seagreen::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 45%, rgba(255, 255, 255, 0.1) 50%, transparent 55%);
            background-size: 200% 200%;
            animation: shine 3s infinite;
        }

        .form-control {
            background: linear-gradient(145deg, #f0f0f0, #e6e6e6);
            border: 1px solid rgba(32, 178, 170, 0.3);
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-color: #20B2AA;
            box-shadow: 0 0 15px rgba(32, 178, 170, 0.2);
            transform: translateY(-1px);
        }

        .form-control:hover {
            background: linear-gradient(145deg, #f5f5f5, #ebebeb);
        }

        @keyframes shine {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }
    </style>



</head>

<body id="page-top">

    <?php include(__DIR__ . '/../sidebar.php') ?>
    <?php include(__DIR__ . '/../header.php') ?>

    <div class="container-fluid">
        <!-- Remove this extra wrapper -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="text-seagreen">
                <i class="fas fa-file-alt mr-2"></i>
                Manage Appraisal Criteria
            </h2>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#addCriteriaModal">
                            Add New Criteria
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="criteriaTable" class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="20%">Criteria Name</th>
                                        <th width="35%">Description</th>
                                        <th width="10%">Weightage</th>
                                        <th width="10%">Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allCriteria as $item): ?>
                                        <tr>
                                            <td class="align-middle"><?php echo $item['criteria_id']; ?></td>
                                            <td class="align-middle font-weight-bold">
                                                <?php echo htmlspecialchars($item['criteria_name']); ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php echo htmlspecialchars($item['description']); ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-primary badge-pill px-3">
                                                    <?php echo $item['weightage']; ?>%
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span
                                                    class="badge badge-<?php echo $item['is_active'] ? 'success' : 'danger'; ?> badge-pill px-3">
                                                    <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <button class="btn btn-outline-info btn-sm edit-criteria mx-1"
                                                    data-id="<?php echo $item['criteria_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($item['criteria_name']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($item['description']); ?>"
                                                    data-weight="<?php echo $item['weightage']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-outline-<?php echo $item['is_active'] ? 'warning' : 'success'; ?> btn-sm toggle-status mx-1"
                                                    data-id="<?php echo $item['criteria_id']; ?>"
                                                    title="<?php echo $item['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i
                                                        class="fas fa-<?php echo $item['is_active'] ? 'times' : 'check'; ?>"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    </div>

    <!-- Add Criteria Modal -->
    <div class="modal fade" id="addCriteriaModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Criteria</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="addCriteriaForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Criteria Name</label>
                            <input type="text" class="form-control" name="criteria_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Weightage (%)</label>
                            <input type="number" class="form-control" name="weightage" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/../footer.php') ?>

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
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>



    <!-- Add this script before the closing body tag -->
    <script>
        $(document).ready(function () {
            $('#criteriaTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                "language": {
                    "lengthMenu": "_MENU_ records per page",
                    "search": "Search: ",
                    "paginate": {
                        "first": '<i class="fas fa-angle-double-left"></i>',
                        "last": '<i class="fas fa-angle-double-right"></i>',
                        "next": '<i class="fas fa-angle-right"></i>',
                        "previous": '<i class="fas fa-angle-left"></i>'
                    }
                }
            });
        });
    </script>

    <!-- Add this style section in the head -->
    <style>
        .table thead th {
            background-color: #4e73df;
            border-top: none;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;

            color: #ffffff;
            border-top: none;
            border-bottom: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 12px 8px;
        }

        .table td {
            border-color: #e3e6f0;
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .btn-outline-info:hover,
        .btn-outline-warning:hover,
        .btn-outline-success:hover {
            color: #fff;
        }

        .table thead {
            border: 1px solid #4e73df;
        }

        .card-header {
            border-bottom: none;
        }
    </style>

    <!-- Bootstrap core JavaScript -->
    <!-- Scripts at bottom -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages -->
    <script src="../js/sb-admin-2.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/datetime.js"></script>
    <script src="js/criteria-manage.js"></script>

</body>

</html>