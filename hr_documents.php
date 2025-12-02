// ... existing header and styles ...
<div class="container-fluid">
    <h3 class="text-dark mb-4">HR Document Portal</h3>
    
    <ul class="nav nav-tabs mb-4" style="border-color: #2c3e50;">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#pending" style="color: #2c3e50;">Pending Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#signed" style="color: #2c3e50;">Completed Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#rejected" style="color: #2c3e50;">Rejected Documents</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Pending Documents Tab -->
        <div id="pending" class="tab-pane fade show active">
            <div class="card shadow mb-4" style="border-color: #2c3e50;">
                <div class="card-header py-3" style="background-color: #2c3e50; color: white;">
                    <h6 class="m-0 font-weight-bold">Pending Approval</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="background-color: #2c3e50; color: white;">Doc Number</th>
                                <th style="background-color: #2c3e50; color: white;">Document</th>
                                <th style="background-color: #2c3e50; color: white;">Employee</th>
                                <th style="background-color: #2c3e50; color: white;">Assigned Date</th>
                                <th style="background-color: #2c3e50; color: white;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['document_number']) ?></td>
                                <td><?= htmlspecialchars($doc['doc_title']) ?></td>
                                <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($doc['assigned_date'])) ?></td>
                                <td>
                                    <a href="hr_review.php?id=<?= $doc['assignment_id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-signature"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Repeat similar structure for Signed and Rejected tabs -->
        <!-- Add document_number column to those tables as well -->
    </div>
</div>