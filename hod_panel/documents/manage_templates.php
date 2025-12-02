<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';
$documentManager = new DocumentManager($con);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && isset($_POST['doc_title']) && isset($_POST['doc_type'])) {
        $doc_title = $_POST['doc_title'];
        $doc_type = $_POST['doc_type'];
        $file = $_FILES['document'];

        // Check if file is a PDF
        if ($file['type'] === 'application/pdf') {
            // Generate unique filename
            $filename = uniqid() . '_' . $file['name'];
            $upload_path = '../../uploads/templates/' . $filename;

            // Create directory if it doesn't exist
            if (!file_exists('../../uploads/templates/')) {
                mkdir('../../uploads/templates/', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Save to database
                $sql = "INSERT INTO document_templates (doc_title, doc_type, file_path, is_active, created_at) 
                        VALUES (?, ?, ?, 1, NOW())";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("sss", $doc_title, $doc_type, $filename);

                if ($stmt->execute()) {
                    echo "<script>alert('Template uploaded successfully!');</script>";
                } else {
                    echo "<script>alert('Error saving to database!');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Error uploading file!');</script>";
            }
        } else {
            echo "<script>alert('Please upload a PDF file!');</script>";
        }
    }
}

// Get all templates
$sql = "SELECT * FROM document_templates ORDER BY created_at DESC";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$templates = [];
while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
}
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
    <title>Manage Document Templates</title>
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

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="text-seagreen">
                <i class="fas fa-file-alt mr-2"></i>
                Document Templates Management
                <div class="h5 mb-0 font-weight-light text-gray-600">Manage your document templates efficiently</div>
            </h2>
        </div>

        <!-- Upload Form -->
        <div class="upload-section">
            <h4>Upload New Template</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Document Title</label>
                    <input type="text" name="doc_title" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Document Type</label>
                    <select name="doc_type" required class="form-control">
                        <option value="onboarding">Onboarding Document</option>
                        <option value="termination">Termination Document</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Upload PDF Template</label>
                    <input type="file" name="document" accept=".pdf" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Upload Template</button>
            </form>
        </div>

        <!-- Templates List -->
        <div class="templates-list">
            <h3>Existing Templates</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $template): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($template['doc_title']); ?></td>
                            <td><?php echo htmlspecialchars($template['doc_type']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($template['created_at'])); ?></td>
                            <td><?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <a href="view_template.php?id=<?php echo $template['template_id']; ?>"
                                    class="btn btn-sm btn-info">View</a>
                                <a href="assign_template.php?id=<?php echo $template['template_id']; ?>"
                                    class="btn btn-sm btn-primary">Assign</a>
                                <button onclick="toggleStatus(<?php echo $template['template_id']; ?>)"
                                    class="btn btn-sm btn-warning">Toggle Status</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                    <a class="btn btn-success" href="http://localhost/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript -->
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
    <script>
        function toggleStatus(templateId) {
            if (confirm('Are you sure you want to change the status of this template?')) {
                // Add AJAX call to toggle status
                fetch('toggle_status.php?id=' + templateId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }
        }
    </script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#dataTable').DataTable();

            // Custom file input
            $(".custom-file-input").on("change", function () {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
        });
    </script>
</body>

</html>