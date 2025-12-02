<?php
// Prevent any output before JSON response
ob_clean();
header('Content-Type: application/json');

include('session.php');
include('connection.php');

// Enable error reporting but log to file instead of output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

try {
    // Start debugging
    $debug = [];
    $debug['post_data'] = $_POST;
    $debug['files'] = $_FILES;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get employee ID
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Employee ID not provided');
    }

    $debug['employee_id'] = $id;

    // Prepare the base update query
    $fields = [
        'first_name', 'last_name', 'email', 'contact', 'address', 'country',
        'degree', 'Institute', 'start_from', 'end_to',
        'status', 'designation', 'department_id', 'EmpLoc', 'EmpDiv', 'EmpGrade',
        'bank_name', 'account_no', 'iban', 'nominee',
        'visa_number', 'visa_type', 'visa_issue_date', 'visa_expiry_date',
        'passport_number', 'passport_type', 'passport_issue_date', 'passport_expiry_date',
        'country_of_issue', 'passport_issue_place'
    ];

    $updates = [];
    $types = '';
    $values = [];
    $debug['field_updates'] = [];

    // Build the update query dynamically
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "`$field` = ?";
            $types .= 's';
            $values[] = $_POST[$field] === '' ? null : $_POST[$field];
            $debug['field_updates'][$field] = $_POST[$field];
        }
    }

    // Add the ID to the values array
    $types .= 'i';
    $values[] = $id;

    // Handle file uploads
    $upload_dir = 'uploads/';
    $document_fields = ['profile_pic', 'visa_doc', 'passport_doc'];
    $debug['file_uploads'] = [];

    foreach ($document_fields as $doc_field) {
        if (isset($_FILES[$doc_field]) && $_FILES[$doc_field]['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES[$doc_field]['tmp_name'];
            $name = basename($_FILES[$doc_field]['name']);
            $upload_path = $upload_dir . $doc_field . 's/' . time() . '_' . $name;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $updates[] = "`$doc_field` = ?";
                $types .= 's';
                $values[] = $upload_path;
                $debug['file_uploads'][$doc_field] = $upload_path;
            }
        }
    }

    // Update full_name
    if (isset($_POST['first_name']) && isset($_POST['last_name'])) {
        $updates[] = "`full_name` = ?";
        $types .= 's';
        $values[] = $_POST['first_name'] . ' ' . $_POST['last_name'];
        $debug['full_name'] = $_POST['first_name'] . ' ' . $_POST['last_name'];
    }

    if (empty($updates)) {
        throw new Exception('No fields to update');
    }

    // Construct and execute the query
    $query = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
    $debug['query'] = $query;
    $debug['types'] = $types;
    $debug['values'] = $values;

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$values);
    $success = mysqli_stmt_execute($stmt);
    $debug['query_success'] = $success;

    if (!$success) {
        throw new Exception('Failed to update employee: ' . mysqli_error($con));
    }

    // Log the update
    $log_query = "INSERT INTO activity_log (employee, activity, eid, performed_by, type) 
                  VALUES (?, 'Employee details updated', ?, ?, 'update')";
    $log_stmt = mysqli_prepare($con, $log_query);
    
    // Get employee name for the log
    $name_query = "SELECT full_name, eid FROM employees WHERE id = ?";
    $name_stmt = mysqli_prepare($con, $name_query);
    mysqli_stmt_bind_param($name_stmt, 'i', $id);
    mysqli_stmt_execute($name_stmt);
    $emp_result = mysqli_stmt_get_result($name_stmt);
    $emp_data = mysqli_fetch_assoc($emp_result);
    
    mysqli_stmt_bind_param($log_stmt, 'sss', $emp_data['full_name'], $emp_data['eid'], $_SESSION['email']);
    $log_success = mysqli_stmt_execute($log_stmt);
    $debug['log_success'] = $log_success;

    echo json_encode([
        'status' => 'success',
        'message' => 'Employee updated successfully',
        'debug' => $debug
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => $debug ?? []
    ]);
} 