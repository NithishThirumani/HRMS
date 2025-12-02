<?php
include('session.php');
include('connection.php');

// Create upload directory if it doesn't exist
$upload_dir = 'doc_uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid = $_POST['eid'];

    // Verify employee exists - Modified query to debug
    $verify_query = "SELECT id FROM employees WHERE eid = ?";
    $stmt = $con->prepare($verify_query);
    $stmt->bind_param("s", $eid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Invalid employee selected";
    } else {
        $document_type = $_POST['document_type'];
        $document_category = $_POST['document_category'];
        $nationality = $_POST['nationality'] ?? null;
        $expiry_date = $_POST['expiry_date'] ?? null;

        if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
            $file = $_FILES['document'];

            // Validate file size (300KB limit)
            if ($file['size'] > 300000) {
                $error = "File size must be less than 300KB";
            } else {
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($file_ext !== 'pdf') {
                    $error = "Only PDF files are allowed";
                } else {
                    // Create filename with employee EID
                    $new_filename = date('Ymd') . '_' . $eid . '_' . $document_category . '_' . uniqid() . '.pdf';
                    $upload_path = $upload_dir . '/' . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $query = "INSERT INTO document_uploads (eid, document_type, file_name, file_path, document_category, nationality, expiry_date, file_size) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("sssssssi", $eid, $document_type, $file['name'], $upload_path, $document_category, $nationality, $expiry_date, $file['size']);

                        if ($stmt->execute()) {
                            $success = "Document uploaded successfully!";
                        } else {
                            $error = "Error uploading document to database: " . $stmt->error;
                        }
                    } else {
                        $error = "Error moving uploaded file";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Document Upload</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        .upload-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .document-type-card {
            border: 2px dashed #e3e6f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .document-type-card:hover {
            border-color: #4e73df;
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 2rem;
            color: #4e73df;
            margin-bottom: 1rem;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
        }

        .success-message {
            background-color: #1cc88a;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .error-message {
            background-color: #e74a3b;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>

    <!-- Add jQuery if not already included -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Add Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: 'Select Employee',
                width: '100%'
            });
        });
    </script>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Document Upload</h1>
        </div>
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="upload-container">
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Employee</label>
                            <select name="eid" class="form-control select2" required>
                                <option value="">Select Employee</option>
                                <?php
                                // Add error reporting for debugging
                                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                                try {
                                    $query = "SELECT id, eid, full_name FROM employees ORDER BY full_name";
                                    $result = mysqli_query($con, $query);

                                    if (!$result) {
                                        throw new Exception("Query failed: " . mysqli_error($con));
                                    }

                                    $count = mysqli_num_rows($result);
                                    if ($count > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='" . htmlspecialchars($row['eid']) . "'>"
                                                . htmlspecialchars($row['full_name'])
                                                . " (" . htmlspecialchars($row['eid']) . ")</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No employees found</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>Error: " . htmlspecialchars($e->getMessage()) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Document Category</label>
                            <select name="document_category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="visa">Visa</option>
                                <option value="passport">Passport</option>
                                <option value="emirates_id">Emirates ID</option>
                                <option value="labour_card">Labour Card</option>
                                <option value="national_id">National ID Card</option>
                                <option value="insurance">Insurance</option>
                                <option value="onboarding">Onboarding Documents</option>
                                <option value="policy">Policy Documents</option>
                                <option value="education">Educational Certificates</option>
                                <option value="other">Other Documents</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="visa-details" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Visa Type</label>
                                <select name="document_type" class="form-control">
                                    <option value="ISE">ISE</option>
                                    <option value="TXM">TXM</option>
                                    <option value="Visit">Visit</option>
                                    <option value="Communik">Communik</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="passport-details" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" name="nationality" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Expiry Date (if applicable)</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
                <div class="document-type-card">
                    <div class="text-center">
                        <i class="fas fa-file-upload upload-icon"></i>
                        <h5>Upload Document</h5>
                        <p class="text-muted">Maximum file size: 300KB, Format: PDF only</p>
                    </div>
                    <input type="file" name="document" class="form-control" accept=".pdf" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-upload mr-2"></i>Upload Document
                </button>
            </form>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: 'Select Employee',
                width: '100%',
                allowClear: true,
                dropdownParent: $('body'),
                language: {
                    noResults: function () {
                        return "No employees found";
                    }
                }
            }).on('select2:open', function () {
                // Force a refresh when dropdown opens
                $(this).trigger('change');
            });

            // Document category change handler
            $('select[name="document_category"]').change(function () {
                const category = $(this).val();
                $('.visa-details, .passport-details').hide();

                if (category === 'visa') {
                    $('.visa-details').show();
                } else if (category === 'passport') {
                    $('.passport-details').show();
                }
            });
        });
    </script>
</body>

</html>