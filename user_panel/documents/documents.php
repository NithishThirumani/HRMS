<?php
include('../connection.php');
include('../session.php');
require_once '../../classes/DocumentManager.php';

$documentManager = new DocumentManager($con);

// Get employee ID from session
$empId = $_SESSION['eid']; // Adjust according to your session variable

// Get all documents assigned to the employee
$documents = $documentManager->getEmployeeDocuments($empId);
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Documents</title>
    <!-- Include your CSS files here -->
</head>

<body>
    <div class="container">
        <h2>My Documents</h2>
        <div class="documents-list">
            <?php foreach ($documents as $doc): ?>
                <div class="document-item">
                    <h4><?php echo htmlspecialchars($doc['doc_title']); ?></h4>
                    <p>Status: <?php echo htmlspecialchars($doc['document_status']); ?></p>
                    <?php if ($doc['document_status'] === 'pending'): ?>
                        <a href="sign_document.php?doc_id=<?php echo $doc['doc_id']; ?>" class="btn btn-primary">Sign
                            Document</a>
                    <?php else: ?>
                        <a href="view_document.php?doc_id=<?php echo $doc['doc_id']; ?>" class="btn btn-secondary">View
                            Document</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>