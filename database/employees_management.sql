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
CREATE DEFINER=`root`@`localhost` PROCEDURE `process_leave_approval` (IN `p_leave_id` INT, IN `p_approver_id` VARCHAR(20), IN `p_action` VARCHAR(20), IN `p_comments` TEXT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `submit_leave_request` (IN `p_employee_id` VARCHAR(20), IN `p_start_date` DATE, IN `p_end_date` DATE, IN `p_leave_type` VARCHAR(50), IN `p_reason` TEXT)   BEGIN
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
CREATE DEFINER=`root`@`localhost` FUNCTION `get_next_approver` (`p_current_role` VARCHAR(50), `p_department_id` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
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
DELIMITER $$
CREATE TRIGGER `after_admin_update` AFTER UPDATE ON `admin` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_admin_delete` BEFORE DELETE ON `admin` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (OLD.id, 'delete', CONCAT('Admin deleted: ', OLD.user_name), COALESCE(OLD.last_login_ip, '127.0.0.1'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `ensure_super_admin_active` BEFORE INSERT ON `admin` FOR EACH ROW BEGIN
    IF NEW.role = 'super_admin' THEN
        SET NEW.status = 'Active';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_super_admin_inactive` BEFORE UPDATE ON `admin` FOR EACH ROW BEGIN
    IF OLD.role = 'super_admin' AND NEW.status = 'Inactive' THEN
        SET NEW.status = 'Active';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_document_queue`
--

CREATE TABLE `admin_document_queue` (
  `queue_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `assigned_date` datetime NOT NULL,
  `status` enum('Pending','Signed','Rejected') NOT NULL DEFAULT 'Pending',
  `signed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anonymous_feedback`
--

CREATE TABLE `anonymous_feedback` (
  `feedback_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','in_review','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_comment` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anonymous_feedback`
--

INSERT INTO `anonymous_feedback` (`feedback_id`, `department`, `message`, `status`, `created_at`, `admin_comment`, `updated_at`) VALUES
(4, 'IT', 'rerdtg', 'pending', '2025-03-17 07:59:21', NULL, '2025-03-17 07:59:21');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_assignments`
--

CREATE TABLE `appraisal_assignments` (
  `id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_criteria`
--

CREATE TABLE `appraisal_criteria` (
  `criteria_id` int(11) NOT NULL,
  `criteria_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weightage` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_criteria`
--

INSERT INTO `appraisal_criteria` (`criteria_id`, `criteria_name`, `description`, `weightage`, `is_active`, `created_at`) VALUES
(1, 'IT', 'Understanding and application of required job skills', 10.00, 1, '2025-03-13 19:22:36'),
(2, 'Quality of Work', 'Accuracy, thoroughness, and effectiveness', 15.00, 1, '2025-03-13 19:22:36'),
(3, 'Communication & Teamwork', 'Interaction with colleagues and team contribution', 12.50, 1, '2025-03-13 19:22:36'),
(4, 'Problem-Solving Ability', 'Analyzing and resolving workplace challenges', 12.50, 1, '2025-03-13 19:22:36'),
(5, 'Initiative & Innovation', 'Self-driven improvements and innovative solutions', 10.00, 1, '2025-03-13 19:22:36'),
(6, 'Adherence to Deadlines', 'Timely completion of assigned tasks', 12.50, 1, '2025-03-13 19:22:36'),
(7, 'Leadership', 'Guidance and team management capabilities', 12.50, 1, '2025-03-13 19:22:36'),
(8, 'Attendance & Punctuality', 'Regular attendance and timeliness', 10.00, 1, '2025-03-13 19:22:36'),
(9, 'Discipline', 'Assesses punctuality, attendance and adherence to company rules, impacting overall performance evaluation.', 10.00, 1, '2025-03-14 11:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_forms`
--

CREATE TABLE `appraisal_forms` (
  `form_id` int(11) NOT NULL,
  `period_id` int(11) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_periods`
--

CREATE TABLE `appraisal_periods` (
  `period_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Draft','Active','Completed') DEFAULT 'Draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_periods`
--

INSERT INTO `appraisal_periods` (`period_id`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(7, '2025-03-01', '2025-03-31', 'Draft', 1, '2025-03-14 16:52:25', '2025-03-14 16:52:25'),
(8, '2024-03-10', '2025-04-15', 'Draft', 1, '2025-03-17 07:45:20', '2025-03-17 07:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_ratings`
--

CREATE TABLE `appraisal_ratings` (
  `rating_id` int(11) NOT NULL,
  `appraisal_id` int(11) DEFAULT NULL,
  `criteria_id` int(11) DEFAULT NULL,
  `self_rating` int(11) DEFAULT NULL,
  `hod_rating` int(11) DEFAULT NULL,
  `hr_rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_hierarchy`
--

CREATE TABLE `approval_hierarchy` (
  `id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_level` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `reports_to_role` varchar(50) DEFAULT NULL,
  `can_approve_types` set('leave','appraisal','esignature') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_hierarchy`
--

INSERT INTO `approval_hierarchy` (`id`, `department_id`, `role_level`, `role`, `reports_to_role`, `can_approve_types`, `created_at`) VALUES
(1, NULL, 1, 'Managing Director', NULL, 'leave,appraisal,esignature', '2025-06-09 10:19:56'),
(2, NULL, 2, 'HR Manager', 'Managing Director', 'leave,appraisal,esignature', '2025-06-09 10:19:56'),
(3, NULL, 3, 'HR Coordinator', 'HR Manager', '', '2025-06-09 10:19:56'),
(4, NULL, 2, 'Head of Sales', 'Managing Director', 'leave,appraisal,esignature', '2025-06-09 10:19:56'),
(5, NULL, 3, 'HR Manager Sales', 'Head of Sales', 'leave,appraisal', '2025-06-09 10:19:56'),
(6, NULL, 4, 'Operation Manager', 'HR Manager Sales', 'leave', '2025-06-09 10:19:56'),
(7, NULL, 5, 'Operations Team', 'Operation Manager', '', '2025-06-09 10:19:56'),
(8, NULL, 2, 'HR Manager Marketing', 'Managing Director', 'leave,appraisal', '2025-06-09 10:19:56'),
(9, NULL, 3, 'Marketing Team', 'HR Manager Marketing', '', '2025-06-09 10:19:56'),
(10, NULL, 3, 'Sales Manager', 'HR Manager Sales', 'leave', '2025-06-09 10:19:56'),
(11, NULL, 4, 'Sales Team Leader', 'Sales Manager', '', '2025-06-09 10:19:56'),
(12, NULL, 4, 'Relationship Officer', 'Sales Manager', '', '2025-06-09 10:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `approval_history`
--

CREATE TABLE `approval_history` (
  `id` int(11) NOT NULL,
  `workflow_id` int(11) NOT NULL,
  `approver_id` varchar(10) NOT NULL,
  `action` enum('approved','rejected','forwarded') NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approval_routes`
--

CREATE TABLE `approval_routes` (
  `id` int(11) NOT NULL,
  `request_type` enum('leave','appraisal','esignature') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_role` varchar(50) NOT NULL,
  `approval_sequence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`approval_sequence`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_routes`
--

INSERT INTO `approval_routes` (`id`, `request_type`, `department_id`, `employee_role`, `approval_sequence`, `created_at`) VALUES
(1, 'leave', NULL, 'HR Coordinator', '[{\"level\": 1, \"role\": \"HR Manager\"}, {\"level\": 2, \"role\": \"Managing Director\"}]', '2025-06-09 10:19:56'),
(2, 'leave', NULL, 'Operations Team', '[{\"level\": 1, \"role\": \"Operation Manager\"}, {\"level\": 2, \"role\": \"HR Manager Sales\"}, {\"level\": 3, \"role\": \"Head of Sales\"}]', '2025-06-09 10:19:56'),
(3, 'leave', NULL, 'Sales Team Leader', '[{\"level\": 1, \"role\": \"Sales Manager\"}, {\"level\": 2, \"role\": \"HR Manager Sales\"}, {\"level\": 3, \"role\": \"Head of Sales\"}]', '2025-06-09 10:19:56'),
(4, 'leave', NULL, 'Marketing Team', '[{\"level\": 1, \"role\": \"HR Manager Marketing\"}, {\"level\": 2, \"role\": \"Managing Director\"}]', '2025-06-09 10:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `approval_workflow`
--

CREATE TABLE `approval_workflow` (
  `id` int(11) NOT NULL,
  `request_type` enum('leave','appraisal','esignature') NOT NULL,
  `request_id` int(11) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `current_level` int(11) NOT NULL DEFAULT 1,
  `current_approver` varchar(10) NOT NULL,
  `next_approver` varchar(10) DEFAULT NULL,
  `status` enum('pending','approved','rejected','in_progress') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `comments` text DEFAULT NULL,
  `approval_chain` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`approval_chain`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `eid` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `week_day` varchar(20) DEFAULT NULL,
  `first_in` time DEFAULT NULL,
  `last_out` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `attendance_type` enum('biometric','field') DEFAULT NULL,
  `shift_type` enum('day','night','swing') DEFAULT NULL,
  `location_coordinates` varchar(100) DEFAULT NULL,
  `location_address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `break_time` decimal(4,2) DEFAULT 1.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `eid`, `department`, `attendance_date`, `week_day`, `first_in`, `last_out`, `total_hours`, `attendance_type`, `shift_type`, `location_coordinates`, `location_address`, `status`, `break_time`, `created_at`) VALUES
(1, 'U07', NULL, '2025-03-07', 'Friday', '12:48:21', '12:50:36', NULL, 'field', NULL, '8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496', 'Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India', NULL, 1.00, '2025-03-07 11:48:21'),
(2, 'U07', NULL, '2025-03-08', 'Saturday', '11:49:10', '11:49:10', NULL, 'field', NULL, '8.5300546,76.9098619', 'Sree Chitira Thirunal Nagar, Akkulam, Thiruvananthapuram, Kerala, 695001, India', NULL, 1.00, '2025-03-08 10:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` enum('login','create','update','delete') NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 15:00:23'),
(2, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 15:57:05'),
(3, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 16:20:59'),
(4, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 17:39:26'),
(5, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 17:45:17'),
(6, 3, 'login', 'Admin login: superadmin', '::1', '2025-06-09 17:45:26'),
(7, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 17:51:15'),
(8, 4, 'update', 'Admin updated: admincommunik (status changed from Active to Inactive)', '::1', '2025-06-09 17:51:15'),
(9, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 17:51:19'),
(10, 4, 'update', 'Admin updated: admincommunik', '::1', '2025-06-09 17:51:19'),
(11, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 17:55:48'),
(12, 4, 'update', 'Admin updated: admincommunik', '::1', '2025-06-09 17:55:48'),
(13, 4, 'update', 'Admin updated: admincommunik (status changed from Inactive to Active)', '::1', '2025-06-09 18:05:18'),
(14, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:09:27'),
(15, 4, 'update', 'Admin updated: admincommunik (status changed from Active to Inactive)', '::1', '2025-06-09 18:09:27'),
(16, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:09:30'),
(17, 4, 'update', 'Admin updated: admincommunik', '::1', '2025-06-09 18:09:30'),
(18, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:09:34'),
(19, 4, 'update', 'Admin updated: admincommunik', '::1', '2025-06-09 18:09:34'),
(20, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:19:21'),
(21, 4, 'update', 'Admin updated: admincommunik', '::1', '2025-06-09 18:19:21'),
(22, 3, 'login', 'Admin login: superadmin', '::1', '2025-06-09 18:19:32'),
(23, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:22:32'),
(24, 4, 'update', 'Admin updated: admincommunik (status changed from Inactive to Active)', '::1', '2025-06-09 18:22:32'),
(25, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 18:23:04'),
(26, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 18:23:23'),
(27, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:23:40'),
(28, 4, 'update', 'Admin updated: admincommunik (status changed from Active to Inactive)', '::1', '2025-06-09 18:23:40'),
(29, 3, 'update', 'Admin updated: superadmin', '::1', '2025-06-09 18:49:50'),
(30, 4, 'update', 'Admin updated: admincommunik (status changed from Inactive to Active)', '::1', '2025-06-09 18:49:50'),
(31, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-09 18:50:00'),
(32, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-10 19:15:03'),
(33, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-11 03:49:40'),
(34, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-11 07:38:50'),
(35, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-11 10:57:53'),
(36, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-11 17:52:07'),
(37, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-11 17:56:10'),
(38, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 04:02:22'),
(39, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 17:18:56'),
(40, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 17:30:08'),
(41, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 17:36:16'),
(42, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 17:43:05'),
(43, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-14 17:47:25'),
(44, 4, 'login', 'Admin login: admincommunik', '::1', '2025-06-15 05:38:49');

-- --------------------------------------------------------

--
-- Table structure for table `backup_settings`
--

CREATE TABLE `backup_settings` (
  `id` int(11) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `retention_days` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backup_settings`
--

INSERT INTO `backup_settings` (`id`, `frequency`, `retention_days`, `created_at`, `updated_at`) VALUES
(1, 'daily', 30, '2025-06-09 14:45:41', '2025-06-09 14:45:41');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `parsed_resume_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `claim_type` varchar(50) DEFAULT NULL,
  `claim_category` varchar(50) DEFAULT NULL,
  `claim_amount` decimal(10,2) DEFAULT NULL,
  `claim_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_approvals`
--

CREATE TABLE `claim_approvals` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `approval_level` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_details`
--

CREATE TABLE `claim_details` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `expense_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `subject`, `message`) VALUES
(1, 'shruti', 'schavda684@rku.ac.in', 'test test test test', 'test test test testtesttesttesttesttest');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department_head_role` varchar(50) DEFAULT NULL,
  `parent_department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `annual_leave_days` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `department_head_role`, `parent_department_id`, `created_at`, `annual_leave_days`) VALUES
(1, 'HR Department', 'HR_MANAGER', NULL, '2025-06-10 07:16:42', 30),
(2, 'Sales Department', 'HEAD_OF_SALES', 1, '2025-06-10 07:16:42', 30),
(3, 'Marketing Department', 'MARKETING_HEAD', 1, '2025-06-10 07:16:42', 30),
(4, 'Operations Department', 'OPERATIONS_MANAGER', 1, '2025-06-10 07:16:42', 30);

-- --------------------------------------------------------

--
-- Table structure for table `department_heads`
--

CREATE TABLE `department_heads` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `head_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `head_name` varchar(100) NOT NULL,
  `head_email` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_heads`
--

INSERT INTO `department_heads` (`id`, `dept_name`, `head_id`, `user_name`, `password`, `head_name`, `head_email`, `department_id`) VALUES
(2, 'HR Department', 0, 'CME0005', '$2y$10$txwX3ecQFo5EY2xEGbEgNuGVb3PZbEznC0uRobHmxMRx2yR4bMzme', 'Manobala  Thathineni Sudharsanam', 'hr@communikmarketing.com', 1),
(4, 'Marketing Department', 0, 'CME0041', '$2y$10$7qzFhBhTPnB0fWDVWnFgKucFgWPS8yS9NBEMF1liDdIQigK2ulySe', 'Bajaj Mehul  Assuda', 'mehul.bajaj@communikmarketing.com', 3),
(5, 'Operations Department', 0, 'CME0001', '$2y$10$NGqakbVZN2JcaxoCkVs40.pgsdnqbYhBz079KR8EuV2uqKHxjhEui', 'Bashir Khan', 'bashid@communikmarketing.com', 4),
(6, 'Sales Department', 0, 'CME0003', '$2y$10$VrbM1rz821gxFHWfUq4chuVXwkyFKCv8QaZxOu2IKuGHzRFCGem5K', 'Muhammad  Arslan', 'arslan@communikmarketing.com', 2);

-- --------------------------------------------------------

--
-- Table structure for table `document_audit_log`
--

CREATE TABLE `document_audit_log` (
  `log_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `version_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_uploads`
--

CREATE TABLE `document_uploads` (
  `id` int(11) NOT NULL,
  `eid` varchar(10) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `document_category` enum('visa','passport','emirates_id','labour_card','national_id','insurance','onboarding','policy','education','other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `visa_type` enum('ISE','TXM','Visit','Communik') DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `status` enum('active','expired','deleted') DEFAULT 'active',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `version_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL,
  `smtp_host` varchar(100) NOT NULL DEFAULT 'smtp.gmail.com',
  `smtp_user` varchar(100) NOT NULL DEFAULT '',
  `smtp_pass` varchar(100) NOT NULL DEFAULT '',
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_settings`
--

INSERT INTO `email_settings` (`id`, `smtp_host`, `smtp_user`, `smtp_pass`, `smtp_port`, `created_at`, `updated_at`) VALUES
(1, 'smtp.gmail.com', '', '', 587, '2025-06-09 14:45:41', '2025-06-09 14:45:41');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `eid` varchar(10) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `birthday` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `maritalsts` varchar(50) NOT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `contact` varchar(20) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `start_from` date DEFAULT NULL,
  `end_to` date DEFAULT NULL,
  `Institute` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `role_id` int(11) DEFAULT NULL,
  `doj` date DEFAULT NULL,
  `EmpLoc` varchar(100) DEFAULT NULL,
  `EmpDiv` varchar(100) DEFAULT NULL,
  `EmpGrade` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `new_role_id` varchar(50) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `reporting_manager` varchar(50) NOT NULL,
  `emp_left_org` varchar(50) DEFAULT NULL,
  `dol` date DEFAULT NULL,
  `EmpCostcenter` varchar(100) DEFAULT NULL,
  `MOLID` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_no` varchar(70) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `nominee` varchar(100) DEFAULT NULL,
  `visa_number` varchar(50) DEFAULT NULL,
  `visa_type` varchar(50) DEFAULT NULL,
  `visa_issue_date` date DEFAULT NULL,
  `visa_expiry_date` date DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `passport_type` varchar(50) DEFAULT NULL,
  `passport_issue_date` date DEFAULT NULL,
  `passport_expiry_date` date DEFAULT NULL,
  `country_of_issue` varchar(50) DEFAULT NULL,
  `passport_issue_place` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(50) DEFAULT NULL,
  `visa_doc` varchar(255) DEFAULT NULL,
  `passport_doc` varchar(255) DEFAULT NULL,
  `is_field_staff` tinyint(1) DEFAULT 0,
  `labour_card_no` varchar(255) DEFAULT NULL,
  `labour_card_start_date` date DEFAULT NULL,
  `labour_card_end_date` date DEFAULT NULL,
  `pregnancy_status` varchar(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `document_number` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_trainee` tinyint(1) DEFAULT 0,
  `workflow_level` int(11) DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `eid`, `first_name`, `last_name`, `full_name`, `user_name`, `email`, `password`, `birthday`, `gender`, `maritalsts`, `blood_group`, `contact`, `address`, `country`, `degree`, `start_from`, `end_to`, `Institute`, `status`, `email_verified`, `role_id`, `doj`, `EmpLoc`, `EmpDiv`, `EmpGrade`, `role`, `new_role_id`, `designation`, `reporting_manager`, `emp_left_org`, `dol`, `EmpCostcenter`, `MOLID`, `bank_name`, `account_no`, `iban`, `nominee`, `visa_number`, `visa_type`, `visa_issue_date`, `visa_expiry_date`, `passport_number`, `passport_type`, `passport_issue_date`, `passport_expiry_date`, `country_of_issue`, `passport_issue_place`, `profile_pic`, `visa_doc`, `passport_doc`, `is_field_staff`, `labour_card_no`, `labour_card_start_date`, `labour_card_end_date`, `pregnancy_status`, `due_date`, `token`, `created_at`, `document_number`, `department_id`, `is_trainee`, `workflow_level`) VALUES
(63, 'CME0001', 'Abdul Bashid', 'Khan', 'Bashir Khan', 'md', 'bashid@communikmarketing.com', '$2y$10$kVEw8em1DG5fW0goTPqYP.L/ViOfwNfdfeLk2V991OvKu0XVbmskC', '1988-06-09', 'Male', 'Married', 'A+', '0551762775', 'Dubai', '', NULL, NULL, NULL, NULL, 'active', 0, NULL, '2018-02-01', 'Dubai', '', '', 'HOD', NULL, 'Managing Director', '', '', NULL, '', '', NULL, 'test', NULL, NULL, 'test23456', NULL, NULL, NULL, 'ytest234545', 'Regular', NULL, NULL, NULL, NULL, '63_1746886577.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '68149857bb29868149857bb29b', '2025-05-02 10:05:41', NULL, 4, 0, 4),
(64, 'CME0002', 'Muhammad Usman ', 'Tassawar', 'Muhammad Usman  Tassawar', 'usman.t', 'usman.t@communikmarketing.com', '$2y$10$xChhjqdIBP93/eg7PKoEJeh6Gy0zyRjo5Ef2g3B3u48NQroeKW8CG', '1987-08-29', 'Male', 'Married', 'A+', '0543226604', 'Dubai', 'United Arab Emirates', 'Bachelor od commerce', '2005-09-15', '2008-09-15', 'University of Punjab', 'active', 0, NULL, '2002-12-12', 'Duabi', '', '', 'user', NULL, 'Operations Manager', 'CME0001', 'No', NULL, '', ' MB242144088AE', NULL, 'NA', NULL, NULL, '20120232467274', 'Employement Visa', '2023-05-18', '2025-05-17', 'AU7894511', 'Regular', '2022-09-13', '2027-09-12', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1746271544.jpg', 'uploads/documents/visa_1746271544.pdf', 'uploads/documents/passport_1746271544.jpeg', 0, '20029088787017', '2024-03-20', '2025-03-21', NULL, NULL, '6815f8f4192ce6815f8f4192d0', '2025-05-03 11:25:44', NULL, 4, 0, 4),
(65, 'CME0003', 'Muhammad ', 'Arslan', 'Muhammad  Arslan', 'arslan', 'arslan@communikmarketing.com', '$2y$10$o6N6RBvoepjfKzUGB8/yoOK69Qp/4GoVK7/6VVDno5uiPAl/l1Xia', '1997-11-09', 'Male', 'Married', 'A+', '556402690', 'Dubai', 'United Arab Emirates', 'Master of Business Administration - MBA', NULL, NULL, NULL, 'active', 0, NULL, '2025-02-10', '', '', '', 'user', NULL, 'Sales Director', 'CME0001', '', NULL, '', '', NULL, 'NA', NULL, NULL, '1234567', NULL, '2024-09-20', '2026-09-19', 'FR1914162', 'Regular', '2022-06-10', '2027-06-09', 'Pakistan', 'Muzzafargrah', 'uploads/profile_pics/profile_1746273796.jpg', '', 'uploads/documents/passport_1746273796.jpeg', 0, NULL, NULL, NULL, NULL, NULL, '68160170d7c7068160170d7c73', '2025-05-03 12:03:16', NULL, 2, 0, 4),
(66, 'CME0004', 'MD Sameer ', 'Alam', 'MD Sameer  Alam', 'sameer', 'sameer@communikmarketing.com', '$2y$10$AsAiymDabZE4xh9Y9ybCIO0pizvyoZcvJVAKtbsOD7ZzXMIYpDTsa', '1995-05-10', 'Male', 'Married', 'A+', '523061867', 'Dubai', 'United Arab Emirates', 'Bachelor od commerce', '2021-06-22', '2023-06-23', 'Himalayan garhwal university ', 'active', 0, NULL, '2025-03-02', '', '', '', 'user', NULL, 'Asst Sales Manager', 'CME0003', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120257071997', NULL, '2025-03-02', '2027-03-10', 'P3723130', 'Regular', '2016-07-11', '2026-07-10', 'India', 'Ranchi', '66_1746452419.PNG', 'uploads/documents/visa_1746276943.pdf', '', 0, NULL, NULL, NULL, NULL, NULL, '681610db51ca2681610db51ca5', '2025-05-03 12:55:43', NULL, 2, 0, 4),
(67, 'CME0005', 'Manobala ', 'Thathineni Sudharsanam', 'Manobala  Thathineni Sudharsanam', 'hr', 'hr@communikmarketing.com', '$2y$10$PC1WfGwhzvQE8cwJlux0kuAwD6O/DGT6qVnaY88ON35OLHBziF64W', '1988-08-22', 'Male', 'Married', 'A+', '588771932', NULL, 'United Arab Emirates', 'Master of science', NULL, NULL, 'Staffordshire universtry', 'active', 0, NULL, '2025-02-03', '', '', '', 'hr', NULL, 'HR Manager', 'CME0001', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20220242890859', 'Emp Change', '2024-12-09', '2026-12-08', 'Z5904796', '', '2020-03-02', '2030-03-01', 'India', 'Chenni', 'uploads/profile_pics/profile_1746444543.jpg', 'uploads/documents/visa_1746444543.pdf', 'uploads/documents/passport_1746444543.pdf', 0, NULL, NULL, NULL, NULL, NULL, '68189f02aff3b68189f02aff3e', '2025-05-05 11:29:03', NULL, 1, 0, 4),
(68, 'CME0006', 'Rehmat', ' Ali', 'Rehmat  Ali', 'rehmat', 'rehmat@communikmarketing.com', '$2y$10$DAc1h6FNqTG3cXzcl0qRVeJXeBH.J69yKNbanoD2iDIgw9M.mbloy', '1997-05-17', 'Male', 'Married', 'A+', '525446788', NULL, 'United Arab Emirates', 'Bachelor of Science in Software Engineering', '2015-09-20', '2019-09-18', 'Gecos university Peshwar', 'active', 0, NULL, '2024-01-11', '', '', '', 'user', NULL, 'Asst. Operation', 'CME0002', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20220232948222', 'Emp Change', '2024-03-02', '2026-02-01', 'WQ1813512', '', '2023-04-23', '2033-04-25', 'Pakistan', 'Kohat', 'uploads/profile_pics/profile_1746445670.png', 'uploads/documents/visa_1746445670.pdf', '', 0, NULL, NULL, NULL, NULL, NULL, '6818a253a12396818a253a123c', '2025-05-05 11:47:50', NULL, 4, 0, 4),
(69, 'CME0007', 'MD Yeakub', 'Ali', 'MD YEAKUB  ALI', 'yeakub.ali', 'yeakub.ali@communikmarketing.com', '$2y$10$jMvf5bdapqyJXm4Pr//wk.WP8nXm50HdCU7e6pC.q8aFzo/4TMHTe', '1996-10-01', 'Male', 'Married', 'A+', '558405225', NULL, 'United Arab Emirates', 'BACHELOR OF ENVIRONMENTAL SCIENCE', NULL, NULL, 'BANGLADESH', 'active', 0, NULL, '2025-02-21', '', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20220232007785', NULL, '2023-08-07', '2025-08-06', 'EF02966721', 'Regular', '2020-02-16', '2025-02-15', 'Bangladesh', 'Dhaka', 'uploads/profile_pics/profile_1746446433.jpg', 'uploads/documents/visa_1746446433.jpg', 'uploads/documents/passport_1746446433.jpg', 0, NULL, NULL, NULL, NULL, NULL, '6818a5e5dc1b26818a5e5dc1b4', '2025-05-05 12:00:33', NULL, 2, 0, 4),
(70, 'CME0008', 'Ali ', 'Abbas ', 'Ali  Abbas ', 'ali.abbas', 'ali.abbas@communikmarketing.com', '$2y$10$kKxTfqGieuWDE1Dhu7pefOxX2R8Ix1/dUsUm4sJhq/FL5j4HnCCO.', '1996-05-12', 'Male', 'Married', 'A+', '521316706', NULL, 'United Arab Emirates', 'High School Diploma:', '2016-10-01', '2018-10-01', 'SLAMIA GOVT ,ARTS & COMMERCE COLLEGE', 'active', 0, NULL, '2025-02-20', '', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', NULL, 'NA', NULL, NULL, '2012024381625', 'Emp Change', '2024-05-06', '2026-05-05', 'UL1016111', 'Ordinary', '2022-09-23', '2027-09-26', 'Pakistan', 'Nagar', 'uploads/profile_pics/profile_1746448764.jpeg', 'uploads/documents/visa_1746448764.pdf', 'uploads/documents/passport_1746448764.pdf', 0, NULL, NULL, NULL, NULL, NULL, '6818ac5f084486818ac5f0844a', '2025-05-05 12:39:24', NULL, 2, 0, 4),
(74, 'CME0012', 'Rohit', 'Singh', 'Rohit Singh', 'rohit', 'rohit@communikmarketing.com', '$2y$10$hjLy2Ap2YbbRoLl9aKzSHePSMfNsQcqF02hBUQW/ciq/CgBQN39am', '1991-05-01', 'Male', 'Married', 'A+', '0559358325', 'Dubai', 'United Arab Emirates', 'Master in Bussiiness Adminstration', '2010-01-01', '2013-01-01', 'JIWAJI University,', 'active', 0, NULL, '2025-02-04', 'Dubai', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120252543044', 'Employement Visa', '2025-04-07', '2027-04-06', 'V4188140', 'Regular', '2022-02-08', '2032-02-07', 'India', 'Bhopal', 'uploads/profile_pics/profile_1746512559.png', 'uploads/documents/visa_1746512559.pdf', 'uploads/documents/passport_1746512559.pdf', 0, NULL, NULL, NULL, NULL, NULL, '6819a814c8c9a6819a814c8c9c', '2025-05-06 06:22:39', NULL, 2, 0, 4),
(75, 'CME0013', 'Muhammad ', 'Moazzam', 'Muhammad  Moazzam', 'moazzam', 'moazzam@communikmarketing.com', '$2y$10$pwklKSr6iJD4E/5Mb6s3R.ArrXXZy4gaKRgVs7ztV7iTgy9U3pQAa', '1998-04-01', 'Male', 'Single', 'A+', '556182738', 'Dubai', 'United Arab Emirates', 'Bachelor in Arts', '2019-01-01', '2021-01-01', 'Allama Iqbal Open universitry Lahore', 'active', 0, NULL, '2025-03-15', 'Dubai', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120242253059', NULL, '2024-03-29', '2026-03-28', ' CG0911361', 'Regular', '2021-09-29', '2026-09-28', 'Pakistan', NULL, '75_1746886859.jpg', 'uploads/documents/visa_1746606224.pdf', '', 0, NULL, NULL, NULL, NULL, NULL, '681b163563f53681b163563f54', '2025-05-07 08:23:44', NULL, 2, 0, 4),
(76, 'CME0014', 'Mohammed ', 'Azaam', 'Mohammed  Azaam', 'mohamed.azaam', 'mohamed.azaam@communikmarketing.com', '$2y$10$a9WT7LInnoICtde..pAUQeBiABZ9rUm0aIGD.s2nSwdKhotOtw/lG', '1987-06-10', 'Male', 'Married', '', '522335188', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-02-20', 'Dubai', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', '', 'NA', '', '', '2022023263685', 'Emp Change', '2023-09-18', '2025-09-17', 'NB134112', 'Ordinary', '2021-03-13', '2029-03-13', 'Sri Lanka', 'Hatton', 'uploads/profile_pics/profile_1746609706.PNG', 'uploads/documents/visa_1746609706.pdf', 'uploads/documents/passport_1746609706.jpg', 0, '', NULL, NULL, NULL, NULL, '681b2334baa95681b2334baa96', '2025-05-07 09:21:46', NULL, 2, 0, 4),
(77, 'CME0015', 'Asma ', 'Begum', 'Asma  Begum', 'asma.b', 'asma.b@communikmarketing.com', '$2y$10$qNWJ55NyKyWd.jspIHEpr.mRXgp1TwIcyZLIhVD5AEDmMKnHAwOgO', '1991-09-30', 'Female', 'Married', '', '544027621', 'Dubai', 'Bangladesh', 'Bachelor of Business  Administration ', '2011-01-01', '2014-01-01', 'Eden Mohila College  Dhaka', 'active', 0, NULL, '2025-03-17', 'Dubai', '', '', 'user', NULL, 'Asst. Operation', 'CME0002', '', NULL, '', '', '', 'NA', '', '', '20120203235577', 'Spouse Visa', '2024-12-10', '2026-12-09', 'A00020151', 'Ordinary', '2020-09-23', '2030-09-22', 'Bangladesh', 'Dhaka', 'uploads/profile_pics/profile_1746610278.jpg', 'uploads/documents/visa_1746610278.pdf', 'uploads/documents/passport_1746610278.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '681b265f2c467681b265f2c468', '2025-05-07 09:31:18', NULL, 4, 0, 4),
(78, 'CME0016', 'Ayesha', 'Shamas', 'Ayesha Shamas', 'cv', 'cv@communikmarketing.com', '$2y$10$bJRzeN8A6nulMO.jFPMlVuH/k0LdZG5DgET41LJTPFHKgtoFe/ijG', '1996-04-30', 'Female', 'Married', '', '547786485', 'Dubai', 'United Arab Emirates', 'Bachelor of Arts', '2020-01-01', '2022-01-01', 'Punjab University Lahore', 'active', 0, NULL, '2025-03-18', 'Dubai', '', '', 'user', NULL, 'Office Asst', 'CME0005', '', NULL, '', '', '', 'NA', '', '', '20220242194916', 'Emp Change', '2024-10-08', '2026-10-07', 'KK5161171', 'Ordinary', '2023-09-13', '2028-09-11', 'Pakistan', 'Chakwal', 'uploads/profile_pics/profile_1746610879.PNG', 'uploads/documents/visa_1746610879.pdf', 'uploads/documents/passport_1746610879.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '681b289fc8c8d681b289fc8c8f', '2025-05-07 09:41:19', NULL, 1, 0, 4),
(79, 'CME0017', 'Rida', 'Arooj', 'Rida Arooj', 'rida', 'rida@communikmarketing.com', '$2y$10$ccCY3lGNJ2n2NamMeRulc.ck3TIJ6P9vsbxwcfS34ykLKxGuezrGK', '1997-08-12', 'Female', 'Single', '', '522875919', 'Dubai', 'United Arab Emirates', 'Master in Mathmatics', '2020-01-01', '2022-01-01', 'Punjab University Lahore', 'active', 0, NULL, '2024-05-15', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', 'Emirates NBD', '1015916289501', 'AE150260001015916289501', 'Rida Arooj', '20120242875281', 'Employement Visa', '2024-07-12', '2025-06-30', ' NB1982431', 'Ordinary', '2023-11-16', '2033-11-14', 'Pakistan', 'Sheikupura', 'uploads/profile_pics/profile_1746613945.jpeg', 'uploads/documents/visa_1746613945.pdf', 'uploads/documents/passport_1746613945.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '681b34c56c43e681b34c56c43f', '2025-05-07 10:32:25', NULL, 2, 0, 4),
(80, 'CME0018', 'Nanda', 'Kirana', 'Nanda Kirana', 'nanda', 'nanda@communikmarketing.com', '$2y$10$CW7CqOLDNKAP9u.2zjplje315B.YKOF3HWPbeXDkkc8f/F9JpeE8C', '1988-04-10', 'Male', 'Married', '', '545237850', 'Dubai', 'Pakistan', 'Diploma Engireeing trade', '2005-07-01', '2007-07-01', 'Govt IT College', 'active', 0, NULL, '2024-04-22', '', '', '', 'user', NULL, 'Relationship officer', 'CME0012', '', NULL, '', '', 'ADCB', '13546324910001', 'AE380030013546324910001', 'Nanda Kirana', '20120242720212', 'Employement Visa', '2024-05-28', '2026-05-27', 'U2947341', 'Ordinary', '2020-11-04', '2030-11-03', 'India', 'Bengaluru', 'uploads/profile_pics/profile_1746614964.jpeg', 'uploads/documents/visa_1746614964.pdf', 'uploads/documents/passport_1746614964.pdf', 0, '', NULL, NULL, NULL, NULL, '681b373b279e6681b373b279e7', '2025-05-07 10:49:24', NULL, 2, 0, 4),
(81, 'CME0019', 'Jashan', 'Ralh', 'Jashan Ralh', 'jashan', 'jashan@communikmarketing.com', '$2y$10$AXAP1qeADKSKo4V/KVCjvuBXN5XJLPnEPoDr.y7i8NoeOz8xT8q4W', '2001-04-26', 'Male', 'Single', '', '521235604', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2024-06-04', '', '', '', 'user', NULL, 'Relationship officer', 'CME0012', '', NULL, '', '', 'ADCB', '13820568920001', 'AE970030013820568920001', '', '20220242056990', 'Employement Visa', '2024-08-05', '2026-08-04', 'X5435485', 'Ordinary', '2023-04-15', '2033-04-14', 'India', 'Jalandhar', 'uploads/profile_pics/profile_1746615580.JPG', 'uploads/documents/visa_1746615580.pdf', 'uploads/documents/passport_1746615580.pdf', 0, '', NULL, NULL, NULL, NULL, '681b3b833cf8f681b3b833cf90', '2025-05-07 10:59:40', NULL, 2, 0, 4),
(82, 'CME0020', 'Aman', 'Ahmed', 'Aman Ahmed', 'aman', 'aman@communikmarketing.com', '$2y$10$D.CO4ci95dUcwvhJXw//vu1M09R/VfhGoThVoMKC2/z8YLUIa2mqG', '1989-01-01', 'Male', 'Married', 'A+', '568266677', 'Dubai', 'United Arab Emirates', 'intermediate', '2012-01-01', '2013-01-01', 'BISE khanpur', 'active', 0, NULL, '2024-08-15', '', '', '', 'user', NULL, 'Sales Team Leader', 'CME0012', '', NULL, '', '', 'ADCB', '13864909910001', 'AE310030013864909910001', 'Aman Ahmed', '20220242273448', 'Employement Visa', '2024-08-27', '2026-08-26', 'Y6173281', 'Regular', '2023-09-15', '2033-09-14', 'India', 'Lucknow', 'uploads/profile_pics/profile_1746617725.JPG', 'uploads/documents/visa_1746617725.pdf', 'uploads/documents/passport_1746617725.pdf', 0, NULL, NULL, NULL, NULL, NULL, '681b42a0a74f8681b42a0a74f9', '2025-05-07 11:35:25', NULL, 2, 0, 4),
(83, 'CME0021', 'Shumaila ', 'Imran', 'Shumaila  Imran', 'shumaila', 'shumaila@communikmarketing.com', '$2y$10$tlyMX95d6bXDBfwrr0p8bOptRJzYkDrlrdYym6gbO2JueoU4SXZwu', '1979-11-10', 'Female', 'Married', 'A+', '527240470', 'Dubai', 'United Arab Emirates', 'Master in Arts', '2016-01-01', '2018-01-01', 'Allama Iqbal Open university ', 'active', 0, NULL, '2024-05-15', '', '', '', 'user', NULL, 'Relationship officer', 'CME0012', '', NULL, '', '', 'Al Hilal ', '17240470001', 'AE360530000017240470001', 'Shumila Imran', '20220242548551', 'Employement Visa', '2024-11-05', '2026-11-04', 'BU1224432', 'Regular', '2022-05-21', '2027-05-20', 'Pakistan', 'Sahiwal', 'uploads/profile_pics/profile_1746620334.JPG', 'uploads/documents/visa_1746620334.pdf', 'uploads/documents/passport_1746620334.jpeg', 0, NULL, NULL, NULL, 'Not Pregnant', '0000-00-00', '681b4d990eb43681b4d990eb45', '2025-05-07 12:18:54', NULL, 2, 0, 4),
(84, 'CME0022', 'Muheet', 'Mirza', 'Muheet Mirza', 'muheet', 'muheet@communikmarketing.com', '$2y$10$HnsWK/Zd/fU9S2T10f9IV.Xe7g2FFBvWrY2FtLOjezB7pbd8XbFJ6', '2000-11-05', 'Male', 'Married', '', '586118535', 'Dubai', 'United Arab Emirates', ' Intermediate (Science) ', '2019-01-01', '2021-01-01', 'Government Boys Degree College, New Karachi', 'active', 0, NULL, '2024-02-09', '', '', '', 'user', NULL, 'Relationship officer', 'CME0012', '', NULL, '', '', 'Emirates NBD', '1015904621801', 'AE450260001015904621801', 'Muheet Mirza', '20220242647504', 'Employement Visa', '2024-11-04', '2026-11-03', 'CE2922262', 'Ordinary', '2024-05-24', '2029-05-23', 'Pakistan', 'Karachi ', 'uploads/profile_pics/profile_1746621014.jpeg', 'uploads/documents/visa_1746621014.jpeg', 'uploads/documents/passport_1746621014.pdf', 0, '', NULL, NULL, NULL, NULL, '681b5089ce85c681b5089ce85d', '2025-05-07 12:30:14', NULL, 2, 0, 4),
(85, 'CME0023', 'Muhammad ', 'Nauman', 'Muhammad  Nauman', 'nauman', 'nauman@communikmarketing.com', '$2y$10$/u/Kk4.3GWF2/.ZpmI9ZHunLf0mK/04bKMU4qy2xqzvN9IKTrbiiG', '1989-08-10', 'Male', 'Married', '', '543354996', 'Dubai', 'United Arab Emirates', 'Intermediate', '2007-01-01', '2009-01-01', 'BISE Lahore', 'active', 0, NULL, '2024-01-11', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', 'Al Hilal', '1335499601', 'AE090530000013354996001', 'Muhammad Nouman', '20120247943535', 'Employement Visa', '2024-07-25', '2026-07-24', 'ES9953421', 'Ordinary', '2021-07-07', '2026-07-06', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1746621712.jpg', 'uploads/documents/visa_1746621712.pdf', 'uploads/documents/passport_1746621712.pdf', 0, '', NULL, NULL, NULL, NULL, '681b52b126715681b52b126716', '2025-05-07 12:41:52', NULL, 2, 0, 4),
(86, 'CME0024', 'Ariba ', 'Atif', 'Ariba  Atif', 'ariba', 'ariba@communikmarketing.com', '$2y$10$GqI3BP6PLsIPGD.lY4GQ.uAaCXoEC9y18chFYdlzaVryaDOR5Gm/a', '2001-07-22', 'Female', 'Married', 'A+', '547007453', 'Dubai', 'United Arab Emirates', 'Intermediate', '2016-01-01', '2018-01-01', 'BISE Lahore', 'active', 0, NULL, '2025-02-20', '', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20022070143695', 'Family Visa', '2024-06-05', '2026-06-04', 'BA1486951', '', '2022-06-14', '2027-06-13', 'Pakistan', 'Sheikhupura', 'uploads/profile_pics/profile_1746624243.JPG', '', 'uploads/documents/passport_1746624243.jpeg', 0, '125780217', NULL, '2027-04-30', 'Not Pregnant', '0000-00-00', '681b5d5ec24db681b5d5ec24dc', '2025-05-07 13:24:03', NULL, 2, 0, 4),
(87, 'CME0025', 'Aizaz ', 'Ahmed Malik', 'Aizaz  Ahmed Malik', 'aizaz', 'aizaz@communikmarketing.com', '$2y$10$mYQ0DoHlWalRATfClI/4V.pMHCQLVpyx8jFkVg5X57fiuO8A5q/IG', '1999-03-03', 'Male', 'Married', '', '585539656', 'Dubai', 'United Arab Emirates', ' Matriculation', '2013-01-01', '2015-01-01', 'Government comprehensive School Sialkot Pakistan', 'active', 0, NULL, '2025-02-20', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', '', 'NA', '', '', '20120242307819', 'Emp Change', '2024-03-06', '2026-03-05', 'FZ0768323', 'Ordinary', '2024-07-17', '2029-07-16', 'Pakistan', 'Sailkot', 'uploads/profile_pics/profile_1746699137.jpeg', 'uploads/documents/visa_1746699137.jpg', 'uploads/documents/passport_1746699137.jpeg', 0, '', NULL, NULL, NULL, NULL, '681c81b69c8e4681c81b69c8e5', '2025-05-08 10:12:17', NULL, 2, 0, 4),
(88, 'CME0026', 'Saira ', 'Javaid', 'Saira  Javaid', 'saira', 'saira@communikmarketing.com', '$2y$10$IMUnZmdzl29ffM0OJJiqM.HAsHd1dOaNMxY9CXSbiTulhQ.A27GYy', '1997-08-27', 'Female', 'Single', '', '568406994', 'Dubai', 'Pakistan', 'Master of Commerce', '2015-10-01', '2017-01-01', 'National college of bussiness adminstartion lahore', 'active', 0, NULL, '2025-02-11', '', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', '', 'NA', '', '', '20120252636666', 'Employement Visa', '2025-03-11', '2027-03-10', 'A88692622', 'Ordinary', '2021-01-14', '2026-01-13', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1746699839.jpeg', 'uploads/documents/visa_1746699839.pdf', 'uploads/documents/passport_1746699839.pdf', 0, '123751949', NULL, '2027-03-05', 'Not Pregnant', '0000-00-00', '681c843e67797681c843e67798', '2025-05-08 10:23:59', NULL, 2, 0, 4),
(89, 'CME0027', 'Shega', ' Ameena Nazar', 'Shega  Ameena Nazar', 'shega', 'shega@communikmarketing.com', '$2y$10$fH2WC5.M82o2zm6GWuJvxeQGTTie6qImr0p4MebyvsA5fy.5GN5E2', '2022-05-20', 'Female', 'Single', '', '585025370', 'Dubai', 'United Arab Emirates', 'Bachelor of English', '2020-01-01', '2022-01-01', 'Calicut university india', 'active', 0, NULL, '2025-02-19', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '20120252373564', 'Employement Visa', '2025-03-04', '2027-03-03', 'W0884745', 'Ordinary', '2022-05-20', '2032-05-19', 'India', 'Cochin', 'uploads/profile_pics/profile_1746700927.jpeg', 'uploads/documents/visa_1746700927.pdf', 'uploads/documents/passport_1746700927.jpeg', 0, '123380791', NULL, '2027-02-24', 'Not Pregnant', '0000-00-00', '681c88bfb1e44681c88bfb1e47', '2025-05-08 10:42:07', NULL, 2, 0, 4),
(90, 'CME0028', 'Ahammed', 'Muzammil', 'Ahammed Muzammil', 'muzammil', 'muzammil@communikmarketing.com', '$2y$10$kD/xaVFEzoQ2qlYwtrtDq.D7NGBC.O5ymsAfby/bKRSOvDeRL0xSK', '2001-10-15', 'Male', 'Single', '', '566679691', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, 'GHSS BEKUR', 'inactive', 0, NULL, '2025-02-02', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '501202420000058', 'Emp Change', '2024-01-20', '2026-01-09', 'V2203812', 'Ordinary', '2021-08-30', '2031-08-29', 'India', 'kozhikode', 'uploads/profile_pics/profile_1746701616.jpeg', 'uploads/documents/visa_1746701616.jpeg', 'uploads/documents/passport_1746701616.jpeg', 0, '', NULL, NULL, NULL, NULL, '681c8b743fce2681c8b743fce4', '2025-05-08 10:53:36', NULL, 2, 0, 4),
(91, 'CME0029', 'MD ZAMINUL ', 'HAQUE', 'MD ZAMINUL  HAQUE', 'zaminul', 'zaminul@communikmarketing.com', '$2y$10$.PVhhmaohFcGhNH82lk7MeMvsw6JtNqpWUcWj/pO1XLlVHZVDuI5m', '1983-05-04', 'Male', 'Married', '', '555811746', 'Dubai', 'United Arab Emirates', 'M.A in Philosophy', NULL, '2004-01-01', 'National university.Dhaka', 'active', 0, NULL, '2024-02-24', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '000000000', 'Emp Change', '2024-07-11', '2026-07-10', 'AD3219862', 'Ordinary', '2022-02-23', '2032-02-22', 'Bangladesh', 'Dhaka', 'uploads/profile_pics/profile_1746702973.jpg', '', 'uploads/documents/passport_1746702973.jpeg', 0, '', NULL, NULL, NULL, NULL, '681c8d775989b681c8d775989d', '2025-05-08 11:16:13', NULL, 2, 0, 4),
(92, 'CME0030', 'Sandeep', 'Krishna ', 'Sandeep Krishna ', 'sandeep', 'sandeep@communikmarketing.com', '$2y$10$28bdpkLaMMTrHY06cTBIreLt4u.5nONSt0K8EwLUh8MH7ODIEQOKm', '1988-03-07', 'Male', 'Single', '', '523875697', 'Dubai', 'United Arab Emirates', 'Intermediate', '2004-01-01', '2005-01-01', '', 'active', 0, NULL, '2025-03-03', '', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', '', 'NA', '', '', '20120252569309', 'Employement Visa', '2025-04-02', '2027-04-01', 'V3778057', 'Ordinary', '2021-10-28', '2031-10-27', 'India', 'hyderabad', 'uploads/profile_pics/profile_1746788978.jpeg', 'uploads/documents/visa_1746788978.pdf', 'uploads/documents/passport_1746788979.pdf', 0, '', NULL, NULL, NULL, NULL, '681de155919c7681de155919c8', '2025-05-09 11:09:39', NULL, 2, 0, 4),
(93, 'CME0031', 'Mohamed Ezzat ', 'Aboelyazzed Eltalawy', 'Mohamed Ezzat  Aboelyazzed Eltalawy', 'ezzat', 'ezzat@communikmarketing.com', '$2y$10$5.KglFy6CM3Hbv85Z8k7OOdA8gE/1NgtAY7CYpMNnRirXNF7co5Ee', '1995-01-12', 'Male', 'Single', '', '581137798', 'Dubai', 'United Arab Emirates', 'Bachelor of Science', '2015-01-01', '2017-01-01', 'Tanta University', 'active', 0, NULL, '2025-03-03', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '20120252466146', 'Employement Visa', '2025-03-24', '2027-03-23', 'A39053980', 'Ordinary', '2024-09-25', '2031-09-24', 'Egypt', '', 'uploads/profile_pics/profile_1746789888.jpeg', 'uploads/documents/visa_1746789888.pdf', 'uploads/documents/passport_1746789888.pdf', 0, '123986824', NULL, '2027-03-10', NULL, NULL, '681de2d1e206c681de2d1e206d', '2025-05-09 11:24:48', NULL, 2, 0, 4),
(94, 'CME0032', 'Mukter', 'Ahmed', 'Mukter Ahmed', 'mukter', 'mukter@communikmarketing.com', '$2y$10$y1ISfSRDmO9rlG5hh6OmPeVpv.7zDxyq7J/I19XHQlvK1AhALVrg.', '1993-05-05', 'Male', 'Single', '', '528727451', 'Dubai', 'United Arab Emirates', 'Bachelor of Business  Administration ', '2019-01-01', '2021-01-01', 'SOUTHERN UNIVERSITY DHAKA', 'active', 0, NULL, '2025-03-07', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '20120247572072', 'Employement Visa', '2024-02-29', '2026-02-27', 'B00633419', 'Ordinary', '2022-07-31', '2032-07-30', 'Bangladesh', 'Dhaka', 'uploads/profile_pics/profile_1746791414.jpeg', 'uploads/documents/visa_1746791414.pdf', 'uploads/documents/passport_1746791414.pdf', 0, '', NULL, NULL, NULL, NULL, '681dea392d136681dea392d138', '2025-05-09 11:50:14', NULL, 2, 0, 4),
(95, 'CME0033', 'Zahra ', 'Wahab', 'Zahra  Wahab', '', '', '$2y$10$j0nF2PXykKIlnuSLvQqW/.Usp/5yYdIq0acTuyBm1ZKt.AB6HEBmW', '1992-11-11', 'Female', 'Married', '', '552857547', 'Dubai', 'United Arab Emirates', 'Bachelor of Commerce', '2011-01-01', '2013-01-01', ' Punjab University', 'active', 0, NULL, '2025-03-26', '', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', '', 'NA', '', '', '20120252774906', 'Employement Visa', '2025-05-05', '2027-05-04', ' BB6135701', 'Ordinary', '2023-12-27', '2028-12-25', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1746792167.JPG', 'uploads/documents/visa_1746792167.pdf', 'uploads/documents/passport_1746792167.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '681ded145ac93681ded145ac94', '2025-05-09 12:02:47', NULL, NULL, 0, 4),
(96, 'CME0034', 'Mohammed Masrooq ', 'Munner', 'Mohammed Masrooq  Munner', 'masrooq', 'masrooq@communikmarketing.com', '$2y$10$RPigw4RPv1TV0nk6zhXPZO4YFtdmqMjKdbT1Rge1D8KmZN5dZMq82', '1993-09-26', 'Male', 'Single', '', '547002445', 'Dubai', 'United Arab Emirates', 'Bachelor of engineering', '2014-01-11', '2016-01-01', 'paavai engineering college', 'active', 0, NULL, '2025-02-20', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '201202411400197271', 'Tourist', '2024-05-24', '2024-07-22', 'R4193679', 'Ordinary', '2017-11-13', '2027-11-12', 'India', 'kozhikode', 'uploads/profile_pics/profile_1746853294.JPG', 'uploads/documents/visa_1746853294.pdf', 'uploads/documents/passport_1746853294.jpeg', 0, '', NULL, NULL, NULL, NULL, '681edbf63ce3a681edbf63ce3b', '2025-05-10 05:01:34', NULL, 2, 0, 4),
(97, 'CME0035', 'Saumya', 'Silva', 'Saumya Silva', 'saumya', 'saumya@communikmarketing.com', '$2y$10$dL63f1Teqhg/qw4CYAc/BOPKLBJv0AfFYOqMqnnn7y8qiIQov1HiW', '1994-06-26', 'Female', 'Single', '', '505021007', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-02-24', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '201202411400254323', 'Tourist', '2024-06-02', '2024-07-31', 'N8088119', 'Ordinary', '2016-12-17', '2028-12-17', 'Sri Lanka', 'Kalijabowila', 'uploads/profile_pics/profile_1746854194.JPG', 'uploads/documents/visa_1746854194.jpeg', 'uploads/documents/passport_1746854194.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '681edf5abecd6681edf5abecd7', '2025-05-10 05:16:34', NULL, 2, 0, 4),
(98, 'CME0036', 'Rana Maanzar ', 'Hussain', 'Rana Maanzar  Hussain', 'rana', 'rana@communikmarketing.com', '$2y$10$m.0StZ8xHiIymkm4e0JaK.xi/1UtB8tgevtFp.PpLaN5YnxtQ6I.e', '1987-09-16', 'Male', 'Married', '', '543343207', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, '', 'inactive', 0, NULL, '2025-02-24', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '000000', 'Employement Visa', '2023-11-17', '2025-11-16', 'LV6894172', 'Ordinary', '2023-05-12', '2028-05-15', 'Pakistan', 'Faisalabad', 'uploads/profile_pics/profile_1746854706.jpeg', '', 'uploads/documents/passport_1746854706.jpeg', 0, '', NULL, NULL, NULL, NULL, '681ee17b3bcf9681ee17b3bcfa', '2025-05-10 05:25:06', NULL, 2, 0, 4),
(99, 'CME0037', 'Naimat ', 'Ullah', 'Naimat  Ullah', 'naimat', 'naimat@communikmarketing.com', '$2y$10$2gOE7frS8FVdWLo1pyKg.O86lRuvwhC0neMMMYUsnQU8THd3Yv.IK', '1997-01-01', 'Male', 'Single', '', '527563131', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2023-02-21', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '20120252753048', 'Employement Visa', '2025-05-01', '2025-06-29', 'RH2745641', 'Ordinary', '2022-10-21', '2032-10-19', 'Pakistan', 'Karachi ', 'uploads/profile_pics/profile_1746855090.jpg', 'uploads/documents/visa_1746855090.pdf', 'uploads/documents/passport_1746855090.pdf', 0, '', NULL, NULL, NULL, NULL, '681ee3a250e39681ee3a250e3a', '2025-05-10 05:31:30', NULL, 2, 0, 4),
(100, 'CME0038', 'Harry', 'Bhatti', 'Harry Bhatti', 'harry', 'harry@communikmarketing.com', '$2y$10$5.W.RcCgVOJl8Rbz8kCceeXbiuTBBi2GvEWsyxgm6y1ceyUbk5zDm', '2000-02-21', 'Male', '', '', '567463423', 'Dubai', 'United Arab Emirates', 'Intermediate', '2012-01-01', '2014-01-01', 'BISE Lahore', 'active', 0, NULL, '2025-04-04', '', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120257057548', 'Emp Visa Change Status', '2025-03-24', '2027-03-23', 'EH3177171', 'Ordinary', '2022-10-13', '2027-10-12', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1746855543.jpg', 'uploads/documents/visa_1746855543.jpg', 'uploads/documents/passport_1746855543.pdf', 0, '', NULL, NULL, NULL, NULL, '681ee512736d9681ee512736da', '2025-05-10 05:39:03', NULL, 2, 0, 4),
(101, 'CME0039', 'Zohaib ', 'Iqbal', 'Zohaib  Iqbal', 'zohaib', 'zohaib@communikmarketing.com', '$2y$10$YGSZ0Fr83VdyG140EnojpunysQsJ7itNyoxxizaQUMgc26CwMPHjG', '1995-01-15', 'Male', 'Single', '', '554310733', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-04-04', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '20120257086176', 'Emp Visa Change Status', '2025-03-22', '2027-03-21', ' ZQ1338351', 'Ordinary', '2023-04-03', '2028-04-01', 'Pakistan', 'Peshwar', 'uploads/profile_pics/profile_1746855870.jpg', 'uploads/documents/visa_1746855870.pdf', 'uploads/documents/passport_1746855870.pdf', 0, '', NULL, NULL, NULL, NULL, '681ee6a0918c4681ee6a0918c5', '2025-05-10 05:44:30', NULL, 2, 0, 4),
(102, 'CME0040', 'KALAI KAMAL ', 'SIVAGNANASUNDARAM', 'KALAI KAMAL  SIVAGNANASUNDARAM', 'kalaikamal', 'kalaikamal@communikmarketing.com', '$2y$10$5u3tlx6O8r5ViFKGKc7y7OmBQe/E/RQTRo9.eeTHq9T7ipm8uUmNG', '1986-03-18', 'Male', '', '', '568557536', 'Dubai', 'United Arab Emirates', 'G.C.E Advanced Level', '2004-01-01', '2005-01-01', 'Kotagala Tamil Maha Vidyalaya ', 'active', 0, NULL, '2025-02-20', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '201202411400364165', 'Tourist', '2024-06-20', '2024-08-18', 'N11444768', 'Ordinary', '2024-06-20', '2034-06-20', 'Sri Lanka', 'NAWALAPITIYA', 'uploads/profile_pics/profile_1746856978.jpg', 'uploads/documents/visa_1746856978.pdf', 'uploads/documents/passport_1746856978.pdf', 0, '', NULL, NULL, NULL, NULL, '681ee970d3986681ee970d3987', '2025-05-10 06:02:58', NULL, 2, 0, 4),
(103, 'CME0041', 'Bajaj Mehul ', 'Assuda', 'Bajaj Mehul  Assuda', 'mehul.bajaj', 'mehul.bajaj@communikmarketing.com', '$2y$10$LLMM2B5leskb9M8ZvPRdTe4YR3eEK1AOwFxXryMJ9yrnCDjJszip2', '1982-11-18', 'Male', 'Married', '', '585976696', 'Dubai', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-04-08', '', '', '', 'user', NULL, 'Asst Sales Manager', 'CME0003', '', NULL, '', '', '', 'NA', '', '', '20120232961573', 'Emp Visa Change Status', '2023-08-17', '2025-08-16', 'S2764043', 'Ordinary', '2018-04-26', '2028-04-25', 'India', 'Mumbai', 'uploads/profile_pics/profile_1746857787.jpg', 'uploads/documents/visa_1746857787.jpg', 'uploads/documents/passport_1746857787.jpg', 0, '', NULL, NULL, NULL, NULL, '681eed661cf03681eed661cf05', '2025-05-10 06:16:27', NULL, 2, 0, 4),
(104, 'CME0042', 'Anum ', 'Riaz ', 'Anum  Riaz ', 'support', 'support@communikmarketing.com', '$2y$10$oaGJFPSHQfwc85Gnr1ebXuw2YiSUOd.3BE/czAOm4LeOJN7vZZlfu', '1993-05-28', 'Male', 'Single', 'A+', '585879883', 'Dubai', 'United Arab Emirates', 'Bachelor of Arts', '2017-01-01', '2018-01-01', 'Sindh board', 'active', 0, NULL, '2025-04-10', '', '', '', 'user', NULL, 'Asst. Operation', 'CME0041', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120237372511', 'Emp Visa Change Status', '2023-07-10', '2025-07-09', ' FV9911852', 'Regular', '2021-08-18', '2031-08-18', 'Pakistan', 'Karachi ', '104_1746864079.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f065fdf78b681f065fdf78d', '2025-05-10 07:59:22', NULL, 2, 0, 4),
(105, 'CME0043', 'Suheb', ' Saifi', 'Suheb  Saifi', 'Shuaibsaifimail', 'Shuaibsaifimail@gmail.com', '$2y$10$eJpNQxTDXQWEon2IY0ZbTO3hyTeVjTLl1hZibZ6/yN1hoXexRWaMm', '1998-08-23', 'Male', 'Single', 'A+', '561196258', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, NULL, 'active', 0, NULL, '2025-04-21', '', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400151519', 'Tourist', '2025-02-10', '2025-06-26', 'W8591672', 'Regular', '2023-01-03', '2033-01-02', 'India', 'Delhi', '105_1746868553.PNG', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f188805fba681f188805fbb', '2025-05-10 09:14:58', NULL, 2, 1, 4),
(106, 'CME0044', 'Fida Hussain', ' Shah', 'Fida Hussain  Shah', 'syedfida7908', 'syedfida7908@gmail.com', '$2y$10$Q3HVIFQCAiaJRacdG8NVue.MEtbGET4qjdiZ6plN38iUmvRR2OVxO', '1993-12-24', 'Male', 'Single', 'A+', '588745006', 'Dubai', 'United Arab Emirates', 'Bachelor of Arts', NULL, NULL, 'Punjab University Lahore', 'active', 0, NULL, '2025-04-18', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120247030875', 'Emp Visa Change Status', '2024-10-21', '2026-10-20', 'ZE5152172', 'Regular', '2024-12-24', '2029-12-25', 'Pakistan', 'Islamabad', '106_1746869248.PNG', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f1a556ad41681f1a556ad42', '2025-05-10 09:24:11', NULL, 2, 1, 4),
(107, 'CME0045', 'Danush ', 'Mani', 'Danush  Mani', 'dhanushthara07', 'dhanushthara07@gmail.com', '$2y$10$tJo2d0KKeg1Um8O.Ha.tJO4eRSfzTg.Ancw/aMh9NdzU/OyZqjvAq', '1996-06-12', 'Male', 'Single', 'A+', '506871784', 'dubai', 'United Arab Emirates', 'B.Tech/B.E. ', NULL, NULL, 'Sri Krishna College of Engineering and Technology, Coimbatore', 'active', 0, NULL, '2025-04-29', '', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', NULL, 'NA', NULL, NULL, '3012025114/0080060', 'Tourist', '2025-04-26', '2025-06-25', 'Z7975295 ', 'Regular', '2024-09-09', '2034-09-08', 'India', ' Coimbatore', '107_1746871743.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f24ccead37681f24ccead39', '2025-05-10 10:07:45', NULL, 2, 1, 4),
(108, 'CME0046', 'Mohammad ', 'Sithik ', 'Mohammad  Sithik ', 'sithik2912', 'sithik2912@gail.com', '$2y$10$J2eJS2BZXG5xwCB1bomVUOHzL8Fgo4RqfElCfpNd.ml/6MxTng7aK', '2003-12-29', 'Male', 'Single', 'A+', '506043447', 'Dubai', 'United Arab Emirates', 'Diploma in Mechinaical Engireeing ', NULL, NULL, NULL, 'active', 0, NULL, '2025-04-18', '', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400311145', 'Tourist', '2025-03-14', '2025-06-02', 'Y7189483', '', '2023-08-10', '2033-08-09', 'India', 'Madurai', '108_1746872639.PNG', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f281731171681f281731173', '2025-05-10 10:22:22', NULL, 2, 1, 4),
(109, 'CME0047', ' Suhail', 'Attar', ' Suhail Attar', 'suhailattar7799', 'suhailattar7799@gmail.com', '$2y$10$PmyqfvJXIIgxA893sN6A.e1FSlXCn9qLX.YW9vjo2s/0qcQXHLZji', '1995-04-13', 'Male', 'Single', 'A+', '507400143', 'Dubai', 'United Arab Emirates', 'Bachelor Degree', NULL, NULL, 'Sir Krishna Devaraya Universities', 'active', 0, NULL, '2025-04-29', '', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', NULL, 'NA', NULL, NULL, '3012025114/0046570', 'Tourist', '2025-03-14', '2025-05-13', 'P6896272 ', 'Regular', '2017-01-12', '2027-01-11', 'India', 'hyderabad', '109_1746873243.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f2a2d0bf3a681f2a2d0bf3b', '2025-05-10 10:33:09', NULL, 2, 1, 4),
(110, 'CME0048', 'Parfool ', 'Soni ', 'Parfool  Soni ', 'pksoni707070', 'pksoni707070@gmail.com', '$2y$10$wet9kg7bNd8QdP3AMNKdyeMXs12vR9NFRjGVfM7dLXKR6voifVhFe', '2001-07-15', 'Male', 'Single', 'A+', '558414475', 'Dubai', 'United Arab Emirates', 'Business in commerce', '2019-06-06', '2022-06-11', 'University of sindh', 'active', 0, NULL, '2025-04-21', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', NULL, 'NA', NULL, NULL, '20120232834988', NULL, '2023-06-25', '2025-06-25', 'FG4224691', 'Regular', '2022-06-09', '2027-06-08', 'Pakistan', 'Mipurkhas', '110_1746873740.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f2c89f1352681f2c89f1354', '2025-05-10 10:41:33', NULL, 2, 1, 4),
(111, 'CME0049', 'MINA SAMIR ', 'NESSIM GIRGES ATTALAH', 'MINA SAMIR  NESSIM GIRGES ATTALAH', 'mina.nessiem15', 'mina.nessiem15@gmail.com', '$2y$10$xX3O3O/AM4kYS/./gKtMRObrH9DsmawPIcnwh6QfWvkuwU.UGoEJi', '1993-04-15', 'Male', 'Single', 'A+', '586634399', 'Dubai', 'United Arab Emirates', 'Bachelor of Commerce', '2016-01-01', NULL, 'Alexandria', 'active', 0, NULL, '2025-03-19', '', '', '', 'user', NULL, 'Relationship officer', 'CME0012', '', NULL, '', '', NULL, 'NA', NULL, NULL, '30120251140034204', 'Tourist', '2025-02-26', '2025-05-26', 'A36195618 ', 'Regular', '2024-01-08', '2031-01-07', 'Egypt', NULL, '111_1746876523.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f35e742b74681f35e742b75', '2025-05-10 11:21:07', NULL, 2, 1, 4),
(112, 'CME0050', 'LAKSHMIPATHI ', 'GORREPATI ', 'LAKSHMIPATHI  GORREPATI ', 'lakshmipathi98978', 'lakshmipathi98978@gmail.com', '$2y$10$SPjtl6f8D1nQlwnS8cpCmOan8Q6R77VIa31KiML0WfhaYP4Ce2whG', '2000-03-01', 'Male', 'Single', 'A+', '503275341', 'Dubai', 'United Arab Emirates', 'Bachelor of Commerce', '2017-06-01', '2020-12-01', 'Bangalore university Bengaluru', 'inactive', 0, NULL, '2025-04-28', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400424364', 'Tourist', '2025-04-09', '2025-06-07', 'W6855455', 'Regular', '2022-11-10', '2032-11-09', 'India', 'Bengaluru', '112_1746876886.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f38b58e602681f38b58e603', '2025-05-10 11:33:54', NULL, 2, 1, 4),
(113, 'CME0051', 'Dipendra ', 'Kumar SAH', 'Dipendra  Kumar SAH', 'dksonu75', 'dksonu75@gmail.com', '$2y$10$LBEklaTMwWd6rDN8ckfW1e3s2ETOJfxaaRQ.ilZ9./vpusk1kWFL.', '1992-08-27', 'Male', 'Single', 'A+', '528531106', 'Dubai', 'United Arab Emirates', 'MBA (Finance and Marketing) ', NULL, NULL, 'GURU GOVIND SINGH INDRAPRASTHA  UNIVERSITY', 'active', 0, NULL, '2025-04-26', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', NULL, 'NA', NULL, NULL, '301202572102', 'Emp Visa Change Status', '2025-02-05', '2027-02-04', '10453176', 'Regular', '2017-07-04', '2027-07-03', 'Nepal', 'Mofa', '113_1746879615.PNG', '', '', 0, NULL, NULL, NULL, NULL, NULL, '681f423fb6fa2681f423fb6fa4', '2025-05-10 12:13:38', NULL, 2, 1, 4),
(114, 'CME0052', 'Wilkinson ', 'Fernandes', 'Wilkinson  Fernandes', 'wilkinston.fernandes', 'wilkinston.fernandes@gmail.com', '$2y$10$lTXyj4gHb527KYTb6fcc5OwT/i9ch6o0tX0u6HKeY5sBr76Tr8YHS', '1994-12-23', 'Male', 'Single', 'A+', '503582201', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, NULL, 'inactive', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400446253', 'Tourist', '2025-04-14', '2025-06-12', 'B9612451', 'Regular', '2024-01-11', '2034-01-10', 'India', 'Panaji', '114_1747821088.jpg', 'uploads/documents/visa_1747820876.pdf', '', 0, NULL, NULL, NULL, NULL, NULL, '682da01b33777682da01b33778', '2025-05-21 09:47:56', NULL, 2, 1, 4),
(115, 'CME0053', 'Huyam', ' Abbas', 'Huyam  Abbas', 'heam23', 'heam23@live.com', '$2y$10$gUKdemUvh8tlmgaZD0nZ6e8GIwmJCYBmBo4SoQlKWQHhbWH2iOg.2', '1983-01-01', 'Female', '', '', '561073188', 'Dubai', 'United Arab Emirates', ' B.Sc. In Computer Science', NULL, NULL, 'Science Faculty Of: Computer Science and Information Technolog', 'inactive', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120223327984', 'Spouse Visa', '2022-08-21', '2032-08-20', ' P07019056', 'Ordinary', '2020-08-30', '2025-08-29', 'Sudan', 'Omarawapa', 'uploads/profile_pics/profile_1747822875.jpg', 'uploads/documents/visa_1747822875.pdf', 'uploads/documents/passport_1747822875.jpg', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '682da7e0e651f682da7e0e6522', '2025-05-21 10:21:15', NULL, 2, 1, 4),
(116, 'CME0054', 'Muhammad imran', ' Naeem', 'Muhammad imran  Naeem', 'Chimrannaseem2', 'Chimrannaseem2@gmail.com', '$2y$10$g5jETUHCDIBTkDBgRWHWKOK3i35Q9ynADwd92t0nduP4/A1dbodGu', '1974-03-25', 'Male', 'Married', '', '501092160', 'Dubai', 'United Arab Emirates', 'BSC Physics', NULL, NULL, 'THE ISLAMIA UNVERSITY BAHAWALPUR', 'active', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120252403601', 'Emp Visa Change Status', '2025-04-05', '2027-04-04', 'AG9854884', 'Ordinary', '2021-03-17', '2026-03-17', 'Pakistan', 'Bawalnagar', 'uploads/profile_pics/profile_1747823309.jpg', 'uploads/documents/visa_1747823309.jpg', 'uploads/documents/passport_1747823309.pdf', 0, '', NULL, NULL, NULL, NULL, '682da965b3d73682da965b3d74', '2025-05-21 10:28:29', NULL, 2, 1, 4),
(117, 'CME0055', 'Dilan ', 'Champaka', 'Dilan  Champaka', 'dilan', 'dilan@communikmarketing.com', '$2y$10$Y.moVlGKkdq7tmgVi/z/5OcmPC2H26M8sG0zyMASc8fxbyWXiaGg6', '1986-05-17', 'Male', 'Married', '', '505021007', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, '', 'active', 0, NULL, '2025-05-05', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '20220242449353', 'Emp Visa Change Status', '2024-09-30', '2026-09-29', 'N11410221', 'Ordinary', '2024-09-30', '2026-09-29', 'Sri Lanka', 'Sri ', '117_1747823926.PNG', 'uploads/documents/visa_1747823810.jpg', 'uploads/documents/passport_1747823810.jpg', 0, '', NULL, NULL, NULL, NULL, '682dab3e8c148682dab3e8c149', '2025-05-21 10:36:50', NULL, 2, 0, 4),
(118, 'CME0056', 'Aisha ', 'Salman', 'Aisha  Salman', 'aishasalman460', 'aishasalman460@gmail.com', '$2y$10$AWwbGxDxNLp4cGkpBzeWCuCcEVTLc1YM5Q.pxpxjtZ/Tphkjztg6S', '1999-02-05', 'Female', 'Married', '', '552564134', '', 'United Arab Emirates', 'masters in Arts & Social Sciences', NULL, NULL, 'University of Karachi', 'active', 0, NULL, '2025-05-14', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120257016842', 'Emp Visa Change Status', '2025-01-21', '2027-01-20', 'NG5751941', 'Ordinary', '2022-06-16', '2032-06-15', 'Pakistan', 'Kara', '118_1747825966.PNG', 'uploads/documents/visa_1747825667.pdf', 'uploads/documents/passport_1747825667.pdf', 0, '', NULL, NULL, '', '0000-00-00', '682db291ac306682db291ac307', '2025-05-21 11:07:47', NULL, 2, 0, 4),
(119, 'CME0057', 'Azmat', ' Pasha ', 'Azmat  Pasha ', 'azmath707', 'azmath707@gmail.com', '$2y$10$OTiqeO61s7wf32qvmnnmF.2RJixh4A8EmY2BrU.vGI6tehQ/gA6gO', '1985-11-08', 'Male', 'Married', '', '585708687', 'Dubai', 'United Arab Emirates', 'Bachelor of Commerce', NULL, NULL, 'osmania University', 'inactive', 0, NULL, '2025-05-12', '', '', '', 'user', NULL, 'Relationship officer', 'CME0007', '', NULL, '', '', '', 'NA', '', '', '20120247310265', 'Emp Visa Change Status', '2025-01-11', '2027-01-10', 'Y3436706', 'Ordinary', '2024-06-10', '2034-06-04', 'India', 'hyderabad', 'uploads/profile_pics/profile_1747826296.jpg', '', 'uploads/documents/passport_1747826296.pdf', 0, '', NULL, NULL, NULL, NULL, '682db53d92844682db53d92845', '2025-05-21 11:18:16', NULL, 2, 1, 4),
(120, 'CME0058', 'Afraz ', 'Shaikh', 'Afraz  Shaikh', 'AFRAZSHAIKH2122', 'AFRAZSHAIKH2122@GMAIL.COM', '$2y$10$FaaDIcTnlogR6I.VJbCYHe1TqiPt3AdW7JFGoOFtJtLRF7gItwueW', '1988-02-21', 'Male', 'Married', '', '566783347', 'Dubai', 'United Arab Emirates', 'Intermediate', NULL, NULL, '', 'inactive', 0, NULL, '2025-05-08', '', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '20120242421595', 'Emp Visa Change Status', '2024-05-25', '2026-05-24', 'X7753198', 'Ordinary', '2023-03-21', '2033-03-20', 'India', 'MANGALORE', '120_1747826875.PNG', 'uploads/documents/visa_1747826766.pdf', 'uploads/documents/passport_1747826766.jpg', 0, '', NULL, NULL, NULL, NULL, '682db70884441682db70884443', '2025-05-21 11:26:06', NULL, 2, 1, 4),
(121, 'CME0059', 'Vikram ', 'Melath R', 'Vikram  Melath R', 'Vikram', 'Vikram@communikmarketing.com', '$2y$10$DVBYuaoWU.Dx4M9F1hDjueOttTGHiAiluvgoZGaWK1YAknUgEIS2K', '0000-00-00', 'Male', '', '', '566134254', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-04-28', 'Dubai', '', '', 'user', NULL, 'Asst Sales Manager', 'CME0041', '', NULL, '', '', '', 'NA', '', '', 'NA', '', NULL, NULL, 'NA', '', NULL, NULL, '', '', '', '', '', 0, '', NULL, NULL, NULL, NULL, '682db9e71457b682db9e71457c', '2025-05-21 11:35:43', NULL, 2, 0, 4),
(122, 'CME0060', 'Roy ', 'Jayanta', 'Roy  Jayanta', 'jayroy1005', 'jayroy1005@gmail.com', '$2y$10$i8OmiOgd41x/htgoRuzhdekpvTP/RrP.qd5rTMWjB.FmAt4EFZpGO', '1997-07-14', 'Male', 'Single', '', '581799042', 'Dubai', 'United Arab Emirates', 'Bachelor of Arts', NULL, NULL, '', 'active', 0, NULL, '2025-05-20', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '201202511400536322', 'Tourist', '2025-05-03', '2025-07-01', 'V2551741', 'Ordinary', '2021-10-25', '2031-10-24', 'India', 'Kolkata', '122_1748003628.PNG', 'uploads/documents/visa_1748003532.pdf', 'uploads/documents/passport_1748003532.pdf', 0, '', NULL, NULL, NULL, NULL, '683069de35c80683069de35c82', '2025-05-23 12:32:12', NULL, 2, 1, 4),
(123, 'CME0061', 'KEN MAR ', 'MOISES SECATIN', 'KEN MAR  MOISES SECATIN', 'kensecatin', 'kensecatin@gmail.com', '$2y$10$eY7p8fBF.WWZBOkCbILceOCL9PZRV6S1HmHu/d7t/VAL2aX3j8IhK', '1998-06-22', 'Female', 'Single', '', '562679852', '', 'United Arab Emirates', 'Bachelor of Science in Hotel and  Restaurant Management ', NULL, NULL, '', 'inactive', 0, NULL, '2025-05-19', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120252340122', 'Emp Visa Change Status', '2025-02-25', '2027-02-24', ' P2284294B', 'Ordinary', '2019-06-22', '2029-06-21', 'Philippines', '', '123_1748080010.PNG', 'uploads/documents/visa_1748079877.pdf', 'uploads/documents/passport_1748079877.jpg', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '68319402bed4968319402bed4a', '2025-05-24 09:44:37', NULL, 2, 1, 4),
(124, 'CME0062', 'Joseph ', 'Lalnunthanga', 'Joseph  Lalnunthanga', 'Josephlalnunthanga042', 'Josephlalnunthanga042@gmail.com', '$2y$10$ey4Bz2EuHdUcL3a5FZE8buN8x480xPH3Gsdv7oEbhEqSOubg9tOEy', '2005-10-10', 'Male', 'Single', 'A+', '503822396', NULL, 'United Arab Emirates', NULL, NULL, NULL, NULL, 'active', 0, NULL, '2025-05-16', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400422577', 'Tourist', '2025-04-09', '2025-06-07', 'C2913118', 'Regular', '2024-11-19', '2034-11-18', 'India', NULL, '124_1748080416.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '6831965dd4b846831965dd4b85', '2025-05-24 09:52:38', NULL, 2, 1, 4),
(125, 'CME0063', 'ANIL ', 'KOURANI', 'ANIL  KOURANI', 'anilkourani89', 'anilkourani89@gmail.com', '$2y$10$eFZheyEukz4dGv8tOs8CHuE2.jZTqOKAPwYHuQk6KslNGEmQCb2Ei', '1996-03-21', 'Male', 'Married', 'A+', '585107907', NULL, 'United Arab Emirates', 'B.COM', NULL, NULL, NULL, 'active', 0, NULL, '2025-05-19', 'Dubai', '', '', 'user', NULL, 'Sales Team Leader', 'CME0004', '', NULL, '', '', NULL, 'NA', NULL, NULL, '601202578071', 'Emp Visa Change Status', '2025-02-07', '2027-02-06', 'Y9539527', 'Regular', '2023-10-26', '2033-10-25', 'India', 'Rajastan', '125_1748082542.jpg', '', '', 0, NULL, NULL, NULL, NULL, NULL, '68319e846030c68319e846030d', '2025-05-24 10:27:58', NULL, 2, 0, 4),
(126, 'CME0064', ' MOHAMED ', 'IRSHAT', ' MOHAMED  IRSHAT', 'Dildilsha44', 'Dildilsha44@Gmail.com', '$2y$10$FEpAhJ3/.HRs0hhCdIBtteWFbzW2CVL7ZMSFr/uZASWCDQ36pZcjm', '1994-03-05', 'Male', '', '', '0502484063', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-22', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0014', '', NULL, '', '', '', 'NA', '', '', '20120242443439', 'Emp Visa Change Status', '2024-10-16', '2026-10-15', ' N9489367', 'Ordinary', '2022-04-29', '2032-04-29', 'Sri Lanka', '', '126_1748247377.PNG', 'uploads/documents/visa_1748247289.pdf', 'uploads/documents/passport_1748247289.pdf', 0, '', NULL, NULL, NULL, NULL, '68342204432eb68342204432ec', '2025-05-26 08:14:49', NULL, 2, 1, 4),
(127, 'CME0065', 'Balda ', 'Narsimhulu', 'Balda  Narsimhulu', 'balda.narsimhulu2011', 'balda.narsimhulu2011@gmail.com', '$2y$10$9anjyejCX1s.baLou1jLEOGg.lEONvd80U5EYk9kfnJojJSGXHW8W', '1984-06-05', 'Male', 'Married', '', '509614565', '', 'United Arab Emirates', 'Bachelor of Arts', NULL, NULL, 'Osmania University', 'active', 0, NULL, '2025-05-02', '', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', '', 'NA', '', '', '201202511400360588', 'Tourist', '2025-03-24', '2025-05-22', 'R2076907', 'Ordinary', '2017-09-14', '2027-09-13', 'India', 'hyderabad', 'uploads/profile_pics/profile_1748421920.PNG', 'uploads/documents/visa_1748421920.pdf', 'uploads/documents/passport_1748421920.jpeg', 0, '', NULL, NULL, NULL, NULL, '6836cbe94e2b06836cbe94e2b3', '2025-05-28 08:45:20', NULL, 2, 1, 4);
INSERT INTO `employees` (`id`, `eid`, `first_name`, `last_name`, `full_name`, `user_name`, `email`, `password`, `birthday`, `gender`, `maritalsts`, `blood_group`, `contact`, `address`, `country`, `degree`, `start_from`, `end_to`, `Institute`, `status`, `email_verified`, `role_id`, `doj`, `EmpLoc`, `EmpDiv`, `EmpGrade`, `role`, `new_role_id`, `designation`, `reporting_manager`, `emp_left_org`, `dol`, `EmpCostcenter`, `MOLID`, `bank_name`, `account_no`, `iban`, `nominee`, `visa_number`, `visa_type`, `visa_issue_date`, `visa_expiry_date`, `passport_number`, `passport_type`, `passport_issue_date`, `passport_expiry_date`, `country_of_issue`, `passport_issue_place`, `profile_pic`, `visa_doc`, `passport_doc`, `is_field_staff`, `labour_card_no`, `labour_card_start_date`, `labour_card_end_date`, `pregnancy_status`, `due_date`, `token`, `created_at`, `document_number`, `department_id`, `is_trainee`, `workflow_level`) VALUES
(128, 'CME0066', 'SOBIT', ' PAUL', 'SOBIT  PAUL', 'sobitpaul7624', 'sobitpaul7624@gmail.com', '$2y$10$X/exOkq5x9jy7cmA3UgEFObllz.Ir0CicBsSVIHiRcJuFLjtzIX7e', '1993-04-11', 'Male', 'Single', 'A+', '568142510', NULL, 'United Arab Emirates', 'Master in Bussiiness Adminstration', NULL, NULL, 'T. John Institute of  Management & Science,  Bengaluru, India ', 'active', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', NULL, 'NA', NULL, NULL, '3012025114/0071751', 'Tourist', '2025-04-15', '2025-06-15', 'Y4004471', 'Regular', '2024-06-13', '2034-06-12', 'India', 'kozhikode', 'uploads/profile_pics/profile_1748422284.jpg', 'uploads/documents/visa_1748422284.pdf', 'uploads/documents/passport_1748422284.jpeg', 0, NULL, NULL, NULL, NULL, NULL, '6836cd921c89e6836cd921c89f', '2025-05-28 08:51:24', NULL, 2, 1, 4),
(129, 'CME0067', 'MARY CHRISTINA ', ' CHRISTY', 'MARY CHRISTINA   CHRISTY', 'cmary.christina', 'cmary.christina@gmail.com', '$2y$10$lrfT.XvCr92hAL.ZOOWH.ezWS.PFKiTJt7bWvXUGVlKK8ndQzNxpS', '1983-12-21', 'Male', '', '', '561659737', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-01', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', '', 'NA', '', '', '201202511400494180', 'Tourist', '2025-04-24', '2025-06-22', 'B6690674', 'Ordinary', '2023-10-27', '2033-10-26', 'India', 'Telanganga', 'uploads/profile_pics/profile_1748422790.jpg', 'uploads/documents/visa_1748422790.pdf', 'uploads/documents/passport_1748422790.pdf', 0, '', NULL, NULL, NULL, NULL, '6836cfa7ea3ee6836cfa7ea3ef', '2025-05-28 08:59:50', NULL, 2, 1, 4),
(130, 'CME0068', 'Amina ', 'Mohammad ', 'Amina  Mohammad ', 'aminamuhammad089', 'aminamuhammad089@gmail.com', '$2y$10$63q7vQBT6hF5hgmSRlGWOOmsaB.YIFNvtsO20ALGuvj6mNxfasVcS', '1980-01-01', 'Female', '', '', '569868095', '', 'United Arab Emirates', 'Bachelor of Arts', NULL, NULL, 'Hazara University Mansehra.', 'active', 0, NULL, '2025-06-08', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', '', 'NA', '', '', '20120212112158', 'Emp Visa Change Status', '2023-03-14', '2025-03-13', 'HF3098504', 'Ordinary', '2023-08-15', '2028-08-13', 'Pakistan', 'Manesra', 'uploads/profile_pics/profile_1748423185.jpg', 'uploads/documents/visa_1748423185.pdf', 'uploads/documents/passport_1748423185.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '6836d0d362cd06836d0d362cd1', '2025-05-28 09:06:25', NULL, 2, 1, 4),
(131, 'CME0069', 'Nouman ', 'Shahid', 'Nouman  Shahid', 'noumanshahid69', 'noumanshahid69@gmail.com', '$2y$10$/mtWHoOMYMuw0/6yVOJRuOVkK50Q0MHS8o1g2sPRq.YIkIv6mqDEO', '1996-05-31', 'Male', '', '', '569252049', '', 'United Arab Emirates', 'Master in Law', NULL, NULL, 'University of Punjab', 'active', 0, NULL, '2025-05-15', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', '', 'NA', '', '', '20120232965471', 'Emp Visa Change Status', '2023-10-06', '2025-10-05', 'GR5145362', 'Ordinary', '2022-01-27', '2032-01-26', 'Pakistan', 'Lahore', 'uploads/profile_pics/profile_1748426984.jpeg', 'uploads/documents/visa_1748426984.pdf', 'uploads/documents/passport_1748426984.jpeg', 0, '', NULL, NULL, NULL, NULL, '6836dff51bc266836dff51bc27', '2025-05-28 10:09:44', NULL, 2, 1, 4),
(132, 'CME0070', 'Mohammad ', 'Azeem', 'Mohammad  Azeem', 'azeemalone83', 'azeemalone83@gmail.com', '$2y$10$rr2CVjnumTZHHVR2.Bz5n.gS0RWrhbUKduutjqAGO4udBu6YR/8au', '1997-05-25', 'Male', 'Single', 'A+', '552217120', NULL, 'United Arab Emirates', 'Bachelor of Arts', NULL, NULL, 'ALIGARH MUSLIM UNIVERSITY', 'active', 0, NULL, '2025-05-19', '', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400581763', 'Tourist', '2025-05-15', '2025-07-13', 'V4408696', 'Regular', '2021-11-16', '2031-11-15', 'India', 'uttar pardesh', '132_1748427632.PNG', 'uploads/documents/visa_1748427459.pdf', 'uploads/documents/passport_1748427459.pdf', 0, NULL, NULL, NULL, NULL, NULL, '6836e1c99fad06836e1c99fad1', '2025-05-28 10:17:39', NULL, 2, 1, 4),
(133, 'CME0071', 'Nimra', ' Nasir ', 'Nimra  Nasir ', 'nimranovel', 'nimranovel@gmail.com', '$2y$10$Hpy2rGqSKxmHDXyBvW8hpO9pM9u9qYNsrjZqTnHdUH0Zk9zn08EvG', '1996-11-15', 'Female', 'Single', 'A+', '554929149', NULL, 'United Arab Emirates', 'BBA Hons.(Finance): ', NULL, NULL, 'University of Punjab', 'active', 0, NULL, '2025-05-06', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0041', '', NULL, '', '', NULL, 'NA', NULL, NULL, '201202511400051660', 'Tourist', '2025-05-26', '2025-07-24', 'NY9825441', 'Regular', '2024-11-14', '2029-11-13', 'Pakistan', 'Lahore', '133_1748428250.PNG', 'uploads/documents/visa_1748428175.pdf', 'uploads/documents/passport_1748428175.jpg', 0, NULL, NULL, NULL, '', '0000-00-00', '6836e4928e3c86836e4928e3c9', '2025-05-28 10:29:35', NULL, 2, 1, 4),
(134, 'CME0072', 'Navoda ', 'Piyadarshini', 'Navoda  Piyadarshini', 'navoda.piya8899', 'navoda.piya8899@icloud.com', '$2y$10$kyj8E6.c/e3jj.P/VET0X.pBHXnYqcQCi1XTaOedHeEVWmOqwzlR.', '2000-10-08', 'Female', '', '', '566281870', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-20', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0059', '', NULL, '', '', '', 'NA', '', '', '20120237668108', 'Emp Visa Change Status', '2024-01-17', '2026-01-16', 'N10546391', 'Ordinary', '2023-05-09', '2033-05-09', 'Sri Lanka', '', 'uploads/profile_pics/profile_1748428867.jpg', 'uploads/documents/visa_1748428867.jpeg', 'uploads/documents/passport_1748428867.pdf', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '6836e730000656836e73000066', '2025-05-28 10:41:07', NULL, 2, 1, 4),
(135, 'CME0073', 'Mohammad ', 'Nasser', 'Mohammad  Nasser', 'm.n4sser', 'm.n4sser@gmail.com', '$2y$10$usmWbvIGBwCOTgNQLBA3GOxmqTjiZ3HP3J1VSQJApdKANLK6YjQOe', '1993-04-09', 'Male', '', '', '586264058', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-19', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '20120237725681', 'Emp Visa Change Status', '2024-01-24', '2026-01-23', 'A03840973', 'Ordinary', '2022-04-18', '2032-04-17', 'Bangladesh', 'Dhaka', 'uploads/profile_pics/profile_1748429327.jpg', 'uploads/documents/visa_1748429327.pdf', 'uploads/documents/passport_1748429327.pdf', 0, '', NULL, NULL, NULL, NULL, '6836e933ea7766836e933ea777', '2025-05-28 10:48:47', NULL, 2, 1, 4),
(136, 'CME0074', 'Pragati ', 'kachhawaha', 'Pragati  kachhawaha', 'pragati31102000', 'pragati31102000@gmail.com', '$2y$10$BvE8thRvvFzaWbm8QSJ0nuEetx/qJs8T5XN1Q0nktdgfIsIUIGFjO', '2000-10-31', 'Female', '', '', '586264058', '', 'United Arab Emirates', '', NULL, NULL, '', 'inactive', 0, NULL, '2025-05-19', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0013', '', NULL, '', '', '', 'NA', '', '', '30120251140088182', 'Tourist', '2025-05-03', '2025-07-01', 'W2080584', 'Ordinary', '2022-06-28', '2032-06-27', 'India', 'Rajastan', 'uploads/profile_pics/profile_1748429675.jpg', 'uploads/documents/visa_1748429675.pdf', 'uploads/documents/passport_1748429675.jpg', 0, '', NULL, NULL, 'Not Pregnant', '0000-00-00', '6836ea750cfa16836ea750cfa2', '2025-05-28 10:54:35', NULL, 2, 1, 4),
(137, 'CME0075', 'Abrar ', 'Ahmed ', 'Abrar  Ahmed ', 'abrarahmmedcm269', 'abrarahmmedcm269@gmail.com', '$2y$10$pJGsrUdj2NrG0zWGsGxhsOgBUUlrcDkRbQVAayUcmN7frUhZSG8yW', '2004-10-26', 'Male', '', '', '543839706', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-13', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0020', '', NULL, '', '', '', 'NA', '', '', '20220242523658', 'Emp Visa Change Status', '2024-10-16', '2026-10-15', 'Y6381614', 'Ordinary', '2023-07-21', '2033-07-20', 'India', 'Chenni', 'uploads/profile_pics/profile_1748431428.jpg', 'uploads/documents/visa_1748431428.pdf', 'uploads/documents/passport_1748431428.pdf', 0, '', NULL, NULL, NULL, NULL, '6836f124ed3546836f124ed355', '2025-05-28 11:23:48', NULL, 2, 1, 4),
(138, 'CME0076', 'Shaik Abdul ', 'Raheem ', 'Shaik Abdul  Raheem ', 'shaikraheem849', 'shaikraheem849@gmail.com', '$2y$10$NmrgWo4SxfwzjgnPL0BKbOSQs8FzfhLGA8clcGhRh7KnjStx6s8D6', '2001-10-31', 'Male', '', '', '558728161', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0020', '', NULL, '', '', '', 'NA', '', '', '201202511400319166', 'Tourist', '2025-03-16', '2025-05-14', ' U9199003', 'Ordinary', '2021-03-30', '2031-03-29', 'India', 'hyderabad', 'uploads/profile_pics/profile_1748431955.png', 'uploads/documents/visa_1748431955.pdf', 'uploads/documents/passport_1748431955.pdf', 0, '', NULL, NULL, NULL, NULL, '6836f38e014ef6836f38e014f1', '2025-05-28 11:32:35', NULL, 2, 1, 4),
(139, 'CME0077', 'Nameera ', 'Tabassum', 'Nameera  Tabassum', 'nameeratabassumhrn', 'nameeratabassumhrn@gmail.com', '$2y$10$ejOmJFaa9g2xmIzVffvzheJEtU2xbNSbU1sfn0P9ZuMeAwPyyJc6K', '2001-07-09', 'Male', '', '', '559674061', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0020', '', NULL, '', '', '', 'NA', '', '', '30120251140090189', 'Tourist', '2025-05-06', '2025-07-04', 'W8733670', 'Ordinary', '2022-12-20', '2032-12-19', 'India', 'hyderabad', 'uploads/profile_pics/profile_1748435941.jpeg', 'uploads/documents/visa_1748435941.pdf', 'uploads/documents/passport_1748435941.jpeg', 0, '', NULL, NULL, NULL, NULL, '683702fc23018683702fc23019', '2025-05-28 12:39:01', NULL, 2, 1, 4),
(140, 'CME0078', 'HAROON ALI ', 'SHAIK', 'HAROON ALI  SHAIK', 'haroonaly78q', 'haroonaly78q@gmail.com', '$2y$10$gVp0pPGnI9b.Y9/eqtPc5u9pbVKRYXCSffnwzRoV1u7bfm2seOJEC', '2003-01-13', 'Male', '', '', '589884341', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-12', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0020', '', NULL, '', '', '', 'NA', '', '', '201202511400440039', 'Tourist', '2025-04-14', '2025-06-12', 'C5166249', 'Ordinary', '2024-11-11', '2034-11-10', 'India', 'hyderabad', 'uploads/profile_pics/profile_1748436567.jpeg', 'uploads/documents/visa_1748436567.pdf', 'uploads/documents/passport_1748436567.pdf', 0, '', NULL, NULL, NULL, NULL, '683704ac812c0683704ac812c1', '2025-05-28 12:49:27', NULL, 2, 1, 4),
(141, 'CME0079', ' Saket Sureshkumar ', 'Gambhir', ' Saket Sureshkumar  Gambhir', 'saket.gambhir14', 'saket.gambhir14@gmail.com', '$2y$10$JKTo6E3nRq8/nkmCW9HRsekZEL0yjnieK7ZjuETVHoK.RPdZfzJOq', '2001-01-26', 'Male', '', '', '0585416477', '', 'United Arab Emirates', 'Bacholer in Bussiiness Adminstration', NULL, NULL, '', 'active', 0, NULL, '2025-05-26', 'Dubai', '', '', 'user', NULL, 'Digital Sales', 'CME0001', '', NULL, '', '', '', 'NA', '', '', '20220242446630', 'Emp Visa Change Status', '2024-10-23', '2026-10-22', ' T2345362', 'Ordinary', '2019-03-08', '2029-03-07', 'India', 'Gujrat', 'uploads/profile_pics/profile_1748520436.png', 'uploads/documents/visa_1748520436.pdf', 'uploads/documents/passport_1748520436.pdf', 0, '', NULL, NULL, NULL, NULL, '68384c7c3e19668384c7c3e197', '2025-05-29 12:07:16', NULL, 1, 0, 4),
(142, 'CME0080', 'Ibrar ', 'Ashraf', 'Ibrar  Ashraf', 'ibrarashraf05', 'ibrarashraf05@gmail.com', '$2y$10$/Niv8yJKZtj.f/1qpCVmUelqm4MDKtizPQr7hZVrg940n3e8ZLcjK', '2000-08-05', 'Male', '', '', '552620356', '', 'United Arab Emirates', '', NULL, NULL, '', 'active', 0, NULL, '2025-05-26', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0063', '', NULL, '', '', '', 'NA', '', '', '20120232927570', 'Emp Visa Change Status', '2023-08-24', '2025-08-23', 'MW1846311', 'Ordinary', '2024-05-16', '2034-05-15', 'Pakistan', 'Gujrat', 'uploads/profile_pics/profile_1748681479.jpg', 'uploads/documents/visa_1748681479.jpg', 'uploads/documents/passport_1748681479.jpg', 0, '', NULL, NULL, NULL, NULL, '683ac2123cb6b683ac2123cb6c', '2025-05-31 08:51:19', NULL, 2, 1, 4),
(143, 'CME0081', 'Ayan ', 'Khan', 'Ayan  Khan', 'khanayan.3343', 'khanayan.3343@gmail.com', '$2y$10$VJ5VRUybvbt8HtTTGImiqOYauPfva93DNWRwN2Vk5PWb7IJRlhuEe', '2001-11-03', 'Male', '', '', '585870493', '', 'United Arab Emirates', ' Higher Secondary (Intermed', NULL, NULL, '', 'active', 0, NULL, '2025-05-01', 'Dubai', '', '', 'user', NULL, 'Relationship officer', 'CME0008', '', NULL, '', '', '', 'NA', '', '', '20120242175672', 'Emp Visa Change Status', '2024-03-15', '2026-03-14', ' V4761984', 'Ordinary', '2021-12-30', '2031-12-31', 'India', 'uttar pardesh', '143_1748861884.PNG', 'uploads/documents/visa_1748861759.pdf', 'uploads/documents/passport_1748861759.pdf', 0, '', NULL, NULL, NULL, NULL, '683d82116ebd6683d82116ebd8', '2025-06-02 10:55:59', NULL, 2, 0, 4),
(146, 'CME082', 'super admin', 'communik', 'super admin communik', 'superadmin', 'superadmin@communikmarketing.com', '$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW', '0000-00-00', '', '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, '', 'super_admin', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '', '2025-06-09 10:52:59', NULL, NULL, 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `employee_appraisals`
--

CREATE TABLE `employee_appraisals` (
  `appraisal_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `period_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Self_Submitted','HOD_Reviewed','HR_Reviewed','Completed') DEFAULT 'Pending',
  `final_rating` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `visa_under` varchar(100) DEFAULT NULL,
  `manager` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `client_team` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `offer_letter` date DEFAULT NULL,
  `payroll_start` date DEFAULT NULL,
  `absent_days` int(11) DEFAULT NULL,
  `late_entries` int(11) DEFAULT NULL,
  `sick_leave` int(11) DEFAULT NULL,
  `approved_leave` int(11) DEFAULT NULL,
  `half_days` int(11) DEFAULT NULL,
  `annual_leave` int(11) DEFAULT NULL,
  `ontime_entries` int(11) DEFAULT NULL,
  `payable_days` decimal(5,2) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`id`, `employee_code`, `full_name`, `visa_under`, `manager`, `status`, `designation`, `client_team`, `mobile`, `email`, `joining_date`, `offer_letter`, `payroll_start`, `absent_days`, `late_entries`, `sick_leave`, `approved_leave`, `half_days`, `annual_leave`, `ontime_entries`, `payable_days`, `upload_date`, `created_at`, `updated_at`) VALUES
(1, 'edi', 'gop', 'ads', 'sdf', 'sadf', 'sdafkj', 'jkl', 'jkl', '', '0000-00-00', '0000-00-00', '0000-00-00', 0, 0, 0, 0, 0, 0, 0, 0.00, '2025-04-28 08:18:08', '2025-04-28 08:17:59', '2025-04-28 08:18:08');

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `doc_id` int(11) NOT NULL,
  `emp_id` varchar(10) NOT NULL,
  `template_id` int(11) NOT NULL,
  `version` int(11) DEFAULT 1,
  `document_status` enum('pending','signed','rejected') DEFAULT 'pending',
  `file_path` varchar(255) DEFAULT NULL,
  `signature_type` enum('draw','type','both') DEFAULT NULL,
  `signature_data` text DEFAULT NULL,
  `signature_image` text DEFAULT NULL,
  `signed_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_family`
--

CREATE TABLE `employee_family` (
  `id` int(11) NOT NULL,
  `eid` varchar(10) DEFAULT NULL,
  `fm_name` varchar(100) DEFAULT NULL,
  `fm_dob` date DEFAULT NULL,
  `fm_nationality` varchar(50) DEFAULT NULL,
  `fm_blood_group` varchar(10) DEFAULT NULL,
  `fm_gender` varchar(10) DEFAULT NULL,
  `fm_profession` varchar(100) DEFAULT NULL,
  `fm_relation` varchar(50) DEFAULT NULL,
  `anniversary_date` date DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_emergency_contact` enum('Yes','No') DEFAULT 'No',
  `education` varchar(100) DEFAULT NULL,
  `occupation_status` enum('Student','Working','Retired','Homemaker') DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `health_insurance_no` varchar(50) DEFAULT NULL,
  `govt_id` varchar(50) DEFAULT NULL,
  `current_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_family`
--

INSERT INTO `employee_family` (`id`, `eid`, `fm_name`, `fm_dob`, `fm_nationality`, `fm_blood_group`, `fm_gender`, `fm_profession`, `fm_relation`, `anniversary_date`, `phone_number`, `email`, `is_emergency_contact`, `education`, `occupation_status`, `medical_condition`, `health_insurance_no`, `govt_id`, `current_address`) VALUES
(1, 'U03', 'Moni Singh', '1986-09-10', 'Indian', 'O+', 'Female', 'Teacher', 'Spouse', NULL, NULL, NULL, 'No', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'U03', 'Sanjhi', '2015-09-05', 'indian', 'O+', 'Female', 'Child', 'Daughter', NULL, NULL, NULL, 'No', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_goals`
--

CREATE TABLE `employee_goals` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `goal_description` text NOT NULL,
  `target_date` date NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `progress` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_balance`
--

CREATE TABLE `employee_leave_balance` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(10) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `balance` decimal(6,2) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `last_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_leave_balance`
--

INSERT INTO `employee_leave_balance` (`id`, `emp_id`, `leave_type`, `balance`, `year`, `last_updated`) VALUES
(1, '7', 'Paternity Leave', 2.00, 2025, NULL),
(2, 'CME0042', 'Hajj and Umrah Leave', 23.00, 2025, NULL),
(3, 'CME0042', 'Hajj and Umrah Leave', 13.00, 2025, NULL),
(4, '0', 'Hajj and Umrah Leave', -44.00, 2025, NULL),
(5, '0', 'Hajj and Umrah Leave', -121.00, 2025, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_onboarding`
--

CREATE TABLE `employee_onboarding` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `biometric_login` enum('Yes','No','NA') DEFAULT 'NA',
  `process_evaluation` enum('Yes','No','NA') DEFAULT 'NA',
  `offer_letter` enum('Yes','No','NA') DEFAULT 'NA',
  `resume` enum('Yes','No','NA') DEFAULT 'NA',
  `kyc` enum('Yes','No','NA') DEFAULT 'NA',
  `policy_docs` enum('Yes','No','NA') DEFAULT 'NA',
  `staff_id` enum('Yes','No','NA') DEFAULT 'NA',
  `deem_id_request` enum('Yes','No','NA') DEFAULT 'NA',
  `deem_id` enum('Yes','No','NA') DEFAULT 'NA',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_roles_backup`
--

CREATE TABLE `employee_roles_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `eid` varchar(10) NOT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_roles_backup`
--

INSERT INTO `employee_roles_backup` (`id`, `eid`, `role`) VALUES
(63, 'CME0001', 'HOD'),
(64, 'CME0002', 'user'),
(65, 'CME0003', 'user'),
(66, 'CME0004', 'user'),
(67, 'CME0005', 'hr'),
(68, 'CME0006', 'user'),
(69, 'CME0007', 'user'),
(70, 'CME0008', 'user'),
(74, 'CME0012', 'user'),
(75, 'CME0013', 'user'),
(76, 'CME0014', 'user'),
(77, 'CME0015', 'user'),
(78, 'CME0016', 'user'),
(79, 'CME0017', 'user'),
(80, 'CME0018', 'user'),
(81, 'CME0019', 'user'),
(82, 'CME0020', 'user'),
(83, 'CME0021', 'user'),
(84, 'CME0022', 'user'),
(85, 'CME0023', 'user'),
(86, 'CME0024', 'user'),
(87, 'CME0025', 'user'),
(88, 'CME0026', 'user'),
(89, 'CME0027', 'user'),
(90, 'CME0028', 'user'),
(91, 'CME0029', 'user'),
(92, 'CME0030', 'user'),
(93, 'CME0031', 'user'),
(94, 'CME0032', 'user'),
(95, 'CME0033', 'user'),
(96, 'CME0034', 'user'),
(97, 'CME0035', 'user'),
(98, 'CME0036', 'user'),
(99, 'CME0037', 'user'),
(100, 'CME0038', 'user'),
(101, 'CME0039', 'user'),
(102, 'CME0040', 'user'),
(103, 'CME0041', 'user'),
(104, 'CME0042', 'user'),
(105, 'CME0043', 'user'),
(106, 'CME0044', 'user'),
(107, 'CME0045', 'user'),
(108, 'CME0046', 'user'),
(109, 'CME0047', 'user'),
(110, 'CME0048', 'user'),
(111, 'CME0049', 'user'),
(112, 'CME0050', 'user'),
(113, 'CME0051', 'user'),
(114, 'CME0052', 'user'),
(115, 'CME0053', 'user'),
(116, 'CME0054', 'user'),
(117, 'CME0055', 'user'),
(118, 'CME0056', 'user'),
(119, 'CME0057', 'user'),
(120, 'CME0058', 'user'),
(121, 'CME0059', 'user'),
(122, 'CME0060', 'user'),
(123, 'CME0061', 'user'),
(124, 'CME0062', 'user'),
(125, 'CME0063', 'user'),
(126, 'CME0064', 'user'),
(127, 'CME0065', 'user'),
(128, 'CME0066', 'user'),
(129, 'CME0067', 'user'),
(130, 'CME0068', 'user'),
(131, 'CME0069', 'user'),
(132, 'CME0070', 'user'),
(133, 'CME0071', 'user'),
(134, 'CME0072', 'user'),
(135, 'CME0073', 'user'),
(136, 'CME0074', 'user'),
(137, 'CME0075', 'user'),
(138, 'CME0076', 'user'),
(139, 'CME0077', 'user'),
(140, 'CME0078', 'user'),
(141, 'CME0079', 'user'),
(142, 'CME0080', 'user'),
(143, 'CME0081', 'user'),
(146, 'CME082', 'super_admin');

-- --------------------------------------------------------

--
-- Table structure for table `emp_login`
--

CREATE TABLE `emp_login` (
  `id` int(11) NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `emp_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emp_login`
--

INSERT INTO `emp_login` (`id`, `user_name`, `email`, `password`, `status`, `emp_id`) VALUES
(63, 'md', 'bashid@communikmarketing.com', '$2y$10$kVEw8em1DG5fW0goTPqYP.L/ViOfwNfdfeLk2V991OvKu0XVbmskC', 'active', 'CME0001'),
(64, 'usman.t', 'usman.t@communikmarketing.com', '$2y$10$xChhjqdIBP93/eg7PKoEJeh6Gy0zyRjo5Ef2g3B3u48NQroeKW8CG', 'active', 'CME0002'),
(65, 'arslan', 'arslan@communikmarketing.com', '$2y$10$o6N6RBvoepjfKzUGB8/yoOK69Qp/4GoVK7/6VVDno5uiPAl/l1Xia', 'active', 'CME0003'),
(66, 'sameer', 'sameer@communikmarketing.com', '$2y$10$AsAiymDabZE4xh9Y9ybCIO0pizvyoZcvJVAKtbsOD7ZzXMIYpDTsa', 'active', 'CME0004'),
(67, 'hr', 'hr@communikmarketing.com', '$2y$10$.y.7RPyeSwCCOStKxKWJt.0smt7rTATK5.vzcV.s.iOnGpFq3Kuui', 'active', 'CME0005'),
(68, 'rehmat', 'rehmat@communikmarketing.com', '$2y$10$DAc1h6FNqTG3cXzcl0qRVeJXeBH.J69yKNbanoD2iDIgw9M.mbloy', 'active', 'CME0006'),
(69, 'yeakub.ali', 'yeakub.ali@communikmarketing.com', '$2y$10$jMvf5bdapqyJXm4Pr//wk.WP8nXm50HdCU7e6pC.q8aFzo/4TMHTe', 'active', 'CME0007'),
(70, 'ali.abbas', 'ali.abbas@communikmarketing.com', '$2y$10$kKxTfqGieuWDE1Dhu7pefOxX2R8Ix1/dUsUm4sJhq/FL5j4HnCCO.', 'active', 'CME0008'),
(74, 'rohit', 'rohit@communikmarketing.com', '$2y$10$hjLy2Ap2YbbRoLl9aKzSHePSMfNsQcqF02hBUQW/ciq/CgBQN39am', 'active', 'CME0012'),
(75, 'moazzam', 'moazzam@communikmarketing.com', '$2y$10$pwklKSr6iJD4E/5Mb6s3R.ArrXXZy4gaKRgVs7ztV7iTgy9U3pQAa', 'active', 'CME0013'),
(76, 'mohamed.azaam', 'mohamed.azaam@communikmarketing.com', '$2y$10$a9WT7LInnoICtde..pAUQeBiABZ9rUm0aIGD.s2nSwdKhotOtw/lG', 'active', 'CME0014'),
(77, 'asma.b', 'asma.b@communikmarketing.com', '$2y$10$qNWJ55NyKyWd.jspIHEpr.mRXgp1TwIcyZLIhVD5AEDmMKnHAwOgO', 'active', 'CME0015'),
(78, 'cv', 'cv@communikmarketing.com', '$2y$10$wIRWoW5wbJu5jxVvMDiOGOJUV1YCO5JpVgp.hLwOSEMvj7kJsRxbe', 'active', 'CME0016'),
(79, 'rida', 'rida@communikmarketing.com', '$2y$10$ccCY3lGNJ2n2NamMeRulc.ck3TIJ6P9vsbxwcfS34ykLKxGuezrGK', 'active', 'CME0017'),
(80, 'nanda', 'nanda@communikmarketing.com', '$2y$10$CW7CqOLDNKAP9u.2zjplje315B.YKOF3HWPbeXDkkc8f/F9JpeE8C', 'active', 'CME0018'),
(81, 'jashan', 'jashan@communikmarketing.com', '$2y$10$AXAP1qeADKSKo4V/KVCjvuBXN5XJLPnEPoDr.y7i8NoeOz8xT8q4W', 'active', 'CME0019'),
(82, 'aman', 'aman@communikmarketing.com', '$2y$10$D.CO4ci95dUcwvhJXw//vu1M09R/VfhGoThVoMKC2/z8YLUIa2mqG', 'active', 'CME0020'),
(83, 'shumaila', 'shumaila@communikmarketing.com', '$2y$10$tlyMX95d6bXDBfwrr0p8bOptRJzYkDrlrdYym6gbO2JueoU4SXZwu', 'active', 'CME0021'),
(84, 'muheet', 'muheet@communikmarketing.com', '$2y$10$HnsWK/Zd/fU9S2T10f9IV.Xe7g2FFBvWrY2FtLOjezB7pbd8XbFJ6', 'active', 'CME0022'),
(85, 'nauman', 'nauman@communikmarketing.com', '$2y$10$/u/Kk4.3GWF2/.ZpmI9ZHunLf0mK/04bKMU4qy2xqzvN9IKTrbiiG', 'active', 'CME0023'),
(86, 'ariba', 'ariba@communikmarketing.com', '$2y$10$GqI3BP6PLsIPGD.lY4GQ.uAaCXoEC9y18chFYdlzaVryaDOR5Gm/a', 'active', 'CME0024'),
(87, 'aizaz', 'aizaz@communikmarketing.com', '$2y$10$mYQ0DoHlWalRATfClI/4V.pMHCQLVpyx8jFkVg5X57fiuO8A5q/IG', 'active', 'CME0025'),
(88, 'saira', 'saira@communikmarketing.com', '$2y$10$IMUnZmdzl29ffM0OJJiqM.HAsHd1dOaNMxY9CXSbiTulhQ.A27GYy', 'active', 'CME0026'),
(89, 'shega', 'shega@communikmarketing.com', '$2y$10$fH2WC5.M82o2zm6GWuJvxeQGTTie6qImr0p4MebyvsA5fy.5GN5E2', 'active', 'CME0027'),
(90, 'muzammil', 'muzammil@communikmarketing.com', '$2y$10$kD/xaVFEzoQ2qlYwtrtDq.D7NGBC.O5ymsAfby/bKRSOvDeRL0xSK', 'active', 'CME0028'),
(91, 'zaminul', 'zaminul@communikmarketing.com', '$2y$10$.PVhhmaohFcGhNH82lk7MeMvsw6JtNqpWUcWj/pO1XLlVHZVDuI5m', 'active', 'CME0029'),
(92, 'sandeep', 'sandeep@communikmarketing.com', '$2y$10$28bdpkLaMMTrHY06cTBIreLt4u.5nONSt0K8EwLUh8MH7ODIEQOKm', 'active', 'CME0030'),
(93, 'ezzat', 'ezzat@communikmarketing.com', '$2y$10$5.KglFy6CM3Hbv85Z8k7OOdA8gE/1NgtAY7CYpMNnRirXNF7co5Ee', 'active', 'CME0031'),
(94, 'mukter', 'mukter@communikmarketing.com', '$2y$10$y1ISfSRDmO9rlG5hh6OmPeVpv.7zDxyq7J/I19XHQlvK1AhALVrg.', 'active', 'CME0032'),
(95, '', '', '$2y$10$j0nF2PXykKIlnuSLvQqW/.Usp/5yYdIq0acTuyBm1ZKt.AB6HEBmW', 'active', 'CME0033'),
(96, 'masrooq', 'masrooq@communikmarketing.com', '$2y$10$RPigw4RPv1TV0nk6zhXPZO4YFtdmqMjKdbT1Rge1D8KmZN5dZMq82', 'active', 'CME0034'),
(97, 'saumya', 'saumya@communikmarketing.com', '$2y$10$dL63f1Teqhg/qw4CYAc/BOPKLBJv0AfFYOqMqnnn7y8qiIQov1HiW', 'active', 'CME0035'),
(98, 'rana', 'rana@communikmarketing.com', '$2y$10$m.0StZ8xHiIymkm4e0JaK.xi/1UtB8tgevtFp.PpLaN5YnxtQ6I.e', 'active', 'CME0036'),
(99, 'naimat', 'naimat@communikmarketing.com', '$2y$10$2gOE7frS8FVdWLo1pyKg.O86lRuvwhC0neMMMYUsnQU8THd3Yv.IK', 'active', 'CME0037'),
(100, 'harry', 'harry@communikmarketing.com', '$2y$10$5.W.RcCgVOJl8Rbz8kCceeXbiuTBBi2GvEWsyxgm6y1ceyUbk5zDm', 'active', 'CME0038'),
(101, 'zohaib', 'zohaib@communikmarketing.com', '$2y$10$YGSZ0Fr83VdyG140EnojpunysQsJ7itNyoxxizaQUMgc26CwMPHjG', 'active', 'CME0039'),
(102, 'kalaikamal', 'kalaikamal@communikmarketing.com', '$2y$10$5u3tlx6O8r5ViFKGKc7y7OmBQe/E/RQTRo9.eeTHq9T7ipm8uUmNG', 'active', 'CME0040'),
(103, 'mehul.bajaj', 'mehul.bajaj@communikmarketing.com', '$2y$10$LLMM2B5leskb9M8ZvPRdTe4YR3eEK1AOwFxXryMJ9yrnCDjJszip2', 'active', 'CME0041'),
(104, 'support', 'support@communikmarketing.com', '$2y$10$FcTO8Wyt8TcpZb.sTb13fO0yOBwm8HqCNlHdXarnO7qPjrT3d9egG', 'active', 'CME0042'),
(105, 'Shuaibsaifimail', 'Shuaibsaifimail@gmail.com', '$2y$10$eJpNQxTDXQWEon2IY0ZbTO3hyTeVjTLl1hZibZ6/yN1hoXexRWaMm', 'active', 'CME0043'),
(106, 'syedfida7908', 'syedfida7908@gmail.com', '$2y$10$Q3HVIFQCAiaJRacdG8NVue.MEtbGET4qjdiZ6plN38iUmvRR2OVxO', 'active', 'CME0044'),
(107, 'dhanushthara07', 'dhanushthara07@gmail.com', '$2y$10$tJo2d0KKeg1Um8O.Ha.tJO4eRSfzTg.Ancw/aMh9NdzU/OyZqjvAq', 'active', 'CME0045'),
(108, 'sithik2912', 'sithik2912@gail.com', '$2y$10$J2eJS2BZXG5xwCB1bomVUOHzL8Fgo4RqfElCfpNd.ml/6MxTng7aK', 'active', 'CME0046'),
(109, 'suhailattar7799', 'suhailattar7799@gmail.com', '$2y$10$PmyqfvJXIIgxA893sN6A.e1FSlXCn9qLX.YW9vjo2s/0qcQXHLZji', 'active', 'CME0047'),
(110, 'pksoni707070', 'pksoni707070@gmail.com', '$2y$10$wet9kg7bNd8QdP3AMNKdyeMXs12vR9NFRjGVfM7dLXKR6voifVhFe', 'active', 'CME0048'),
(111, 'mina.nessiem15', 'mina.nessiem15@gmail.com', '$2y$10$xX3O3O/AM4kYS/./gKtMRObrH9DsmawPIcnwh6QfWvkuwU.UGoEJi', 'active', 'CME0049'),
(112, 'lakshmipathi98978', 'lakshmipathi98978@gmail.com', '$2y$10$SPjtl6f8D1nQlwnS8cpCmOan8Q6R77VIa31KiML0WfhaYP4Ce2whG', 'active', 'CME0050'),
(113, 'dksonu75', 'dksonu75@gmail.com', '$2y$10$LBEklaTMwWd6rDN8ckfW1e3s2ETOJfxaaRQ.ilZ9./vpusk1kWFL.', 'active', 'CME0051'),
(114, 'wilkinston.fernandes', 'wilkinston.fernandes@gmail.com', '$2y$10$lTXyj4gHb527KYTb6fcc5OwT/i9ch6o0tX0u6HKeY5sBr76Tr8YHS', 'active', 'CME0052'),
(115, 'heam23', 'heam23@live.com', '$2y$10$gUKdemUvh8tlmgaZD0nZ6e8GIwmJCYBmBo4SoQlKWQHhbWH2iOg.2', 'active', 'CME0053'),
(116, 'Chimrannaseem2', 'Chimrannaseem2@gmail.com', '$2y$10$g5jETUHCDIBTkDBgRWHWKOK3i35Q9ynADwd92t0nduP4/A1dbodGu', 'active', 'CME0054'),
(117, 'dilan', 'dilan@communikmarketing.com', '$2y$10$Y.moVlGKkdq7tmgVi/z/5OcmPC2H26M8sG0zyMASc8fxbyWXiaGg6', 'active', 'CME0055'),
(118, 'aishasalman460', 'aishasalman460@gmail.com', '$2y$10$AWwbGxDxNLp4cGkpBzeWCuCcEVTLc1YM5Q.pxpxjtZ/Tphkjztg6S', 'active', 'CME0056'),
(119, 'azmath707', 'azmath707@gmail.com', '$2y$10$OTiqeO61s7wf32qvmnnmF.2RJixh4A8EmY2BrU.vGI6tehQ/gA6gO', 'active', 'CME0057'),
(120, 'AFRAZSHAIKH2122', 'AFRAZSHAIKH2122@GMAIL.COM', '$2y$10$FaaDIcTnlogR6I.VJbCYHe1TqiPt3AdW7JFGoOFtJtLRF7gItwueW', 'active', 'CME0058'),
(121, 'Vikram', 'Vikram@communikmarketing.com', '$2y$10$DVBYuaoWU.Dx4M9F1hDjueOttTGHiAiluvgoZGaWK1YAknUgEIS2K', 'active', 'CME0059'),
(122, 'jayroy1005', 'jayroy1005@gmail.com', '$2y$10$i8OmiOgd41x/htgoRuzhdekpvTP/RrP.qd5rTMWjB.FmAt4EFZpGO', 'active', 'CME0060'),
(123, 'kensecatin', 'kensecatin@gmail.com', '$2y$10$eY7p8fBF.WWZBOkCbILceOCL9PZRV6S1HmHu/d7t/VAL2aX3j8IhK', 'active', 'CME0061'),
(124, 'Josephlalnunthanga042', 'Josephlalnunthanga042@gmail.com', '$2y$10$ey4Bz2EuHdUcL3a5FZE8buN8x480xPH3Gsdv7oEbhEqSOubg9tOEy', 'active', 'CME0062'),
(125, 'anilkourani89', 'anilkourani89@gmail.com', '$2y$10$eFZheyEukz4dGv8tOs8CHuE2.jZTqOKAPwYHuQk6KslNGEmQCb2Ei', 'active', 'CME0063'),
(126, 'Dildilsha44', 'Dildilsha44@Gmail.com', '$2y$10$FEpAhJ3/.HRs0hhCdIBtteWFbzW2CVL7ZMSFr/uZASWCDQ36pZcjm', 'active', 'CME0064'),
(127, 'balda.narsimhulu2011', 'balda.narsimhulu2011@gmail.com', '$2y$10$9anjyejCX1s.baLou1jLEOGg.lEONvd80U5EYk9kfnJojJSGXHW8W', 'active', 'CME0065'),
(128, 'sobitpaul7624', 'sobitpaul7624@gmail.com', '$2y$10$X/exOkq5x9jy7cmA3UgEFObllz.Ir0CicBsSVIHiRcJuFLjtzIX7e', 'active', 'CME0066'),
(129, 'cmary.christina', 'cmary.christina@gmail.com', '$2y$10$lrfT.XvCr92hAL.ZOOWH.ezWS.PFKiTJt7bWvXUGVlKK8ndQzNxpS', 'active', 'CME0067'),
(130, 'aminamuhammad089', 'aminamuhammad089@gmail.com', '$2y$10$63q7vQBT6hF5hgmSRlGWOOmsaB.YIFNvtsO20ALGuvj6mNxfasVcS', 'active', 'CME0068'),
(131, 'noumanshahid69', 'noumanshahid69@gmail.com', '$2y$10$/mtWHoOMYMuw0/6yVOJRuOVkK50Q0MHS8o1g2sPRq.YIkIv6mqDEO', 'active', 'CME0069'),
(132, 'azeemalone83', 'azeemalone83@gmail.com', '$2y$10$rr2CVjnumTZHHVR2.Bz5n.gS0RWrhbUKduutjqAGO4udBu6YR/8au', 'active', 'CME0070'),
(133, 'nimranovel', 'nimranovel@gmail.com', '$2y$10$Hpy2rGqSKxmHDXyBvW8hpO9pM9u9qYNsrjZqTnHdUH0Zk9zn08EvG', 'active', 'CME0071'),
(134, 'navoda.piya8899', 'navoda.piya8899@icloud.com', '$2y$10$kyj8E6.c/e3jj.P/VET0X.pBHXnYqcQCi1XTaOedHeEVWmOqwzlR.', 'active', 'CME0072'),
(135, 'm.n4sser', 'm.n4sser@gmail.com', '$2y$10$usmWbvIGBwCOTgNQLBA3GOxmqTjiZ3HP3J1VSQJApdKANLK6YjQOe', 'active', 'CME0073'),
(136, 'pragati31102000', 'pragati31102000@gmail.com', '$2y$10$BvE8thRvvFzaWbm8QSJ0nuEetx/qJs8T5XN1Q0nktdgfIsIUIGFjO', 'active', 'CME0074'),
(137, 'abrarahmmedcm269', 'abrarahmmedcm269@gmail.com', '$2y$10$pJGsrUdj2NrG0zWGsGxhsOgBUUlrcDkRbQVAayUcmN7frUhZSG8yW', 'active', 'CME0075'),
(138, 'shaikraheem849', 'shaikraheem849@gmail.com', '$2y$10$NmrgWo4SxfwzjgnPL0BKbOSQs8FzfhLGA8clcGhRh7KnjStx6s8D6', 'active', 'CME0076'),
(139, 'nameeratabassumhrn', 'nameeratabassumhrn@gmail.com', '$2y$10$ejOmJFaa9g2xmIzVffvzheJEtU2xbNSbU1sfn0P9ZuMeAwPyyJc6K', 'active', 'CME0077'),
(140, 'haroonaly78q', 'haroonaly78q@gmail.com', '$2y$10$gVp0pPGnI9b.Y9/eqtPc5u9pbVKRYXCSffnwzRoV1u7bfm2seOJEC', 'active', 'CME0078'),
(141, 'saket.gambhir14', 'saket.gambhir14@gmail.com', '$2y$10$JKTo6E3nRq8/nkmCW9HRsekZEL0yjnieK7ZjuETVHoK.RPdZfzJOq', 'active', 'CME0079'),
(142, 'ibrarashraf05', 'ibrarashraf05@gmail.com', '$2y$10$/Niv8yJKZtj.f/1qpCVmUelqm4MDKtizPQr7hZVrg940n3e8ZLcjK', 'active', 'CME0080'),
(143, 'khanayan.3343', 'khanayan.3343@gmail.com', '$2y$10$VJ5VRUybvbt8HtTTGImiqOYauPfva93DNWRwN2Vk5PWb7IJRlhuEe', 'active', 'CME0081'),
(146, 'superadmin', 'superadmin@communikmarketing.com', '$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW', 'active', 'CME082');

--
-- Triggers `emp_login`
--
DELIMITER $$
CREATE TRIGGER `keep_active_status` BEFORE UPDATE ON `emp_login` FOR EACH ROW BEGIN
    IF NEW.status <> 'active' THEN
        SET NEW.status = 'active';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `emp_passport`
--

CREATE TABLE `emp_passport` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `passport_number` varchar(50) NOT NULL,
  `passport_type` varchar(50) NOT NULL,
  `pissue_date` date DEFAULT NULL,
  `pvalidity` date DEFAULT NULL,
  `country` varchar(80) NOT NULL,
  `pissue_place` varchar(50) NOT NULL,
  `pissue_city` varchar(50) NOT NULL,
  `passport_add1` varchar(200) NOT NULL,
  `passport_add2` varchar(200) NOT NULL,
  `passport_held` varchar(50) NOT NULL,
  `Remarks` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emp_passport`
--

INSERT INTO `emp_passport` (`id`, `emp_id`, `passport_number`, `passport_type`, `pissue_date`, `pvalidity`, `country`, `pissue_place`, `pissue_city`, `passport_add1`, `passport_add2`, `passport_held`, `Remarks`) VALUES
(19, 0, 'a8745d74d', 'Diplomatic', '2015-10-10', '2030-11-12', 'India', 'pune', 'pune', 'puyne', 'pune', 'test', ''),
(20, 0, 'a8745d74d', 'Diplomatic', '2015-10-10', '2030-11-12', 'India', 'pune', 'pune', 'puyne', 'pune', 'test', ''),
(21, 0, 'a8745d74d', 'Diplomatic', '2015-10-10', '2030-11-12', 'India', 'pune', 'pune', 'puyne', 'pune', 'test', ''),
(22, 0, '7777777', 'Diplomatic', '2025-01-29', '2025-02-13', 'Afghanistan', 'af', 'af', 'af', 'af', 'af', '');

-- --------------------------------------------------------

--
-- Table structure for table `esign_comments`
--

CREATE TABLE `esign_comments` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `esign_documents`
--

CREATE TABLE `esign_documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('pdf','jpg','jpeg','png') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','pending','in_progress','completed','rejected') NOT NULL DEFAULT 'draft',
  `current_level` int(11) NOT NULL DEFAULT 1,
  `total_levels` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `document_type` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esign_documents`
--

INSERT INTO `esign_documents` (`id`, `title`, `description`, `file_path`, `file_type`, `created_by`, `created_at`, `status`, `current_level`, `total_levels`, `is_deleted`, `document_type`, `expiry_date`) VALUES
(2, 'testtt', 'asdfasdf', '../uploads/documents/684dd5c3b9bfd_1749931459.pdf', 'pdf', 4, '2025-06-14 20:04:19', 'pending', 1, 2, 0, 'policy', '2025-06-21');

-- --------------------------------------------------------

--
-- Table structure for table `esign_notifications`
--

CREATE TABLE `esign_notifications` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `esign_settings`
--

CREATE TABLE `esign_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `signature_type` varchar(20) NOT NULL DEFAULT 'draw',
  `notification_email` tinyint(1) NOT NULL DEFAULT 1,
  `notification_sms` tinyint(1) NOT NULL DEFAULT 0,
  `default_expiry` int(11) NOT NULL DEFAULT 30,
  `auto_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_days` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esign_settings`
--

INSERT INTO `esign_settings` (`id`, `user_id`, `signature_type`, `notification_email`, `notification_sms`, `default_expiry`, `auto_reminder`, `reminder_days`, `created_at`, `updated_at`) VALUES
(1, 4, 'upload', 1, 0, 30, 1, 3, '2025-06-14 19:43:49', '2025-06-14 19:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `esign_templates`
--

CREATE TABLE `esign_templates` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esign_templates`
--

INSERT INTO `esign_templates` (`id`, `title`, `description`, `document_type`, `file_path`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'fsdfasd', 'asfasdfasf', 'other', 'template_684dcaa2bcccd6.61546279.pdf', 4, '2025-06-14 19:16:50', '2025-06-14 19:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `esign_workflow`
--

CREATE TABLE `esign_workflow` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `signed_at` timestamp NULL DEFAULT NULL,
  `signature_type` enum('upload','draw','type') DEFAULT NULL,
  `signature_data` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approver_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esign_workflow`
--

INSERT INTO `esign_workflow` (`id`, `document_id`, `level`, `approver_id`, `role_id`, `status`, `signed_at`, `signature_type`, `signature_data`, `comments`, `created_at`, `updated_at`, `approver_type`) VALUES
(1, 2, 0, 2, 0, 'pending', NULL, NULL, NULL, NULL, '2025-06-14 20:04:19', '2025-06-14 20:04:19', 'head'),
(2, 2, 1, 78, 0, 'pending', NULL, NULL, NULL, NULL, '2025-06-14 20:04:19', '2025-06-14 20:04:19', 'emp'),
(3, 2, 2, 4, 0, '', '2025-06-15 06:46:57', 'draw', 'uploads/signatures/signature_2_4_1749970017.png', '', '2025-06-14 20:04:19', '2025-06-15 06:46:57', 'head');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `starting_time` time NOT NULL,
  `ending_time` time NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_pt`
--

CREATE TABLE `event_pt` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_starting_time` time DEFAULT NULL,
  `event_ending_time` time DEFAULT NULL,
  `venue_address` text DEFAULT NULL,
  `title_of_participation` varchar(255) DEFAULT NULL,
  `additional_information` text DEFAULT NULL,
  `admin_remark` varchar(100) DEFAULT 'Applied'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_about_us`
--

CREATE TABLE `guest_about_us` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_about_us`
--

INSERT INTO `guest_about_us` (`id`, `title`, `para`, `img`, `icon`) VALUES
(1, 'Our Mission', 'At Employeehub, we\'re all about making employee management a breeze. Our EMS Hub is designed to streamline tasks, boost productivity, and keep everyone on track.', '65fc45a1aab5b_about-mission.jpg', 'ion-ios-speedometer-outline'),
(2, 'Our Plan', 'From leave applications to project management, we\'ve got it all covered. Say goodbye to the tedious process and hello to seamless efficiency!', '65fc45f7c4899_about-plan.jpg', 'ion-ios-list-outline'),
(3, 'Our Vision', 'We on a mission to revolutionize the way businesses manage the employees. Our EMS Hub is the ultimate solution for efficient employee management.', '65fc462290374_about-vision.jpg', 'ion-ios-eye-outline');

-- --------------------------------------------------------

--
-- Table structure for table `guest_benefits`
--

CREATE TABLE `guest_benefits` (
  `id` int(11) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_benefits`
--

INSERT INTO `guest_benefits` (`id`, `icon`, `title`, `para`) VALUES
(1, 'ion-ios-bookmarks-outline', 'Future of Employee Management ', 'Explore the future of employee management and the role of technology in shaping the workplace of tomorrow. '),
(2, 'ion-ios-stopwatch-outline', 'Future of Employee Management', 'Explore the future of employee management and the role of technology in shaping the workplace of tomorrow.'),
(3, 'ion-ios-heart-outline', 'ion-ios-heart-outline', 'Workspace\', \'With a focus on productivity, simplicity, and innovation, we\'re here to take your workforce management to the next level.');

-- --------------------------------------------------------

--
-- Table structure for table `guest_contact`
--

CREATE TABLE `guest_contact` (
  `id` int(11) NOT NULL,
  `icon` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `para` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_contact`
--

INSERT INTO `guest_contact` (`id`, `icon`, `title`, `para`) VALUES
(1, 'ion-ios-location-outline', 'Address', 'Sec 23A,Gurugram,Haryana-122017, India'),
(2, 'ion-ios-telephone-outline', 'Phone Number', '+91 70200 78847'),
(3, 'ion-ios-email-outline', 'Email', 'info@san-solutions.in');

-- --------------------------------------------------------

--
-- Table structure for table `guest_facts`
--

CREATE TABLE `guest_facts` (
  `id` int(11) NOT NULL,
  `number` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_facts`
--

INSERT INTO `guest_facts` (`id`, `number`, `title`) VALUES
(1, '274', 'clients'),
(2, '421', 'projects'),
(3, '1,364', 'Hours Of Support '),
(4, '18', 'Hard Workers ');

-- --------------------------------------------------------

--
-- Table structure for table `guest_header`
--

CREATE TABLE `guest_header` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_header`
--

INSERT INTO `guest_header` (`id`, `name`, `title`, `link`) VALUES
(1, 'index.php', 'HOME', 'emps/index.php'),
(6, 'contact.php', 'CONTACT', 'contact.php'),
(7, 'login.php', 'LOGIN', 'login.php');

-- --------------------------------------------------------

--
-- Table structure for table `guest_our_clients`
--

CREATE TABLE `guest_our_clients` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_our_clients`
--

INSERT INTO `guest_our_clients` (`id`, `title`, `img`) VALUES
(1, 'strider', 'client-1.png'),
(2, 'runtastic', 'client-2.png'),
(3, 'Editshare', 'client-3.png'),
(4, 'InFocus', 'client-4.png'),
(5, 'Gategroup', 'client-5.png'),
(6, 'cadent', 'client-6.png'),
(7, 'ceph', 'client-7.png'),
(8, 'alitalia', 'client-8.png');

-- --------------------------------------------------------

--
-- Table structure for table `guest_our_portfolio`
--

CREATE TABLE `guest_our_portfolio` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_our_portfolio`
--

INSERT INTO `guest_our_portfolio` (`id`, `title`, `para`, `img`, `category`) VALUES
(1, 'Android Development', 'Java and kotlin', 'app1.jpg', 'app'),
(2, 'Front-End', 'HTML & CSS', 'web3.jpg', 'web'),
(3, 'Flutter Framework', 'dart', 'app2.jpg', 'app'),
(4, 'Database', 'Oracle', 'card2.jpg', 'db'),
(5, 'Framework', 'Laravel & Codegniter', 'web2.jpg', 'web'),
(6, 'Diverse App Languages', 'Swift / Objective-C', 'app3.jpg', 'app'),
(7, 'Database', 'SQL', 'card1.jpg', 'db'),
(8, 'Database', 'MongoDB', 'card3.jpg', 'db'),
(9, 'Back-End', 'Python, Java', 'web1.jpg', 'web');

-- --------------------------------------------------------

--
-- Table structure for table `guest_our_skills`
--

CREATE TABLE `guest_our_skills` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `progress` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_our_skills`
--

INSERT INTO `guest_our_skills` (`id`, `title`, `progress`, `color`) VALUES
(1, 'php', '100', 'success'),
(2, 'javascript', '90', 'info'),
(3, 'java', '75', 'warning'),
(4, 'ruby', '55', 'danger');

-- --------------------------------------------------------

--
-- Table structure for table `guest_services`
--

CREATE TABLE `guest_services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_services`
--

INSERT INTO `guest_services` (`id`, `title`, `para`, `icon`) VALUES
(1, 'Request leaves', 'Enable the employees to conveniently request leaves online. It streamlines the leave application process, making it efficient and accessible. Employees can specify the type of leave, duration, and any additional notes for approval.', 'ion-ios-analytics-outline'),
(2, 'Business Tour Request', 'Employees can submit travel requests for work-related trips. This streamlines the approval process for business tours, allowing staff to provide necessary details such as destination, purpose, and expected duration.', 'ion-ios-bookmarks-outline'),
(3, 'Salary Information', 'Allows employees to access detailed information about their compensation. They can view their base salary, bonus details, and the total amount, providing transparency and clarity regarding their financial remuneration.', 'ion-ios-paper-outline'),
(4, 'Task Management', 'Employees can efficiently manage their tasks and projects through this feature. It provides a centralized platform where staff can view their assigned tasks, track progress, and stay organized with their work responsibilities.', 'ion-ios-speedometer-outline'),
(5, 'Employee Leaderboard', 'Fostering a sense of healthy competition, the Employee Leaderboard allows employees to view the names and points of their colleagues, encouraging friendly competition, recognition, and motivates employees to excel in their roles.', 'ion-ios-barcode-outline'),
(6, 'View Project Details', 'Employees can access detailed information about their assigned projects, including project, descriptions, and other details. Administrators have the ability to assign the project information as needed for employees.', 'ion-ios-people-outline');

-- --------------------------------------------------------

--
-- Table structure for table `guest_slider_images`
--

CREATE TABLE `guest_slider_images` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_slider_images`
--

INSERT INTO `guest_slider_images` (`id`, `title`, `para`, `img`) VALUES
(1, 'we are professional', 'Employeeshub is the ultimate solution for efficient employee management.', '1.jpg'),
(2, 'Team Work', 'At Employeeshub, we\'re all about making employee management a breeze. Our Employeeshub is designed to streamline tasks, boost productivity, and keep everyone on track.', '2.jpg'),
(3, 'Employees leave', 'From leave applications to project management, we have all covered. Say goodbye to tedious admin work and hello to seamless efficiency!', '3.jpg'),
(4, 'Let\'s work together', 'Join us on this journey to revolutionize the way you manage your workforce.', '4.jpg'),
(5, 'Employees Hub', 'EMS Hub has transformed the way to manage the employees. It\'s a game-changer!', '5.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `guest_team`
--

CREATE TABLE `guest_team` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `position` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_team`
--

INSERT INTO `guest_team` (`id`, `name`, `position`, `img`) VALUES
(1, 'Gopal Singh', 'Chief Executive Officer', 'team-1.jpg'),
(2, 'Sanjhi Suryavanshi', 'Product Manager', 'team-2.jpg'),
(3, 'Moni Singh', 'Entrepreneur', 'team-3.jpg'),
(4, 'Suryavanshi', 'Accountant', 'team-4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `guest_testimonial`
--

CREATE TABLE `guest_testimonial` (
  `id` int(11) NOT NULL,
  `person` varchar(200) NOT NULL,
  `position` varchar(200) NOT NULL,
  `para` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_testimonial`
--

INSERT INTO `guest_testimonial` (`id`, `person`, `position`, `para`, `img`) VALUES
(1, 'Saul Goodman', 'Ceo &amp; Founder', 'EMS Hub is a must-have for any organization looking to streamline employee management.', 'testimonial-1.jpg'),
(2, 'Sara Wilsson', 'Designer', 'The EMS Hub has made our HR tasks a breeze. It\'s like having a personal assistant for every employee.', 'testimonial-2.jpg'),
(3, 'Jena Karlis', 'Store Owner', 'EMS Hub is the ultimate solution for efficient employee management.', 'testimonial-3.jpg'),
(4, 'Matt Brandon', 'Freelancer', 'EMS Hub has transformed the way we manage our employees.', 'testimonial-4.jpg'),
(5, 'John Larson', 'Entrepreneur', 'I never knew employee management could be this fun and efficient.', 'testimonial-5.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `labor_cards`
--

CREATE TABLE `labor_cards` (
  `id` int(11) NOT NULL,
  `eid` varchar(50) DEFAULT NULL,
  `labor_card_no` varchar(50) NOT NULL,
  `labor_card_start_date` date NOT NULL,
  `labor_card_end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(20) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `type_of_leave` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `current_month_ldays` int(11) DEFAULT 0,
  `total_days` int(10) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(10) NOT NULL,
  `hod_id` int(11) NOT NULL,
  `hod_name` varchar(255) NOT NULL,
  `doctor_cert` varchar(255) DEFAULT NULL,
  `hr_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `hr_remarks` text DEFAULT NULL,
  `hr_action_date` datetime DEFAULT NULL,
  `hr_by` varchar(100) DEFAULT NULL,
  `hod_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `hod_remarks` text DEFAULT NULL,
  `hod_action_date` datetime DEFAULT NULL,
  `hod_by` varchar(100) DEFAULT NULL,
  `current_level` int(11) DEFAULT 1,
  `max_level` int(11) DEFAULT 3,
  `current_approver_role` varchar(50) DEFAULT NULL,
  `next_approver_role` varchar(50) DEFAULT NULL,
  `recommender_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `recommender_remarks` text DEFAULT NULL,
  `recommender_action_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `emp_id`, `user_name`, `reason`, `type_of_leave`, `start_date`, `end_date`, `current_month_ldays`, `total_days`, `applied_at`, `status`, `hod_id`, `hod_name`, `doctor_cert`, `hr_status`, `hr_remarks`, `hr_action_date`, `hr_by`, `hod_status`, `hod_remarks`, `hod_action_date`, `hod_by`, `current_level`, `max_level`, `current_approver_role`, `next_approver_role`, `recommender_id`, `approver_id`, `recommender_remarks`, `recommender_action_date`) VALUES
(9, '4', 'Imran Khan', 'test', 'annual', '2025-03-01', '2025-03-03', 0, 8, '2025-03-01 11:18:32', 'Rejected', 3, 'Gopal Singh', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '4', 'Imran Khan', 'test', 'annual', '2025-03-03', '2025-03-04', 2, 5, '2025-03-03 14:22:47', 'Approved', 3, 'Gopal Singh', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '7', 'arun kumar', 'test', 'sick', '2025-03-13', '2025-03-14', 2, 5, '2025-03-12 17:13:57', 'Approved', 3, 'Gopal Singh', NULL, 'approved', 'Auto-approved by Admin bypass', '2025-03-13 15:34:18', 'admin', 'approved', 'Auto-approved by Admin bypass', '2025-03-13 15:34:18', 'admin', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '7', 'arun kumar', 'PAt', 'Paternity Leave', '2025-03-14', '2025-03-15', 2, 3, '2025-03-13 05:09:45', 'Approved', 3, 'Gopal Singh', '7_1741842585.png', 'approved', 'Auto-approved by Admin bypass', '2025-03-13 15:31:59', 'admin', 'approved', 'Auto-approved by Admin bypass', '2025-03-13 15:31:59', 'admin', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '7', 'arun kumar', 'restion', 'Paternity Leave', '2025-03-18', '2025-03-18', 1, 9, '2025-03-17 07:57:22', 'Rejected', 3, 'Gopal Singh', '7_1742198242.pdf', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'CME0042', 'Anum  Riaz ', 'rtestasf', 'Hajj and Umrah Leave', '2025-06-12', '2025-06-13', 2, 7, '2025-06-11 08:59:59', 'Approved', 0, '', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, 78, 109, 'gdfg', '2025-06-13 22:32:22'),
(15, 'CME0042', 'Anum  Riaz ', 'reatasd', 'Hajj and Umrah Leave', '2025-06-16', '2025-06-18', 3, 10, '2025-06-11 11:30:05', 'Approved', 0, '', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, 78, 109, 'rec', '2025-06-13 22:32:09'),
(16, '104', 'Anum  Riaz ', 'sdsd', 'Hajj and Umrah Leave', '2025-07-02', '2025-07-04', 0, 3, '2025-06-11 13:09:54', 'Approved', 6, 'Muhammad  Arslan', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '104', 'Anum  Riaz ', 'sdsd', 'Hajj and Umrah Leave', '2025-07-02', '2025-07-04', 0, 6, '2025-06-11 13:09:54', 'Approved', 6, 'Muhammad  Arslan', NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `leaves`
--
DELIMITER $$
CREATE TRIGGER `after_leave_status_update` AFTER UPDATE ON `leaves` FOR EACH ROW BEGIN
    DECLARE current_balance DECIMAL(10,2);
    
    IF NEW.status = 'Approved' AND OLD.status != 'Approved' THEN
        -- Get current balance or max_days if no balance exists
        SELECT COALESCE(
            (SELECT balance 
             FROM employee_leave_balance 
             WHERE emp_id = NEW.emp_id 
             AND leave_type = NEW.type_of_leave 
             AND year = YEAR(CURRENT_DATE)),
            (SELECT max_days 
             FROM leave_policies 
             WHERE leave_type = NEW.type_of_leave)
        ) INTO current_balance;
        
        -- Insert or update the balance
        INSERT INTO employee_leave_balance (emp_id, leave_type, year, balance)
        VALUES (NEW.emp_id, NEW.type_of_leave, YEAR(CURRENT_DATE), 
                current_balance - NEW.total_days)
        ON DUPLICATE KEY UPDATE
            balance = balance - NEW.total_days;
            
    ELSEIF (NEW.status = 'Cancelled' OR NEW.status = 'Surrendered') 
           AND OLD.status = 'Approved' THEN
        -- Restore the balance
        UPDATE employee_leave_balance
        SET balance = balance + OLD.total_days
        WHERE emp_id = OLD.emp_id 
        AND leave_type = OLD.type_of_leave
        AND year = YEAR(CURRENT_DATE);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_leave_days_on_delete` AFTER DELETE ON `leaves` FOR EACH ROW BEGIN
    DECLARE yearly_leave_sum INT;

    -- Calculate cumulative leave days for the year excluding the deleted row
    SELECT IFNULL(SUM(total_days), 0) INTO yearly_leave_sum
    FROM leaves
    WHERE emp_id = OLD.emp_id
    AND YEAR(start_date) = YEAR(CURDATE());

    -- Update the total days in the remaining rows for that employee
    UPDATE leaves
    SET total_days = yearly_leave_sum
    WHERE emp_id = OLD.emp_id
    AND YEAR(start_date) = YEAR(CURDATE());

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_leave_days_on_update` BEFORE UPDATE ON `leaves` FOR EACH ROW BEGIN
    DECLARE start_month DATE;
    DECLARE end_month DATE;
    DECLARE current_month_start DATE;
    DECLARE current_month_end DATE;
    DECLARE yearly_leave_sum INT;

  
    SET current_month_start = DATE_FORMAT(CURDATE(), '%Y-%m-01');
    SET current_month_end = LAST_DAY(CURDATE());

   
    SET start_month = GREATEST(NEW.start_date, current_month_start);
    SET end_month = LEAST(NEW.end_date, current_month_end);

    IF start_month <= end_month THEN
        SET NEW.current_month_ldays = DATEDIFF(end_month, start_month) + 1;
    ELSE
        SET NEW.current_month_ldays = 0;
    END IF;

 
    SELECT IFNULL(SUM(total_days), 0) INTO yearly_leave_sum
    FROM leaves
    WHERE emp_id = NEW.emp_id
    AND id != NEW.id
    AND YEAR(start_date) = YEAR(CURDATE());

 
    SET NEW.total_days = yearly_leave_sum + DATEDIFF(NEW.end_date, NEW.start_date) + 1;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `leave_approval_flow`
--

CREATE TABLE `leave_approval_flow` (
  `id` int(11) NOT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_role` varchar(50) DEFAULT NULL,
  `approver_role` varchar(50) DEFAULT NULL,
  `approval_level` int(11) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_approval_history`
--

CREATE TABLE `leave_approval_history` (
  `id` int(11) NOT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `action_by_id` varchar(20) DEFAULT NULL,
  `action_by_role` varchar(50) DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_hierarchy`
--

CREATE TABLE `leave_hierarchy` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `recommender_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `type` enum('recommender','approver') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_hierarchy`
--

INSERT INTO `leave_hierarchy` (`id`, `employee_id`, `recommender_id`, `approver_id`, `department_id`, `type`) VALUES
(1, 104, 78, NULL, 2, 'recommender'),
(2, 104, 67, NULL, 2, 'recommender'),
(3, 104, NULL, 109, 2, 'approver');

-- --------------------------------------------------------

--
-- Table structure for table `leave_notifications`
--

CREATE TABLE `leave_notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_policies`
--

CREATE TABLE `leave_policies` (
  `id` int(11) NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `max_days` int(11) DEFAULT NULL,
  `min_service_months` int(11) DEFAULT NULL,
  `requires_certificate` tinyint(1) DEFAULT NULL,
  `gender_restriction` enum('all','male','female') DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT NULL,
  `monthly_accrual` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_policies`
--

INSERT INTO `leave_policies` (`id`, `leave_type`, `max_days`, `min_service_months`, `requires_certificate`, `gender_restriction`, `is_paid`, `monthly_accrual`) VALUES
(1, 'Annual Leave', 30, 12, 0, 'all', 1, 2.50),
(2, 'Casual Leave', 4, 6, 0, 'all', 1, 2.00),
(3, 'Sick Leave', 15, 3, 1, 'all', 1, 1.25),
(4, 'Sick Leave Half Pay', 30, 3, 1, 'all', 0, 0.00),
(5, 'Maternity Leave', 60, 6, 1, 'female', 1, 0.00),
(6, 'Paternity Leave', 5, 0, 1, 'male', 1, 0.00),
(7, 'Hajj and Umrah Leave', 30, 0, 0, 'all', 0, 0.00),
(8, 'Unpaid', 30, 0, 0, 'all', 0, 0.00),
(33, '1', 30, 0, 0, NULL, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `leave_status_types`
--

CREATE TABLE `leave_status_types` (
  `id` int(11) NOT NULL,
  `status_code` varchar(20) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_status_types`
--

INSERT INTO `leave_status_types` (`id`, `status_code`, `status_name`, `description`, `created_at`) VALUES
(1, 'PENDING', 'Pending', 'Leave request is waiting for approval', '2025-06-10 07:23:10'),
(2, 'APPROVED', 'Approved', 'Leave request has been approved', '2025-06-10 07:23:10'),
(3, 'REJECTED', 'Rejected', 'Leave request has been rejected', '2025-06-10 07:23:10'),
(4, 'CANCELLED', 'Cancelled', 'Leave request has been cancelled by employee', '2025-06-10 07:23:10'),
(5, 'IN_PROGRESS', 'In Progress', 'Leave request is being reviewed', '2025-06-10 07:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `leave_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'Regular annual leave entitlement', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(2, 'Sick Leave', 'Leave for medical reasons', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(3, 'Casual Leave', 'Short-term leave for personal matters', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(4, 'Maternity Leave', 'Leave for female employees during pregnancy and childbirth', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(5, 'Paternity Leave', 'Leave for male employees when their spouse gives birth', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(6, 'Bereavement Leave', 'Leave due to death of immediate family member', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(7, 'Unpaid Leave', 'Leave without pay for extended absences', '2025-06-10 12:37:59', '2025-06-10 12:37:59'),
(8, 'Annual Leave', 'Regular annual leave entitlement', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(9, 'Sick Leave', 'Leave for medical reasons', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(10, 'Casual Leave', 'Short-term leave for personal matters', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(11, 'Maternity Leave', 'Leave for female employees during pregnancy and childbirth', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(12, 'Paternity Leave', 'Leave for male employees when their spouse gives birth', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(13, 'Bereavement Leave', 'Leave due to death of immediate family member', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(14, 'Unpaid Leave', 'Leave without pay for extended absences', '2025-06-10 12:43:28', '2025-06-10 12:43:28'),
(15, 'Annual Leave', 'Regular annual leave entitlement', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(16, 'Sick Leave', 'Leave for medical reasons', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(17, 'Casual Leave', 'Short-term leave for personal matters', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(18, 'Maternity Leave', 'Leave for female employees during pregnancy and childbirth', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(19, 'Paternity Leave', 'Leave for male employees when their spouse gives birth', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(20, 'Bereavement Leave', 'Leave due to death of immediate family member', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(21, 'Unpaid Leave', 'Leave without pay for extended absences', '2025-06-10 12:53:11', '2025-06-10 12:53:11'),
(22, 'Annual Leave', 'Regular annual leave entitlement', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(23, 'Sick Leave', 'Leave for medical reasons', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(24, 'Casual Leave', 'Short-term leave for personal matters', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(25, 'Maternity Leave', 'Leave for female employees during pregnancy and childbirth', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(26, 'Paternity Leave', 'Leave for male employees when their spouse gives birth', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(27, 'Bereavement Leave', 'Leave due to death of immediate family member', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(28, 'Unpaid Leave', 'Leave without pay for extended absences', '2025-06-10 12:55:56', '2025-06-10 12:55:56'),
(29, 'Annual Leave', 'Regular annual leave entitlement', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(30, 'Sick Leave', 'Leave for medical reasons', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(31, 'Casual Leave', 'Short-term leave for personal matters', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(32, 'Maternity Leave', 'Leave for female employees during pregnancy and childbirth', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(33, 'Paternity Leave', 'Leave for male employees when their spouse gives birth', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(34, 'Bereavement Leave', 'Leave due to death of immediate family member', '2025-06-10 13:00:20', '2025-06-10 13:00:20'),
(35, 'Unpaid Leave', 'Leave without pay for extended absences', '2025-06-10 13:00:20', '2025-06-10 13:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `leave_workflow`
--

CREATE TABLE `leave_workflow` (
  `id` int(11) NOT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `employee_role` varchar(50) DEFAULT NULL,
  `current_approver_role` varchar(50) DEFAULT NULL,
  `next_approver_role` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `current_level` int(11) DEFAULT 1,
  `max_level` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(50) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','archived') NOT NULL DEFAULT 'unread',
  `type` enum('document_signed','system','other') NOT NULL DEFAULT 'document_signed',
  `title` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `leader_name` varchar(100) NOT NULL,
  `leader_email` varchar(50) NOT NULL,
  `p_description` varchar(200) NOT NULL,
  `due_date` date NOT NULL,
  `sub_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `projects`
--
DELIMITER $$
CREATE TRIGGER `calculate_bonus_trigger` AFTER UPDATE ON `projects` FOR EACH ROW BEGIN
    DECLARE bonus FLOAT;
    
    -- Check if points are NULL or 0
    IF NEW.points IS NULL OR NEW.points = 0 THEN
        SET bonus = 0;
    ELSE
        -- Calculate bonus based on points
        IF NEW.points = 10 THEN
            SET bonus = 0.1 * 15000;
        ELSEIF NEW.points = 20 THEN
            SET bonus = 0.2 * 15000;
        ELSEIF NEW.points = 30 THEN
            SET bonus = 0.3 * 15000;
        ELSEIF NEW.points = 40 THEN
            SET bonus = 0.4 * 15000;
        ELSEIF NEW.points = 50 THEN
            SET bonus = 0.5 * 15000;
        ELSEIF NEW.points = 60 THEN
            SET bonus = 0.6 * 15000;
        ELSEIF NEW.points = 70 THEN
            SET bonus = 0.7 * 15000;
        ELSEIF NEW.points = 80 THEN
            SET bonus = 0.8 * 15000;
        ELSEIF NEW.points = 90 THEN
            SET bonus = 0.9 * 15000;
        ELSEIF NEW.points = 100 THEN
            SET bonus = 10 * 15000; -- Max bonus
        END IF;
    END IF;
    
    -- Update bonus and total_salary in the salary table for the respective employee
    UPDATE salary
    SET bonus = CONCAT(NEW.points,'%'),
        total_salary = 15000 + bonus
    WHERE emp_id = NEW.leader_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `registration_attempts`
--

CREATE TABLE `registration_attempts` (
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_attempts`
--

INSERT INTO `registration_attempts` (`ip_address`, `attempt_time`) VALUES
('::1', '2025-03-17 07:21:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_level` int(11) NOT NULL COMMENT '1=Top Level, 2=Second Level, etc.',
  `can_recommend` tinyint(1) DEFAULT 0,
  `can_apply` tinyint(1) DEFAULT 1,
  `can_approve` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_code`, `role_name`, `role_level`, `can_recommend`, `can_apply`, `can_approve`, `is_active`, `created_at`) VALUES
(1, 'MANAGING_DIRECTOR', 'Managing Director', 1, 0, 0, 1, 1, '2025-06-10 07:17:39'),
(2, 'HR_MANAGER', 'HR Manager', 2, 1, 1, 1, 1, '2025-06-10 07:17:39'),
(3, 'HEAD_OF_SALES', 'Head of Sales/Sales Director', 2, 1, 1, 1, 1, '2025-06-10 07:17:39'),
(4, 'OPERATIONS_MANAGER', 'Operations Manager', 3, 1, 1, 0, 1, '2025-06-10 07:17:39'),
(5, 'SALES_MANAGER', 'Sales Manager', 3, 1, 1, 0, 1, '2025-06-10 07:17:39'),
(6, 'HR_COORDINATOR', 'HR Coordinator', 3, 0, 1, 0, 1, '2025-06-10 07:17:39'),
(7, 'MARKETING_TEAM', 'Marketing Team Member', 4, 0, 1, 0, 1, '2025-06-10 07:17:39'),
(8, 'SALES_TEAM_LEADER', 'Sales Team Leader', 4, 0, 1, 0, 1, '2025-06-10 07:17:39'),
(9, 'RELATIONSHIP_OFFICER', 'Relationship Officer', 4, 0, 1, 0, 1, '2025-06-10 07:17:39'),
(10, 'OPERATIONS_TEAM', 'Operations Team Member', 4, 0, 1, 0, 1, '2025-06-10 07:17:39'),
(11, 'HOD', 'Head of Department', 0, 1, 1, 1, 1, '2025-06-13 09:22:16'),
(12, 'MANAGER', 'Manager', 0, 1, 1, 0, 1, '2025-06-13 09:22:16'),
(13, 'EMPLOYEE', 'Employee', 0, 0, 1, 0, 1, '2025-06-13 09:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `role_hierarchy`
--

CREATE TABLE `role_hierarchy` (
  `id` int(11) NOT NULL,
  `role_id` varchar(50) DEFAULT NULL,
  `parent_role_id` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `workflow_level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_hierarchy`
--

INSERT INTO `role_hierarchy` (`id`, `role_id`, `parent_role_id`, `department_id`, `workflow_level`, `created_at`) VALUES
(1, 'HR_COORDINATOR', 'HR_MANAGER', 1, 1, '2025-06-10 07:17:39'),
(2, 'HR_MANAGER', 'MANAGING_DIRECTOR', 1, 2, '2025-06-10 07:17:39'),
(3, 'SALES_TEAM_LEADER', 'SALES_MANAGER', 2, 1, '2025-06-10 07:17:39'),
(4, 'RELATIONSHIP_OFFICER', 'SALES_MANAGER', 2, 1, '2025-06-10 07:17:39'),
(5, 'SALES_MANAGER', 'HEAD_OF_SALES', 2, 2, '2025-06-10 07:17:39'),
(6, 'HEAD_OF_SALES', 'MANAGING_DIRECTOR', 2, 3, '2025-06-10 07:17:39'),
(7, 'MARKETING_TEAM', 'HR_MANAGER', 3, 1, '2025-06-10 07:17:39'),
(8, 'OPERATIONS_TEAM', 'OPERATIONS_MANAGER', 4, 1, '2025-06-10 07:17:39'),
(9, 'OPERATIONS_MANAGER', 'HEAD_OF_SALES', 4, 2, '2025-06-10 07:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `sal`
--

CREATE TABLE `sal` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(50) DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `housing_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transportation_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `performance_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `incentive` decimal(10,2) DEFAULT NULL,
  `calculated_days` int(11) DEFAULT NULL,
  `present_days` decimal(10,2) DEFAULT NULL,
  `leaves` decimal(10,2) DEFAULT NULL,
  `lto` decimal(10,2) DEFAULT NULL,
  `leaves_amt` decimal(10,2) NOT NULL,
  `lto_amt` decimal(10,2) NOT NULL,
  `hold` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `visa_expense` decimal(10,2) DEFAULT NULL,
  `others_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deduction_remarks` text DEFAULT NULL,
  `performance_points` int(11) DEFAULT NULL,
  `payable_salary` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `total_salary` decimal(10,2) DEFAULT NULL,
  `salary_date` date DEFAULT NULL,
  `pay_mode` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sal`
--

INSERT INTO `sal` (`id`, `emp_id`, `base_salary`, `housing_allowance`, `transportation_allowance`, `performance_bonus`, `incentive`, `calculated_days`, `present_days`, `leaves`, `lto`, `leaves_amt`, `lto_amt`, `hold`, `advance_paid`, `visa_expense`, `others_deduction`, `deduction_remarks`, `performance_points`, `payable_salary`, `deductions`, `total_salary`, `salary_date`, `pay_mode`) VALUES
(40, 'CME0002', 2430.00, 810.00, 810.00, 0.00, 3500.00, 30, 30.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '', 0, 7550.00, 0.00, 7550.00, '2025-05-15', 'Cash Memo');

-- --------------------------------------------------------

--
-- Table structure for table `salary`
--

CREATE TABLE `salary` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(255) NOT NULL,
  `base_salary` float NOT NULL DEFAULT 0,
  `bonus` float DEFAULT 0,
  `total_salary` float NOT NULL,
  `salary_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_slips`
--

CREATE TABLE `salary_slips` (
  `id` int(11) NOT NULL,
  `slip_no` int(11) NOT NULL,
  `salary_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_slips`
--

INSERT INTO `salary_slips` (`id`, `slip_no`, `salary_id`, `created_at`) VALUES
(71, 1, 40, '2025-05-21 12:26:47'),
(72, 2, 40, '2025-05-21 12:59:19'),
(73, 3, 40, '2025-05-21 13:17:16'),
(74, 4, 40, '2025-05-23 07:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_logs`
--

CREATE TABLE `sync_logs` (
  `id` int(11) NOT NULL,
  `device_id` varchar(50) DEFAULT NULL,
  `sync_time` datetime DEFAULT NULL,
  `records_count` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `status` enum('success','error') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `action`, `description`, `user_email`, `user_role`, `timestamp`, `ip_address`) VALUES
(1, 'System Initialize', 'System logs table created', 'superadmin@communikmarketing.com', 'super_admin', '2025-06-09 11:06:17', NULL),
(2, 'Logout', 'Super admin logged out', 'superadmin@communikmarketing.com', 'super_admin', '2025-06-09 11:52:14', '::1'),
(3, 'Logout', 'Super admin logged out', 'superadmin@communikmarketing.com', 'super_admin', '2025-06-09 14:28:08', '::1'),
(4, 'Logout', 'Super admin logged out', 'superadmin@communikmarketing.com', 'super_admin', '2025-06-09 14:54:39', '::1'),
(5, 'Logout', 'Super admin logged out', 'superadmin@communikmarketing.com', 'super_admin', '2025-06-09 14:54:44', '::1'),
(6, 'Logout', 'User logged out', 'hr@communikmarketing.com', 'HR', '2025-06-09 18:46:33', '::1'),
(7, 'Logout', 'User logged out', 'hr@communikmarketing.com', 'HR', '2025-06-10 19:14:59', '::1'),
(8, 'Logout', 'User logged out', 'hr@communikmarketing.com', 'HR', '2025-06-11 17:52:04', '::1'),
(9, 'Logout', 'User logged out', 'communikadmin@communikmarketing.com', 'admin', '2025-06-15 19:35:22', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(100) NOT NULL DEFAULT 'Employeeshub',
  `site_email` varchar(100) NOT NULL DEFAULT 'admin@employeeshub.com',
  `site_contact` varchar(20) NOT NULL DEFAULT '+1234567890',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `site_name`, `site_email`, `site_contact`, `created_at`, `updated_at`) VALUES
(1, 'Communik - HR Matrix', 'admin@hrmatrix.san-solutions.in', '+917020078847', '2025-06-09 14:45:41', '2025-06-09 14:46:40');

-- --------------------------------------------------------

--
-- Table structure for table `token1`
--

CREATE TABLE `token1` (
  `token_id` int(11) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `s_time` datetime DEFAULT NULL,
  `token` varchar(1000) DEFAULT NULL,
  `otp` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `nid` varchar(50) NOT NULL,
  `emp_name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `department` varchar(20) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(200) NOT NULL,
  `address` varchar(500) NOT NULL,
  `mode_of_travel` varchar(50) NOT NULL,
  `total_cost` double NOT NULL,
  `Status` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainees`
--

CREATE TABLE `trainees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_logs`
--

CREATE TABLE `work_logs` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `is_compensated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_admin_type` (`admin_type`);

--
-- Indexes for table `admin_document_queue`
--
ALTER TABLE `admin_document_queue`
  ADD PRIMARY KEY (`queue_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `anonymous_feedback`
--
ALTER TABLE `anonymous_feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `appraisal_assignments`
--
ALTER TABLE `appraisal_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_period` (`period_id`),
  ADD KEY `fk_employee_assignment` (`employee_id`),
  ADD KEY `fk_form_assignment` (`form_id`);

--
-- Indexes for table `appraisal_criteria`
--
ALTER TABLE `appraisal_criteria`
  ADD PRIMARY KEY (`criteria_id`);

--
-- Indexes for table `appraisal_forms`
--
ALTER TABLE `appraisal_forms`
  ADD PRIMARY KEY (`form_id`),
  ADD KEY `period_id` (`period_id`);

--
-- Indexes for table `appraisal_periods`
--
ALTER TABLE `appraisal_periods`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `appraisal_ratings`
--
ALTER TABLE `appraisal_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `appraisal_id` (`appraisal_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `approval_hierarchy`
--
ALTER TABLE `approval_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_department` (`department_id`);

--
-- Indexes for table `approval_history`
--
ALTER TABLE `approval_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workflow_id` (`workflow_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `approval_routes`
--
ALTER TABLE `approval_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_request_type` (`request_type`),
  ADD KEY `idx_employee_role` (`employee_role`);

--
-- Indexes for table `approval_workflow`
--
ALTER TABLE `approval_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `next_approver` (`next_approver`),
  ADD KEY `idx_request_type_id` (`request_type`,`request_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_current_approver` (`current_approver`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `backup_settings`
--
ALTER TABLE `backup_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `claim_approvals`
--
ALTER TABLE `claim_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `claim_details`
--
ALTER TABLE `claim_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parent_department` (`parent_department_id`);

--
-- Indexes for table `department_heads`
--
ALTER TABLE `department_heads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`user_name`),
  ADD KEY `head_id` (`head_id`);

--
-- Indexes for table `document_audit_log`
--
ALTER TABLE `document_audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `doc_id` (`doc_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `version_id` (`version_id`);

--
-- Indexes for table `document_uploads`
--
ALTER TABLE `document_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eid` (`eid`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`version_id`),
  ADD KEY `doc_id` (`doc_id`);

--
-- Indexes for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `eid` (`eid`),
  ADD UNIQUE KEY `visa_number` (`visa_number`),
  ADD UNIQUE KEY `passport_number` (`passport_number`),
  ADD UNIQUE KEY `visa_number_2` (`visa_number`),
  ADD UNIQUE KEY `unique_document_number` (`document_number`),
  ADD KEY `fk_department` (`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_role_id` (`new_role_id`);
ALTER TABLE `employees` ADD FULLTEXT KEY `visa_doc` (`visa_doc`,`passport_doc`);

--
-- Indexes for table `employee_appraisals`
--
ALTER TABLE `employee_appraisals`
  ADD PRIMARY KEY (`appraisal_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `period_id` (`period_id`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `employee_family`
--
ALTER TABLE `employee_family`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eid` (`eid`);

--
-- Indexes for table `employee_goals`
--
ALTER TABLE `employee_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `employee_leave_balance`
--
ALTER TABLE `employee_leave_balance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `emp_login`
--
ALTER TABLE `emp_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_idx` (`email`),
  ADD KEY `fk_emp_id` (`emp_id`);

--
-- Indexes for table `emp_passport`
--
ALTER TABLE `emp_passport`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `esign_comments`
--
ALTER TABLE `esign_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `esign_documents`
--
ALTER TABLE `esign_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `esign_notifications`
--
ALTER TABLE `esign_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `esign_settings`
--
ALTER TABLE `esign_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `esign_templates`
--
ALTER TABLE `esign_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `esign_workflow`
--
ALTER TABLE `esign_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_pt`
--
ALTER TABLE `event_pt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_about_us`
--
ALTER TABLE `guest_about_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_benefits`
--
ALTER TABLE `guest_benefits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_contact`
--
ALTER TABLE `guest_contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_facts`
--
ALTER TABLE `guest_facts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_header`
--
ALTER TABLE `guest_header`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_our_clients`
--
ALTER TABLE `guest_our_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_our_portfolio`
--
ALTER TABLE `guest_our_portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_our_skills`
--
ALTER TABLE `guest_our_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_services`
--
ALTER TABLE `guest_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_slider_images`
--
ALTER TABLE `guest_slider_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_team`
--
ALTER TABLE `guest_team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_testimonial`
--
ALTER TABLE `guest_testimonial`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labor_cards`
--
ALTER TABLE `labor_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eid` (`eid`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_current_approver` (`current_approver_role`),
  ADD KEY `fk_next_approver` (`next_approver_role`);

--
-- Indexes for table `leave_approval_flow`
--
ALTER TABLE `leave_approval_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `employee_role` (`employee_role`),
  ADD KEY `approver_role` (`approver_role`);

--
-- Indexes for table `leave_approval_history`
--
ALTER TABLE `leave_approval_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `action_by_role` (`action_by_role`),
  ADD KEY `action` (`action`);

--
-- Indexes for table `leave_hierarchy`
--
ALTER TABLE `leave_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `recommender_id` (`recommender_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `leave_notifications`
--
ALTER TABLE `leave_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`);

--
-- Indexes for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_status_types`
--
ALTER TABLE `leave_status_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_code` (`status_code`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_workflow`
--
ALTER TABLE `leave_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `employee_role` (`employee_role`),
  ADD KEY `current_approver_role` (`current_approver_role`),
  ADD KEY `next_approver_role` (`next_approver_role`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `idx_emp_id_status` (`emp_id`,`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `registration_attempts`
--
ALTER TABLE `registration_attempts`
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_code` (`role_code`);

--
-- Indexes for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `parent_role_id` (`parent_role_id`);

--
-- Indexes for table `sal`
--
ALTER TABLE `sal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `salary`
--
ALTER TABLE `salary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_slips`
--
ALTER TABLE `salary_slips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `token1`
--
ALTER TABLE `token1`
  ADD PRIMARY KEY (`token_id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `trainees`
--
ALTER TABLE `trainees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `work_logs`
--
ALTER TABLE `work_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_document_queue`
--
ALTER TABLE `admin_document_queue`
  MODIFY `queue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `anonymous_feedback`
--
ALTER TABLE `anonymous_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appraisal_assignments`
--
ALTER TABLE `appraisal_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `appraisal_criteria`
--
ALTER TABLE `appraisal_criteria`
  MODIFY `criteria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `appraisal_forms`
--
ALTER TABLE `appraisal_forms`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appraisal_periods`
--
ALTER TABLE `appraisal_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appraisal_ratings`
--
ALTER TABLE `appraisal_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `approval_hierarchy`
--
ALTER TABLE `approval_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `approval_history`
--
ALTER TABLE `approval_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approval_routes`
--
ALTER TABLE `approval_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `approval_workflow`
--
ALTER TABLE `approval_workflow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `backup_settings`
--
ALTER TABLE `backup_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claim_approvals`
--
ALTER TABLE `claim_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claim_details`
--
ALTER TABLE `claim_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `department_heads`
--
ALTER TABLE `department_heads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `document_audit_log`
--
ALTER TABLE `document_audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `document_uploads`
--
ALTER TABLE `document_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `employee_appraisals`
--
ALTER TABLE `employee_appraisals`
  MODIFY `appraisal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employee_family`
--
ALTER TABLE `employee_family`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_goals`
--
ALTER TABLE `employee_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_leave_balance`
--
ALTER TABLE `employee_leave_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emp_login`
--
ALTER TABLE `emp_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `emp_passport`
--
ALTER TABLE `emp_passport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `esign_comments`
--
ALTER TABLE `esign_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esign_documents`
--
ALTER TABLE `esign_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `esign_notifications`
--
ALTER TABLE `esign_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esign_settings`
--
ALTER TABLE `esign_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `esign_templates`
--
ALTER TABLE `esign_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `esign_workflow`
--
ALTER TABLE `esign_workflow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_pt`
--
ALTER TABLE `event_pt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_about_us`
--
ALTER TABLE `guest_about_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guest_benefits`
--
ALTER TABLE `guest_benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `guest_contact`
--
ALTER TABLE `guest_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guest_facts`
--
ALTER TABLE `guest_facts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `guest_header`
--
ALTER TABLE `guest_header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `guest_our_clients`
--
ALTER TABLE `guest_our_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `guest_our_portfolio`
--
ALTER TABLE `guest_our_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `guest_our_skills`
--
ALTER TABLE `guest_our_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `guest_services`
--
ALTER TABLE `guest_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `guest_slider_images`
--
ALTER TABLE `guest_slider_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `guest_team`
--
ALTER TABLE `guest_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `guest_testimonial`
--
ALTER TABLE `guest_testimonial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `labor_cards`
--
ALTER TABLE `labor_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `leave_approval_flow`
--
ALTER TABLE `leave_approval_flow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_approval_history`
--
ALTER TABLE `leave_approval_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_hierarchy`
--
ALTER TABLE `leave_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_notifications`
--
ALTER TABLE `leave_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `leave_status_types`
--
ALTER TABLE `leave_status_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `leave_workflow`
--
ALTER TABLE `leave_workflow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=464;

--
-- AUTO_INCREMENT for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sal`
--
ALTER TABLE `sal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `salary`
--
ALTER TABLE `salary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `salary_slips`
--
ALTER TABLE `salary_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_logs`
--
ALTER TABLE `sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `token1`
--
ALTER TABLE `token1`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trainees`
--
ALTER TABLE `trainees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_logs`
--
ALTER TABLE `work_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appraisal_assignments`
--
ALTER TABLE `appraisal_assignments`
  ADD CONSTRAINT `fk_employee_assignment` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`eid`),
  ADD CONSTRAINT `fk_form_assignment` FOREIGN KEY (`form_id`) REFERENCES `appraisal_forms` (`form_id`),
  ADD CONSTRAINT `fk_period` FOREIGN KEY (`period_id`) REFERENCES `appraisal_periods` (`period_id`);

--
-- Constraints for table `approval_hierarchy`
--
ALTER TABLE `approval_hierarchy`
  ADD CONSTRAINT `approval_hierarchy_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `approval_history`
--
ALTER TABLE `approval_history`
  ADD CONSTRAINT `approval_history_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `approval_workflow` (`id`),
  ADD CONSTRAINT `approval_history_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`eid`);

--
-- Constraints for table `approval_routes`
--
ALTER TABLE `approval_routes`
  ADD CONSTRAINT `approval_routes_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `approval_workflow`
--
ALTER TABLE `approval_workflow`
  ADD CONSTRAINT `approval_workflow_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`eid`),
  ADD CONSTRAINT `approval_workflow_ibfk_2` FOREIGN KEY (`current_approver`) REFERENCES `employees` (`eid`),
  ADD CONSTRAINT `approval_workflow_ibfk_3` FOREIGN KEY (`next_approver`) REFERENCES `employees` (`eid`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_parent_department` FOREIGN KEY (`parent_department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `document_uploads`
--
ALTER TABLE `document_uploads`
  ADD CONSTRAINT `document_uploads_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `employees` (`eid`) ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`new_role_id`) REFERENCES `roles` (`role_code`);

--
-- Constraints for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  ADD CONSTRAINT `employee_onboarding_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `emp_login`
--
ALTER TABLE `emp_login`
  ADD CONSTRAINT `emp_login_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`),
  ADD CONSTRAINT `fk_emp_id` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`);

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `fk_current_approver` FOREIGN KEY (`current_approver_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `fk_next_approver` FOREIGN KEY (`next_approver_role`) REFERENCES `roles` (`role_code`);

--
-- Constraints for table `leave_approval_flow`
--
ALTER TABLE `leave_approval_flow`
  ADD CONSTRAINT `leave_approval_flow_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  ADD CONSTRAINT `leave_approval_flow_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `leave_approval_flow_ibfk_3` FOREIGN KEY (`employee_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `leave_approval_flow_ibfk_4` FOREIGN KEY (`approver_role`) REFERENCES `roles` (`role_code`);

--
-- Constraints for table `leave_approval_history`
--
ALTER TABLE `leave_approval_history`
  ADD CONSTRAINT `leave_approval_history_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  ADD CONSTRAINT `leave_approval_history_ibfk_2` FOREIGN KEY (`action_by_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `leave_approval_history_ibfk_3` FOREIGN KEY (`action`) REFERENCES `leave_status_types` (`status_code`);

--
-- Constraints for table `leave_hierarchy`
--
ALTER TABLE `leave_hierarchy`
  ADD CONSTRAINT `leave_hierarchy_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `leave_hierarchy_ibfk_2` FOREIGN KEY (`recommender_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `leave_hierarchy_ibfk_3` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `leave_hierarchy_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `leave_notifications`
--
ALTER TABLE `leave_notifications`
  ADD CONSTRAINT `leave_notifications_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`);

--
-- Constraints for table `leave_workflow`
--
ALTER TABLE `leave_workflow`
  ADD CONSTRAINT `leave_workflow_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  ADD CONSTRAINT `leave_workflow_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `leave_workflow_ibfk_3` FOREIGN KEY (`employee_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `leave_workflow_ibfk_4` FOREIGN KEY (`current_approver_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `leave_workflow_ibfk_5` FOREIGN KEY (`next_approver_role`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `leave_workflow_ibfk_6` FOREIGN KEY (`status`) REFERENCES `leave_status_types` (`status_code`);

--
-- Constraints for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD CONSTRAINT `role_hierarchy_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `role_hierarchy_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_code`),
  ADD CONSTRAINT `role_hierarchy_ibfk_3` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`role_code`);

--
-- Constraints for table `sal`
--
ALTER TABLE `sal`
  ADD CONSTRAINT `sal_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`);

--
-- Constraints for table `trainees`
--
ALTER TABLE `trainees`
  ADD CONSTRAINT `trainees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `trainees_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `trainees_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
