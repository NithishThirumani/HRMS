<?php
require_once '../config.php';
require_once '../connection.php';
session_start();

$document_id = $_GET['id'] ?? 0;
if (!$document_id) {
    echo "Invalid document ID.";
    exit;
}

// Fetch the document
$doc_stmt = $con->prepare("SELECT * FROM esign_documents WHERE id = ?");
$doc_stmt->bind_param("i", $document_id);
$doc_stmt->execute();
$doc = $doc_stmt->get_result()->fetch_assoc();

if (!$doc) {
    echo '<div class="alert alert-danger">Document not found.</div>';
    exit;
}

// Fetch all workflow steps for this document (no joins)
$workflow = [];
$workflow_stmt = $con->prepare("SELECT * FROM esign_workflow WHERE document_id = ? ORDER BY level ASC");
$workflow_stmt->bind_param("i", $document_id);
$workflow_stmt->execute();
$workflow = $workflow_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all comments for this document (no joins)
$comments = [];
$comments_stmt = $con->prepare("SELECT * FROM esign_comments WHERE document_id = ? ORDER BY created_at ASC");
$comments_stmt->bind_param("i", $document_id);
$comments_stmt->execute();
$comments = $comments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Helper function to get approver name by id and type
function getApproverName($con, $id, $type) {
    if (!$id) return '-';
    if ($type === 'emp') {
        $stmt = $con->prepare("SELECT full_name FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['full_name'] ?? '-';
    } elseif ($type === 'admin') {
        $stmt = $con->prepare("SELECT user_name FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['user_name'] ?? '-';
    } elseif ($type === 'head') {
        $stmt = $con->prepare("SELECT head_name FROM department_heads WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['head_name'] ?? '-';
    }
    return '-';
}

function sentenceCase(
    $string
) {
    $string = str_replace('_', ' ', strtolower($string));
    return ucfirst($string);
}

function getApproverTypeName($type) {
    if ($type === 'emp') return 'Employee';
    if ($type === 'admin') return 'Admin';
    if ($type === 'head') return 'Department Head';
    return ucfirst($type);
}

function getDaysPending($created_at) {
    $created = new DateTime($created_at);
    $now = new DateTime();
    return $created->diff($now)->days;
}

// Set user_type and user_name for sidebar
$user_type = null;
$user_name = '';
if (isset($_SESSION['email'])) {
    // Check admin
    $stmt = $con->prepare("SELECT id, user_name, email, role FROM admin WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin) {
        $user_type = 'admin';
        $user_name = $admin['user_name'];
    } else {
        // Check department head
        $stmt = $con->prepare("SELECT id, head_name, head_email as email FROM department_heads WHERE head_email = ?");
        $stmt->bind_param("s", $_SESSION['email']);
        $stmt->execute();
        $dh = $stmt->get_result()->fetch_assoc();
        if ($dh) {
            $user_type = 'department_head';
            $user_name = $dh['head_name'];
        } else {
            // Check employee
            $stmt = $con->prepare("SELECT id, full_name, email FROM employees WHERE email = ? AND status = 'active'");
            $stmt->bind_param("s", $_SESSION['email']);
            $stmt->execute();
            $emp = $stmt->get_result()->fetch_assoc();
            if ($emp) {
                $user_type = 'employee';
                $user_name = $emp['full_name'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Workflow for <?= htmlspecialchars($doc['title'] ?? 'Document') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="container mt-5">
    <h2>Document Workflow</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h4><?= htmlspecialchars($doc['title']) ?></h4>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($doc['description'])) ?></p>
            <p><strong>Created By (ID):</strong> <?= htmlspecialchars($doc['created_by']) ?></p>
            <p><strong>Created At:</strong> <?= htmlspecialchars($doc['created_at']) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($doc['status']) ?></p>
        </div>
    </div>
    <h5>Workflow Stages</h5>
    <?php if (empty($workflow)): ?>
        <div class="alert alert-warning">No workflow steps found for this document.</div>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <?php
                $headers = array_keys($workflow[0]);
                foreach ($headers as $col) {
                    if ($col === 'approver_id') {
                        echo '<th>Approver name</th>';
                    } elseif ($col === 'approver_type') {
                        echo '<th>Approver type</th>';
                    } else {
                        echo '<th>' . sentenceCase($col) . '</th>';
                    }
                }
                echo '<th>Pending with</th>';
                echo '<th>Days pending</th>';
                ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($workflow as $step): ?>
            <tr>
                <?php foreach ($step as $key => $val): ?>
                    <?php if ($key === 'approver_id'): ?>
                        <td><?= htmlspecialchars(getApproverName($con, $val, $step['approver_type'])) ?></td>
                    <?php elseif ($key === 'approver_type'): ?>
                        <td><?= htmlspecialchars(getApproverTypeName($val)) ?></td>
                    <?php else: ?>
                        <td><?= sentenceCase($val ?? '') ?></td>
                    <?php endif; ?>
                <?php endforeach; ?>
                <td>
                    <?php if ($step['status'] === 'pending'): ?>
                        <?= htmlspecialchars(getApproverName($con, $step['approver_id'], $step['approver_type'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(getDaysPending($step['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <h5 class="mt-5">All Comments (Raw Data)</h5>
    <?php if (empty($comments)): ?>
        <div class="alert alert-info">No comments or actions yet.</div>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <?php foreach (array_keys($comments[0]) as $col): ?>
                    <th><?= ucfirst(str_replace('_', ' ', $col)) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $c): ?>
            <tr>
                <?php foreach ($c as $val): ?>
                    <td><?= htmlspecialchars($val ?? '') ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html> 