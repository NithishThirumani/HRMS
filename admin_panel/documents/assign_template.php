<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$isSalaryPage = false;  // 

// Check if admin is logged in
if (!isset($_SESSION['username'])) {  // Changed to match your session variable
    $_SESSION['error'] = "Please login to access this page.";
    header('Location: ../../login.php');
    exit;
}

$documentManager = new DocumentManager($con);
$templateId = isset($_GET['id']) ? $_GET['id'] : 0;

// Validate template ID
if (!$templateId) {
    $_SESSION['error'] = "Invalid template ID.";
    header('Location: manage_templates.php');
    exit;
}

// Get template details
$sql = "SELECT * FROM document_templates WHERE template_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $templateId);
$stmt->execute();
$result = $stmt->get_result();
$template = $result->fetch_assoc();
$stmt->close();

// Check if template exists
if (!$template) {
    $_SESSION['error'] = "Template not found.";
    header('Location: manage_templates.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employees = isset($_POST['employees']) ? $_POST['employees'] : [];
    $successCount = 0;
    $errorCount = 0;

    if (empty($employees)) {
        $_SESSION['error'] = "Please select at least one employee.";
    } else {
        // Get admin ID for assignment
        $adminSql = "SELECT id FROM admin WHERE user_name = ?";
        $adminStmt = $con->prepare($adminSql);
        $adminStmt->bind_param("s", $_SESSION['username']);
        $adminStmt->execute();
        $adminResult = $adminStmt->get_result();
        $admin = $adminResult->fetch_assoc();
        $adminStmt->close();

        if (!$admin) {
            $_SESSION['error'] = "Admin user not found.";
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $templateId);
            exit;
        }

        $adminId = $admin['id'];

        foreach ($employees as $empId) {
            if ($documentManager->assignDocumentToEmployee($empId, $templateId, $adminId)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $_SESSION['success'] = "Successfully assigned document to $successCount employee(s).";
            if ($errorCount > 0) {
                $_SESSION['warning'] = "$errorCount assignment(s) failed.";
            }
            header('Location: manage_templates.php');
            exit;
        }
    }
}

// Get all employees - modify this query

// Change to:
$sql = "SELECT id, eid, first_name, last_name FROM employees WHERE status = 'active' ORDER BY eid";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$employees = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Assign Document Template</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

</head>

<body>
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>
    <div class="container-fluid">
        <!-- Add message display here -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="text-seagreen"> <i class="fas fa-file-alt mr-2"></i>Assign Document:

            </h2>
        </div>
        <form method="POST" id="assignForm">
            <div class="form-group">
                <label>Select Employees</label>
                <select name="employees[]" multiple class="form-control select2" required>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo htmlspecialchars($employee['id']); ?>">
                            <?php echo htmlspecialchars($employee['eid'] . " - " . $employee['first_name'] . ' ' . $employee['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="assignmentStatus"></div>
            <button type="submit" class="btn btn-primary">Assign Document</button>
            <a href="manage_templates.php" class="btn btn-secondary">Back</a>
        </form>
        
        <script>
        $(document).ready(function() {
            $('#assignForm').on('submit', function(e) {
                e.preventDefault();
                var selectedEmployees = $('.select2').val();
                var totalEmployees = selectedEmployees.length;
                var processed = 0;
        
                if (totalEmployees === 0) {
                    $('#assignmentStatus').html('<div class="alert alert-danger">Please select at least one employee.</div>');
                    return;
                }
        
                $('#assignmentStatus').html('<div class="alert alert-info">Processing assignments...</div>');
        
                selectedEmployees.forEach(function(empId) {
                    $.ajax({
                        url: 'process_assignment.php',
                        method: 'POST',
                        data: {
                            employee_id: empId,
                            template_id: <?php echo $templateId; ?>
                        },
                        success: function(response) {
                            processed++;
                            var result = JSON.parse(response);
                            
                            if (processed === totalEmployees) {
                                if (result.success) {
                                    $('#assignmentStatus').html('<div class="alert alert-success">All assignments completed successfully!</div>');
                                    setTimeout(function() {
                                        window.location.href = 'manage_templates.php';
                                    }, 2000);
                                } else {
                                    $('#assignmentStatus').html('<div class="alert alert-danger">Some assignments failed. Please try again.</div>');
                                }
                            }
                        },
                        error: function() {
                            processed++;
                            $('#assignmentStatus').html('<div class="alert alert-danger">Error occurred during assignment.</div>');
                        }
                    });
                });
            });
        });
        </script>
    </div>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: 'Select employees',
                allowClear: true
            });
        });
    </script>
    <?php include('../footer.php'); ?>

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
