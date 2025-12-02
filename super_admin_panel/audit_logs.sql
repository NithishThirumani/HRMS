-- Add missing columns to admin table
ALTER TABLE `admin` 
ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `last_login_ip` varchar(45) DEFAULT NULL;

-- Create audit_logs table
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` enum('login','create','update','delete') NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_type` (`action_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS after_admin_login;
DROP TRIGGER IF EXISTS after_admin_create;
DROP TRIGGER IF EXISTS after_admin_update;
DROP TRIGGER IF EXISTS before_admin_delete;

-- Create trigger for admin login
DELIMITER //
CREATE TRIGGER after_admin_login
AFTER UPDATE ON admin
FOR EACH ROW
BEGIN
    IF (NEW.last_login IS NOT NULL AND OLD.last_login != NEW.last_login) OR 
       (OLD.last_login IS NULL AND NEW.last_login IS NOT NULL) THEN
        INSERT INTO audit_logs (user_id, action_type, description, ip_address)
        VALUES (NEW.id, 'login', CONCAT('Admin login: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
    END IF;
END //
DELIMITER ;

-- Create trigger for admin creation
DELIMITER //
CREATE TRIGGER after_admin_create
AFTER INSERT ON admin
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (NEW.id, 'create', CONCAT('New admin created: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
END //
DELIMITER ;

-- Create trigger for admin updates
DELIMITER //
CREATE TRIGGER after_admin_update
AFTER UPDATE ON admin
FOR EACH ROW
BEGIN
    IF NOT (NEW.last_login != OLD.last_login) THEN -- Skip if it's just a login update
        INSERT INTO audit_logs (user_id, action_type, description, ip_address)
        VALUES (NEW.id, 'update', 
            CONCAT('Admin updated: ', NEW.user_name, 
                CASE 
                    WHEN NEW.user_name != OLD.user_name THEN CONCAT(' (name changed from ', OLD.user_name, ')')
                    WHEN NEW.email != OLD.email THEN CONCAT(' (email changed from ', OLD.email, ')')
                    WHEN NEW.status != OLD.status THEN CONCAT(' (status changed from ', OLD.status, ' to ', NEW.status, ')')
                    ELSE ''
                END
            ), 
            COALESCE(NEW.last_login_ip, '127.0.0.1'));
    END IF;
END //
DELIMITER ;

-- Create trigger for admin deletion
DELIMITER //
CREATE TRIGGER before_admin_delete
BEFORE DELETE ON admin
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (OLD.id, 'delete', CONCAT('Admin deleted: ', OLD.user_name), COALESCE(OLD.last_login_ip, '127.0.0.1'));
END //
DELIMITER ; 