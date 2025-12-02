<?php
/**
 * Biometric Device Configuration
 * 
 * This file contains configuration settings for biometric devices
 */

// Database connection parameters - update these to match your existing database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emps');

// Biometric device settings
$biometric_config = [
    'default_device' => [
        'ip_address' => '192.168.1.100', // Change to your device IP
        'port' => 4370,                  // Standard ZKTeco port
        'device_id' => 1,
        'timeout' => 5,                  // connection timeout in seconds
        'model' => 'ZKTeco F18',         // Change to your device model
        'comm_key' => 0,                 // communication password, 0 means no password
    ],
    // Add more devices as needed
];

// Attendance settings
$attendance_settings = [
    'sync_interval' => 5,     // minutes
    'log_retention' => 90,    // days
    'auto_sync' => true,
    'working_hours' => [
        'start' => '08:00',
        'end' => '17:00',
    ],
];