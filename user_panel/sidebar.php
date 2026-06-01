<?php
if (!isset($con) || !($con instanceof mysqli)) {
    require_once __DIR__ . '/session.php';
}

if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header('Location: /emps/login.php');
    exit();
}

if (!isset($user_data) || !is_array($user_data) || empty($user_data['eid'])) {
    $eid = $_SESSION['eid'];
    $query = 'SELECT e.*, d.name AS department_name
              FROM employees e
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE e.eid = ?
              LIMIT 1';
    $stmt = $con->prepare($query);
    $stmt->bind_param('s', $eid);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
}

if (!$user_data) {
    header('Location: /emps/login.php?error=invalid_user');
    exit();
}

$collapsed = '';
$show = '';

// Determine if we're in a subdirectory
$request_uri = $_SERVER['REQUEST_URI'];
$in_subdirectory = strpos($request_uri, '/leave_management/') !== false || 
                  strpos($request_uri, '/documents/') !== false || 
                  strpos($request_uri, '/appraisal/') !== false;

$base_url = $in_subdirectory ? '../' : './';

if (!isset($_SESSION['user_id']) && !empty($user_data['id'])) {
    $_SESSION['user_id'] = (int) $user_data['id'];
}
$emp_numeric_id = (int) ($_SESSION['user_id'] ?? $user_data['id'] ?? 0);

// Use numeric id for all checks
function isRecommenderOrApprover($con, $emp_numeric_id) {
    $stmt = $con->prepare("SELECT 1 FROM leave_hierarchy WHERE (recommender_id = ? OR approver_id = ?) LIMIT 1");
    $stmt->bind_param("ii", $emp_numeric_id, $emp_numeric_id);
    $stmt->execute();
    $stmt->store_result();
    $is = $stmt->num_rows > 0;
    $stmt->close();
    return $is;
}
?>

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/emps/user_panel/index.php">
            <div class="sidebar-brand-icon">
                <img src="/emps/user_panel/img/favicon.png" alt="Logo" style="width: 50px; height: 50px;">
            </div>
            <div class="sidebar-brand-text mx-3">Employee Panel</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="/emps/user_panel/index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Nav Item - Profile -->
        <li class="nav-item">
            <a class="nav-link" href="/emps/user_panel/Manage_profile.php">
                <i class="fas fa-fw fa-user"></i>
                <span>My Profile</span>
            </a>
        </li>

        <!-- Nav Item - Leave Management -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLeave" 
               aria-expanded="true" aria-controls="collapseLeave">
                <i class="fas fa-fw fa-calendar-minus"></i>
                <span>Leave Management</span>
            </a>
            <div id="collapseLeave" class="collapse" aria-labelledby="headingLeave" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="/emps/user_panel/leave_management/dashboard.php">Leaves</a>
                    <a class="collapse-item" href="/emps/user_panel/leave_management/apply.php">Apply Leave</a>
                    <a class="collapse-item" href="/emps/user_panel/leave_management/leave_history.php">Leave History</a>
                    <?php if (isRecommenderOrApprover($con, $emp_numeric_id)): ?>
                        <a class="collapse-item" href="/emps/user_panel/leave_management/recommender.php">Recommender</a>
                    <?php endif; ?>
                </div>
            </div>
        </li>

        <!-- Nav Item - Salary -->
        <li class="nav-item">
            <a class="nav-link" href="/emps/user_panel/view_payslip.php">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>My Payslips</span>
            </a>
        </li>

        <!-- Nav Item - Documents -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDocuments" 
               aria-expanded="true" aria-controls="collapseDocuments">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Documents</span>
            </a>
            <div id="collapseDocuments" class="collapse" aria-labelledby="headingDocuments" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="/emps/esignature/index.php">My Documents</a>
                    <a class="collapse-item" href="/emps/esignature/pending.php">Pending Signatures</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Appraisal -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAppraisal" 
               aria-expanded="true" aria-controls="collapseAppraisal">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Appraisal</span>
            </a>
            <div id="collapseAppraisal" class="collapse" aria-labelledby="headingAppraisal" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="/emps/user_panel/appraisal/current_appraisal.php">Current Appraisal</a>
                    <a class="collapse-item" href="/emps/user_panel/appraisal/appraisal_history.php">Appraisal History</a>
                    <a class="collapse-item" href="/emps/user_panel/appraisal/goals.php">Goals</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Feedback -->
        <li class="nav-item">
            <a class="nav-link" href="/emps/views/feedback/submit_feedback.php">
                <i class="fas fa-fw fa-comment"></i>
                <span>Submit Feedback</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- User Info Section -->
        <li class="nav-item">
            <div class="nav-link">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <?php 
                        $displayName = $user_data['full_name'] ?? $user_data['first_name'] ?? 'User';
                        $firstLetter = strtoupper(substr($displayName, 0, 1));
                        ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 35px; height: 35px; background-color: rgba(255,255,255,0.2); 
                                    color: white; font-weight: bold; font-size: 14px;">
                            <?php echo $firstLetter; ?>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-2">
                        <div class="text-white" style="font-size: 12px; font-weight: 600;">
                            <?php echo htmlspecialchars($displayName); ?>
                        </div>
                        <div class="text-white-50" style="font-size: 10px;">
                            <?php echo htmlspecialchars($user_data['department_name'] ?? 'Employee'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Logout Section -->
        <li class="nav-item">
            <a class="nav-link text-danger" href="#" onclick="confirmLogout()" 
               style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px;">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

        <!-- Additional Logout Link (Fallback) -->
        <li class="nav-item" style="margin-top: 20px;">
            <a class="nav-link btn btn-danger btn-sm" href="#" onclick="confirmLogout()" 
               style="margin: 0 10px; text-align: center; font-size: 12px; 
                      padding: 5px 10px; width: 80px; display: inline-block;">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" 
             aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="logoutModalLabel">
                            <i class="fas fa-sign-out-alt mr-2"></i>Confirm Logout
                        </h5>
                        <button type="button" class="close text-white" 
                                data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to logout?</p>
                        <p class="text-muted small mb-0">You will be redirected to the login page.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                        <a href="/emps/user_panel/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function confirmLogout() {
            $('#logoutModal').modal('show');
        }
        </script>
    </ul>
    <!-- End of Sidebar -->