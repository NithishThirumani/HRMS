<?php
$collapsed = '';
$show = '';
include(__DIR__ . '/connection.php');

// Path configuration
$url = $_SERVER['REQUEST_URI'];
$url = parse_url($url, PHP_URL_PATH);
$arr_url = explode("/", $url);
$root_path = "/admin_panel/";
$current_path = $_SERVER['REQUEST_URI'];
$current_page = basename($_SERVER['PHP_SELF']);

// Check if we're in a subdirectory
$is_in_appraisal = strpos($current_path, '/appraisal/') !== false;
$is_in_documents = strpos($current_path, '/documents/') !== false;
$is_in_subdirectory = $is_in_appraisal || $is_in_documents;

// Set paths based on current location
$base_path = $is_in_subdirectory ? '../' : '';
$root_path = "/admin_panel/";
$isSalaryPage = isset($arr_url[3]) && ($arr_url[3] == "add_sal.php" || $arr_url[3] == "view_salary.php");

$employee_pages = ['add_emp.php', 'view_emp1.php', 'visapassport_view.php', 'view_labor_cards.php'];
$show = in_array($current_page, $employee_pages) ? 'show' : '';
$collapsed = $show ? '' : 'collapsed';

// Check for attendance pages
$attendance_pages = ['manage_attendance.php', 'attendance_report.php'];
$attendance_show = in_array($current_page, $attendance_pages) ? 'show' : '';
$attendance_collapsed = $attendance_show ? '' : 'collapsed';

// Check for salary pages
$salary_pages = ['add_sal.php', 'view_salary.php'];
$salary_show = in_array($current_page, $salary_pages) ? 'show' : '';
$salary_collapsed = $salary_show ? '' : 'collapsed';

// Check for leave pages
$leave_pages = ['pending_leaves.php', 'leave_history.php', 'leave_report.php', 'leave_settings.php', 'email_settings.php', 'email_logs.php'];
$leave_show = in_array($current_page, $leave_pages) ? 'show' : '';
$leave_collapsed = $leave_show ? '' : 'collapsed';

// Check for appraisal pages
$appraisal_pages = ['manage_periods.php', 'manage_criteria.php', 'view_appraisals.php', 'initiate_appraisal.php'];
$is_appraisal_page = false;

// Check if current page is in appraisal directory
if ($is_in_appraisal) {
    $is_appraisal_page = in_array($current_page, $appraisal_pages);
}
$appraisal_show = $is_appraisal_page ? 'show' : '';
$appraisal_collapsed = $appraisal_show ? '' : 'collapsed';
// Check for document pages
$document_pages = ['manage_templates.php', 'template_list.php', 'assign_template.php', 'admin_documents.php', 'admin_signed_documents.php'];
$is_document_page = false;

// Check if current page is in documents directory
if ($is_in_documents) {
    $is_document_page = in_array($current_page, $document_pages);
}
$document_show = $is_document_page ? 'show' : '';
$document_collapsed = $document_show ? '' : 'collapsed';

// Check for control system pages
$control_pages = ['access_control.php', 'add_department.php', 'manage_employee_status.php', 'add_admin.php', 'Manage_profile.php'];
$control_show = in_array($current_page, $control_pages) ? 'show' : '';
$control_collapsed = $control_show ? '' : 'collapsed';

// Check for feedback system pages
$feedback_pages = ['hod_feedback.php', 'view_feedback.php'];
$feedback_show = in_array($current_page, $feedback_pages) ? 'show' : '';
$feedback_collapsed = $feedback_show ? '' : 'collapsed';

// Check for website content pages
$content_pages = [
    'add_header.php',
    'add_slider.php',
    'add_benefits.php',
    'add_about_us.php',
    'add_services.php',
    'add_skills.php',
    'add_facts.php',
    'add_portfolio.php',
    'add_clients.php',
    'add_testimonial.php',
    'add_team.php',
    'add_contact.php'
];
$content_show = in_array($current_page, $content_pages) ? 'show' : '';
$content_collapsed = $content_show ? '' : 'collapsed';

