<?php
include('session.php');

if(isset($_POST['leave_id'])) {
    $leave_id = $_POST['leave_id'];
    
    $query = "SELECT l.*, e.full_name, d.name as department, e.email
              FROM leaves l 
              JOIN employees e ON l.emp_id = e.id 
              LEFT JOIN departments d ON e.department_id = d.id 
              WHERE l.id = ?";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    
    if($leave) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">Employee Information</h6>
                <p><strong>Name:</strong> <?php echo $leave['full_name']; ?></p>
                <p><strong>Department:</strong> <?php echo $leave['department']; ?></p>
                <p><strong>Email:</strong> <?php echo $leave['email']; ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Leave Information</h6>
                <p><strong>Type:</strong> <?php echo $leave['type_of_leave']; ?></p>
                <p><strong>Duration:</strong> <?php echo date('d M Y', strtotime($leave['start_date'])); ?> to <?php echo date('d M Y', strtotime($leave['end_date'])); ?></p>
                <p><strong>Total Days:</strong> <?php echo $leave['total_days']; ?></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <h6 class="font-weight-bold">Leave Details</h6>
                <p><strong>Reason:</strong> <?php echo $leave['reason']; ?></p>
                <?php if($leave['doctor_cert']) { ?>
                    <p><strong>Medical Certificate:</strong> 
                        <a href="../uploads/certificates/<?php echo $leave['doctor_cert']; ?>" target="_blank">View Certificate</a>
                    </p>
                <?php } ?>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold">HR Review</h6>
                <p><strong>Status:</strong> <?php echo $leave['hr_status']; ?></p>
                <p><strong>Remarks:</strong> <?php echo $leave['hr_remarks'] ? $leave['hr_remarks'] : 'No remarks'; ?></p>
                <p><strong>Reviewed By:</strong> <?php echo $leave['hr_by'] ? $leave['hr_by'] : 'Pending'; ?></p>
                <p><strong>Review Date:</strong> <?php echo $leave['hr_action_date'] ? date('d M Y H:i', strtotime($leave['hr_action_date'])) : 'Pending'; ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold">Admin Review</h6>
                <p><strong>Status:</strong> <?php echo isset($leave['admin_status']) ? $leave['admin_status'] : 'Pending'; ?></p>
                <p><strong>Remarks:</strong> <?php echo isset($leave['admin_remarks']) && $leave['admin_remarks'] ? $leave['admin_remarks'] : 'No remarks'; ?></p>
                <p><strong>Reviewed By:</strong> <?php echo isset($leave['admin_by']) && $leave['admin_by'] ? $leave['admin_by'] : 'Pending'; ?></p>
                <p><strong>Review Date:</strong> <?php echo isset($leave['admin_action_date']) && $leave['admin_action_date'] ? date('d M Y H:i', strtotime($leave['admin_action_date'])) : 'Pending'; ?></p>
            </div>
        </div>
        <?php
    } else {
        echo "Leave request not found.";
    }
}
?>