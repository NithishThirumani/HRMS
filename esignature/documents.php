<?php
require_once '../config.php';
require_once '../connection.php';
require_once 'includes/auth_user.php';
session_start();

$resolved = esign_resolve_user($con);
if (!$resolved) {
    header('Location: /emps/login.php');
    exit();
}

$user = $resolved['user'];
$user_type = $resolved['user_type'];
$user_id = (int)$user['id'];
$user_name = $user['full_name'] ?? ($user['email'] ?? 'User');

$pending_query = "SELECT COUNT(*) AS count FROM esign_documents d
                 INNER JOIN esign_workflow w ON d.id = w.document_id
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $con->prepare("SELECT * FROM esign_documents WHERE is_deleted = 0 ORDER BY created_at DESC");
$stmt->execute();
$docs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - E-Signature</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-left: 250px; background: #f8f9fa; }
        .main-content { padding: 20px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0">All Documents</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($doc = $docs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$doc['id']; ?></td>
                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                <td><?php echo htmlspecialchars($doc['document_type'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($doc['created_at']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($doc['status']); ?></span></td>
                                <td>
                                    <a href="view.php?id=<?php echo (int)$doc['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="workflow.php?id=<?php echo (int)$doc['id']; ?>" class="btn btn-sm btn-primary">Workflow</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
