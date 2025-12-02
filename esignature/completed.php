<?php
require_once '../config.php';
require_once '../connection.php';
session_start();

// Check if user is logged in (handle all user types)
if (!isset($_SESSION['eid']) && !isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Set user_id based on user type
$user_id = isset($_SESSION['eid']) ? $_SESSION['eid'] : $_SESSION['admin_id'];

// Get completed documents
$query = "SELECT d.*, e.name as creator_name,
          (SELECT GROUP_CONCAT(CONCAT(e2.name, ' (', r.role_name, ')') SEPARATOR ' → ')
           FROM esign_workflow w2
           INNER JOIN employees e2 ON w2.approver_id = e2.id
           INNER JOIN roles r ON w2.role_id = r.id
           WHERE w2.document_id = d.id
           ORDER BY w2.level) as approval_flow
          FROM esign_documents d 
          INNER JOIN employees e ON d.created_by = e.id
          WHERE d.status IN ('completed', 'rejected')
          AND (d.created_by = ? OR EXISTS (
              SELECT 1 FROM esign_workflow w 
              WHERE w.document_id = d.id AND w.approver_id = ?
          ))
          ORDER BY d.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .document-card {
            transition: transform 0.2s;
        }
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .approval-flow {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Completed Documents</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (empty($documents)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No completed documents found.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($documents as $doc): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card document-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($doc['title']); ?></h5>
                                    <span class="badge <?php echo $doc['status'] === 'completed' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($doc['description']); ?>
                                </p>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Created by: <?php echo htmlspecialchars($doc['creator_name']); ?>
                                    </small>
                                </div>
                                
                                <div class="approval-flow mb-3">
                                    <small>
                                        <i class="fas fa-random"></i> Approval Flow:<br>
                                        <?php echo htmlspecialchars($doc['approval_flow']); ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="view.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($doc['file_type'] === 'pdf'): ?>
                                        <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 