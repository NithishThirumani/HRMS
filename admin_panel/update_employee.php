<?php
// Start buffer and suppress ALL output
ob_start();
@include('session.php');
@include('connection.php');

// Immediately clean buffer after includes
ob_end_clean();
ob_start();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'c:\xampp\htdocs\emps\admin_panel\update_employee_errors.log');

try {
    // Verify database connection
    if (!$con || mysqli_connect_errno()) {
        throw new Exception('Database connection failed');
    }

    // Validate POST input
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid employee ID');
    }
    $id = (int)$_POST['id'];

    // Define allowed fields
    $fields = [
        'first_name', 'last_name', 'email', 'birthday', 'gender', 'maritalsts',
        'blood_group', 'contact', 'address', 'department_id', 'designation', 'doj',
        'labour_card_no', 'labour_card_start_date', 'labour_card_end_date',
        'visa_number', 'visa_type', 'visa_issue_date', 'visa_expiry_date',
        'passport_number', 'passport_type', 'passport_issue_date', 'passport_expiry_date',
        'country_of_issue', 'passport_issue_place', 'degree', 'Institute',
        'start_from', 'end_to', 'bank_name', 'account_no', 'iban', 'nominee'
    ];

    // Collect updates and values
    $updates = [];
    $values = [];
    $types = '';

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = $_POST[$field] === '' ? null : $_POST[$field];
            $updates[] = "`$field` = ?";
            $values[] = $value;
            $types .= $value === null ? 's' : 's'; // Handle null as string
        }
    }

    if (empty($updates)) {
        throw new Exception('No fields to update');
    }

    // Add ID as last parameter
    $values[] = $id;
    $types .= 'i';

    // Prepare statement
    $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = ?";
    if (!($stmt = mysqli_prepare($con, $sql))) {
        throw new Exception('Prepare failed: ' . mysqli_error($con));
    }

    // Bind parameters correctly using references
    $params = array_merge([$types], $values);
    $refs = [];
    foreach ($params as $key => $param) {
        $refs[$key] = &$params[$key];
    }

    if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
        throw new Exception('Bind failed: ' . mysqli_stmt_error($stmt));
    }

    // Execute and validate
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
    }

    // Final JSON output with exit
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Update successful'
    ]);
    exit();

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($con);
