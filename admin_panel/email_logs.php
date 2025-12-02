<?php
session_start();
include('connection.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clear_logs':
            if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
                $con->query("DELETE FROM email_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $success_message = "Email logs older than 30 days have been cleared.";
            }
            break;
    }
}

// Get email logs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM email_logs";
$count_result = $con->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get email logs
$logs_query = "SELECT el.*, l.type_of_leave, l.start_date, l.end_date 
               FROM email_logs el 
               LEFT JOIN leaves l ON el.leave_id = l.id 
               ORDER BY el.created_at DESC 
               LIMIT $limit OFFSET $offset";
$logs_result = $con->query($logs_query);

// Get statistics
$stats_query = "SELECT 
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_emails,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_emails,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_emails
                FROM email_logs";
$stats_result = $con->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Email Logs - Admin Panel</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Email Logs</h1>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Emails</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_emails']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Sent Successfully</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['sent_emails']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Failed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['failed_emails']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_emails']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Logs Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Email Logs</h6>
                            <div>
                                <a href="email_logs.php?action=clear_logs&confirm=yes" 
                                   class="btn btn-sm btn-warning" 
                                   onclick="return confirm('Are you sure you want to clear logs older than 30 days?')">
                                    <i class="fas fa-trash"></i> Clear Old Logs
                                </a>
                                <a href="email_settings.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-cog"></i> Email Settings
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="emailLogsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Recipient</th>
                                            <th>Type</th>
                                            <th>Email Type</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Leave Details</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($log['recipient_email']); ?></div>
                                                    <small class="text-muted"><?php echo ucfirst($log['recipient_type']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $log['recipient_type'] == 'applicant' ? 'primary' : 
                                                            ($log['recipient_type'] == 'recommender' ? 'warning' : 
                                                            ($log['recipient_type'] == 'approver' ? 'success' : 'info')); 
                                                    ?>">
                                                        <?php echo ucfirst($log['recipient_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $log['email_type'] == 'leave_application' ? 'primary' : 
                                                            ($log['email_type'] == 'leave_recommendation' ? 'warning' : 
                                                            ($log['email_type'] == 'leave_approval' ? 'success' : 'secondary')); 
                                                    ?>">
                                                        <?php echo str_replace('_', ' ', ucfirst($log['email_type'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['subject']); ?></td>
                                                <td>
                                                    <?php if ($log['status'] == 'sent'): ?>
                                                        <span class="badge badge-success">Sent</span>
                                                    <?php elseif ($log['status'] == 'failed'): ?>
                                                        <span class="badge badge-danger">Failed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['leave_id'] && $log['type_of_leave']): ?>
                                                        <div><strong><?php echo htmlspecialchars($log['type_of_leave']); ?></strong></div>
                                                        <small class="text-muted">
                                                            <?php echo date('d M Y', strtotime($log['start_date'])); ?> - 
                                                            <?php echo date('d M Y', strtotime($log['end_date'])); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['status'] == 'failed' && $log['error_message']): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="showError('<?php echo htmlspecialchars(addslashes($log['error_message'])); ?>')">
                                                            <i class="fas fa-exclamation-triangle"></i> View Error
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Email logs pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include('footer.php'); ?>
        </div>
    </div>
    
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Error Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre id="errorMessage" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#emailLogsTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
            });
        });
        
        function showError(errorMessage) {
            document.getElementById('errorMessage').textContent = errorMessage;
            $('#errorModal').modal('show');
        }
    </script>
</body>
</html> 