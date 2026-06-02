<div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card employee-card">
        <div class="status-badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
            <?php echo ucfirst($row['status']); ?>
        </div>
        <div class="action-buttons">
            <a href="edit_employee.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
            </a>
            <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo (int) $row['id']; ?>)">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <div class="card-body text-center">
            <?php
            if (!function_exists('hrms_employee_upload_url')) {
                require_once dirname(__DIR__) . '/includes/hrms_paths.php';
            }
            ?>
            <img src="<?php echo htmlspecialchars(hrms_employee_upload_url($row['profile_pic'] ?? null)); ?>"
                 class="profile-img mb-3" alt="Profile Picture">
            <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['full_name']); ?></h5>
            <p class="text-muted small mb-2"><?php echo htmlspecialchars($row['designation']); ?></p>
            <div class="badge badge-primary mb-2"><?php echo htmlspecialchars($row['department'] ?? $row['department_name'] ?? ''); ?></div>
            <hr>
            <div class="text-left small">
                <p><i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($row['email']); ?></p>
                <p><i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($row['contact']); ?></p>
                <p><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($row['EmpLoc']); ?></p>
            </div>
        </div>
    </div>
</div>
