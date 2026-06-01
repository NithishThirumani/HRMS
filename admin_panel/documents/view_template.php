<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$documentManager = new DocumentManager($con);
$templateId = isset($_GET['id']) ? $_GET['id'] : 0;

// Add validation for template ID
if (!$templateId) {
    die("Invalid template ID provided");
}
// Get template details
$sql = "SELECT * FROM document_templates WHERE template_id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$templateId]);
$result = $stmt->get_result();
$template = $result->fetch_assoc();
$stmt->close();




// Get assignment statistics
// Update the statistics query
$sql = "SELECT 
            COUNT(*) as total_assigned,
            SUM(CASE WHEN status = 'signed' THEN 1 ELSE 0 END) as total_signed
        FROM document_assignments 
        WHERE template_id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$templateId]);
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

// Update the recent assignments query
$sql = "SELECT da.*, dt.doc_title, e.first_name, e.last_name, e.eid 
        FROM document_assignments da
        JOIN document_templates dt ON da.template_id = dt.template_id
        JOIN employees e ON da.emp_id = e.id
        WHERE da.template_id = ?
        ORDER BY da.assigned_date DESC";
$stmt = $con->prepare($sql);
$stmt->execute([$templateId]);
$result = $stmt->get_result();
$recentAssignments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>View Document Template</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/document-styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="stylesheet" href="../../assets/css/style.css">

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

    <style>
        /* Add these styles to your existing style block */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 10px;
        }

        .stat-box {
            background: linear-gradient(145deg, rgb(192, 187, 187), rgb(220, 217, 217));
            border-radius: 15px;
            padding: 12px;
            text-align: center;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1),
                -5px -5px 15px rgba(255, 255, 255, 0.8);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.15),
                -8px -8px 20px rgba(255, 255, 255, 0.9);
        }

        .stat-box h4 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-box p {
            color: #20B2AA;
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            background: linear-gradient(45deg, #20B2AA, #2cdad5);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .template-stats h3 {
            color: #444;
            padding: 20px;
            margin-bottom: 0;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
        }
    </style>
</head>

<body>
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>
    <div class="container-fluid">
        <div class="container-fluid">
            <!-- Template Info Card Row -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Document Template</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($template['doc_title']); ?></div>
                                    <div class="small text-muted mt-2">
                                        <div>Type: <?php echo htmlspecialchars($template['doc_type']); ?></div>
                                        <div>Created: <?php echo date('M d, Y', strtotime($template['created_at'])); ?></div>
                                        <div>Status: <?php echo $template['is_active'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>'; ?></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Statistics Card -->
                <div class="col-xl-4 col-md-6">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <h4>Assigned</h4>
                                    <p><?php echo number_format($stats['total_assigned']); ?></p>
                                </div>
                                <div class="stat-box">
                                    <h4>Signed</h4>
                                    <p><?php echo number_format($stats['total_signed']); ?></p>
                                </div>
                                <div class="stat-box">
                                    <h4>Pending</h4>
                                    <p><?php echo number_format($stats['total_assigned'] - $stats['total_signed']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <!-- Document Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="onboarding-tab" data-toggle="tab" href="#onboarding" role="tab">
                                <i class="fas fa-user-plus mr-2"></i>Onboarding Documents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="termination-tab" data-toggle="tab" href="#termination" role="tab">
                                <i class="fas fa-user-minus mr-2"></i>Termination Documents
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="onboarding" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="onboardingTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Status</th>
                                            <th>Assigned Date</th>
                                            <th>Signed Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['eid'] . ' - ' . $assignment['first_name'] . ' ' . $assignment['last_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $assignment['status'] == 'signed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($assignment['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($assignment['assigned_date'])); ?></td>
                                                <td><?php echo $assignment['signed_date'] ? date('M d, Y', strtotime($assignment['signed_date'])) : '-'; ?></td>
                                                <td>
                                                    <?php if ($assignment['signed_file_path']): ?>
                                                        <a href="download_signed.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-success" title="Download Signed Document">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="view_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="termination" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                Termination documents will be available when processing employee exit.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col">
                    <a href="manage_templates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                    <a href="assign_template.php?id=<?php echo $templateId; ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>Assign to Employees
                    </a>
                    <?php if ($template && isset($template['file_path'])): ?>
                        <a href="download_document.php?id=<?php echo $templateId; ?>" class="btn btn-info">
                            <i class="fas fa-download mr-2"></i>Download Template
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('../footer.php'); ?>
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
</body>

</html>