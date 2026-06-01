<?php
// Include the main session file first
include('../session.php');
// Include other required files
include('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['eid']) || empty($_SESSION['eid'])) {
    header("Location: ../login.php");
    exit();
}

$emp_id = $_SESSION['eid'];
$errors = [];
$success_message = '';

// Check if this is a POST request to delete a leave
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_leave'])) {
    $leave_id = $_POST['leave_id'] ?? null;
    $confirmation = $_POST['confirmation'] ?? '';
    
    if (!$leave_id) {
        $errors[] = "Leave ID is required.";
    } elseif ($confirmation !== 'DELETE') {
        $errors[] = "Please type 'DELETE' to confirm deletion.";
    } else {
        // Get leave details before deletion for logging
        $leave_query = "SELECT l.*, e.full_name, e.eid 
                       FROM leaves l 
                       LEFT JOIN employees e ON l.emp_id = e.eid 
                       WHERE l.id = ?";
        $stmt = $con->prepare($leave_query);
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $leave_details = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$leave_details) {
            $errors[] = "Leave application not found.";
        } else {
            // Temporarily disable the trigger
            $con->query("DROP TRIGGER IF EXISTS update_leave_days_on_delete");
            
            // Delete the leave application
            $delete_query = "DELETE FROM leaves WHERE id = ?";
            $stmt = $con->prepare($delete_query);
            $stmt->bind_param("i", $leave_id);
            
            if ($stmt->execute()) {
                $success_message = "Leave application #" . $leave_id . " has been deleted successfully.";
                error_log("Leave application #" . $leave_id . " deleted by HR user " . $_SESSION['eid']);
            } else {
                $errors[] = "Error deleting leave application: " . $stmt->error;
            }
            $stmt->close();
            
            // Recreate the trigger
            $con->query("CREATE TRIGGER `update_leave_days_on_delete` AFTER DELETE ON `leaves` FOR EACH ROW BEGIN
                DECLARE yearly_leave_sum INT;
                SELECT IFNULL(SUM(total_days), 0) INTO yearly_leave_sum
                FROM leaves
                WHERE emp_id = OLD.emp_id
                AND YEAR(start_date) = YEAR(CURDATE());
                UPDATE leaves
                SET total_days = yearly_leave_sum
                WHERE emp_id = OLD.emp_id
                AND YEAR(start_date) = YEAR(CURDATE());
            END");
        }
    }
}

// Check if this is a POST request for bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    $leave_ids = $_POST['leave_ids'] ?? [];
    $bulk_confirmation = $_POST['bulk_confirmation'] ?? '';
    
    if (empty($leave_ids)) {
        $errors[] = "Please select at least one leave application to delete.";
    } elseif ($bulk_confirmation !== 'BULK DELETE') {
        $errors[] = "Please type 'BULK DELETE' to confirm bulk deletion.";
    } else {
        // Temporarily disable the trigger
        $con->query("DROP TRIGGER IF EXISTS update_leave_days_on_delete");
        
        $deleted_count = 0;
        $error_count = 0;
        
        foreach ($leave_ids as $leave_id) {
            $delete_query = "DELETE FROM leaves WHERE id = ?";
            $stmt = $con->prepare($delete_query);
            $stmt->bind_param("i", $leave_id);
            
            if ($stmt->execute()) {
                $deleted_count++;
                error_log("Leave application #" . $leave_id . " deleted by HR user " . $_SESSION['eid']);
            } else {
                $error_count++;
            }
            $stmt->close();
        }
        
        // Recreate the trigger
        $con->query("CREATE TRIGGER `update_leave_days_on_delete` AFTER DELETE ON `leaves` FOR EACH ROW BEGIN
            DECLARE yearly_leave_sum INT;
            SELECT IFNULL(SUM(total_days), 0) INTO yearly_leave_sum
            FROM leaves
            WHERE emp_id = OLD.emp_id
            AND YEAR(start_date) = YEAR(CURDATE());
            UPDATE leaves
            SET total_days = yearly_leave_sum
            WHERE emp_id = OLD.emp_id
            AND YEAR(start_date) = YEAR(CURDATE());
        END");
        
        if ($deleted_count > 0) {
            $success_message = "Successfully deleted " . $deleted_count . " leave application(s).";
            if ($error_count > 0) {
                $success_message .= " Failed to delete " . $error_count . " application(s).";
            }
        } else {
            $errors[] = "Failed to delete any leave applications.";
        }
    }
}

// Get all leave applications for display
$leave_applications = [];
$query = "SELECT l.*, 
                 COALESCE(e.full_name, l.user_name) as full_name, 
                 COALESCE(e.eid, l.emp_id) as eid, 
                 d.name as dept_name
          FROM leaves l
          LEFT JOIN employees e ON l.emp_id = e.eid
          LEFT JOIN departments d ON e.department_id = d.id
          ORDER BY l.applied_at DESC";
