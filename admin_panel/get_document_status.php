<?php
include('session.php');
include('connection.php');

if (isset($_POST['employee_id'])) {
    $employee_id = mysqli_real_escape_string($con, $_POST['employee_id']);
    
    $query = "SELECT * FROM document_status WHERE employee_id = '$employee_id'";
    $result = mysqli_query($con, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        // Return default values if no record exists
        $default_status = [
            'biometric_login' => 'No',
            'process_evaluation' => 'No',
            'offer_letter' => 'No',
            'resume' => 'No',
            'kyc' => 'No',
            'policy_docs' => 'No',
            'staff_id' => 'No',
            'deem_id_request' => 'No',
            'remarks' => ''
        ];
        echo json_encode($default_status);
    }
}
?>