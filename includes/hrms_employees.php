<?php
/**
 * Helpers for matching leaves.emp_id (eid or numeric id string) to employees rows.
 */
if (!function_exists('hrms_leave_employee_join')) {
    function hrms_leave_employee_join(string $leaveAlias = 'l', string $empAlias = 'e'): string
    {
        // Mixed collations (general_ci vs 0900_ai_ci) on Aiven/MySQL 8 break JOINs without COLLATE.
        $collation = 'utf8mb4_unicode_ci';
        return "({$leaveAlias}.emp_id COLLATE {$collation} = {$empAlias}.eid COLLATE {$collation}"
            . " OR {$leaveAlias}.emp_id COLLATE {$collation} = CAST({$empAlias}.id AS CHAR) COLLATE {$collation})";
    }
}

if (!function_exists('hrms_leave_balance_emp_id')) {
    function hrms_leave_balance_emp_id(array $leaveRow): string
    {
        if (!empty($leaveRow['employee_eid'])) {
            return (string) $leaveRow['employee_eid'];
        }

        $empId = (string) ($leaveRow['emp_id'] ?? '');
        return $empId;
    }
}
