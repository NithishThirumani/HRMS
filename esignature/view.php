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

$stmt = $con->prepare('SELECT * FROM esign_documents WHERE id = ? AND is_deleted = 0 LIMIT 1');
$stmt->bind_param('i', $document_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    header('Location: documents.php');
    exit();
}

$allowed = false;
if ($user_type === 'admin') {
    $allowed = true;
} elseif ($user_type === 'employee') {
    $allowed = ((int)$document['created_by'] === $user_id);
    if (!$allowed) {
        $check = $con->prepare('SELECT 1 FROM esign_workflow WHERE document_id = ? AND approver_id = ? LIMIT 1');
        $check->bind_param('ii', $document_id, $user_id);
        $check->execute();
        $allowed = (bool)$check->get_result()->fetch_assoc();
    }
} elseif ($user_type === 'department_head') {
    $check = $con->prepare('SELECT 1 FROM esign_workflow w
        LEFT JOIN employees e ON w.approver_id = e.id AND w.approver_type IN ("emp", "employee")
        LEFT JOIN department_heads dh ON w.approver_id = dh.id AND w.approver_type IN ("head", "department_head")
        WHERE w.document_id = ? AND (e.department_id = ? OR dh.department_id = ?) LIMIT 1');
    $deptId = (int)$user['department_id'];
    $check->bind_param('iii', $document_id, $deptId, $deptId);
    $check->execute();
    $allowed = (bool)$check->get_result()->fetch_assoc();
}

if (!$allowed) {
    header('Location: documents.php');
    exit();
}

$document['creator_name'] = esign_creator_name($con, (int)$document['created_by']);

$wfStmt = $con->prepare('SELECT * FROM esign_workflow WHERE document_id = ? ORDER BY level ASC');
$wfStmt->bind_param('i', $document_id);
$wfStmt->execute();
$signatures = $wfStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$approvalParts = [];
foreach ($signatures as $sig) {
    $name = esign_approver_name($con, (int)$sig['approver_id'], $sig['approver_type'] ?? '');
    $when = !empty($sig['signed_at']) ? ' on ' . date('Y-m-d H:i', strtotime($sig['signed_at'])) : '';
    $approvalParts[] = $name . ' - ' . ($sig['status'] ?? 'pending') . $when;
}
$document['approval_flow'] = implode(' → ', $approvalParts);

foreach ($signatures as &$sig) {
    $sig['approver_name'] = esign_approver_name($con, (int)$sig['approver_id'], $sig['approver_type'] ?? '');
    $sig['role_name'] = ucfirst(str_replace('_', ' ', (string)($sig['approver_type'] ?? 'approver')));
}
unset($sig);

$pending_query = "SELECT COUNT(*) AS count FROM esign_documents d
                 INNER JOIN esign_workflow w ON d.id = w.document_id
                 WHERE w.approver_id = ? AND w.status = 'pending'";
$stmt = $con->prepare($pending_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - <?php echo htmlspecialchars($document['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-left: 250px; background: #f8f9fa; }
        .main-content { padding: 20px; }
        .signature-container { border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; margin-bottom: 15px; }
        .signature-image { max-width: 200px; max-height: 100px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo htmlspecialchars($document['title']); ?></h2>
            <div>
                <a href="documents.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> All Documents</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <p class="text-muted"><?php echo htmlspecialchars($document['description'] ?? ''); ?></p>
                        <p><small class="text-muted"><i class="fas fa-user"></i> Created by: <?php echo htmlspecialchars($document['creator_name']); ?></small></p>
                        <p><small class="text-muted"><i class="fas fa-clock"></i> Created: <?php echo date('Y-m-d H:i', strtotime($document['created_at'])); ?></small></p>
                        <p><small class="text-muted"><i class="fas fa-random"></i> Approval Flow: <?php echo htmlspecialchars($document['approval_flow']); ?></small></p>
                        <?php if (!empty($document['file_path'])): ?>
                            <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" width="100%" height="600px"></iframe>
                        <?php else: ?>
                            <div class="alert alert-warning">Document file not found on server.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Signatures</h5>
                        <?php foreach ($signatures as $signature): ?>
                            <div class="signature-container">
                                <strong><?php echo htmlspecialchars($signature['approver_name']); ?></strong>
                                <div class="text-muted"><?php echo htmlspecialchars($signature['role_name']); ?></div>
                                <span class="badge <?php echo $signature['status'] === 'approved' ? 'bg-success' : ($signature['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?php echo ucfirst($signature['status']); ?>
                                </span>
                                <?php if (!empty($signature['signature_data'])): ?>
                                    <div class="mt-2">
                                        <img src="/emps/esignature/<?php echo htmlspecialchars($signature['signature_data']); ?>" class="signature-image" alt="Signature">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
