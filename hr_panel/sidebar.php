<?php
include('connection.php');
$url = $_SERVER['REQUEST_URI'];
// echo $url;
$url = parse_url($url, PHP_URL_PATH);
$arr_url = explode("/", $url);
// echo $arr_url[3];
?>

<!-- Page Wrapper -->
<div id="wrapper">


    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/hr_panel/index.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="sidebar-brand-text mx-3">HR Panel</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link" href="/hr_panel/index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Nav Item - Manage Employees -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#empCollapse">
                <i class="fas fa-calendar-check"></i>
                <span>Emp Management</span>
            </a>
            <div id="empCollapse" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">EMP Controls:</h6>
                    
                    <a class="collapse-item" href="/hr_panel/manage_employees.php">Manage Employees</a>
                    <a class="collapse-item" href="/hr_panel/add_emp.php">Add Emp</a>
                    <h6 class="collapse-header">Trainee Details:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'add_trainee.php') ? 'active' : ''; ?>"
                        href="/hr_panel/add_trainee.php"><i class="fas fa-user-plus"></i> Add Trainee</a>
                    <a class="collapse-item <?php echo ($current_page == 'view_trainees.php') ? 'active' : ''; ?>"
                        href="/hr_panel/view_trainees.php"><i class="fas fa-eye"></i> View Trainees</a>
                    <a class="collapse-item <?php echo ($current_page == 'trainee_reports.php') ? 'active' : ''; ?>"
                        href="/hr_panel/trainee_reports.php"><i class="fas fa-file-alt"></i> Download Reports</a>
                </div>
            </div>
        </li>
    


        <!-- Nav Item - Attendance -->
        <li class="nav-item">
            <a class="nav-link" href="/hr_panel/attendance_report.php">
                <i class="fas fa-fw fa-clock"></i>
                <span>Attendance</span>
            </a>
        </li>

        <!-- Nav Item - LEAVE MANAGEMENT -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#leaveMgmtCollapse" aria-expanded="false" aria-controls="leaveMgmtCollapse">
                <i class="fas fa-calendar-check"></i>
                <span>Leave Management</span>
            </a>
            <div id="leaveMgmtCollapse" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Leave Actions:</h6>
                    <a class="collapse-item" href="/hr_panel/leave_management/dashboard.php">Leaves</a>
                    <a class="collapse-item" href="/hr_panel/leave_management/apply.php">Apply for Leave</a>
                   
                    <a class="collapse-item" href="/hr_panel/leave_management/pending_leaves.php">Pending Recommendations</a>
                    <a class="collapse-item" href="/hr_panel/leave_management/approver_dashboard.php">Approver Dashboard</a>
                    <a class="collapse-item" href="/hr_panel/leave_management/pending_approvals.php">Pending Approvals</a>
                    <a class="collapse-item" href="/hr_panel/leave_management/leave_history.php">Leave History</a>
                      <a class="collapse-item" href="/hr_panel/leave_management/delete_leave.php">Delete Leave Applications</a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Documents -->
        <li class="nav-item">
            <a class="nav-link" href="/hr_panel/documents/hr_documents.php">
                <i class="fas fa-file-signature"></i>
                <span>Documents to Sign</span>
            </a>


        </li>

        <!-- Nav Item - esignature -->
        <li class="nav-item">
            <a class="nav-link <?php echo $document_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#collapseEsignature" aria-expanded="<?php echo $document_show ? 'true' : 'false'; ?>"
                aria-controls="collapseEsignature">
                <i class="fas fa-fw fa-file-signature"></i>
                <span>E-Signature</span>
            </a>
            <div id="collapseEsignature" class="collapse <?php echo $document_show; ?>"
                aria-labelledby="headingEsignature" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">E-Signature Controls:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"
                        href="/esignature/index.php">
                        <i class="fas fa-fw fa-home fa-sm"></i> Dashboard
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'create.php') ? 'active' : ''; ?>"
                        href="/esignature/create.php">
                        <i class="fas fa-fw fa-plus fa-sm"></i> Create Document
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'pending.php') ? 'active' : ''; ?>"
                        href="/esignature/pending.php">
                        <i class="fas fa-fw fa-clock fa-sm"></i> Pending Documents
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'signed.php') ? 'active' : ''; ?>"
                        href="/esignature/signed.php">
                        <i class="fas fa-fw fa-check-circle fa-sm"></i> Signed Documents
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'templates.php') ? 'active' : ''; ?>"
                        href="/esignature/templates.php">
                        <i class="fas fa-fw fa-file-alt fa-sm"></i> Templates
                    </a>
                </div>
            </div>
        </li>

        <!-- Appraisal Nav Item - Reports -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAppraisal"
                aria-expanded="false" aria-controls="collapseAppraisal">
                <i class="fas fa-star"></i>
                <span>Appraisal Management</span>
            </a>
            <div id="collapseAppraisal" class="collapse" aria-labelledby="headingAppraisal" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Appraisal Options:</h6>
                    <a class="collapse-item" href="/hr_panel/appraisal/review_appraisals.php">
                        <i class="far fa-circle fa-sm fa-fw mr-2"></i>Review Appraisals
                    </a>
                    <a class="collapse-item" href="/hr_panel/appraisal/department_reports.php">
                        <i class="far fa-circle fa-sm fa-fw mr-2"></i>Department Reports
                    </a>
                </div>
            </div>
        </li>




    </ul>
    <!-- End of Sidebar -->