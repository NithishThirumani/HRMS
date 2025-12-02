<?php
include('connection.php');

if(isset($_POST['department_id']) && isset($_POST['department_name'])) {
    $department_id = mysqli_real_escape_string($con, $_POST['department_id']);
    
    $managers = array();
    
    // Get Managing Director
    $md_query = "SELECT id, eid, full_name, designation 
                 FROM employees 
                 WHERE designation = 'Managing Director' 
                 AND status = 'active'";
    $md_result = mysqli_query($con, $md_query);
    
    while($row = mysqli_fetch_assoc($md_result)) {
        $managers[] = $row;
    }
    
    // Get department specific managers using only department_id
    $dept_query = "SELECT id, eid, full_name, designation 
                   FROM employees 
                   WHERE department_id = '$department_id' 
                   AND status = 'active'
                   AND (role = 'HOD' 
                        OR designation LIKE '%Manager%' 
                        OR designation LIKE '%Team Leader%'
                        OR designation LIKE '%Director%')
                   ORDER BY 
                        CASE 
                            WHEN designation LIKE '%Director%' THEN 1
                            WHEN designation LIKE '%Manager%' THEN 2
                            WHEN designation LIKE '%Team Leader%' THEN 3
                            ELSE 4
                        END";
    
    $dept_result = mysqli_query($con, $dept_query);
    
    while($row = mysqli_fetch_assoc($dept_result)) {
        $managers[] = $row;
    }
    
    echo '<option value="">Select Reporting Manager</option>';
    foreach($managers as $manager) {
        echo '<option value="' . htmlspecialchars($manager['eid']) . '">' 
             . htmlspecialchars($manager['full_name']) . ' (' . htmlspecialchars($manager['designation']) . ')</option>';
    }
} else {
    echo '<option value="">Select Reporting Manager</option>';
}
?>