$stmt = $con->prepare($query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $leave_applications[] = $row;
    }
    $stmt->close();
} else {
    $errors[] = "Error preparing leave applications query: " . $con->error;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Delete Leave Applications</title>
    <link href="img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet"> 
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">     
    <link rel="stylesheet" href="../css/custom.css">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/search.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .quote-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            border-left: 4px solid var(--primary-color);
        }
    
        .container {
            margin-top: 50px;
        }
        .form-group label {
            font-weight: bold;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('../sidebar.php'); ?>
  
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
            <?php include('../header.php'); ?>
    <div class="container-fluid">
        <h2>Delete Leave Applications</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <!-- Warning Section -->
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Important Notice</h5>
            <p><strong>This action is irreversible!</strong> Deleting a leave application will permanently remove it from the system.</p>
            <p>Please ensure you have the proper authorization before proceeding.</p>
        </div>

        <!-- Leave Applications Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Leave Applications</h6>
                <button type="button" class="btn btn-danger btn-sm" onclick="showBulkDeleteModal()">
                    <i class="fas fa-trash"></i> Bulk Delete
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($leave_applications)): ?>
                    <form id="bulkDeleteForm" method="POST">
                        <input type="hidden" name="bulk_delete" value="1">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="leaveTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                                        </th>
                                        <th>Leave ID</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leave_applications as $leave): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="leave_ids[]" value="<?php echo $leave['id']; ?>" class="leave-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($leave['id']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['eid'] ?? $leave['emp_id'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($leave['full_name'] ?? $leave['user_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo htmlspecialchars($leave['dept_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $leave['type_of_leave']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['start_date']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['end_date']))); ?></td>
                                        <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $leave['status'] === 'Approved' ? 'success' : ($leave['status'] === 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo htmlspecialchars($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($leave['applied_at']))); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="confirmDelete(<?php echo $leave['id']; ?>, '<?php echo htmlspecialchars($leave['full_name'] ?? $leave['user_name'] ?? 'Unknown'); ?>', '<?php echo htmlspecialchars($leave['type_of_leave']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">No leave applications found.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Leave Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Warning!</h6>
                            <p>You are about to delete the following leave application:</p>
                            <ul>
                                <li><strong>Leave ID:</strong> <span id="deleteLeaveId"></span></li>
                                <li><strong>Employee:</strong> <span id="deleteEmployeeName"></span></li>
                                <li><strong>Leave Type:</strong> <span id="deleteLeaveType"></span></li>
                            </ul>
                            <p><strong>This action cannot be undone!</strong></p>
                        </div>
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="delete_leave" value="1">
                            <input type="hidden" name="leave_id" id="deleteLeaveIdInput">
                            <div class="form-group">
                                <label for="confirmation">Type 'DELETE' to confirm:</label>
                                <input type="text" class="form-control" id="confirmation" name="confirmation" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" form="deleteForm" class="btn btn-danger">Delete Leave Application</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Confirmation Modal -->
        <div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Bulk Leave Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Warning!</h6>
                            <p>You are about to delete multiple leave applications at once.</p>
                            <p><strong>This action cannot be undone!</strong></p>
                        </div>
                        <div class="form-group">
                            <label for="bulkConfirmation">Type 'BULK DELETE' to confirm:</label>
                            <input type="text" class="form-control" id="bulkConfirmation" name="bulk_confirmation" required>
                        </div>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Instructions:</h6>
                            <ol>
                                <li>Select the leave applications you want to delete using the checkboxes</li>
                                <li>Click "Bulk Delete" button</li>
                                <li>Type 'BULK DELETE' in the confirmation field</li>
                                <li>Click "Delete Selected Applications"</li>
                            </ol>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="submitBulkDelete()">Delete Selected Applications</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            <a href="leave_history.php" class="btn btn-info">View Leave History</a>
        </div>
    </div>
    <?php include('../footer.php'); ?>
</div>
</div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current
                    session.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>

    <script>
        function confirmDelete(leaveId, employeeName, leaveType) {
            document.getElementById('deleteLeaveId').textContent = leaveId;
            document.getElementById('deleteEmployeeName').textContent = employeeName;
            document.getElementById('deleteLeaveType').textContent = leaveType;
            document.getElementById('deleteLeaveIdInput').value = leaveId;
            document.getElementById('confirmation').value = '';
            $('#deleteModal').modal('show');
        }

        function showBulkDeleteModal() {
            // Check if any checkboxes are selected
            const selectedCheckboxes = document.querySelectorAll('.leave-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one leave application to delete.');
                return;
            }
            
            $('#bulkDeleteForm').attr('action', 'delete_leave.php');
            $('#bulkDeleteForm').attr('method', 'POST');
            $('#bulkConfirmation').val(''); // Clear confirmation input
            $('#bulkDeleteModal').modal('show');
        }

        function toggleAllCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.leave-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function submitBulkDelete() {
            const confirmation = document.getElementById('bulkConfirmation').value;
            if (confirmation !== 'BULK DELETE') {
                alert('Please type "BULK DELETE" to confirm.');
                return false;
            }
            
            const selectedCheckboxes = document.querySelectorAll('.leave-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one leave application to delete.');
                return false;
            }
            
            // Submit the form
            document.getElementById('bulkDeleteForm').submit();
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#leaveTable').DataTable({
                "order": [[0, "desc"]], // Sort by Leave ID descending
                "pageLength": 25
            });
        });
    </script>
</body>
</html> 