-- Create system settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_name VARCHAR(100) NOT NULL DEFAULT 'Employeeshub',
    site_email VARCHAR(100) NOT NULL DEFAULT 'admin@employeeshub.com',
    site_contact VARCHAR(20) NOT NULL DEFAULT '+1234567890',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create email settings table
CREATE TABLE IF NOT EXISTS email_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    smtp_host VARCHAR(100) NOT NULL DEFAULT 'smtp.gmail.com',
    smtp_user VARCHAR(100) NOT NULL DEFAULT '',
    smtp_pass VARCHAR(100) NOT NULL DEFAULT '',
    smtp_port INT NOT NULL DEFAULT 587,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create backup settings table
CREATE TABLE IF NOT EXISTS backup_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL DEFAULT 'daily',
    retention_days INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default values
INSERT INTO system_settings (id, site_name, site_email, site_contact) 
VALUES (1, 'Employeeshub', 'admin@employeeshub.com', '+1234567890')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO email_settings (id, smtp_host, smtp_user, smtp_port) 
VALUES (1, 'smtp.gmail.com', '', 587)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO backup_settings (id, frequency, retention_days) 
VALUES (1, 'daily', 30)
ON DUPLICATE KEY UPDATE id=id; 