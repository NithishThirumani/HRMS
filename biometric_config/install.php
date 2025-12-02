<?php
/**
 * Biometric Integration Installation Script
 * 
 * Creates necessary database tables for biometric integration
 */

require_once 'config.php';
require_once 'database.php';

// Create database tables
function createTables() {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create biometric_users table
    $query = "CREATE TABLE IF NOT EXISTS biometric_users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        name VARCHAR(255) NOT NULL,
        device VARCHAR(50) NOT NULL,
        registered_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY (user_id, device),
        FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($query);
    
    // Create attendance_logs table if not exists
    $query = "CREATE TABLE IF NOT EXISTS attendance_logs (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        timestamp DATETIME NOT NULL,
        status VARCHAR(20) NOT NULL,
        device_id INT(11) NOT NULL,
        synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (user_id),
        INDEX (timestamp),
        FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($query);
    
    // Create sync_logs table
    $query = "CREATE TABLE IF NOT EXISTS sync_logs (
        id INT(11) NOT NULL AUTO_INCREMENT,
        device VARCHAR(50) NOT NULL,
        records_synced INT(11) NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL,
        error_message TEXT NULL,
        sync_time DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($query);
    
    // Create settings table if not exists
    $query = "CREATE TABLE IF NOT EXISTS biometric_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        value TEXT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($query);
    
    // Insert default settings
    $settings = [
        ['sync_interval', '5'],
        ['sync_auto', 'true'],
        ['sync_log_retention', '90'],
        ['working_hours_start', '08:00'],
        ['working_hours_end', '17:00']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO biometric_settings (name, value) VALUES (?, ?)");
    
    foreach ($settings as $setting) {
        $stmt->bind_param('ss', $setting[0], $setting[1]);
        $stmt->execute();
    }
    
    $stmt->close();
    
    return true;
}

// Run installation
if (createTables()) {
    echo "Installation completed successfully!";
} else {
    echo "Installation failed!";
}