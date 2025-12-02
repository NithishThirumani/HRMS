<?php
include('connection.php');

if(isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $query = "SELECT * FROM employees WHERE id = '$id'";
    $result = mysqli_query($con, $query);
    $employee = mysqli_fetch_assoc($result);
    
    if($employee) {
        ?>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                
                <img src="../uploads/<?php echo $employee['profile_pic'] ?: 'default.jpg'; ?>" 
                     class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
                <h4><?php echo htmlspecialchars($employee['full_name']); ?></h4>
                <p class="badge badge-primary"><?php echo htmlspecialchars($employee['designation']); ?></p>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Employee Details</h6>
                        <hr>
                        <p><strong>EID:</strong> <?php echo htmlspecialchars($employee['eid']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($employee['contact']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Labor Card Information</h6>
                        <hr>
                        <p><strong>MOL ID:</strong> <?php echo htmlspecialchars($employee['MOLID']); ?></p>
                        <p><strong>Labor Card No:</strong> <?php echo htmlspecialchars($employee['labour_card_no']); ?></p>
                        <p><strong>Start Date:</strong> <?php echo date('d-m-Y', strtotime($employee['labour_card_start_date'])); ?></p>
                        <p><strong>End Date:</strong> <?php echo date('d-m-Y', strtotime($employee['labour_card_end_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>