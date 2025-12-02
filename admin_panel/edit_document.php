<?php
include('session.php');
include('connection.php');

if (!isset($_GET['id'])) {
    header('Location: view_documents.php');
    exit;
}

$id = mysqli_real_escape_string($con, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_type = $_POST['document_type'];
    $document_category = $_POST['document_category'];
    $nationality = $_POST['nationality'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;

    // Handle file upload if a new file is provided
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
                $new_filename = date('Ymd') . '_' . $document['eid'] . '_' . $document_category . '_' . uniqid() . '.pdf';
                $upload_path = 'doc_uploads/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old file if exists
                    if (file_exists($document['file_path'])) {
                        unlink($document['file_path']);
                    }

                    // Update document with new file information
                    $query = "UPDATE document_uploads 
                             SET document_type = ?, document_category = ?, nationality = ?, 
                                 expiry_date = ?, file_name = ?, file_path = ?, file_size = ? 
                             WHERE id = ?";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("ssssssii", $document_type, $document_category, $nationality, 
                                    $expiry_date, $file['name'], $upload_path, $file['size'], $id);
                } else {
                    $error = "Error moving uploaded file";
                }
            }
        }
    } else {
        // Update document without changing the file
        $query = "UPDATE document_uploads 
                  SET document_type = ?, document_category = ?, nationality = ?, expiry_date = ? 
                  WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssi", $document_type, $document_category, $nationality, $expiry_date, $id);
    }

    if (!isset($error) && $stmt->execute()) {
        $success = "Document updated successfully!";
    } else if (!isset($error)) {
        $error = "Error updating document: " . $stmt->error;
    }
}

// Get document details
$query = "SELECT d.*, e.full_name 
          FROM document_uploads d 
          LEFT JOIN employees e ON d.eid = e.eid 
          WHERE d.id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
    header('Location: view_documents.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Document</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .edit-container {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .preview-container {
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 1rem;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Document</h1>
            <a href="view_documents.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to List
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="edit-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employee</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($document['full_name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Document Category</label>
                            <select name="document_category" class="form-control" required>
                                <option value="visa" <?php echo $document['document_category'] === 'visa' ? 'selected' : ''; ?>>Visa</option>
                                <option value="passport" <?php echo $document['document_category'] === 'passport' ? 'selected' : ''; ?>>Passport</option>
                                <option value="emirates_id" <?php echo $document['document_category'] === 'emirates_id' ? 'selected' : ''; ?>>Emirates ID</option>
                                <option value="labour_card" <?php echo $document['document_category'] === 'labour_card' ? 'selected' : ''; ?>>Labour Card</option>
                                <option value="national_id" <?php echo $document['document_category'] === 'national_id' ? 'selected' : ''; ?>>National ID Card</option>
                                <option value="insurance" <?php echo $document['document_category'] === 'insurance' ? 'selected' : ''; ?>>Insurance</option>
                                <option value="onboarding" <?php echo $document['document_category'] === 'onboarding' ? 'selected' : ''; ?>>Onboarding Documents</option>
                                <option value="policy" <?php echo $document['document_category'] === 'policy' ? 'selected' : ''; ?>>Policy Documents</option>
                                <option value="education" <?php echo $document['document_category'] === 'education' ? 'selected' : ''; ?>>Educational Certificates</option>
                                <option value="other" <?php echo $document['document_category'] === 'other' ? 'selected' : ''; ?>>Other Documents</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Document Type</label>
                            <input type="text" name="document_type" class="form-control" value="<?php echo htmlspecialchars($document['document_type']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Nationality (if applicable)</label>
                            <input type="text" name="nationality" class="form-control" value="<?php echo htmlspecialchars($document['nationality']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Expiry Date (if applicable)</label>
                            <input type="date" name="expiry_date" class="form-control" value="<?php echo $document['expiry_date']; ?>">
                        </div>

                        <div class="document-upload-section mt-4">
                            <h5>Update Document File</h5>
                            <div class="alert alert-info">
                                <small>Leave empty to keep the existing file. Only PDF files up to 300KB are allowed.</small>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="document" name="document" accept=".pdf">
                                <label class="custom-file-label" for="document">Choose file</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">
                            <i class="fas fa-save fa-sm"></i> Save Changes
                        </button>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Document Preview</h5>
                        <div class="preview-container">
                            <embed src="<?php echo htmlspecialchars($document['file_path']); ?>" type="application/pdf" width="100%" height="100%">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add this script to show selected filename
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
</body>
</html>