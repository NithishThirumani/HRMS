-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2025 at 07:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employees_management`
--

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `process_leave_approval` (IN `p_leave_id` INT, IN `p_approver_id` VARCHAR(20), IN `p_action` VARCHAR(20), IN `p_comments` TEXT)   BEGIN
    DECLARE v_approver_role VARCHAR(50);
    DECLARE v_next_approver VARCHAR(50);
    DECLARE v_current_level INT;
    DECLARE v_max_level INT;
    DECLARE v_department_id INT;
    
    -- Get approver details
    SELECT new_role_id 
    INTO v_approver_role
    FROM employees 
    WHERE eid = p_approver_id;
    
    -- Get current workflow status
    SELECT current_level, max_level, department_id
    INTO v_current_level, v_max_level, v_department_id
    FROM leave_workflow
    WHERE leave_id = p_leave_id;
    
    -- Start transaction
    START TRANSACTION;
    
    IF p_action = 'APPROVED' THEN
        IF v_current_level >= v_max_level THEN
            -- Final approval
            UPDATE leaves 
            SET status = 'APPROVED'
            WHERE id = p_leave_id;
            
            UPDATE leave_workflow
            SET status = 'APPROVED',
                current_approver_role = NULL,
                next_approver_role = NULL
            WHERE leave_id = p_leave_id;
        ELSE
            -- Get next approver
            SELECT parent_role_id 
            INTO v_next_approver
            FROM role_hierarchy
            WHERE department_id = v_department_id
            AND workflow_level = v_current_level + 1;
            
            -- Update to next level
            UPDATE leave_workflow
            SET current_level = v_current_level + 1,
                current_approver_role = v_next_approver,
                status = 'IN_PROGRESS'
            WHERE leave_id = p_leave_id;
        END IF;
    ELSE
        -- Rejected
        UPDATE leaves 
        SET status = 'REJECTED'
        WHERE id = p_leave_id;
        
        UPDATE leave_workflow
        SET status = 'REJECTED'
        WHERE leave_id = p_leave_id;
    END IF;
    
    -- Record action in history
    INSERT INTO leave_approval_history (
        leave_id,
        action_by_id,
        action_by_role,
        action,
        comments
    ) VALUES (
        p_leave_id,
        p_approver_id,
        v_approver_role,
        p_action,
        p_comments
    );
    
    COMMIT;
END$$

CREATE PROCEDURE `submit_leave_request` (IN `p_employee_id` VARCHAR(20), IN `p_start_date` DATE, IN `p_end_date` DATE, IN `p_leave_type` VARCHAR(50), IN `p_reason` TEXT)   BEGIN
    DECLARE v_employee_role VARCHAR(50);
    DECLARE v_department_id INT;
    DECLARE v_next_approver VARCHAR(50);
    DECLARE v_max_level INT;
    
    -- Get employee details
    SELECT new_role_id, department_id 
    INTO v_employee_role, v_department_id
    FROM employees 
    WHERE eid = p_employee_id;
    
    -- Get next approver from hierarchy
    SELECT parent_role_id, workflow_level 
    INTO v_next_approver, v_max_level
    FROM role_hierarchy 
    WHERE role_id = v_employee_role 
    AND department_id = v_department_id;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Insert leave request
    INSERT INTO leaves (
        emp_id, 
        start_date, 
        end_date, 
        type_of_leave, 
        reason, 
        status
    ) VALUES (
        p_employee_id,
        p_start_date,
        p_end_date,
        p_leave_type,
        p_reason,
        'PENDING'
    );
    
    -- Create workflow entry
    INSERT INTO leave_workflow (
        leave_id,
        employee_id,
        employee_role,
        current_approver_role,
        next_approver_role,
        department_id,
        current_level,
        max_level,
        status
    ) VALUES (
        LAST_INSERT_ID(),
        p_employee_id,
        v_employee_role,
        v_next_approver,
        v_next_approver,
        v_department_id,
        1,
        v_max_level,
        'PENDING'
    );
    
    COMMIT;
END$$

--
-- Functions
--
CREATE FUNCTION `get_next_approver` (`p_current_role` VARCHAR(50), `p_department_id` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE next_role VARCHAR(50);
    
    SELECT parent_role_id INTO next_role
    FROM role_hierarchy
    WHERE role_id = p_current_role
    AND department_id = p_department_id
    LIMIT 1;
    
    RETURN next_role;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `employee` varchar(100) DEFAULT NULL,
  `activity` text DEFAULT NULL,
  `eid` varchar(10) DEFAULT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `pic` varchar(50) DEFAULT 'undraw_profile.jpg',
  `role` varchar(50) DEFAULT 'admin',
  `admin_type` enum('super_admin','admin') DEFAULT 'admin',
  `password` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Inactive',
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `user_name`, `email`, `gender`, `contact`, `pic`, `role`, `admin_type`, `password`, `status`, `last_login`, `last_login_ip`) VALUES
(3, 'superadmin', 'superadmin@communikmarketing.com', '', '', 'undraw_profile.jpg', 'super_admin', 'super_admin', '$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW', 'Active', '2025-06-09 23:49:32', '::1'),
(4, 'admincommunik', 'communikadmin@communikmarketing.com', 'Male', '7894561232', '', 'admin', 'admin', '$2y$10$7ZUhH5xA69XTGSHf6J9mpuM/074u81DxpjRKKXvB46/ovL0k4JVqm', 'Active', '2025-06-15 11:08:49', '::1');

--
-- Triggers `admin`
--
DELIMITER $$
CREATE TRIGGER `after_admin_create` AFTER INSERT ON `admin` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (NEW.id, 'create', CONCAT('New admin created: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `after_admin_login` AFTER UPDATE ON `admin` FOR EACH ROW BEGIN
    IF (NEW.last_login IS NOT NULL AND OLD.last_login != NEW.last_login) OR 
       (OLD.last_login IS NULL AND NEW.last_login IS NOT NULL) THEN
        INSERT INTO audit_logs (user_id, action_type, description, ip_address)
        VALUES (NEW.id, 'login', CONCAT('Admin login: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
    END IF;
END
$$
DELIMITER ; 