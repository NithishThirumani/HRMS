<?php
include('session.php');
include('connection.php');

if (isset($_GET['id'])) {
    $leave_id = $_GET['id'];
    
    // Get leave details with employee and department information
    $query = "SELECT l.*, e.full_name, e.email, d.name as department
              FROM leaves l
              JOIN employees e ON l.emp_id = e.eid
              JOIN departments d ON e.department_id = d.id
              WHERE l.id = ?";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if ($leave) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">Employee Details</h6>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($leave['full_name']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($leave['department']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($leave['email']); ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Leave Details</h6>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($leave['type_of_leave']); ?></p>
                <p><strong>From:</strong> <?php echo date('d M Y', strtotime($leave['start_date'])); ?></p>
                <p><strong>To:</strong> <?php echo date('d M Y', strtotime($leave['end_date'])); ?></p>
                <p><strong>Total Days:</strong> <?php echo $leave['total_days']; ?></p>
                <p><strong>Status:</strong> <?php echo $leave['status']; ?></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="font-weight-bold">Reason for Leave</h6>
                <p><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></p>
            </div>
        </div>
        <?php if ($leave['doctor_cert']): ?>
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="font-weight-bold">Medical Certificate</h6>
                <a href="../uploads/certificates/<?php echo htmlspecialchars($leave['doctor_cert']); ?>" 
                   class="btn btn-info btn-sm" target="_blank">
                    <i class="fas fa-file-medical"></i> View Certificate
                </a>
            </div>
        </div>
        <?php endif; ?>
        <?php
    } else {
        echo "Leave request not found.";
    }
}
?>