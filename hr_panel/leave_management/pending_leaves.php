<?php
include('../session.php');

include('includes/leave_functions.php');

// Get current user's details
$stmt = $con->prepare("SELECT id, new_role_id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $_SESSION['eid']);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$current_user_id = $current_user['id'];
$current_user_role = $current_user['new_role_id'];

// Fetch pending leaves for the current user based on leave hierarchy
// This query handles both cases: when emp_id is stored as EID or as numeric ID
$query = "SELECT l.*, e.full_name as employee_name, e.eid, d.name as dept_name,
          lh.recommender_id, lh.approver_id, lh.type as hierarchy_type
          FROM leaves l 
          LEFT JOIN employees e ON (l.emp_id = e.eid OR l.emp_id = e.id) 
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN leave_hierarchy lh ON lh.employee_id = e.id
          WHERE (lh.recommender_id = ? OR lh.approver_id = ?) 
          AND l.status = 'Pending' 
          ORDER BY l.applied_at DESC";

// Debug: Log the query and parameters
error_log("Query: " . $query);
error_log("Current user ID: " . $current_user_id);

$stmt = $con->prepare($query);
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$pending_leaves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug: Log the query results
error_log("Number of pending leaves found: " . count($pending_leaves));

// Additional debug: Check if there are any leaves at all
$all_leaves_query = "SELECT COUNT(*) as total FROM leaves WHERE status = 'Pending'";
$all_leaves_result = $con->query($all_leaves_query);
$total_pending = $all_leaves_result->fetch_assoc()['total'];
error_log("Total pending leaves in system: " . $total_pending);

// Debug: Check leave hierarchy for current user
$hierarchy_debug_query = "SELECT * FROM leave_hierarchy WHERE recommender_id = ? OR approver_id = ?";
$hierarchy_debug_stmt = $con->prepare($hierarchy_debug_query);
$hierarchy_debug_stmt->bind_param("ii", $current_user_id, $current_user_id);
$hierarchy_debug_stmt->execute();
$hierarchy_debug_result = $hierarchy_debug_stmt->get_result();
$hierarchy_count = $hierarchy_debug_result->num_rows;
error_log("Number of hierarchy records where current user is recommender/approver: " . $hierarchy_count);

// Debug: Check what employees the current user is responsible for
$emp_debug_query = "SELECT lh.*, e.eid, e.full_name 
                    FROM leave_hierarchy lh 
                    LEFT JOIN employees e ON lh.employee_id = e.id 
                    WHERE lh.recommender_id = ? OR lh.approver_id = ?";
$emp_debug_stmt = $con->prepare($emp_debug_query);
$emp_debug_stmt->bind_param("ii", $current_user_id, $current_user_id);
$emp_debug_stmt->execute();
$emp_debug_result = $emp_debug_stmt->get_result();
error_log("Employees under current user's responsibility:");
while ($emp_debug = $emp_debug_result->fetch_assoc()) {
    error_log("Employee ID: " . $emp_debug['employee_id'] . ", EID: " . $emp_debug['eid'] . ", Name: " . $emp_debug['full_name'] . ", Type: " . $emp_debug['type']);
}

// Debug: Check what leaves exist and their employee IDs
$leaves_debug_query = "SELECT l.*, e.eid, e.full_name, e.id as emp_numeric_id 
                       FROM leaves l 
                       LEFT JOIN employees e ON l.emp_id = e.eid 
                       WHERE l.status = 'Pending'";
$leaves_debug_result = $con->query($leaves_debug_query);
error_log("Pending leaves in system:");
while ($leave_debug = $leaves_debug_result->fetch_assoc()) {
    error_log("Leave ID: " . $leave_debug['id'] . ", Employee EID: " . $leave_debug['emp_id'] . ", Employee Numeric ID: " . $leave_debug['emp_numeric_id'] . ", Employee Name: " . $leave_debug['full_name']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Leaves for Action</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #495057;
            margin: 0;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
            padding: 15px 12px;
        }
        
        .table td {
            padding: 15px 12px;
            vertical-align: middle;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 20px;
        }
        
        .btn-group .btn {
            margin-right: 5px;
            border-radius: 6px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_info {
            margin-top: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('../sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include('../header.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="page-title">
                                    <i class="fas fa-clock me-2"></i>Pending Leaves for Action
                                </h1>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="recommender_dashboard.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                                         <?php if (isset($_SESSION['error'])): ?>
                         <div class="alert alert-danger">
                             <?php 
                             echo $_SESSION['error'];
                             unset($_SESSION['error']);
                             ?>
                         </div>
                     <?php endif; ?>



                    <!-- Pending Leaves Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list-alt me-2"></i>Leaves Pending Your Action
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="pendingLeavesTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Leave Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Total Days</th>
                                            <th>Reason</th>
                                            <th>Applied On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pending_leaves)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No leaves pending your action</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pending_leaves as $leave): ?>
                                                <tr>
                                                                                                         <td>
                                                         <div class="d-flex align-items-center">
                                                             <div class="avatar-sm me-2">
                                                                 <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                             </div>
                                                             <div>
                                                                 <strong><?php echo htmlspecialchars($leave['employee_name'] ?? 'Unknown'); ?></strong>
                                                                 <br>
                                                                 <small class="text-muted">ID: <?php echo htmlspecialchars($leave['eid'] ?? ''); ?></small>
                                                             </div>
                                                         </div>
                                                     </td>
                                                     <td>
                                                         <span class="badge bg-info"><?php echo htmlspecialchars($leave['dept_name'] ?? 'N/A'); ?></span>
                                                     </td>
                                                    <td>
                                                        <?php
                                                        $leave_type = $leave['type_of_leave'];
                                                        echo htmlspecialchars($leave_type);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                                    <td><?php echo htmlspecialchars($leave['applied_at']); ?></td>
                                                                                                         <td>
                                                         <button type="button" 
                                                                 class="btn btn-info btn-sm viewBtn" 
                                                                 onclick="viewLeaveDetails(<?php echo $leave['id']; ?>)">
                                                             <i class="fas fa-eye"></i> View
                                                         </button>
                                                         <?php if ($leave['recommender_id'] == $current_user_id): ?>
                                                             <button type="button" 
                                                                     class="btn btn-success btn-sm recommendBtn" 
                                                                     onclick="showRecommendModal(<?php echo $leave['id']; ?>)">
                                                                 <i class="fas fa-thumbs-up"></i> Recommend
                                                             </button>
                                                         <?php endif; ?>
                                                         <?php if ($leave['approver_id'] == $current_user_id): ?>
                                                             <button type="button" 
                                                                     class="btn btn-success btn-sm approveBtn" 
                                                                     onclick="showApproveModal(<?php echo $leave['id']; ?>)">
                                                                 <i class="fas fa-check"></i> Approve
                                                             </button>
                                                         <?php endif; ?>
                                                         <button type="button" 
                                                                 class="btn btn-danger btn-sm rejectBtn" 
                                                                 onclick="showRejectModal(<?php echo $leave['id']; ?>)">
                                                             <i class="fas fa-times"></i> Reject
                                                         </button>
                                                     </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <?php include('../footer.php'); ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- View Leave Details Modal -->
    <div class="modal fade" id="viewLeaveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="leaveDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Recommend Modal -->
    <div class="modal fade" id="recommendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-thumbs-up me-2"></i>Recommend Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="recommendForm" action="process/process_recommendation.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="recommend_leave_id">
                        <input type="hidden" name="action" value="recommend">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-comment me-2"></i>Remarks (Optional)
                            </label>
                            <textarea class="form-control" name="remarks" rows="3" 
                                      placeholder="Add any remarks or comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-thumbs-up me-1"></i>Recommend
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check me-2"></i>Approve Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" action="process/process_approval.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="approve_leave_id">
                        <input type="hidden" name="action" value="approve">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-comment me-2"></i>Remarks (Optional)
                            </label>
                            <textarea class="form-control" name="remarks" rows="3" 
                                      placeholder="Add any remarks or comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Leave
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" action="process/process_approval.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="leave_id" id="reject_leave_id">
                        <input type="hidden" name="action" value="reject">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-exclamation-triangle me-2"></i>Reason for Rejection <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="remarks" rows="3" required 
                                      placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function viewLeaveDetails(leaveId) {
            $.get('ajax/get_leave_details.php', { id: leaveId }, function(response) {
                $('#leaveDetailsContent').html(response);
                $('#viewLeaveModal').modal('show');
            });
        }

        function showRecommendModal(leaveId) {
            $('#recommend_leave_id').val(leaveId);
            $('#recommendModal').modal('show');
        }

        function showApproveModal(leaveId) {
            $('#approve_leave_id').val(leaveId);
            $('#approveModal').modal('show');
        }

        function showRejectModal(leaveId) {
            $('#reject_leave_id').val(leaveId);
            $('#rejectModal').modal('show');
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#pendingLeavesTable').DataTable({
                order: [[7, 'desc']], // Sort by applied date by default
                pageLength: 25,
                responsive: true,
                language: {
                    search: "Search applications:",
                    lengthMenu: "Show _MENU_ applications per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ applications",
                    infoEmpty: "No applications to show",
                    infoFiltered: "(filtered from _MAX_ total applications)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [6, 8] } // Disable sorting for reason and actions columns
                ]
            });
        });
    </script>
</body>
</html> 