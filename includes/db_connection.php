<?php
/**
 * Shared database connection for HRMS.
 * Uses Docker env vars when present; falls back to local XAMPP defaults.
 */
if (!function_exists('hrms_get_db_connection')) {
    function hrms_get_db_connection(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD');
        if ($pass === false) {
            $pass = 'Nizam123$';
        }
        $name = getenv('DB_NAME') ?: 'EMPS';
        $port = (int) (getenv('DB_PORT') ?: 3306);
        $useSsl = filter_var(getenv('DB_SSL') ?: '0', FILTER_VALIDATE_BOOLEAN);

        $connection = mysqli_init();
        if (!$connection) {
            throw new Exception('mysqli_init failed');
        }

        if ($useSsl) {
            mysqli_ssl_set($connection, null, null, null, null, null);
        }

        $flags = $useSsl ? MYSQLI_CLIENT_SSL : 0;
        if (!mysqli_real_connect($connection, $host, $user, $pass, $name, $port, null, $flags)) {
            throw new Exception('Connection failed: ' . mysqli_connect_error());
        }

        if (!mysqli_set_charset($connection, 'utf8mb4')) {
            throw new Exception('Error setting charset: ' . mysqli_error($connection));
        }

        return $connection;
    }
}

try {
    $con = hrms_get_db_connection();
} catch (Exception $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] Database Error: ' . $e->getMessage());
    die('Database connection error. Please try again later.');
}
