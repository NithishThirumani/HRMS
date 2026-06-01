<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Attendance - Admin Panel</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upload Attendance Data</h6>
            </div>
            <div class="card-body">
                <!-- Upload Form -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="process_attendance_upload.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Upload Excel/CSV File</label>
                                <input type="file" class="form-control" name="attendance_file" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="form-group">
                                <label>Attendance date <span class="text-muted">(required for employee list files)</span></label>
                                <input type="date" class="form-control" name="import_date" value="<?php echo date('Y-m-d'); ?>">
                                <small class="form-text text-muted">If your file is an <strong>employee list</strong> (Employee ID, Name, Department…), pick the date to mark attendance for all listed employees.</small>
                            </div>
                            <div class="alert alert-info">
                                <h6>Supported formats</h6>
                                <p><strong>Option A — Attendance file:</strong> columns <code>eid</code>, <code>attendance_date</code>, <code>status</code></p>
                                <p class="mb-0"><strong>Option B — Employee list</strong> (like <em>employee list.xlsx</em>): columns <code>Employee ID</code>, etc. + choose <strong>Attendance date</strong> above. Status column <code>Active</code> = Present.</p>
                            </div>
                            <?php if (!empty($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">Upload Attendance Data</button>
                        </form>
                    </div>
                </div>

                <!-- Sample Template Download -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Download Template</h5>
                                <p class="card-text">Download the sample Excel template for attendance upload.</p>
                                <a href="templates/attendance_template.xlsx" class="btn btn-success">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>