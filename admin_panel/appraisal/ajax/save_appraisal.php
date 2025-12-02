<?php
session_start();
require_once(__DIR__ . '/../../../connection.php');
require_once(__DIR__ . '/../../../classes/AppraisalPeriod.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $con->begin_transaction();
    
    // Insert appraisal period
    $status = 'Draft';
    $query = "INSERT INTO appraisal_periods (start_date, end_date, status, created_by) 
              VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssi", $_POST['start_date'], $_POST['end_date'], $status, $_SESSION['user_id']);
    $stmt->execute();
    $periodId = $con->insert_id;
    
    // Get valid employee IDs from selected departments
    $departments = $_POST['departments'];
    $placeholders = str_repeat('?,', count($departments) - 1) . '?';
    $employeeQuery = "SELECT eid FROM employees WHERE department IN ($placeholders) AND status = 'Active'";
    $stmt = $con->prepare($employeeQuery);
    $stmt->bind_param(str_repeat('s', count($departments)), ...$departments);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Create assignments for valid employees
    while ($employee = $result->fetch_assoc()) {
        $assignQuery = "INSERT INTO appraisal_assignments (period_id, employee_id, status) 
                       VALUES (?, ?, 'Pending')";
        $stmt = $con->prepare($assignQuery);
        $stmt->bind_param("is", $periodId, $employee['eid']);
        $stmt->execute();
    }
    
    $con->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}