<?php
$collapsed = '';
$show = '';
include(__DIR__ . '/connection.php');

// Path configuration
$url = $_SERVER['REQUEST_URI'];
$url = parse_url($url, PHP_URL_PATH);
$arr_url = explode("/", $url);
$root_path = "./hod_panel/";
$current_path = $_SERVER['REQUEST_URI'];
$current_page = basename($_SERVER['PHP_SELF']);

// Check if we're in a subdirectory
$is_in_appraisal = strpos($current_path, '/appraisal/') !== false;
$is_in_documents = strpos($current_path, '/documents/') !== false;
$is_in_leave_management = strpos($current_path, '/leave_management/') !== false;
$is_in_subdirectory = $is_in_appraisal || $is_in_documents || $is_in_leave_management;

// Set paths based on current location
$base_path = $is_in_subdirectory ? '../' : '';
$root_path = "./hod_panel/";
$isSalaryPage = isset($arr_url[3]) && ($arr_url[3] == "add_sal.php" || $arr_url[3] == "view_salary.php");

$employee_pages = ['add_emp.php', 'view_emp1.php', 'visapassport_view.php', 'view_labor_cards.php'];
$show = in_array($current_page, $employee_pages) ? 'show' : '';
$collapsed = $show ? '' : 'collapsed';

// Check for attendance pages - REMOVED
// $attendance_pages = ['manage_attendance.php', 'attendance_report.php'];
// $attendance_show = in_array($current_page, $attendance_pages) ? 'show' : '';
// $attendance_collapsed = $attendance_show ? '' : 'collapsed';

// Check for salary pages
$salary_pages = ['add_sal.php', 'view_salary.php'];
$salary_show = in_array($current_page, $salary_pages) ? 'show' : '';
$salary_collapsed = $salary_show ? '' : 'collapsed';

// Check for leave pages
$leave_pages = ['pending_leaves.php', 'leave_history.php', 'leave_report.php', 'leave_settings.php'];
$leave_management_pages = ['index.php', 'pending_leaves.php', 'leave_reports.php', 'leave_history.php'];
$is_in_leave_management = strpos($current_path, '/leave_management/') !== false;

// Check if current page is in leave management directory
$is_leave_management_page = false;
if ($is_in_leave_management) {
    $is_leave_management_page = in_array($current_page, $leave_management_pages);
}

$leave_show = (in_array($current_page, $leave_pages) || $is_leave_management_page) ? 'show' : '';
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

// Check for control system pages - REMOVED
// $control_pages = ['access_control.php', 'add_department.php', 'manage_employee_status.php', 'add_admin.php', 'Manage_profile.php'];
// $control_show = in_array($current_page, $control_pages) ? 'show' : '';
// $control_collapsed = $control_show ? '' : 'collapsed';

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
            <div class="sidebar-brand-text mx-3">HOD Panel</div>
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

                    <a class="collapse-item <?php echo ($current_page == 'view_emp1.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_emp1.php">View Employees</a>

                    <h6 class="collapse-header">Documents:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'visapassport_view.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>visapassport_view.php">Visa & Passport</a>
                    <a class="collapse-item <?php echo ($current_page == 'view_labor_cards.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_labor_cards.php">Labor Cards</a>
                </div>
            </div>
        </li>



        <li class="nav-item">
            
            
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
                   
                    <a class="collapse-item <?php echo ($current_page == 'pending.php') ? 'active' : ''; ?>"
                        href="/emps/esignature/pending.php">
                        <i class="fas fa-fw fa-clock fa-sm"></i> Pending Documents
                    </a>
                    <a class="collapse-item <?php echo ($current_page == 'signed.php') ? 'active' : ''; ?>"
                        href="/emps/esignature/signed.php">
                        <i class="fas fa-fw fa-check-circle fa-sm"></i> Signed Documents
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
            <div id="collapseSalary" class="collapse <?php echo $salary_show; ?>">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Salaries:</h6>
                    <a class="collapse-item <?php echo ($current_page == 'view_salary.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>view_salary.php">View Salary</a>
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
                    <a class="collapse-item <?php echo ($is_in_leave_management && $current_page == 'index.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_management/index.php">Dashboard</a>
            
                         
                         <a class="collapse-item <?php echo ($is_in_leave_management && $current_page == 'recommend_leave.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_management/recommend_leave.php">Recommend Leave</a>
                    <a class="collapse-item <?php echo ($is_in_leave_management && $current_page == 'approver_dashboard.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_management/approver_dashboard.php">Approve Leave</a>
                    <a class="collapse-item <?php echo ($is_in_leave_management && $current_page == 'leave_reports.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_management/leave_reports.php">Reports & Analytics</a>
                    <a class="collapse-item <?php echo ($is_in_leave_management && $current_page == 'leave_history.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>leave_management/leave_history.php">Leave History</a>
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
                    
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'view_appraisals.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/view_appraisals.php">View Appraisals</a>
                    <a class="collapse-item <?php echo ($is_in_appraisal && $current_page == 'initiate_appraisal.php') ? 'active' : ''; ?>"
                        href="<?php echo $base_path; ?>appraisal/initiate_appraisal.php">Initiate Appraisal</a>
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



        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    </ul>
    <!-- End of Sidebar -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Remove hash from URL if present
            if (window.location.hash) {
                history.replaceState(null, null, window.location.pathname);
            }

            // Handle navigation for all sidebar links
            document.querySelectorAll('.nav-link, .collapse-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    // Only prevent default for collapse toggles (those without href)
                    if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
                        return; // Let collapse toggles work normally
                    }
                    
                    // For actual navigation links, ensure they work properly
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        // Allow normal navigation
                        return;
                    }
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
    <!-- End of Sidebar -->