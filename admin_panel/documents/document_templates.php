<?php
require_once '../includes/config.php';
require_once '../classes/DocumentManager.php';

$documentManager = new DocumentManager($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && isset($_POST['doc_title']) && isset($_POST['doc_type'])) {
        $uploaded = $documentManager->uploadTemplate(
            $_POST['doc_title'],
            $_POST['doc_type'],
            $_FILES['document']
        );
        if ($uploaded) {
            $_SESSION['success'] = "Document template uploaded successfully";
        } else {
            $_SESSION['error'] = "Failed to upload document template";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Document Templates Management</title>
    <!-- Include your CSS files here -->
</head>

<body>
    <div class="container">
        <h2>Upload Document Template</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Document Title</label>
                <input type="text" name="doc_title" required class="form-control">
            </div>

            <div class="form-group">
                <label>Document Type</label>
                <select name="doc_type" required class="form-control">
                    <option value="onboarding">Onboarding</option>
                    <option value="termination">Termination</option>
                </select>
            </div>

            <div class="form-group">
                <label>Document File (PDF)</label>
                <input type="file" name="document" accept=".pdf" required class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Upload Template</button>
        </form>
    </div>
    <script src="../js/datetime.js"></script>
</body>

</html>