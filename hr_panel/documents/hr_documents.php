<?php
include('../connection.php');
include('../session.php');

// The session.php already verifies HR role, so we can proceed directly
// Remove the redundant role verification since session.php handles it

// Get documents needing HR signature using esign_documents and esign_workflow tables
$stmt = $con->prepare("SELECT 
    ed.id as document_id,
    ed.title as doc_title,
    ed.description,
    ed.file_path,
    ed.document_type,
    ed.created_at as assigned_date,
    ed.status as document_status,
    ed.current_level,
    ed.total_levels,
    ew.id as workflow_id,
    ew.level as workflow_level,
    ew.status as workflow_status,
    ew.signed_at,
    ew.signature_data,
    ew.approver_type,
    e.full_name as employee_name,
    e.eid as employee_id,
    CASE 
        WHEN ew.status = 'pending' AND ew.approver_type = 'head' THEN 'pending_hr'
        WHEN ew.status = 'pending' AND ew.approver_type = 'emp' THEN 'pending_employee'
        WHEN ew.status = 'approved' OR ed.status = 'completed' THEN 'completed'
        ELSE 'other'
    END as status_label
FROM esign_documents ed
LEFT JOIN esign_workflow ew ON ed.id = ew.document_id
LEFT JOIN employees e ON ew.approver_id = e.id
WHERE ed.is_deleted = 0 
AND ed.status != 'draft'
AND (ew.approver_type = 'head' OR ew.approver_type = 'emp')
ORDER BY 
    CASE 
        WHEN ew.status = 'pending' AND ew.approver_type = 'head' THEN 1
        WHEN ew.status = 'pending' AND ew.approver_type = 'emp' THEN 2
        WHEN ew.status = 'approved' OR ed.status = 'completed' THEN 3
        ELSE 4
    END,
    ed.created_at DESC");
$stmt->execute();
$documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>HR Documents</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body class="sb-nav-fixed">
    <?php include('../sidebar.php'); ?>
    <?php include('../header.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Documents Requiring HR Signature</h1>

                <div class="card mb-4">
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="docTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pending-hr-tab" data-toggle="tab" href="#pending_hr"
                                    role="tab">
                                    Pending HR Signature
                                    <span
                                        class="badge badge-primary ml-1"><?= count(array_filter($documents, fn($d) => $d['status_label'] === 'pending_hr')) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pending-emp-tab" data-toggle="tab" href="#pending_employee"
                                    role="tab">
                                    Pending Employee
                                    <span
                                        class="badge badge-warning ml-1"><?= count(array_filter($documents, fn($d) => $d['status_label'] === 'pending_employee')) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed"
                                    role="tab">
                                    Completed
                                    <span
                                        class="badge badge-success ml-1"><?= count(array_filter($documents, fn($d) => $d['status_label'] === 'completed')) ?></span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="docTabContent">
                            <!-- Pending HR Tab -->
                            <div class="tab-pane fade show active" id="pending_hr" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="background-color: #2c3e50; color: white;">Doc ID</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Title</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Type</th>
                                                <th style="background-color: #2c3e50; color: white;">Employee</th>
                                                <th style="background-color: #2c3e50; color: white;">Created Date</th>
                                                <th style="background-color: #2c3e50; color: white;">Current Level</th>
                                                <th style="background-color: #2c3e50; color: white;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($documents as $doc): ?>
                                                <?php if ($doc['status_label'] === 'pending_hr'): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['document_id']) ?></td>
                                                        <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['document_type']) ?></td>
                                                        <td><?= htmlspecialchars(ucwords(strtolower($doc['employee_name'] ?? 'N/A'))) ?></td>
                                                        <td><?= date('M j, Y', strtotime($doc['assigned_date'])) ?></td>
                                                        <td><?= $doc['current_level'] ?>/<?= $doc['total_levels'] ?></td>
                                                        <td>
                                                            <a href="view_document.php?id=<?= $doc['document_id'] ?>"
                                                                class="btn btn-sm"
                                                                style="background-color: #2c3e50; color: white;">
                                                                <i class="fas fa-signature"></i>
                                                                Review & Sign
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pending Employee Tab -->
                            <div class="tab-pane fade" id="pending_employee" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="background-color: #2c3e50; color: white;">Document ID</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Title</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Type</th>
                                                <th style="background-color: #2c3e50; color: white;">Employee</th>
                                                <th style="background-color: #2c3e50; color: white;">Created Date</th>
                                                <th style="background-color: #2c3e50; color: white;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($documents as $doc): ?>
                                                <?php if ($doc['status_label'] === 'pending_employee'): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['document_id']) ?></td>
                                                        <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['document_type']) ?></td>
                                                        <td><?= htmlspecialchars($doc['employee_name'] ?? 'N/A') ?></td>
                                                        <td><?= date('M j, Y', strtotime($doc['assigned_date'])) ?></td>
                                                        <td>
                                                            <span class="badge" style="background-color: #e67e22;">
                                                                Awaiting Employee
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Completed Tab -->
                            <div class="tab-pane fade" id="completed" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="background-color: #2c3e50; color: white;">Document ID</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Title</th>
                                                <th style="background-color: #2c3e50; color: white;">Document Type</th>
                                                <th style="background-color: #2c3e50; color: white;">Employee</th>
                                                <th style="background-color: #2c3e50; color: white;">Created Date</th>
                                                <th style="background-color: #2c3e50; color: white;">Signed Date</th>
                                                <th style="background-color: #2c3e50; color: white;">Status</th>
                                                <th style="background-color: #2c3e50; color: white;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($documents as $doc): ?>
                                                <?php if ($doc['status_label'] === 'completed'): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($doc['document_id']) ?></td>
                                                        <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                                        <td><?= htmlspecialchars($doc['document_type']) ?></td>
                                                        <td><?= htmlspecialchars($doc['employee_name'] ?? 'N/A') ?></td>
                                                        <td><?= date('M j, Y', strtotime($doc['assigned_date'])) ?></td>
                                                        <td><?= $doc['signed_at'] ? date('M j, Y H:i', strtotime($doc['signed_at'])) : 'N/A' ?></td>
                                                        <td>
                                                            <?php if ($doc['document_status'] === 'completed'): ?>
                                                                <span class="badge badge-success">Fully Completed</span>
                                                            <?php elseif ($doc['workflow_status'] === 'approved'): ?>
                                                                <span class="badge badge-info">HR Signed</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary"><?= ucfirst($doc['document_status']) ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="view_document.php?id=<?= $doc['document_id'] ?>" 
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <?php if ($doc['signature_data']): ?>
                                                                <a href="<?= $doc['signature_data'] ?>" 
                                                                   class="btn btn-sm btn-secondary ml-1" 
                                                                   target="_blank">
                                                                    <i class="fas fa-signature"></i> Signature
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include('../footer.php'); ?>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Add this before closing </body> tag -->
    <script>
        $(document).ready(function () {
            // Initialize tabs
            $('#docTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Add fade effect
            $('.tab-pane').addClass('fade');
            $('.tab-pane.active').addClass('show');
        });
    </script>
</body>

</html>