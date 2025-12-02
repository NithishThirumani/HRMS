<?php
include('session.php');
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = mysqli_real_escape_string($con, $_POST['employee_id']);
    
    // Fields to update
    $fields = [
        'biometric_login',
        'process_evaluation',
        'offer_letter',
        'resume',
        'kyc',
        'policy_docs',
        'staff_id',
        'deem_id_request',
        'remarks'
    ];
    
    // Build the values array
    $values = [];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $values[$field] = mysqli_real_escape_string($con, $_POST[$field]);
        }
    }
    
    // Check if record exists
    $check_query = "SELECT id FROM document_status WHERE employee_id = '$employee_id'";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing record
        $set_clauses = [];
        foreach ($values as $field => $value) {
            $set_clauses[] = "$field = '$value'";
        }
        $set_string = implode(', ', $set_clauses);
        
        $query = "UPDATE document_status SET $set_string WHERE employee_id = '$employee_id'";
    } else {
        // Insert new record
        $fields_string = implode(', ', array_keys($values));
        $values_string = "'" . implode("', '", $values) . "'";
        
        $query = "INSERT INTO document_status (employee_id, $fields_string) 
                  VALUES ('$employee_id', $values_string)";
    }
    
    if (mysqli_query($con, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($con)]);
    }
}
?>