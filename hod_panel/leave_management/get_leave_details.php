<?php
session_start();
include('../session.php');
include('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    http_response_code(401);
    echo "Unauthorized access";
    exit();
}

if (!isset($_GET['leave_id'])) {
    http_response_code(400);
    echo "Leave ID is required";
    exit();
}

$leave_id = intval($_GET['leave_id']);

// Get leave details with employee and department information
$query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email, e.eid as employee_eid,
          d.name as department_name, d.name as dept_name
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.id = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$result = $stmt->get_result();
$leave = $result->fetch_assoc();

if (!$leave) {
    echo '<div class="alert alert-danger">Leave application not found.</div>';
    exit();
}

// Format dates
$start_date = date('d-m-Y', strtotime($leave['start_date']));
$end_date = date('d-m-Y', strtotime($leave['end_date']));
$applied_date = date('d-m-Y H:i', strtotime($leave['applied_at']));
$action_date = $leave['hod_action_date'] ? date('d-m-Y H:i', strtotime($leave['hod_action_date'])) : 'Not yet processed';

// Get status badge class
$status_class = '';
switch($leave['status']) {
    case 'Pending':
        $status_class = 'badge-warning';
        break;
    case 'Recommended':
        $status_class = 'badge-success';
        break;
    case 'Not Recommended':
        $status_class = 'badge-danger';
        break;
    case 'Approved':
        $status_class = 'badge-success';
        break;
    case 'Rejected':
        $status_class = 'badge-danger';
        break;
    default:
        $status_class = 'badge-secondary';
}
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="font-weight-bold text-primary">Employee Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Employee Name:</strong></td>
                <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Employee ID:</strong></td>
                <td><?php echo htmlspecialchars($leave['employee_eid']); ?></td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td><?php echo htmlspecialchars($leave['dept_name'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($leave['employee_email'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="font-weight-bold text-primary">Leave Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Leave Type:</strong></td>
                <td><span class="badge badge-primary"><?php echo htmlspecialchars($leave['type_of_leave']); ?></span></td>
            </tr>
            <tr>
                <td><strong>Start Date:</strong></td>
                <td><?php echo $start_date; ?></td>
            </tr>
            <tr>
                <td><strong>End Date:</strong></td>
                <td><?php echo $end_date; ?></td>
            </tr>
            <tr>
                <td><strong>Total Days:</strong></td>
                <td><span class="badge badge-info"><?php echo $leave['total_days']; ?> days</span></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($leave['status']); ?></span></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h6 class="font-weight-bold text-primary">Leave Details</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Reason:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></td>
            </tr>
            <tr>
                <td><strong>Applied On:</strong></td>
                <td><?php echo $applied_date; ?></td>
            </tr>
            <?php if ($leave['hod_remarks']): ?>
            <tr>
                <td><strong>HOD Remarks:</strong></td>
                <td><?php echo nl2br(htmlspecialchars($leave['hod_remarks'])); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($leave['hod_action_date']): ?>
            <tr>
                <td><strong>HOD Action Date:</strong></td>
                <td><?php echo $action_date; ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if ($leave['status'] == 'Pending'): ?>
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Action Required:</strong> This leave application is pending your recommendation or approval.
        </div>
    </div>
</div>
<?php endif; ?> 