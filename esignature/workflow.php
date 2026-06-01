<?php
require_once '../config.php';
require_once '../connection.php';
require_once 'includes/auth_user.php';
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: /emps/login.php');
    exit();
}

$resolved = esign_resolve_user($con);
if (!$resolved) {
    header('Location: /emps/login.php');
    exit();
}

$user = $resolved['user'];
$user_type = $resolved['user_type'];
$user_id = (int)$user['id'];
$user_name = $user['full_name'] ?? ($user['email'] ?? 'User');

$document_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($document_id <= 0) {
    header('Location: documents.php');
    exit();
}

$doc_stmt = $con->prepare('SELECT * FROM esign_documents WHERE id = ? AND is_deleted = 0 LIMIT 1');
$doc_stmt->bind_param('i', $document_id);
$doc_stmt->execute();
$doc = $doc_stmt->get_result()->fetch_assoc();

if (!$doc) {
    header('Location: documents.php');
    exit();
}

$workflow_stmt = $con->prepare('SELECT * FROM esign_workflow WHERE document_id = ? ORDER BY level ASC');
$workflow_stmt->bind_param('i', $document_id);
$workflow_stmt->execute();
$workflow = $workflow_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$comments_stmt = $con->prepare('SELECT c.*, COALESCE(e.full_name, a.user_name, "User") AS author_name
    FROM esign_comments c
    LEFT JOIN employees e ON c.user_id = e.id
    LEFT JOIN admin a ON c.user_id = a.id
    WHERE c.document_id = ?
    ORDER BY c.created_at ASC');
$comments_stmt->bind_param('i', $document_id);
$comments_stmt->execute();
$comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pending_query = "SELECT COUNT(*) AS count FROM esign_documents d
                 INNER JOIN esign_workflow w ON d.id = w.document_id
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

$creator_name = esign_creator_name($con, (int)$doc['created_by']);

function esign_status_badge(string $status): string
{
    $map = [
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        'completed' => 'bg-success',
        'in_progress' => 'bg-info text-dark',
    ];
    $class = $map[strtolower($status)] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow - <?php echo htmlspecialchars($doc['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-left: 250px; background: #f8f9fa; min-height: 100vh; }
        .main-content { padding: 24px; max-width: 1200px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .card-header { background: #fff; border-bottom: 1px solid #eee; font-weight: 600; }
        .workflow-step { border-left: 4px solid #dee2e6; padding: 1rem 1.25rem; margin-bottom: 1rem; background: #fff; border-radius: 0 8px 8px 0; }
        .workflow-step.pending { border-left-color: #ffc107; }
        .workflow-step.approved { border-left-color: #198754; }
        .workflow-step.rejected { border-left-color: #dc3545; }
        .meta-label { color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.03em; }
        @media (max-width: 768px) {
            body { padding-left: 0; padding-top: 60px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h2 class="mb-1">Document Workflow</h2>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($doc['title']); ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="view.php?id=<?php echo $document_id; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View Document</a>
                <a href="documents.php" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All Documents</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3">Document Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($doc['description'] ?? '')); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2"><span class="meta-label">Created by</span><br><?php echo htmlspecialchars($creator_name); ?></div>
                        <div class="mb-2"><span class="meta-label">Created at</span><br><?php echo htmlspecialchars($doc['created_at']); ?></div>
                        <div class="mb-2"><span class="meta-label">Status</span><br><?php echo esign_status_badge($doc['status']); ?></div>
                        <div><span class="meta-label">Approval levels</span><br><?php echo (int)($doc['total_levels'] ?? count($workflow)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3">Approval Stages</div>
            <div class="card-body">
                <?php if (empty($workflow)): ?>
                    <div class="alert alert-warning mb-0">No workflow steps found for this document.</div>
                <?php else: ?>
                    <?php foreach ($workflow as $step): ?>
                        <?php
                        $status = strtolower($step['status'] ?? 'pending');
                        $approverName = esign_approver_name($con, (int)$step['approver_id'], $step['approver_type'] ?? '');
                        $typeLabel = ucfirst(str_replace('_', ' ', $step['approver_type'] ?? 'approver'));
                        ?>
                        <div class="workflow-step <?php echo htmlspecialchars($status); ?>">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <strong>Level <?php echo (int)$step['level'] + 1; ?></strong>
                                    <span class="text-muted"> — <?php echo htmlspecialchars($approverName); ?></span>
                                    <div class="small text-muted"><?php echo htmlspecialchars($typeLabel); ?></div>
                                </div>
                                <div><?php echo esign_status_badge($status); ?></div>
                            </div>
                            <?php if (!empty($step['signed_at'])): ?>
                                <div class="small text-muted mt-2"><i class="fas fa-clock"></i> Signed: <?php echo htmlspecialchars($step['signed_at']); ?></div>
                            <?php elseif ($status === 'pending'): ?>
                                <div class="small text-warning mt-2"><i class="fas fa-hourglass-half"></i> Awaiting signature from <?php echo htmlspecialchars($approverName); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($step['comments'])): ?>
                                <div class="mt-2 p-2 bg-light rounded small"><strong>Comment:</strong> <?php echo htmlspecialchars($step['comments']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3">Comments &amp; Activity</div>
            <div class="card-body">
                <?php if (empty($comments)): ?>
                    <div class="alert alert-info mb-0">No comments or actions yet.</div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <strong><?php echo htmlspecialchars($comment['author_name'] ?? 'User'); ?></strong>
                            <span class="text-muted small ms-2"><?php echo htmlspecialchars($comment['created_at'] ?? ''); ?></span>
                            <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($comment['comment'] ?? '')); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
