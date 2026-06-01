<?php
/**
 * Helpers for matching leaves.emp_id (eid or numeric id string) to employees rows.
 */
if (!function_exists('hrms_leave_employee_join')) {
    function hrms_leave_employee_join(string $leaveAlias = 'l', string $empAlias = 'e'): string
    {
        return "({$leaveAlias}.emp_id = {$empAlias}.eid OR {$leaveAlias}.emp_id = CAST({$empAlias}.id AS CHAR))";
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
