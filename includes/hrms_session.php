<?php
/**
 * Scoped PHP sessions so admin and employee can stay logged in
 * in different browser tabs without overwriting each other.
 */

function hrms_session_name(string $scope): string
{
    $map = [
        'admin' => 'HRMS_ADMIN',
        'super_admin' => 'HRMS_SUPER',
        'employee' => 'HRMS_EMP',
        'login' => 'HRMS_LOGIN',
    ];
    return $map[$scope] ?? 'HRMS_LOGIN';
}

function hrms_start_session(string $scope): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/emps/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name(hrms_session_name($scope));
    session_start();
}

function hrms_destroy_session(string $scope): void
{
    hrms_start_session($scope);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? false);
    }
    session_destroy();
}
