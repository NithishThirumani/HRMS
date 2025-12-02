<?php
include('session.php');
include('connection.php');

if (!isset($_GET['id'])) {
    header('Location: view_documents.php');
    exit;
}

$id = mysqli_real_escape_string($con, $_GET['id']);
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
    <title>View Document</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .document-container {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .document-details {
            margin-bottom: 2rem;
        }

        .document-preview {
            height: 800px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Document Details</h1>
            <div>
                <a href="edit_document.php?id=<?php echo $document['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit fa-sm"></i> Edit Document
                </a>
                <a href="view_documents.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left fa-sm"></i> Back to List
                </a>
            </div>
        </div>

        <div class="document-container">
            <div class="row">
                <div class="col-md-4">
                    <div class="document-details">
                        <h5>Document Information</h5>
                        <hr>
                        <p><strong>Employee:</strong> <?php echo htmlspecialchars($document['full_name']); ?></p>
                        <p><strong>Category:</strong>
                            <?php echo ucfirst(htmlspecialchars($document['document_category'])); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($document['document_type']); ?></p>
                        <p><strong>Upload Date:</strong>
                            <?php echo date('d M Y', strtotime($document['upload_date'])); ?></p>
                        <?php if ($document['expiry_date']): ?>
                            <p><strong>Expiry Date:</strong>
                                <?php echo date('d M Y', strtotime($document['expiry_date'])); ?></p>
                        <?php endif; ?>
                        <?php if ($document['nationality']): ?>
                            <p><strong>Nationality:</strong> <?php echo htmlspecialchars($document['nationality']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="document-preview">
                        <embed src="<?php echo htmlspecialchars($document['file_path']); ?>" type="application/pdf"
                            width="100%" height="100%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>