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
                
                <img src="<?php echo !empty($row['profile_pic']) ? '../uploads/' . $row['profile_pic'] : '../uploads/profile_pics/default.jpg'; ?>" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px;"alt="Profile Picture">
                                                    
                
                <h4><?php echo htmlspecialchars($employee['full_name'] ?? ''); ?></h4>
                <p class="badge badge-primary"><?php echo htmlspecialchars($employee['designation'] ?? ''); ?></p>
            </div>
            <div class="col-md-8">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#personal">Personal Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#employment">Employment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#documents">Documents</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div id="personal" class="tab-pane active">
                        <div class="row">
                            <div class="col-md-6">
                                 <p><strong>Birthday:</strong> <?php echo htmlspecialchars($employee['birthday'] ?? ''); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email'] ?? ''); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($employee['contact'] ?? ''); ?></p>
                                <p><strong>Marital Status:</strong> <?php echo htmlspecialchars($employee['maritalsts'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($employee['address'] ?? ''); ?></p>
                                <p><strong>Country:</strong> <?php echo htmlspecialchars($employee['country'] ?? ''); ?></p>
                                <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($employee['blood_group'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                    <div id="employment" class="tab-pane fade">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['eid']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($employee['EmpLoc']); ?></p>
                                <p><strong>Division:</strong> <?php echo htmlspecialchars($employee['EmpDiv']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Grade:</strong> <?php echo htmlspecialchars($employee['EmpGrade']); ?></p>
                                <p><strong>Date of Joining:</strong> <?php echo htmlspecialchars($employee['doj']); ?></p>
                                <p><strong>Reporting Manager:</strong> <?php echo htmlspecialchars($employee['reporting_manager']); ?></p>
                                <p><strong>Cost Center:</strong> <?php echo htmlspecialchars($employee['EmpCostcenter']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div id="documents" class="tab-pane fade">
                        <div class="row">
                            <div class="col-12">
                                <p><strong>MOL ID:</strong> <?php echo htmlspecialchars($employee['MOLID']); ?></p>
                                 <div class="mt-3">
                                    <h6>Documents:</h6>
                                    <?php if($employee['visa_doc']): ?>
                                    
                                           <a href="../uploads/<?php echo htmlspecialchars($employee['visa_doc']); ?>" 
                                           class="btn btn-sm btn-info m-1" target="_blank">
                                            <i class="fas fa-passport mr-1"></i>Visa
                                        </a>
                                    <?php endif; ?>
                                    <?php if($employee['passport_doc']): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($employee['passport_doc']); ?>" 
                                           class="btn btn-sm btn-info m-1" target="_blank">
                                            <i class="fas fa-id-card mr-1"></i>Passport
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>