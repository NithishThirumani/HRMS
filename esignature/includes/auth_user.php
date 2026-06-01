<?php
/**
 * Shared helpers for e-signature module.
 */

function esign_approver_role_id(mysqli $con, string $type, int $approverId): int
{
    if ($type === 'emp' && $approverId > 0) {
        $stmt = $con->prepare('SELECT role_id FROM employees WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $approverId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && !empty($row['role_id'])) {
            return (int)$row['role_id'];
        }
    }
    return 0;
}

function esign_approver_name(mysqli $con, int $approverId, ?string $approverType): string
{
    $type = strtolower((string)$approverType);
    if ($approverId <= 0) {
        return '-';
    }

    if ($type === 'emp' || $type === 'employee') {
        $stmt = $con->prepare('SELECT full_name FROM employees WHERE id = ? LIMIT 1');
    } elseif ($type === 'head' || $type === 'department_head') {
        $stmt = $con->prepare('SELECT head_name AS full_name FROM department_heads WHERE id = ? LIMIT 1');
    } elseif ($type === 'admin') {
        $stmt = $con->prepare('SELECT user_name AS full_name FROM admin WHERE id = ? LIMIT 1');
    } else {
        $stmt = $con->prepare('SELECT full_name FROM employees WHERE id = ? LIMIT 1');
    }

    $stmt->bind_param('i', $approverId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['full_name'] ?? ('User #' . $approverId);
}

function esign_creator_name(mysqli $con, int $createdBy): string
{
    $stmt = $con->prepare('SELECT full_name FROM employees WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $createdBy);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && !empty($row['full_name'])) {
        return $row['full_name'];
    }

    $stmt = $con->prepare('SELECT user_name FROM admin WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $createdBy);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && !empty($row['user_name'])) {
        return $row['user_name'];
    }

    return 'Unknown';
}

function esign_resolve_user(mysqli $con): ?array
{
    if (!isset($_SESSION['email'])) {
        return null;
    }

    $email = $_SESSION['email'];

    $stmt = $con->prepare("SELECT id, email, role, user_name FROM admin WHERE email = ? AND LOWER(status) = 'active'");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin) {
        $admin['full_name'] = $admin['user_name'] ?? $admin['email'];
        return ['user' => $admin, 'user_type' => 'admin'];
    }

    $stmt = $con->prepare('SELECT dh.id, dh.head_email AS email, dh.head_name AS full_name, dh.department_id
                          FROM department_heads dh WHERE dh.head_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $dept_head = $stmt->get_result()->fetch_assoc();
    if ($dept_head) {
        return ['user' => $dept_head, 'user_type' => 'department_head'];
    }

    $stmt = $con->prepare("SELECT e.id, e.eid, e.full_name, e.department_id
                          FROM employees e WHERE e.email = ? AND LOWER(e.status) = 'active'");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
    if ($employee) {
        return ['user' => $employee, 'user_type' => 'employee'];
    }

    return null;
}
