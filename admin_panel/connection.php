<?php
// Error handling at the very top
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

require_once dirname(__DIR__) . '/includes/db_connection.php';

// Add debug logging for session
error_log("[" . date('Y-m-d H:i:s') . "] Session started for IP: " . $_SERVER['REMOTE_ADDR']);
error_reporting(E_ALL);