?>

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center"
            href="<?php echo $base_path; ?>index.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Admin Panel</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?php echo $base_path; ?>index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            CRUD
        </div>

        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item">
            <a class="nav-link <?php echo $collapsed; ?>" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="<?php echo $show ? 'true' : 'false'; ?>">
                <i class="fas fa-fw fa-person-booth"></i>
                <span>Employee Mgmt</span>
            </a>
            <div id="collapseTwo" class="collapse <?php echo $show; ?>" aria-labelledby="headingTwo"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Employee Details:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'add_emp.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_emp.php"><i class="fas fa-user-plus"></i>Add Employee</a>
                    <a class="collapse-item" href="document_upload.php" style="color: #000;">
                        <i class="fas fa-upload"></i>
                        <span>Docs Upload</span>
                    </a>
                    <a class="collapse-item" href="view_documents.php" style="color: #000;">
                        <i class="fas fa-eye"></i>
                        <span>View Upload Docs</span>
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'view_emp1.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_emp1.php"><i class="fas fa-users"></i>View Employees</a>

                    <h6 class="collapse-header">Details:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'visapassport_view.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>visapassport_view.php"><i class="fas fa-passport"></i>Visa & Passport</a>
                    <a class="collapse-item <?php echo ($current_page == 'view_labor_cards.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_labor_cards.php"><i class="fas fa-id-card"></i>Labor Cards</a>
                    <!-- Add this under your existing menu items -->

                    <a class="collapse-item" href="onboarding_dashboard.php" style="color: #000;">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Onboarding Dashboard</span>
                    </a>

                </div>
            </div>
        </li>

        <!-- Nav Item - Attendance -->
        <li class="nav-item">
            <a class="nav-link <?php echo $attendance_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#collapseAttendance" aria-expanded="<?php echo $attendance_show ? 'true' : 'false'; ?>">
                <i class="fas fa-fw fa-calendar-check"></i>
                <span>Attendance</span>
            </a>
            <div id="collapseAttendance" class="collapse <?php echo $attendance_show; ?>"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Attendance Management:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'manage_attendance.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>upload_attendance.php"><i class="fas fa-tasks"></i>Manage Attendance</a>
                    <a class="collapse-item <?php echo ($current_page == 'attendance_report.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>attendance_report.php"> <i class="fas fa-file-alt"></i> Attendance Report</a>
                </div>
            </div>
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
                        href="../esignature/index.php">
                        <i class="fas fa-fw fa-home fa-sm"></i> Dashboard
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'create.php') ? 'active' : ''; ?>"
                        href="../esignature/create.php">
                        <i class="fas fa-fw fa-plus fa-sm"></i> Create Document
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'pending.php') ? 'active' : ''; ?>"
                        href="../esignature/pending.php">
                        <i class="fas fa-fw fa-clock fa-sm"></i> Pending Documents
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'signed.php') ? 'active' : ''; ?>"
                        href="../esignature/signed.php">
                        <i class="fas fa-fw fa-check-circle fa-sm"></i> Signed Documents
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'templates.php') ? 'active' : ''; ?>"
                        href="../esignature/templates.php">
                        <i class="fas fa-fw fa-file-alt fa-sm"></i> Templates
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>"
                        href="../esignature/settings.php">
                        <i class="fas fa-fw fa-cog fa-sm"></i> Settings
                    </a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Salary -->
        <li class="nav-item">
            <a class="nav-link <?php echo $salary_collapsed; ?>" href="#" data-toggle="collapse" 
                data-target="#collapseSalary" aria-expanded="<?php echo $salary_show ? 'true' : 'false'; ?>">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Salary Mgmt</span>
            </a>
            <div id="collapseSalary" class="collapse <?php echo $salary_show; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Manage Salaries:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'add_sal.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_sal.php">
                        <i class="fas fa-plus-circle"></i> Add Salary
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'view_salary.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_salary.php">
                        <i class="fas fa-list"></i> View Salary
                    </a>
                </div>
            </div>
        </li>

        <!-- Nav Item -Leaves Tables -->
        <li class="nav-item">
            <a class="nav-link <?php echo $leave_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#leaveCollapse" aria-expanded="<?php echo $leave_show ? 'true' : 'false'; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Leave Mgmt</span>
            </a>
            <div id="leaveCollapse" class="collapse <?php echo $leave_show; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Leave Controls:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'pending_leaves.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>pending_leaves.php">Pending Approvals</a>
                    <a class="collapse-item <?php echo ($current_page == 'leave_history.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_history.php">Leave History</a>
                    
                    <a class="collapse-item <?php echo ($current_page == 'leave_settings.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_settings.php">Leave Settings</a>
                        <a class="collapse-item <?php echo ($current_page == 'manage_hods.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>manage_hods.php">Manage HODs</a>
                        <a class="collapse-item <?php echo ($current_page == 'leave_hirearchy.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_hierarchy.php">Leave Hirearchy</a>
                    <a class="collapse-item <?php echo ($current_page == 'email_settings.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>email_settings.php">
                        <i class="fas fa-envelope"></i> Email Settings
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'email_logs.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>email_logs.php">
                        <i class="fas fa-list"></i> Email Logs
                    </a>
                </div>
            </div>
        </li>
        <!-- Appraisal System -->
        <li class="nav-item">
            <a class="nav-link <?php echo $appraisal_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#appraisalCollapse" aria-expanded="<?php echo $appraisal_show ? 'true' : 'false'; ?>">
                <i class="fas fa-star"></i>
                <span>Appraisal System</span>
            </a>
            <div id="appraisalCollapse" class="collapse <?php echo $appraisal_show; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Appraisal Controls:</h6>
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'manage_periods.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/manage_periods.php">Manage Periods</a>
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'manage_criteria.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/manage_criteria.php">Manage Criteria</a>
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'view_appraisals.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/view_appraisals.php">View Appraisals</a>
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'initiate_appraisal.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/initiate_appraisal.php">Initiate Appraisal</a>
                </div>
            </div>
        </li>

        <!-- Control System -->
        <li class="nav-item">
            <a class="nav-link <?php echo $control_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#controlCollapse" aria-expanded="<?php echo $control_show ? 'true' : 'false'; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Control System</span>
            </a>
            <div id="controlCollapse" class="collapse <?php echo $control_show; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Controls:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'access_control.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>access_control.php">Access Control</a>
                    <a class="collapse-item <?php echo ($current_page == 'add_department.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_department.php">Manage Deptt</a>
                        <a class="collapse-item <?php echo ($current_page == 'designations.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>designations.php">Manage Designation</a>
                    <a class="collapse-item <?php echo ($current_page == 'manage_employee_status.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>manage_employee_status.php">Manage Employee</a>
                    <a class="collapse-item <?php echo ($current_page == 'add_admin.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_admin.php">Admin Ctrl</a>
                    <a class="collapse-item <?php echo ($current_page == 'Manage_profile.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>Manage_profile.php">Manage Admin</a>
                        <a class="collapse-item <?php echo ($current_page == 'reset_passwords.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>reset_passwords.php">Pwd Reset Users</a>
                </div>
            </div>
        </li>

        <!-- Feedback System -->
        <li class="nav-item">
            <a class="nav-link <?php echo $feedback_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#feedbackCollapse" aria-expanded="<?php echo $feedback_show ? 'true' : 'false'; ?>">
                <i class="fas fa-comments"></i>
                <span>Feedback System</span>
            </a>
            <div id="feedbackCollapse" class="collapse <?php echo $feedback_show; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Feedback Controls:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'hod_feedback.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>hod_feedback.php">
                        <i class="fas fa-fw fa-inbox fa-sm"></i> Manage Feedback
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'view_feedback.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_feedback.php">
                        <i class="fas fa-fw fa-history fa-sm"></i> Feedback History
                    </a>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Information
        </div>

        <!-- Nav Item - Charts -->

        <li class="nav-item <?php if (isset($arr_url[3]) && $arr_url[3] == "assign_project.php") {
            echo "active";
        } ?>">
            <a class="nav-link" href="assign_project.php">
                <i class="fas fa-fw fa-chart-area"></i>
                <span>Assignment</span></a>
        </li>

        <li class="nav-item <?php if (isset($arr_url[3]) && $arr_url[3] == "project_status.php" || $arr_url[3] == "assign_marks.php") {
            echo "active";
        } ?>">
            <a class="nav-link" href="project_status.php">
                <i class="fas fa-fw fa-hourglass"></i>
                <span>Status</span></a>
        </li>

        <li class="nav-item <?php if (isset($arr_url[3]) && $arr_url[3] == "event.php" || $arr_url[3] == "add_events.php" || $arr_url[3] == "edit_events.php" || $arr_url[3] == "event_pt.php") {
            echo "active";
        } ?>">
            <a class="nav-link" href="event.php">
                <i class="fas fa-fw fa-layer-group"></i>
                <span>Event</span></a>
        </li>

        <li class="nav-item <?php if (isset($arr_url[3]) && $arr_url[3] == "tours.php") {
            echo "active";
        } ?>">
            <a class="nav-link" href="tours.php">
                <i class="fas fa-fw fa-plane"></i>
                <span>Tours</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Guest Panel
        </div>

        <!-- Nav Item - Guest Panel Menu -->
        <li class="nav-item">
            <a class="nav-link <?php echo $content_collapsed; ?>" href="#" data-toggle="collapse"
                data-target="#collapseGuestPanel" aria-expanded="<?php echo $content_show ? 'true' : 'false'; ?>"
                aria-controls="collapseGuestPanel">
                <i class="fas fa-fw fa-user-circle"></i>
                <span>Website Content</span>
            </a>
            <div id="collapseGuestPanel" class="collapse <?php echo $content_show; ?>"
                aria-labelledby="headingGuestPanel" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Page Sections:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'add_header.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_header.php">
                        <i class="fas fa-fw fa-expand fa-sm"></i> Header
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'add_slider.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>add_slider.php">
                        <i class="fas fa-fw fa-image fa-sm"></i> Slider
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_benefits.php") {
                        echo "active";
                    } ?>" href="add_benefits.php">
                        <i class="fas fa-fw fa-smile fa-sm"></i> Benefits
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_about_us.php") {
                        echo "active";
                    } ?>" href="add_about_us.php">
                        <i class="fas fa-fw fa-user fa-sm"></i> About us
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_services.php") {
                        echo "active";
                    } ?>" href="add_services.php">
                        <i class="fas fa-fw fa-hands fa-sm"></i> Services
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_skills.php") {
                        echo "active";
                    } ?>" href="add_skills.php">
                        <i class="fas fa-fw fa-magic fa-sm"></i> Skills
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_facts.php") {
                        echo "active";
                    } ?>" href="add_facts.php">
                        <i class="fas fa-fw fa-angle-double-right fa-sm"></i> Facts
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_portfolio.php") {
                        echo "active";
                    } ?>" href="add_portfolio.php">
                        <i class="fas fa-fw fa-camera-retro fa-sm"></i> Portfolio
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_clients.php") {
                        echo "active";
                    } ?>" href="add_clients.php">
                        <i class="fas fa-fw fa-user fa-sm"></i> Clients
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_testimonial.php") {
                        echo "active";
                    } ?>" href="add_testimonial.php">
                        <i class="fas fa-fw fa-heartbeat fa-sm"></i> Testimonials
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_team.php") {
                        echo "active";
                    } ?>" href="add_team.php">
                        <i class="fas fa-fw fa-object-group fa-sm"></i> Team
                    </a>
                    <a class="collapse-item <?php if (isset($arr_url[3]) && $arr_url[3] == "add_contact.php") {
                        echo "active";
                    } ?>" href="add_contact.php">
                        <i class="fas fa-fw fa-phone fa-sm"></i> Contact
                    </a>
                </div>
            </div>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle">
        <i class="fas fa-angle-left"></i>
    </button>
