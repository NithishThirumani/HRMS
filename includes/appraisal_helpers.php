<?php
/**
 * Shared helpers for the appraisal module.
 */
if (!function_exists('hrms_get_departments_list')) {
    function hrms_get_departments_list(mysqli $con): array
    {
        $departments = [];
        $result = $con->query('SELECT id, name FROM departments ORDER BY name ASC');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }
            $result->free();
        }
        return $departments;
    }
}
