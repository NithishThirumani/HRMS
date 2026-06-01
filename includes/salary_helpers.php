<?php
/**
 * Salary / payslip helpers shared by admin and employee panels.
 */
if (!function_exists('hrms_ensure_salary_slip')) {
    function hrms_ensure_salary_slip(mysqli $con, int $salaryId): int
    {
        $stmt = $con->prepare('SELECT slip_no FROM salary_slips WHERE salary_id = ? LIMIT 1');
        $stmt->bind_param('i', $salaryId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            return (int) $row['slip_no'];
        }

        $maxResult = $con->query('SELECT COALESCE(MAX(slip_no), 0) + 1 AS next_no FROM salary_slips');
        $nextNo = (int) ($maxResult->fetch_assoc()['next_no'] ?? 1);

        $insert = $con->prepare('INSERT INTO salary_slips (slip_no, salary_id) VALUES (?, ?)');
        $insert->bind_param('ii', $nextNo, $salaryId);
        $insert->execute();

        return $nextNo;
    }
}