</div>

    </ul>
    
    <!-- End of Sidebar -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Remove hash from URL if present
            if (window.location.hash) {
                history.replaceState(null, null, window.location.pathname);
            }

            // Prevent default hash behavior for collapse menu
            document.querySelectorAll('.collapse-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.location.href = this.getAttribute('href');
                });
            });
        });
    </script>
    <style>
        .sidebar.bg-gradient-primary {
            background: linear-gradient(145deg, #2c3e50 0%, #3498db 100%) !important;
        }

        .sidebar .nav-item .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-item.active .nav-link {
            color: #ffffff !important;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
        }

        .sidebar-dark .sidebar-heading {
            color: rgba(255, 255, 255, 0.6);
        }

        .sidebar .collapse-inner {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-dark hr.sidebar-divider {
            border-color: rgba(255, 255, 255, 0.15);
        }
    </style>
     <!-- Sidebar Toggle (Topbar) -->
    <script>
        // Toggle the side navigation
        document.getElementById("sidebarToggle").addEventListener("click", function(e) {
            e.preventDefault();
            document.body.classList.toggle("sidebar-toggled");
            document.querySelector(".sidebar").classList.toggle("toggled");
            
            // If sidebar is toggled, collapse expanded menus
            if (document.querySelector(".sidebar").classList.contains("toggled")) {
                // Add this to prevent expanded dropdowns from showing
                var dropdowns = document.querySelectorAll('.sidebar .collapse');
                dropdowns.forEach(function(dropdown) {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Close any open menu accordions when window is resized below 768px
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                // Add this to prevent expanded dropdowns from showing
                var dropdowns = document.querySelectorAll('.sidebar .collapse');
                dropdowns.forEach(function(dropdown) {
                    dropdown.classList.remove('show');
                });
            }
        });
    </script>
    <!-- End of Sidebar -->