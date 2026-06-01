<?php
require_once __DIR__ . '/../bootstrap_session.php';
require_once dirname(__DIR__, 3) . '/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $departments = $_POST['departments'] ?? [];

    if ($startDate === '' || $endDate === '' || empty($departments)) {
        throw new Exception('Start date, end date, and at least one department are required.');
    }

    $departmentIds = array_map('intval', (array) $departments);
    $departmentIds = array_filter($departmentIds, static fn($id) => $id > 0);

    if (empty($departmentIds)) {
        throw new Exception('No valid departments selected.');
    }

    $con->begin_transaction();

    $status = 'Active';
    $createdBy = (int) $_SESSION['user_id'];
    $stmt = $con->prepare(
        'INSERT INTO appraisal_periods (start_date, end_date, status, created_by) VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('sssi', $startDate, $endDate, $status, $createdBy);
    $stmt->execute();
    $periodId = (int) $con->insert_id;

    $placeholders = implode(',', array_fill(0, count($departmentIds), '?'));
    $types = str_repeat('i', count($departmentIds));
    $employeeSql = "SELECT id, eid FROM employees 
                    WHERE department_id IN ($placeholders) 
                    AND LOWER(TRIM(status)) = 'active'";
    $stmt = $con->prepare($employeeSql);
    $stmt->bind_param($types, ...$departmentIds);
    $stmt->execute();
    $employees = $stmt->get_result();

    $created = 0;
    while ($employee = $employees->fetch_assoc()) {
        $empId = (int) $employee['id'];
        $eid = $employee['eid'];

        $check = $con->prepare(
            'SELECT appraisal_id FROM employee_appraisals WHERE employee_id = ? AND period_id = ? LIMIT 1'
        );
        $check->bind_param('ii', $empId, $periodId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            continue;
        }

        $ins = $con->prepare(
            "INSERT INTO employee_appraisals (employee_id, period_id, status) VALUES (?, ?, 'Pending')"
        );
        $ins->bind_param('ii', $empId, $periodId);
        $ins->execute();

        $assign = $con->prepare(
            "INSERT INTO appraisal_assignments (period_id, employee_id, status) VALUES (?, ?, 'Pending')"
        );
        $assign->bind_param('is', $periodId, $eid);
        $assign->execute();

        $created++;
    }

    $con->commit();

    echo json_encode([
        'success' => true,
        'message' => "Appraisal initiated for $created employee(s).",
        'period_id' => $periodId,
    ]);
} catch (Exception $e) {
    if (isset($con) && $con instanceof mysqli) {
        $con->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
