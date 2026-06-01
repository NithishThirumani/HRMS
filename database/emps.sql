-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: EMPS
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_rights`
--

DROP TABLE IF EXISTS `access_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `can_view_employees` tinyint(1) DEFAULT 0,
  `can_view_projects` tinyint(1) DEFAULT 0,
  `can_view_leaves` tinyint(1) DEFAULT 0,
  `can_view_salary` tinyint(1) DEFAULT 0,
  `can_view_tours` tinyint(1) DEFAULT 0,
  `can_edit_employees` tinyint(1) DEFAULT 0,
  `can_edit_projects` tinyint(1) DEFAULT 0,
  `can_edit_leaves` tinyint(1) DEFAULT 0,
  `can_edit_salary` tinyint(1) DEFAULT 0,
  `can_edit_tours` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `access_rights_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_rights`
--

LOCK TABLES `access_rights` WRITE;
/*!40000 ALTER TABLE `access_rights` DISABLE KEYS */;
INSERT INTO `access_rights` VALUES
(1,'HR Officer','HR',1,0,0,1,0,1,0,0,1,0,4,'2025-08-22 08:35:05','2025-08-22 08:35:05'),
(2,'Network Engg','IT',1,0,0,0,1,1,0,0,0,0,4,'2025-10-30 14:14:09','2025-10-30 14:14:09');
/*!40000 ALTER TABLE `access_rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT current_timestamp(),
  `employee` varchar(100) DEFAULT NULL,
  `activity` text DEFAULT NULL,
  `eid` varchar(10) DEFAULT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES
(1,'2025-07-01 21:59:59','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update'),
(2,'2025-07-01 22:01:04','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update'),
(3,'2025-07-01 22:01:45','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update'),
(4,'2025-07-01 23:48:38','Rohit Singh','Employee details updated','CME0012','hr@communikmarketing.com','update'),
(5,'2025-07-02 00:08:03','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update'),
(6,'2025-07-02 00:09:26','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update'),
(7,'2025-07-02 00:55:37','Bashid Khan','Employee details updated','CME0001','hr@communikmarketing.com','update');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `last_login_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_admin_type` (`admin_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES
(3,'superadmin','superadmin@bizwy.com','','','undraw_profile.jpg','super_admin','super_admin','$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW','Active','2025-10-22 15:32:09','122.177.243.69'),
(4,'admincommunik','admin@bizwy.com','Male','7894561232','','admin','admin','$2y$10$7ZUhH5xA69XTGSHf6J9mpuM/074u81DxpjRKKXvB46/ovL0k4JVqm','Active','2026-05-21 08:06:42','122.177.244.199'),
(5,'Asif','support@communikmarketing.com','Male','0526588186','68a82730d0f92_Asif Kifayat Chougule_Photo.jpg','admin','admin','Communik@12345','Active','2025-10-21 11:24:57','91.75.108.249');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `ensure_super_admin_active` BEFORE INSERT ON `admin` FOR EACH ROW BEGIN
    IF NEW.role = 'super_admin' THEN
        SET NEW.status = 'Active';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_admin_create` AFTER INSERT ON `admin` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (NEW.id, 'create', CONCAT('New admin created: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `prevent_super_admin_inactive` BEFORE UPDATE ON `admin` FOR EACH ROW BEGIN
    IF OLD.role = 'super_admin' AND NEW.status = 'Inactive' THEN
        SET NEW.status = 'Active';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_admin_login` AFTER UPDATE ON `admin` FOR EACH ROW BEGIN
    IF (NEW.last_login IS NOT NULL AND OLD.last_login != NEW.last_login) OR 
       (OLD.last_login IS NULL AND NEW.last_login IS NOT NULL) THEN
        INSERT INTO audit_logs (user_id, action_type, description, ip_address)
        VALUES (NEW.id, 'login', CONCAT('Admin login: ', NEW.user_name), COALESCE(NEW.last_login_ip, '127.0.0.1'));
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_admin_update` AFTER UPDATE ON `admin` FOR EACH ROW BEGIN
    IF NOT (NEW.last_login != OLD.last_login) THEN 
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_admin_delete` BEFORE DELETE ON `admin` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action_type, description, ip_address)
    VALUES (OLD.id, 'delete', CONCAT('Admin deleted: ', OLD.user_name), COALESCE(OLD.last_login_ip, '127.0.0.1'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `admin_document_queue`
--

DROP TABLE IF EXISTS `admin_document_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_document_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `assigned_date` datetime NOT NULL,
  `status` enum('Pending','Signed','Rejected') NOT NULL DEFAULT 'Pending',
  `signed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `assignment_id` (`assignment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_document_queue`
--

LOCK TABLES `admin_document_queue` WRITE;
/*!40000 ALTER TABLE `admin_document_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_document_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anonymous_feedback`
--

DROP TABLE IF EXISTS `anonymous_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anonymous_feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','in_review','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_comment` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anonymous_feedback`
--

LOCK TABLES `anonymous_feedback` WRITE;
/*!40000 ALTER TABLE `anonymous_feedback` DISABLE KEYS */;
INSERT INTO `anonymous_feedback` VALUES
(4,'IT','rerdtg','pending','2025-03-17 07:59:21',NULL,'2025-03-17 07:59:21'),
(5,'HR Department','Where is my payout please? ','pending','2025-10-17 10:33:12',NULL,'2025-10-17 10:33:12');
/*!40000 ALTER TABLE `anonymous_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appraisal_assignments`
--

DROP TABLE IF EXISTS `appraisal_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appraisal_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_period` (`period_id`),
  KEY `fk_employee_assignment` (`employee_id`),
  KEY `fk_form_assignment` (`form_id`),
  CONSTRAINT `fk_employee_assignment` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`eid`),
  CONSTRAINT `fk_form_assignment` FOREIGN KEY (`form_id`) REFERENCES `appraisal_forms` (`form_id`),
  CONSTRAINT `fk_period` FOREIGN KEY (`period_id`) REFERENCES `appraisal_periods` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appraisal_assignments`
--

LOCK TABLES `appraisal_assignments` WRITE;
/*!40000 ALTER TABLE `appraisal_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appraisal_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appraisal_criteria`
--

DROP TABLE IF EXISTS `appraisal_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appraisal_criteria` (
  `criteria_id` int(11) NOT NULL AUTO_INCREMENT,
  `criteria_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weightage` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`criteria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appraisal_criteria`
--

LOCK TABLES `appraisal_criteria` WRITE;
/*!40000 ALTER TABLE `appraisal_criteria` DISABLE KEYS */;
INSERT INTO `appraisal_criteria` VALUES
(1,'IT','Understanding and application of required job skills',10.00,1,'2025-03-13 19:22:36'),
(2,'Quality of Work','Accuracy, thoroughness, and effectiveness',15.00,1,'2025-03-13 19:22:36'),
(3,'Communication & Teamwork','Interaction with colleagues and team contribution',12.50,1,'2025-03-13 19:22:36'),
(4,'Problem-Solving Ability','Analyzing and resolving workplace challenges',12.50,1,'2025-03-13 19:22:36'),
(5,'Initiative & Innovation','Self-driven improvements and innovative solutions',10.00,1,'2025-03-13 19:22:36'),
(6,'Adherence to Deadlines','Timely completion of assigned tasks',12.50,1,'2025-03-13 19:22:36'),
(7,'Leadership','Guidance and team management capabilities',12.50,1,'2025-03-13 19:22:36'),
(8,'Attendance & Punctuality','Regular attendance and timeliness',10.00,1,'2025-03-13 19:22:36'),
(9,'Discipline','Assesses punctuality, attendance and adherence to company rules, impacting overall performance evaluation.',10.00,1,'2025-03-14 11:58:39');
/*!40000 ALTER TABLE `appraisal_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appraisal_forms`
--

DROP TABLE IF EXISTS `appraisal_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appraisal_forms` (
  `form_id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  PRIMARY KEY (`form_id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appraisal_forms`
--

LOCK TABLES `appraisal_forms` WRITE;
/*!40000 ALTER TABLE `appraisal_forms` DISABLE KEYS */;
INSERT INTO `appraisal_forms` VALUES
(2,8,'0','Pending');
/*!40000 ALTER TABLE `appraisal_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appraisal_periods`
--

DROP TABLE IF EXISTS `appraisal_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appraisal_periods` (
  `period_id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Draft','Active','Completed') DEFAULT 'Draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appraisal_periods`
--

LOCK TABLES `appraisal_periods` WRITE;
/*!40000 ALTER TABLE `appraisal_periods` DISABLE KEYS */;
INSERT INTO `appraisal_periods` VALUES
(7,'2025-03-01','2025-03-31','Draft',1,'2025-03-14 16:52:25','2025-03-14 16:52:25'),
(8,'2024-03-10','2025-04-15','Active',1,'2025-03-17 07:45:20','2025-06-25 07:22:55');
/*!40000 ALTER TABLE `appraisal_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appraisal_ratings`
--

DROP TABLE IF EXISTS `appraisal_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appraisal_ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `appraisal_id` int(11) DEFAULT NULL,
  `criteria_id` int(11) DEFAULT NULL,
  `self_rating` int(11) DEFAULT NULL,
  `hod_rating` int(11) DEFAULT NULL,
  `hr_rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`rating_id`),
  KEY `appraisal_id` (`appraisal_id`),
  KEY `criteria_id` (`criteria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appraisal_ratings`
--

LOCK TABLES `appraisal_ratings` WRITE;
/*!40000 ALTER TABLE `appraisal_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `appraisal_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_hierarchy`
--

DROP TABLE IF EXISTS `approval_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `role_level` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `reports_to_role` varchar(50) DEFAULT NULL,
  `can_approve_types` set('leave','appraisal','esignature') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`),
  KEY `idx_department` (`department_id`),
  CONSTRAINT `approval_hierarchy_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_hierarchy`
--

LOCK TABLES `approval_hierarchy` WRITE;
/*!40000 ALTER TABLE `approval_hierarchy` DISABLE KEYS */;
INSERT INTO `approval_hierarchy` VALUES
(1,NULL,1,'Managing Director',NULL,'leave,appraisal,esignature','2025-06-09 10:19:56'),
(2,NULL,2,'HR Manager','Managing Director','leave,appraisal,esignature','2025-06-09 10:19:56'),
(3,NULL,3,'HR Coordinator','HR Manager','','2025-06-09 10:19:56'),
(4,NULL,2,'Head of Sales','Managing Director','leave,appraisal,esignature','2025-06-09 10:19:56'),
(5,NULL,3,'HR Manager Sales','Head of Sales','leave,appraisal','2025-06-09 10:19:56'),
(6,NULL,4,'Operation Manager','HR Manager Sales','leave','2025-06-09 10:19:56'),
(7,NULL,5,'Operations Team','Operation Manager','','2025-06-09 10:19:56'),
(8,NULL,2,'HR Manager Marketing','Managing Director','leave,appraisal','2025-06-09 10:19:56'),
(9,NULL,3,'Marketing Team','HR Manager Marketing','','2025-06-09 10:19:56'),
(10,NULL,3,'Sales Manager','HR Manager Sales','leave','2025-06-09 10:19:56'),
(11,NULL,4,'Sales Team Leader','Sales Manager','','2025-06-09 10:19:56'),
(12,NULL,4,'Relationship Officer','Sales Manager','','2025-06-09 10:19:56');
/*!40000 ALTER TABLE `approval_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_history`
--

DROP TABLE IF EXISTS `approval_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `approver_id` varchar(10) NOT NULL,
  `action` enum('approved','rejected','forwarded') NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `approval_history_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `approval_workflow` (`id`),
  CONSTRAINT `approval_history_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_history`
--

LOCK TABLES `approval_history` WRITE;
/*!40000 ALTER TABLE `approval_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_routes`
--

DROP TABLE IF EXISTS `approval_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_type` enum('leave','appraisal','esignature') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_role` varchar(50) NOT NULL,
  `approval_sequence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`approval_sequence`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `idx_request_type` (`request_type`),
  KEY `idx_employee_role` (`employee_role`),
  CONSTRAINT `approval_routes_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_routes`
--

LOCK TABLES `approval_routes` WRITE;
/*!40000 ALTER TABLE `approval_routes` DISABLE KEYS */;
INSERT INTO `approval_routes` VALUES
(1,'leave',NULL,'HR Coordinator','[{\"level\": 1, \"role\": \"HR Manager\"}, {\"level\": 2, \"role\": \"Managing Director\"}]','2025-06-09 10:19:56'),
(2,'leave',NULL,'Operations Team','[{\"level\": 1, \"role\": \"Operation Manager\"}, {\"level\": 2, \"role\": \"HR Manager Sales\"}, {\"level\": 3, \"role\": \"Head of Sales\"}]','2025-06-09 10:19:56'),
(3,'leave',NULL,'Sales Team Leader','[{\"level\": 1, \"role\": \"Sales Manager\"}, {\"level\": 2, \"role\": \"HR Manager Sales\"}, {\"level\": 3, \"role\": \"Head of Sales\"}]','2025-06-09 10:19:56'),
(4,'leave',NULL,'Marketing Team','[{\"level\": 1, \"role\": \"HR Manager Marketing\"}, {\"level\": 2, \"role\": \"Managing Director\"}]','2025-06-09 10:19:56');
/*!40000 ALTER TABLE `approval_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_workflow`
--

DROP TABLE IF EXISTS `approval_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `approval_chain` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`approval_chain`)),
  PRIMARY KEY (`id`),
  KEY `next_approver` (`next_approver`),
  KEY `idx_request_type_id` (`request_type`,`request_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_current_approver` (`current_approver`),
  KEY `idx_status` (`status`),
  CONSTRAINT `approval_workflow_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`eid`),
  CONSTRAINT `approval_workflow_ibfk_2` FOREIGN KEY (`current_approver`) REFERENCES `employees` (`eid`),
  CONSTRAINT `approval_workflow_ibfk_3` FOREIGN KEY (`next_approver`) REFERENCES `employees` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_workflow`
--

LOCK TABLES `approval_workflow` WRITE;
/*!40000 ALTER TABLE `approval_workflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES
(1,'U07',NULL,'2025-03-07','Friday','12:48:21','12:50:36',NULL,'field',NULL,'8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496;8.5327872,76.906496','Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India; Akkulam, Thiruvananthapuram, Kerala, 695001, India',NULL,1.00,'2025-03-07 11:48:21'),
(2,'U07',NULL,'2025-03-08','Saturday','11:49:10','11:49:10',NULL,'field',NULL,'8.5300546,76.9098619','Sree Chitira Thirunal Nagar, Akkulam, Thiruvananthapuram, Kerala, 695001, India',NULL,1.00,'2025-03-08 10:49:10');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` enum('login','create','update','delete') NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_type` (`action_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=498 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,4,'login','Admin login: admincommunik','::1','2025-06-09 15:00:23'),
(2,4,'login','Admin login: admincommunik','::1','2025-06-09 15:57:05'),
(3,4,'login','Admin login: admincommunik','::1','2025-06-09 16:20:59'),
(4,4,'login','Admin login: admincommunik','::1','2025-06-09 17:39:26'),
(5,4,'login','Admin login: admincommunik','::1','2025-06-09 17:45:17'),
(6,3,'login','Admin login: superadmin','::1','2025-06-09 17:45:26'),
(7,3,'update','Admin updated: superadmin','::1','2025-06-09 17:51:15'),
(8,4,'update','Admin updated: admincommunik (status changed from Active to Inactive)','::1','2025-06-09 17:51:15'),
(9,3,'update','Admin updated: superadmin','::1','2025-06-09 17:51:19'),
(10,4,'update','Admin updated: admincommunik','::1','2025-06-09 17:51:19'),
(11,3,'update','Admin updated: superadmin','::1','2025-06-09 17:55:48'),
(12,4,'update','Admin updated: admincommunik','::1','2025-06-09 17:55:48'),
(13,4,'update','Admin updated: admincommunik (status changed from Inactive to Active)','::1','2025-06-09 18:05:18'),
(14,3,'update','Admin updated: superadmin','::1','2025-06-09 18:09:27'),
(15,4,'update','Admin updated: admincommunik (status changed from Active to Inactive)','::1','2025-06-09 18:09:27'),
(16,3,'update','Admin updated: superadmin','::1','2025-06-09 18:09:30'),
(17,4,'update','Admin updated: admincommunik','::1','2025-06-09 18:09:30'),
(18,3,'update','Admin updated: superadmin','::1','2025-06-09 18:09:34'),
(19,4,'update','Admin updated: admincommunik','::1','2025-06-09 18:09:34'),
(20,3,'update','Admin updated: superadmin','::1','2025-06-09 18:19:21'),
(21,4,'update','Admin updated: admincommunik','::1','2025-06-09 18:19:21'),
(22,3,'login','Admin login: superadmin','::1','2025-06-09 18:19:32'),
(23,3,'update','Admin updated: superadmin','::1','2025-06-09 18:22:32'),
(24,4,'update','Admin updated: admincommunik (status changed from Inactive to Active)','::1','2025-06-09 18:22:32'),
(25,4,'login','Admin login: admincommunik','::1','2025-06-09 18:23:04'),
(26,4,'login','Admin login: admincommunik','::1','2025-06-09 18:23:23'),
(27,3,'update','Admin updated: superadmin','::1','2025-06-09 18:23:40'),
(28,4,'update','Admin updated: admincommunik (status changed from Active to Inactive)','::1','2025-06-09 18:23:40'),
(29,3,'update','Admin updated: superadmin','::1','2025-06-09 18:49:50'),
(30,4,'update','Admin updated: admincommunik (status changed from Inactive to Active)','::1','2025-06-09 18:49:50'),
(31,4,'login','Admin login: admincommunik','::1','2025-06-09 18:50:00'),
(32,4,'login','Admin login: admincommunik','::1','2025-06-10 19:15:03'),
(33,4,'login','Admin login: admincommunik','::1','2025-06-11 03:49:40'),
(34,4,'login','Admin login: admincommunik','::1','2025-06-11 07:38:50'),
(35,4,'login','Admin login: admincommunik','::1','2025-06-11 10:57:53'),
(36,4,'login','Admin login: admincommunik','::1','2025-06-11 17:52:07'),
(37,4,'login','Admin login: admincommunik','::1','2025-06-11 17:56:10'),
(38,4,'login','Admin login: admincommunik','::1','2025-06-14 04:02:22'),
(39,4,'login','Admin login: admincommunik','::1','2025-06-14 17:18:56'),
(40,4,'login','Admin login: admincommunik','::1','2025-06-14 17:30:08'),
(41,4,'login','Admin login: admincommunik','::1','2025-06-14 17:36:16'),
(42,4,'login','Admin login: admincommunik','::1','2025-06-14 17:43:05'),
(43,4,'login','Admin login: admincommunik','::1','2025-06-14 17:47:25'),
(44,4,'login','Admin login: admincommunik','::1','2025-06-15 05:38:49'),
(45,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 09:07:53'),
(46,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 11:10:45'),
(47,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 14:13:43'),
(48,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 15:12:20'),
(49,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 15:21:39'),
(50,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 15:59:24'),
(51,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-16 16:10:07'),
(52,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-20 06:24:16'),
(53,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-20 06:27:29'),
(54,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-20 06:33:39'),
(55,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-20 06:51:45'),
(56,4,'login','Admin login: admincommunik','103.120.51.181','2025-06-20 12:10:59'),
(57,4,'login','Admin login: admincommunik','157.51.232.62','2025-06-25 10:24:31'),
(58,4,'login','Admin login: admincommunik','157.51.232.62','2025-06-25 10:36:52'),
(59,3,'login','Admin login: superadmin','157.51.232.62','2025-06-25 10:41:28'),
(60,3,'update','Admin updated: superadmin','157.51.232.62','2025-06-25 10:42:08'),
(61,4,'login','Admin login: admincommunik','157.51.232.62','2025-06-25 10:42:14'),
(62,4,'login','Admin login: admincommunik','157.51.233.200','2025-06-25 11:37:57'),
(63,4,'login','Admin login: admincommunik','157.51.233.200','2025-06-25 11:42:58'),
(64,4,'login','Admin login: admincommunik','157.51.233.200','2025-06-25 11:44:45'),
(65,4,'login','Admin login: admincommunik','157.51.232.177','2025-06-27 09:33:03'),
(66,4,'login','Admin login: admincommunik','157.51.232.230','2025-06-28 11:06:03'),
(67,4,'login','Admin login: admincommunik','157.51.235.123','2025-06-28 17:08:47'),
(68,4,'login','Admin login: admincommunik','157.51.241.163','2025-06-28 19:56:17'),
(69,4,'login','Admin login: admincommunik','157.51.237.40','2025-06-29 10:53:50'),
(70,4,'login','Admin login: admincommunik','157.51.238.136','2025-06-29 16:17:32'),
(71,4,'login','Admin login: admincommunik','157.51.236.69','2025-06-29 17:24:25'),
(72,4,'login','Admin login: admincommunik','157.51.236.69','2025-06-29 17:43:23'),
(73,4,'login','Admin login: admincommunik','157.51.236.69','2025-06-29 17:46:53'),
(74,4,'login','Admin login: admincommunik','86.98.16.101','2025-06-30 16:04:14'),
(75,4,'login','Admin login: admincommunik','157.51.238.119','2025-07-01 18:44:51'),
(76,4,'login','Admin login: admincommunik','157.51.238.119','2025-07-01 19:10:11'),
(77,4,'login','Admin login: admincommunik','157.51.229.208','2025-07-02 05:44:09'),
(78,4,'login','Admin login: admincommunik','157.51.229.208','2025-07-02 05:58:49'),
(79,4,'login','Admin login: admincommunik','157.51.234.191','2025-07-02 06:37:11'),
(80,4,'login','Admin login: admincommunik','5.31.2.93','2025-07-03 05:32:14'),
(81,4,'login','Admin login: admincommunik','5.31.2.93','2025-07-03 08:28:49'),
(82,4,'login','Admin login: admincommunik','5.31.2.93','2025-07-03 10:44:52'),
(83,4,'login','Admin login: admincommunik','157.50.127.218','2025-07-03 10:59:49'),
(84,4,'login','Admin login: admincommunik','157.50.127.218','2025-07-03 11:05:44'),
(85,4,'login','Admin login: admincommunik','5.31.2.93','2025-07-03 11:55:16'),
(86,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-04 10:16:54'),
(87,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-04 12:20:40'),
(88,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-04 13:53:17'),
(89,4,'login','Admin login: admincommunik','103.120.51.151','2025-07-04 13:56:12'),
(90,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-04 14:56:57'),
(91,4,'login','Admin login: admincommunik','103.120.51.151','2025-07-05 14:33:58'),
(92,4,'login','Admin login: admincommunik','103.120.51.155','2025-07-06 10:59:47'),
(93,4,'login','Admin login: admincommunik','103.120.51.155','2025-07-06 12:29:23'),
(94,4,'login','Admin login: admincommunik','103.120.51.155','2025-07-06 13:00:49'),
(95,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-07 08:45:35'),
(96,4,'login','Admin login: admincommunik','94.207.76.196','2025-07-07 13:34:48'),
(97,4,'login','Admin login: admincommunik','94.207.76.196','2025-07-08 05:07:38'),
(98,4,'login','Admin login: admincommunik','103.120.51.155','2025-07-08 06:02:41'),
(99,4,'login','Admin login: admincommunik','5.31.17.85','2025-07-08 07:13:12'),
(100,4,'login','Admin login: admincommunik','94.207.76.196','2025-07-08 10:00:57'),
(101,4,'login','Admin login: admincommunik','87.201.164.177','2025-07-09 10:12:29'),
(102,4,'login','Admin login: admincommunik','103.120.51.155','2025-07-11 10:24:10'),
(103,4,'login','Admin login: admincommunik','92.97.89.176','2025-07-12 15:39:27'),
(104,4,'login','Admin login: admincommunik','103.120.51.238','2025-07-13 11:30:59'),
(105,4,'login','Admin login: admincommunik','80.227.76.219','2025-07-16 05:53:15'),
(106,4,'login','Admin login: admincommunik','80.227.76.219','2025-07-16 07:41:17'),
(107,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-16 09:05:47'),
(108,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-16 10:14:45'),
(109,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-17 05:12:04'),
(110,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-17 05:17:07'),
(111,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-17 08:05:19'),
(112,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-17 10:22:58'),
(113,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-17 12:33:04'),
(114,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-18 06:36:55'),
(115,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-18 08:18:31'),
(116,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-18 14:00:06'),
(117,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-19 05:24:47'),
(118,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-19 10:17:38'),
(119,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-19 11:31:06'),
(120,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 05:16:29'),
(121,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 06:28:50'),
(122,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 06:31:03'),
(123,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 06:53:48'),
(124,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 07:18:52'),
(125,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 08:36:17'),
(126,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 08:56:51'),
(127,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 11:09:59'),
(128,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-21 11:26:48'),
(129,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 11:27:22'),
(130,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 11:48:15'),
(131,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 12:36:10'),
(132,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-21 13:46:32'),
(133,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-21 18:26:21'),
(134,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-21 18:50:28'),
(135,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-21 18:56:05'),
(136,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 05:07:24'),
(137,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 09:54:58'),
(138,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 10:16:37'),
(139,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 10:35:32'),
(140,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 10:36:26'),
(141,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 11:22:16'),
(142,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 12:10:24'),
(143,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 12:22:46'),
(144,4,'login','Admin login: admincommunik','94.206.195.108','2025-07-22 13:14:03'),
(145,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:12:46'),
(146,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:22:41'),
(147,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:25:39'),
(148,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:30:18'),
(149,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:45:16'),
(150,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:55:29'),
(151,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 05:57:43'),
(152,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 06:42:12'),
(153,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-23 10:07:20'),
(154,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 10:48:52'),
(155,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-23 10:51:53'),
(156,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 10:54:52'),
(157,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 11:06:44'),
(158,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 11:16:48'),
(159,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 11:35:38'),
(160,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 11:39:17'),
(161,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-23 11:49:41'),
(162,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-24 10:23:20'),
(163,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-24 11:17:18'),
(164,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-24 14:13:57'),
(165,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-25 06:24:39'),
(166,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-26 06:19:37'),
(167,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 07:06:47'),
(168,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 07:40:36'),
(169,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 08:59:02'),
(170,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 09:54:02'),
(171,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 09:56:45'),
(172,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 10:00:50'),
(173,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 10:02:29'),
(174,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 10:03:37'),
(175,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 10:13:54'),
(176,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 10:20:24'),
(177,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-26 11:52:58'),
(178,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-26 14:03:22'),
(179,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:08:16'),
(180,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:11:07'),
(181,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:15:05'),
(182,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:19:01'),
(183,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:21:07'),
(184,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 15:42:52'),
(185,4,'login','Admin login: admincommunik','103.120.51.196','2025-07-26 16:23:57'),
(186,4,'login','Admin login: admincommunik','5.30.211.99','2025-07-28 05:02:16'),
(187,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 05:22:30'),
(188,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 06:06:06'),
(189,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 06:22:06'),
(190,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-28 08:15:52'),
(191,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 10:29:01'),
(192,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 11:20:16'),
(193,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 12:01:52'),
(194,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 12:19:05'),
(195,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-28 12:22:48'),
(196,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-28 12:46:20'),
(197,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-28 14:50:09'),
(198,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-29 06:47:41'),
(199,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-29 07:14:43'),
(200,4,'login','Admin login: admincommunik','94.205.216.124','2025-07-29 11:52:16'),
(201,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-29 16:27:04'),
(202,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-30 04:02:47'),
(203,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-30 09:58:33'),
(204,4,'login','Admin login: admincommunik','87.200.94.253','2025-07-31 09:29:10'),
(205,4,'login','Admin login: admincommunik','103.120.51.228','2025-07-31 17:06:59'),
(206,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-01 07:52:49'),
(207,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-02 07:06:49'),
(208,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-02 07:24:13'),
(209,4,'login','Admin login: admincommunik','92.97.161.216','2025-08-02 07:48:41'),
(210,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-02 14:21:42'),
(211,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-03 10:44:39'),
(212,4,'login','Admin login: admincommunik','103.120.51.228','2025-08-03 10:46:57'),
(213,4,'login','Admin login: admincommunik','103.120.51.138','2025-08-04 02:14:24'),
(214,4,'login','Admin login: admincommunik','103.120.51.138','2025-08-04 02:15:55'),
(215,4,'login','Admin login: admincommunik','87.200.91.138','2025-08-04 15:09:38'),
(216,4,'login','Admin login: admincommunik','5.30.209.180','2025-08-06 13:31:45'),
(217,4,'login','Admin login: admincommunik','87.200.91.138','2025-08-09 06:32:52'),
(218,4,'login','Admin login: admincommunik','87.200.91.138','2025-08-11 06:07:48'),
(219,4,'login','Admin login: admincommunik','103.120.51.146','2025-08-11 16:44:23'),
(220,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-12 07:08:08'),
(221,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-12 07:36:20'),
(222,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-12 11:11:56'),
(223,4,'login','Admin login: admincommunik','5.30.209.180','2025-08-12 12:35:57'),
(224,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-13 12:39:41'),
(225,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-14 08:00:50'),
(226,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 06:02:24'),
(227,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 06:06:39'),
(228,4,'login','Admin login: admincommunik','124.123.22.13','2025-08-18 07:16:56'),
(229,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 07:24:45'),
(230,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 09:46:33'),
(231,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 09:56:00'),
(232,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 10:51:12'),
(233,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 11:41:21'),
(234,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 11:42:50'),
(235,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 11:58:42'),
(236,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 13:16:44'),
(237,4,'login','Admin login: admincommunik','94.204.108.58','2025-08-18 13:18:52'),
(238,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-18 15:31:36'),
(239,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-19 07:54:44'),
(240,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-19 09:39:43'),
(241,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-19 10:02:11'),
(242,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-19 14:33:09'),
(243,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-22 07:41:04'),
(244,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-22 07:49:20'),
(245,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-22 07:58:14'),
(246,5,'create','New admin created: Asif','127.0.0.1','2025-08-22 08:15:44'),
(247,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-22 08:50:15'),
(248,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-22 10:46:15'),
(249,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-22 12:07:00'),
(250,4,'login','Admin login: admincommunik','103.120.51.174','2025-08-22 16:11:49'),
(251,4,'login','Admin login: admincommunik','103.120.51.174','2025-08-22 16:39:26'),
(252,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-25 06:16:45'),
(253,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-25 07:50:08'),
(254,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-25 08:25:21'),
(255,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-25 08:29:38'),
(256,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-26 05:39:33'),
(257,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-26 06:45:19'),
(258,4,'login','Admin login: admincommunik','91.74.47.116','2025-08-26 13:19:01'),
(259,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-26 14:06:53'),
(260,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-26 14:27:20'),
(261,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-26 14:59:25'),
(262,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-26 15:03:18'),
(263,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-26 17:43:23'),
(264,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-27 08:14:47'),
(265,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-27 09:11:41'),
(266,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-27 09:13:52'),
(267,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-27 09:29:33'),
(268,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-27 09:59:54'),
(269,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-27 11:00:58'),
(270,4,'login','Admin login: admincommunik','103.120.51.170','2025-08-27 13:15:36'),
(271,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-27 13:27:50'),
(272,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 06:49:53'),
(273,5,'login','Admin login: Asif','94.206.192.189','2025-08-28 07:35:31'),
(274,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 07:41:27'),
(275,5,'login','Admin login: Asif','94.206.192.189','2025-08-28 07:49:38'),
(276,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-28 08:36:23'),
(277,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-28 10:18:48'),
(278,5,'login','Admin login: Asif','94.206.192.189','2025-08-28 11:45:17'),
(279,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 13:01:30'),
(280,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 13:08:00'),
(281,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 13:10:59'),
(282,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-28 14:14:15'),
(283,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-29 05:10:24'),
(284,5,'login','Admin login: Asif','94.206.192.189','2025-08-29 06:58:16'),
(285,5,'login','Admin login: Asif','94.206.192.189','2025-08-29 10:46:56'),
(286,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-29 11:58:21'),
(287,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-29 12:28:36'),
(288,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-29 13:05:47'),
(289,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-29 13:54:31'),
(290,4,'login','Admin login: admincommunik','27.59.61.93','2025-08-30 05:25:47'),
(291,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 06:57:10'),
(292,5,'login','Admin login: Asif','94.206.192.189','2025-08-30 06:57:49'),
(293,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 07:12:07'),
(294,5,'login','Admin login: Asif','94.206.192.189','2025-08-30 07:30:40'),
(295,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 07:58:24'),
(296,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 09:41:23'),
(297,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 10:34:17'),
(298,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 10:52:39'),
(299,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 11:01:14'),
(300,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 11:11:16'),
(301,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 11:24:06'),
(302,4,'login','Admin login: admincommunik','94.204.211.30','2025-08-30 11:34:00'),
(303,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 12:44:15'),
(304,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 12:57:41'),
(305,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 14:12:02'),
(306,4,'login','Admin login: admincommunik','94.206.192.189','2025-08-30 14:41:10'),
(307,4,'login','Admin login: admincommunik','103.120.51.145','2025-08-31 12:43:58'),
(308,4,'login','Admin login: admincommunik','92.97.161.216','2025-08-31 12:58:38'),
(309,5,'login','Admin login: Asif','94.206.192.189','2025-09-01 06:17:26'),
(310,5,'login','Admin login: Asif','94.206.192.189','2025-09-01 07:19:30'),
(311,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-01 07:24:31'),
(312,5,'login','Admin login: Asif','94.206.192.189','2025-09-01 10:22:37'),
(313,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-01 12:08:38'),
(314,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-01 12:15:53'),
(315,5,'login','Admin login: Asif','94.206.192.189','2025-09-01 12:33:37'),
(316,5,'login','Admin login: Asif','94.206.192.189','2025-09-02 05:55:02'),
(317,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-02 06:48:11'),
(318,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-02 08:14:13'),
(319,5,'login','Admin login: Asif','94.206.192.189','2025-09-02 09:34:32'),
(320,4,'login','Admin login: admincommunik','94.204.211.30','2025-09-02 10:27:18'),
(321,5,'login','Admin login: Asif','94.206.192.189','2025-09-02 13:29:30'),
(322,5,'login','Admin login: Asif','94.206.192.189','2025-09-03 08:55:57'),
(323,5,'login','Admin login: Asif','94.206.192.189','2025-09-03 09:59:10'),
(324,4,'login','Admin login: admincommunik','94.206.192.189','2025-09-03 11:18:44'),
(325,4,'login','Admin login: admincommunik','223.228.103.4','2025-09-05 04:43:38'),
(326,4,'login','Admin login: admincommunik','202.53.86.90','2025-09-05 10:32:10'),
(327,4,'login','Admin login: admincommunik','103.120.51.145','2025-09-05 16:50:46'),
(328,4,'login','Admin login: admincommunik','157.119.108.210','2025-09-06 09:07:41'),
(329,4,'login','Admin login: admincommunik','103.120.51.221','2025-09-06 16:53:55'),
(330,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 06:18:39'),
(331,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 07:23:17'),
(332,4,'login','Admin login: admincommunik','91.75.120.187','2025-09-08 08:20:07'),
(333,4,'login','Admin login: admincommunik','91.75.120.187','2025-09-08 08:37:12'),
(334,5,'login','Admin login: Asif','87.201.164.5','2025-09-08 08:41:07'),
(335,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 08:41:22'),
(336,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 08:59:22'),
(337,5,'login','Admin login: Asif','87.201.164.5','2025-09-08 09:37:16'),
(338,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 10:43:00'),
(339,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-08 12:11:29'),
(340,5,'login','Admin login: Asif','87.201.164.5','2025-09-08 12:25:51'),
(341,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-09 06:01:14'),
(342,5,'login','Admin login: Asif','87.201.164.5','2025-09-09 07:23:30'),
(343,4,'login','Admin login: admincommunik','91.75.120.187','2025-09-09 07:47:19'),
(344,4,'login','Admin login: admincommunik','91.75.120.187','2025-09-09 07:59:35'),
(345,4,'login','Admin login: admincommunik','87.201.164.5','2025-09-09 08:37:19'),
(346,5,'login','Admin login: Asif','87.201.164.5','2025-09-09 09:49:24'),
(347,5,'login','Admin login: Asif','87.201.164.5','2025-09-09 10:58:31'),
(348,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-09 14:15:01'),
(349,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-09 14:20:42'),
(350,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-10 05:43:06'),
(351,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-11 06:46:10'),
(352,5,'login','Admin login: Asif','5.30.177.182','2025-09-11 06:53:42'),
(353,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-11 07:58:43'),
(354,5,'login','Admin login: Asif','5.30.177.182','2025-09-11 08:43:18'),
(355,4,'login','Admin login: admincommunik','5.30.177.182','2025-09-11 13:05:22'),
(356,5,'login','Admin login: Asif','5.30.177.182','2025-09-11 13:30:38'),
(357,4,'login','Admin login: admincommunik','103.120.51.155','2025-09-12 16:25:51'),
(358,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-13 07:23:50'),
(359,5,'login','Admin login: Asif','5.30.141.238','2025-09-13 11:25:10'),
(360,4,'login','Admin login: admincommunik','103.120.51.206','2025-09-14 16:13:00'),
(361,4,'login','Admin login: admincommunik','103.120.51.206','2025-09-14 16:16:12'),
(362,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-15 15:22:49'),
(363,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-15 16:00:10'),
(364,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-16 06:53:06'),
(365,4,'login','Admin login: admincommunik','5.30.68.32','2025-09-16 07:04:47'),
(366,4,'login','Admin login: admincommunik','5.30.68.32','2025-09-16 07:37:03'),
(367,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-16 08:59:57'),
(368,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-16 09:38:57'),
(369,4,'login','Admin login: admincommunik','5.30.141.238','2025-09-16 12:23:08'),
(370,5,'login','Admin login: Asif','5.30.141.238','2025-09-16 12:24:55'),
(371,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-17 07:56:54'),
(372,5,'login','Admin login: Asif','87.200.69.204','2025-09-17 12:19:21'),
(373,5,'login','Admin login: Asif','87.200.69.204','2025-09-17 13:38:16'),
(374,5,'login','Admin login: Asif','87.200.69.204','2025-09-18 06:13:57'),
(375,5,'login','Admin login: Asif','87.200.69.204','2025-09-18 06:18:11'),
(376,5,'login','Admin login: Asif','87.200.69.204','2025-09-18 07:01:47'),
(377,5,'login','Admin login: Asif','87.200.69.204','2025-09-18 07:31:27'),
(378,5,'login','Admin login: Asif','87.200.69.204','2025-09-18 07:34:10'),
(379,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-18 11:53:16'),
(380,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-18 13:37:06'),
(381,5,'login','Admin login: Asif','87.200.69.204','2025-09-19 05:39:30'),
(382,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 06:33:33'),
(383,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 07:06:36'),
(384,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 07:33:46'),
(385,5,'login','Admin login: Asif','87.200.69.204','2025-09-19 09:48:47'),
(386,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 12:30:37'),
(387,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 12:51:08'),
(388,5,'login','Admin login: Asif','87.200.69.204','2025-09-19 12:55:53'),
(389,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 13:30:20'),
(390,5,'login','Admin login: Asif','87.200.69.204','2025-09-19 13:38:28'),
(391,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 13:40:14'),
(392,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-19 13:58:27'),
(393,5,'login','Admin login: Asif','87.200.69.204','2025-09-20 07:05:43'),
(394,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-20 07:53:24'),
(395,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-20 08:36:03'),
(396,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-20 08:53:52'),
(397,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-20 10:51:05'),
(398,5,'login','Admin login: Asif','87.200.69.204','2025-09-20 11:21:04'),
(399,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-20 11:44:29'),
(400,5,'login','Admin login: Asif','87.200.69.204','2025-09-20 13:36:33'),
(401,5,'login','Admin login: Asif','87.200.69.204','2025-09-22 07:06:50'),
(402,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-22 07:34:08'),
(403,4,'login','Admin login: admincommunik','124.123.22.13','2025-09-22 11:29:06'),
(404,4,'login','Admin login: admincommunik','124.123.22.13','2025-09-22 11:36:31'),
(405,4,'login','Admin login: admincommunik','94.207.125.250','2025-09-22 11:43:42'),
(406,5,'login','Admin login: Asif','87.200.69.204','2025-09-22 12:03:18'),
(407,5,'login','Admin login: Asif','87.200.69.204','2025-09-22 13:00:24'),
(408,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-23 05:47:38'),
(409,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-23 06:52:50'),
(410,5,'login','Admin login: Asif','87.200.69.204','2025-09-23 07:18:27'),
(411,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-23 11:25:39'),
(412,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-23 12:08:35'),
(413,4,'login','Admin login: admincommunik','87.200.69.204','2025-09-23 12:58:15'),
(414,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-24 07:00:16'),
(415,5,'login','Admin login: Asif','91.75.107.11','2025-09-24 07:57:58'),
(416,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-24 09:56:53'),
(417,5,'login','Admin login: Asif','91.75.107.11','2025-09-24 11:07:42'),
(418,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-25 06:37:10'),
(419,5,'login','Admin login: Asif','91.75.107.11','2025-09-25 06:47:48'),
(420,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-25 07:04:41'),
(421,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-25 07:05:24'),
(422,5,'login','Admin login: Asif','91.75.107.11','2025-09-25 09:47:23'),
(423,4,'login','Admin login: admincommunik','91.75.107.11','2025-09-25 09:48:25'),
(424,5,'login','Admin login: Asif','91.75.107.11','2025-09-25 11:24:12'),
(425,5,'login','Admin login: Asif','91.75.107.11','2025-09-26 07:19:21'),
(426,5,'login','Admin login: Asif','80.227.76.211','2025-09-26 09:48:51'),
(427,5,'login','Admin login: Asif','87.200.65.220','2025-09-26 12:09:07'),
(428,4,'login','Admin login: admincommunik','94.204.208.25','2025-09-27 06:25:47'),
(429,4,'login','Admin login: admincommunik','94.206.204.58','2025-09-27 09:51:08'),
(430,4,'login','Admin login: admincommunik','94.204.208.25','2025-09-27 13:33:35'),
(431,4,'login','Admin login: admincommunik','122.177.240.20','2025-09-29 04:33:48'),
(432,4,'login','Admin login: admincommunik','94.206.204.58','2025-10-02 05:15:34'),
(433,4,'login','Admin login: admincommunik','223.228.104.76','2025-10-06 05:17:23'),
(434,4,'login','Admin login: admincommunik','122.177.241.100','2025-10-09 07:09:45'),
(435,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-10 06:34:51'),
(436,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-10 08:14:04'),
(437,5,'login','Admin login: Asif','5.30.111.228','2025-10-10 12:05:51'),
(438,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 09:22:40'),
(439,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 09:28:47'),
(440,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 09:53:38'),
(441,5,'login','Admin login: Asif','5.30.111.228','2025-10-13 09:54:42'),
(442,5,'login','Admin login: Asif','5.30.111.228','2025-10-13 10:46:03'),
(443,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 13:08:11'),
(444,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 13:33:28'),
(445,4,'login','Admin login: admincommunik','5.30.111.228','2025-10-13 14:23:44'),
(446,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-15 07:55:08'),
(447,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-15 08:41:58'),
(448,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-15 09:36:03'),
(449,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-15 10:06:23'),
(450,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-16 07:12:51'),
(451,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-16 08:11:59'),
(452,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-16 08:41:51'),
(453,5,'login','Admin login: Asif','91.75.103.92','2025-10-16 10:39:31'),
(454,5,'login','Admin login: Asif','91.75.103.92','2025-10-16 12:02:02'),
(455,5,'login','Admin login: Asif','91.75.103.92','2025-10-17 12:21:03'),
(456,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-17 13:22:31'),
(457,5,'login','Admin login: Asif','91.75.103.92','2025-10-18 06:36:42'),
(458,5,'login','Admin login: Asif','91.75.103.92','2025-10-18 08:49:15'),
(459,5,'login','Admin login: Asif','91.75.103.92','2025-10-18 10:49:50'),
(460,5,'login','Admin login: Asif','91.75.103.92','2025-10-18 12:00:17'),
(461,5,'login','Admin login: Asif','91.75.103.92','2025-10-20 05:46:29'),
(462,4,'login','Admin login: admincommunik','91.75.103.92','2025-10-20 08:07:14'),
(463,5,'login','Admin login: Asif','91.75.108.249','2025-10-20 09:23:08'),
(464,4,'login','Admin login: admincommunik','91.75.108.249','2025-10-20 10:41:42'),
(465,5,'login','Admin login: Asif','91.75.108.249','2025-10-21 05:54:57'),
(466,4,'login','Admin login: admincommunik','87.200.171.3','2025-10-21 08:58:24'),
(467,4,'login','Admin login: admincommunik','87.200.171.3','2025-10-21 09:10:40'),
(468,4,'login','Admin login: admincommunik','87.200.171.3','2025-10-21 09:34:41'),
(469,4,'login','Admin login: admincommunik','87.200.171.3','2025-10-21 09:42:11'),
(470,4,'login','Admin login: admincommunik','87.200.171.3','2025-10-21 09:55:00'),
(471,3,'login','Admin login: superadmin','122.177.243.69','2025-10-22 15:30:38'),
(472,3,'update','Admin updated: superadmin','122.177.243.69','2025-10-22 15:30:44'),
(473,3,'login','Admin login: superadmin','122.177.243.69','2025-10-22 15:32:09'),
(474,3,'update','Admin updated: superadmin','122.177.243.69','2025-10-22 15:34:06'),
(475,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 15:34:40'),
(476,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 15:36:07'),
(477,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 15:36:24'),
(478,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 15:41:13'),
(479,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 15:42:36'),
(480,4,'login','Admin login: admincommunik','122.177.243.69','2025-10-22 17:25:03'),
(481,4,'update','Admin updated: admincommunik (email changed from communikadmin@communikmarketing.com)','122.177.243.69','2025-10-22 17:29:20'),
(482,3,'update','Admin updated: superadmin (email changed from superadmin@communikmarketing.com)','122.177.243.69','2025-10-22 17:29:31'),
(483,4,'login','Admin login: admincommunik','103.120.51.145','2025-10-22 17:38:08'),
(484,4,'login','Admin login: admincommunik','122.177.243.87','2025-10-27 13:33:57'),
(485,4,'login','Admin login: admincommunik','122.177.240.189','2025-10-30 14:11:42'),
(486,4,'login','Admin login: admincommunik','122.177.245.123','2025-12-02 10:42:57'),
(487,4,'login','Admin login: admincommunik','122.177.243.208','2025-12-16 14:43:37'),
(488,4,'login','Admin login: admincommunik','49.204.212.119','2025-12-16 15:52:13'),
(489,4,'login','Admin login: admincommunik','122.177.243.208','2025-12-17 07:29:53'),
(490,4,'login','Admin login: admincommunik','122.177.243.44','2026-02-03 05:42:57'),
(491,4,'login','Admin login: admincommunik','122.177.244.46','2026-04-08 06:05:51'),
(492,4,'login','Admin login: admincommunik','122.177.240.249','2026-05-17 09:27:27'),
(493,4,'login','Admin login: admincommunik','122.177.240.249','2026-05-17 09:45:53'),
(494,4,'login','Admin login: admincommunik','122.177.240.166','2026-05-18 05:23:43'),
(495,4,'login','Admin login: admincommunik','122.177.244.199','2026-05-21 07:28:40'),
(496,4,'login','Admin login: admincommunik','122.177.244.199','2026-05-21 07:58:27'),
(497,4,'login','Admin login: admincommunik','122.177.244.199','2026-05-21 08:06:42');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_settings`
--

DROP TABLE IF EXISTS `backup_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `retention_days` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_settings`
--

LOCK TABLES `backup_settings` WRITE;
/*!40000 ALTER TABLE `backup_settings` DISABLE KEYS */;
INSERT INTO `backup_settings` VALUES
(1,'daily',30,'2025-06-09 14:45:41','2025-06-09 14:45:41');
/*!40000 ALTER TABLE `backup_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `parsed_resume_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`candidate_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidates`
--

LOCK TABLES `candidates` WRITE;
/*!40000 ALTER TABLE `candidates` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `claim_approvals`
--

DROP TABLE IF EXISTS `claim_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `claim_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `approval_level` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `claim_id` (`claim_id`),
  KEY `approver_id` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `claim_approvals`
--

LOCK TABLES `claim_approvals` WRITE;
/*!40000 ALTER TABLE `claim_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `claim_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `claim_details`
--

DROP TABLE IF EXISTS `claim_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `claim_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_id` int(11) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `expense_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `claim_id` (`claim_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `claim_details`
--

LOCK TABLES `claim_details` WRITE;
/*!40000 ALTER TABLE `claim_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `claim_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `claims`
--

DROP TABLE IF EXISTS `claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `claim_type` varchar(50) DEFAULT NULL,
  `claim_category` varchar(50) DEFAULT NULL,
  `claim_amount` decimal(10,2) DEFAULT NULL,
  `claim_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `claims`
--

LOCK TABLES `claims` WRITE;
/*!40000 ALTER TABLE `claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` VALUES
(1,'shruti','schavda684@rku.ac.in','test test test test','test test test testtesttesttesttesttest');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_heads`
--

DROP TABLE IF EXISTS `department_heads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_heads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(100) NOT NULL,
  `head_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `head_name` varchar(100) NOT NULL,
  `head_email` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`user_name`),
  KEY `head_id` (`head_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_heads`
--

LOCK TABLES `department_heads` WRITE;
/*!40000 ALTER TABLE `department_heads` DISABLE KEYS */;
INSERT INTO `department_heads` VALUES
(2,'HR Department',0,'CME0005','$2y$10$txwX3ecQFo5EY2xEGbEgNuGVb3PZbEznC0uRobHmxMRx2yR4bMzme','Manobala  Thathineni Sudharsanam','hr@communikmarketing.com',1),
(4,'Marketing Department',0,'CME0041','$2y$10$7qzFhBhTPnB0fWDVWnFgKucFgWPS8yS9NBEMF1liDdIQigK2ulySe','Bajaj Mehul  Assuda','mehul.bajaj@communikmarketing.com',3),
(5,'Operations Department',0,'CME0001','$2y$10$NGqakbVZN2JcaxoCkVs40.pgsdnqbYhBz079KR8EuV2uqKHxjhEui','Bashid Khan','bashid@communikmarketing.com',4),
(6,'Sales Department',0,'CME0003','$2y$10$VrbM1rz821gxFHWfUq4chuVXwkyFKCv8QaZxOu2IKuGHzRFCGem5K','Muhammad  Arslan','arslan@communikmarketing.com',2);
/*!40000 ALTER TABLE `department_heads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `department_head_role` varchar(50) DEFAULT NULL,
  `parent_department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `annual_leave_days` int(11) DEFAULT 30,
  PRIMARY KEY (`id`),
  KEY `fk_parent_department` (`parent_department_id`),
  CONSTRAINT `fk_parent_department` FOREIGN KEY (`parent_department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES
(1,'HR Department','HR_MANAGER',NULL,'2025-06-10 07:16:42',30),
(2,'Sales Department','HEAD_OF_SALES',1,'2025-06-10 07:16:42',30),
(3,'Marketing Department','MARKETING_HEAD',4,'2025-06-10 07:16:42',30),
(4,'Operations Department','OPERATIONS_MANAGER',1,'2025-06-10 07:16:42',30),
(5,'Accounts Department','Managing Director',4,'2025-07-21 06:54:58',30);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_assignments`
--

DROP TABLE IF EXISTS `document_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_assignments`
--

LOCK TABLES `document_assignments` WRITE;
/*!40000 ALTER TABLE `document_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_audit_log`
--

DROP TABLE IF EXISTS `document_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_audit_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `version_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `doc_id` (`doc_id`),
  KEY `emp_id` (`emp_id`),
  KEY `version_id` (`version_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_audit_log`
--

LOCK TABLES `document_audit_log` WRITE;
/*!40000 ALTER TABLE `document_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_uploads`
--

DROP TABLE IF EXISTS `document_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eid` (`eid`),
  CONSTRAINT `document_uploads_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `employees` (`eid`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_uploads`
--

LOCK TABLES `document_uploads` WRITE;
/*!40000 ALTER TABLE `document_uploads` DISABLE KEYS */;
INSERT INTO `document_uploads` VALUES
(2,'CME0046','ISE','passport','M Sithik_Passport.pdf','doc_uploads/20250828_CME0046_passport_68b01e5d9c2bd.pdf','2025-08-28 14:46:13','0000-00-00','',NULL,132604,'active',NULL),
(3,'CME0046','ISE','visa','Sithik E - Visa.pdf','doc_uploads/20250828_CME0046_visa_68b01eb5eadff.pdf','2025-08-28 14:47:41','0000-00-00','',NULL,274319,'active',NULL),
(4,'CME0047','ISE','passport','suhail attar_passport clear copy.pdf','doc_uploads/20250828_CME0047_passport_68b01fb24f092.pdf','2025-08-28 14:51:54','0000-00-00','',NULL,90829,'active',NULL),
(5,'CME0047','ISE','emirates_id','EID_Suhail_Attar.pdf','doc_uploads/20250828_CME0047_emirates_id_68b020053e961.pdf','2025-08-28 14:53:17','0000-00-00','',NULL,48332,'active',NULL),
(6,'CME0002','ISE','passport','Usman_Passport_Ft.pdf','doc_uploads/20250828_CME0002_passport_68b020635aef9.pdf','2025-08-28 14:54:51','0000-00-00','',NULL,90469,'active',NULL),
(7,'CME0043','ISE','visa','Suheb E-Visa.pdf','doc_uploads/20250828_CME0043_visa_68b021feb3179.pdf','2025-08-28 15:01:42','0000-00-00','',NULL,269991,'active',NULL),
(8,'CME0043','ISE','visa','Suheb E-Visa.pdf','doc_uploads/20250828_CME0043_visa_68b02278b3ed6.pdf','2025-08-28 15:03:44','0000-00-00','',NULL,269991,'active',NULL),
(9,'CME0048','ISE','emirates_id','PARFOOL KUMAR - EID 2027.pdf','doc_uploads/20250828_CME0048_emirates_id_68b02333b888c.pdf','2025-08-28 15:06:51','0000-00-00','',NULL,59452,'active',NULL),
(10,'CME0048','ISE','passport','Parfool Kumar - Passport Clear copy.pdf','doc_uploads/20250828_CME0048_passport_68b0235843f07.pdf','2025-08-28 15:07:28','0000-00-00','',NULL,177547,'active',NULL),
(11,'CME0045','ISE','emirates_id','Dhanush - Emirates Id.pdf','doc_uploads/20250828_CME0045_emirates_id_68b0244b79a7a.pdf','2025-08-28 15:11:31','0000-00-00','',NULL,275438,'active',NULL),
(12,'CME0045','ISE','passport','Dhanush M - passport ft.pdf','doc_uploads/20250828_CME0045_passport_68b024625ec06.pdf','2025-08-28 15:11:54','0000-00-00','',NULL,259208,'active',NULL),
(13,'CME0002','ISE','passport','Muhammad Usman Tassawar_Passport_Ft.pdf','doc_uploads/20250828_CME0002_passport_68b0543980679.pdf','2025-08-28 18:36:01','2027-09-12','Pakistan',NULL,90469,'active',NULL),
(14,'CME0002','ISE','passport','Muhammad Usman Tassawar_Passport_Ft.pdf','doc_uploads/20250925_CME0002_passport_68d4e6c5bdc26.pdf','2025-09-25 12:22:53','2027-09-12','Pakistan',NULL,90469,'active',NULL);
/*!40000 ALTER TABLE `document_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_versions` (
  `version_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`version_id`),
  KEY `doc_id` (`doc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_config`
--

DROP TABLE IF EXISTS `email_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT 587,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL DEFAULT 'HRMS System',
  `secure` enum('tls','ssl') NOT NULL DEFAULT 'tls',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_config`
--

LOCK TABLES `email_config` WRITE;
/*!40000 ALTER TABLE `email_config` DISABLE KEYS */;
INSERT INTO `email_config` VALUES
(1,'smtp.gmail.com',587,'gopal.singh1678@gmail.com','ntew vxar nqcf ekcr','gopal.singh1678@gmail.com','HRMS System','tls','2025-08-03 13:47:30','2025-08-03 14:29:43');
/*!40000 ALTER TABLE `email_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_type` enum('applicant','recommender','approver','hr','test') NOT NULL,
  `email_type` enum('leave_application','leave_recommendation','leave_approval','test') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `leave_id` (`leave_id`),
  KEY `recipient_email` (`recipient_email`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
INSERT INTO `email_logs` VALUES
(1,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:31:31','2025-08-03 16:31:31'),
(2,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:31:35','2025-08-03 16:31:35'),
(3,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:33:57','2025-08-03 16:33:57'),
(4,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:49:08','2025-08-03 16:49:08'),
(5,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:58:04','2025-08-03 16:58:04'),
(6,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:58:17','2025-08-03 16:58:17'),
(7,'hr@communikmarketing.com','applicant','leave_recommendation','Leave Recommendation - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 16:58:28','2025-08-03 16:58:28'),
(8,'hr@communikmarketing.com','applicant','leave_approval','Leave Approval - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 17:50:25','2025-08-03 17:50:25'),
(9,'asma.b@communikmarketing.com','recommender','leave_approval','Leave Approval - Manobala  Thathineni Sudharsanam (Approved)','sent',NULL,NULL,'2025-08-03 17:50:30','2025-08-03 17:50:30'),
(10,'asma.b@communikmarketing.com','applicant','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-03 17:50:39','2025-08-03 17:50:39'),
(11,'hr@communikmarketing.com','recommender','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-03 17:50:44','2025-08-03 17:50:44'),
(12,'hr@communikmarketing.com','recommender','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 07:58:58','2025-08-11 07:58:58'),
(13,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 07:59:01','2025-08-11 07:59:01'),
(14,'cv@communikmarketing.com','applicant','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 07:59:05','2025-08-11 07:59:05'),
(15,'hr@communikmarketing.com','recommender','leave_application','Leave Application - Ayesha Shamas (Sick Leave Half Pay)','sent',NULL,NULL,'2025-08-11 16:33:47','2025-08-11 16:33:47'),
(16,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Ayesha Shamas (Sick Leave Half Pay)','sent',NULL,NULL,'2025-08-11 16:33:51','2025-08-11 16:33:51'),
(17,'cv@communikmarketing.com','applicant','leave_application','Leave Application - Ayesha Shamas (Sick Leave Half Pay)','sent',NULL,NULL,'2025-08-11 16:33:54','2025-08-11 16:33:54'),
(18,'hr@communikmarketing.com','recommender','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 16:58:28','2025-08-11 16:58:28'),
(19,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 16:58:33','2025-08-11 16:58:33'),
(20,'cv@communikmarketing.com','applicant','leave_application','Leave Application - Ayesha Shamas (Sick Leave Full pay)','sent',NULL,NULL,'2025-08-11 16:58:37','2025-08-11 16:58:37'),
(21,'cv@communikmarketing.com','applicant','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-11 17:58:50','2025-08-11 17:58:50'),
(22,'hr@communikmarketing.com','recommender','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-11 17:58:53','2025-08-11 17:58:53'),
(23,'cv@communikmarketing.com','applicant','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-11 17:58:57','2025-08-11 17:58:57'),
(24,'hr@communikmarketing.com','recommender','leave_approval','Leave Approval -  (Approved)','sent',NULL,NULL,'2025-08-11 17:59:01','2025-08-11 17:59:01'),
(25,'sameer@communikmarketing.com','recommender','leave_application','Leave Application - Ali  Abbas  (Unpaid)','sent',NULL,NULL,'2025-08-16 09:28:07','2025-08-16 09:28:07'),
(26,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Ali  Abbas  (Unpaid)','sent',NULL,NULL,'2025-08-16 09:28:11','2025-08-16 09:28:11'),
(27,'ali.abbas@communikmarketing.com','applicant','leave_application','Leave Application - Ali  Abbas  (Unpaid)','sent',NULL,NULL,'2025-08-16 09:28:15','2025-08-16 09:28:15'),
(28,'sameer@communikmarketing.com','recommender','leave_application','Leave Application - Zahra  Wahab (Unpaid)','sent',NULL,NULL,'2025-09-03 11:21:42','2025-09-03 11:21:42'),
(29,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Zahra  Wahab (Unpaid)','sent',NULL,NULL,'2025-09-03 11:21:46','2025-09-03 11:21:46'),
(30,'zahra.w@communikmarketing.com','applicant','leave_application','Leave Application - Zahra  Wahab (Unpaid)','sent',NULL,NULL,'2025-09-03 11:21:49','2025-09-03 11:21:49'),
(31,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Muhammad Usman  Tassawar (Annual Leave after 12 Months completed)','sent',NULL,NULL,'2025-09-15 17:17:05','2025-09-15 17:17:05'),
(32,'usman.t@communikmarketing.com','recommender','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-09-24 06:31:27','2025-09-24 06:31:27'),
(33,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-09-24 06:31:34','2025-09-24 06:31:34'),
(34,'retention@communikmarketing.com','applicant','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-09-24 06:31:39','2025-09-24 06:31:39'),
(35,'hr@communikmarketing.com','recommender','leave_application','Leave Application - Ayesha Shamas (Annual Leave after 6 Months completed)','sent',NULL,NULL,'2025-09-25 09:06:28','2025-09-25 09:06:28'),
(36,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Ayesha Shamas (Annual Leave after 6 Months completed)','sent',NULL,NULL,'2025-09-25 09:06:33','2025-09-25 09:06:33'),
(37,'cv@communikmarketing.com','applicant','leave_application','Leave Application - Ayesha Shamas (Annual Leave after 6 Months completed)','sent',NULL,NULL,'2025-09-25 09:06:37','2025-09-25 09:06:37'),
(38,'sameer@communikmarketing.com','recommender','leave_application','Leave Application - Aman Ahmed (Annual Leave after 12 Months completed)','sent',NULL,NULL,'2025-10-08 14:13:14','2025-10-08 14:13:14'),
(39,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Aman Ahmed (Annual Leave after 12 Months completed)','sent',NULL,NULL,'2025-10-08 14:13:18','2025-10-08 14:13:18'),
(40,'aman@communikmarketing.com','applicant','leave_application','Leave Application - Aman Ahmed (Annual Leave after 12 Months completed)','sent',NULL,NULL,'2025-10-08 14:13:22','2025-10-08 14:13:22'),
(41,'usman.t@communikmarketing.com','recommender','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-10-17 07:03:10','2025-10-17 07:03:10'),
(42,'bashid@communikmarketing.com','approver','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-10-17 07:03:13','2025-10-17 07:03:13'),
(43,'retention@communikmarketing.com','applicant','leave_application','Leave Application - Shobana  M (Sick Leave Full pay)','sent',NULL,NULL,'2025-10-17 07:03:17','2025-10-17 07:03:17');
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_notification_settings`
--

DROP TABLE IF EXISTS `email_notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_application_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `leave_recommendation_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `leave_approval_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `notify_applicant` tinyint(1) NOT NULL DEFAULT 1,
  `notify_recommender` tinyint(1) NOT NULL DEFAULT 1,
  `notify_approver` tinyint(1) NOT NULL DEFAULT 1,
  `notify_hr` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_notification_settings`
--

LOCK TABLES `email_notification_settings` WRITE;
/*!40000 ALTER TABLE `email_notification_settings` DISABLE KEYS */;
INSERT INTO `email_notification_settings` VALUES
(1,1,1,1,1,1,1,1,'2025-08-03 13:47:30','2025-08-03 14:32:06');
/*!40000 ALTER TABLE `email_notification_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_settings`
--

DROP TABLE IF EXISTS `email_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(100) NOT NULL DEFAULT 'smtp.gmail.com',
  `smtp_user` varchar(100) NOT NULL DEFAULT '',
  `smtp_pass` varchar(100) NOT NULL DEFAULT '',
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_settings`
--

LOCK TABLES `email_settings` WRITE;
/*!40000 ALTER TABLE `email_settings` DISABLE KEYS */;
INSERT INTO `email_settings` VALUES
(1,'smtp.gmail.com','','',587,'2025-06-09 14:45:41','2025-06-09 14:45:41');
/*!40000 ALTER TABLE `email_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('leave_application','leave_recommendation','leave_approval','test') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_name` (`template_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
INSERT INTO `email_templates` VALUES
(1,'Leave Application Notification','leave_application','Leave Application - {employee_name} ({leave_type})','<html><head><style>body{font-family:Arial,sans-serif}.header{background-color:#007bff;color:white;padding:20px;text-align:center}.content{padding:20px}.details{background-color:#f8f9fa;padding:15px;margin:10px 0;border-radius:5px}.footer{background-color:#6c757d;color:white;padding:10px;text-align:center;font-size:12px}</style></head><body><div class=\"header\"><h2>Leave Application Notification</h2></div><div class=\"content\"><p>A new leave application has been submitted.</p><div class=\"details\"><h3>Leave Details:</h3><p><strong>Employee:</strong> {employee_name}</p><p><strong>Leave Type:</strong> {leave_type}</p><p><strong>Start Date:</strong> {start_date}</p><p><strong>End Date:</strong> {end_date}</p><p><strong>Total Days:</strong> {total_days}</p><p><strong>Reason:</strong> {reason}</p><p><strong>Application Date:</strong> {applied_at}</p></div><p>Please review and take appropriate action.</p></div><div class=\"footer\"><p>This is an automated notification from HRMS System</p></div></body></html>',1,'2025-08-03 13:47:30','2025-08-03 13:47:30'),
(2,'Leave Recommendation Update','leave_recommendation','Leave Recommendation - {employee_name} ({recommendation_status})','<html><head><style>body{font-family:Arial,sans-serif}.header{background-color:#ffc107;color:white;padding:20px;text-align:center}.content{padding:20px}.details{background-color:#f8f9fa;padding:15px;margin:10px 0;border-radius:5px}.footer{background-color:#6c757d;color:white;padding:10px;text-align:center;font-size:12px}</style></head><body><div class=\"header\"><h2>Leave Recommendation Update</h2></div><div class=\"content\"><p>The leave application has been reviewed by the recommender.</p><div class=\"details\"><h3>Leave Details:</h3><p><strong>Employee:</strong> {employee_name}</p><p><strong>Leave Type:</strong> {leave_type}</p><p><strong>Recommender:</strong> {recommender_name}</p><p><strong>Recommendation:</strong> <span style=\"color:#28a745;font-weight:bold\">{recommendation_status}</span></p><p><strong>Remarks:</strong> {recommender_remarks}</p></div></div><div class=\"footer\"><p>This is an automated notification from HRMS System</p></div></body></html>',1,'2025-08-03 13:47:30','2025-08-03 13:47:30'),
(3,'Leave Approval Decision','leave_approval','Leave Approval - {employee_name} ({approval_status})','<html><head><style>body{font-family:Arial,sans-serif}.header{background-color:#28a745;color:white;padding:20px;text-align:center}.content{padding:20px}.details{background-color:#f8f9fa;padding:15px;margin:10px 0;border-radius:5px}.footer{background-color:#6c757d;color:white;padding:10px;text-align:center;font-size:12px}</style></head><body><div class=\"header\"><h2>Leave Approval Decision</h2></div><div class=\"content\"><p>The leave application has been reviewed and a decision has been made.</p><div class=\"details\"><h3>Leave Details:</h3><p><strong>Employee:</strong> {employee_name}</p><p><strong>Leave Type:</strong> {leave_type}</p><p><strong>Approver:</strong> {approver_name}</p><p><strong>Decision:</strong> <span style=\"color:#28a745;font-weight:bold\">{approval_status}</span></p><p><strong>Remarks:</strong> {hr_remarks}</p></div></div><div class=\"footer\"><p>This is an automated notification from HRMS System</p></div></body></html>',1,'2025-08-03 13:47:30','2025-08-03 13:47:30'),
(4,'Test Email','test','HRMS Email Test - {test_time}','<html><head><style>body{font-family:Arial,sans-serif}.header{background-color:#007bff;color:white;padding:20px;text-align:center}.content{padding:20px}.footer{background-color:#6c757d;color:white;padding:10px;text-align:center;font-size:12px}</style></head><body><div class=\"header\"><h2>HRMS Email Test</h2></div><div class=\"content\"><p>This is a test email from your HRMS system.</p><p><strong>Test Time:</strong> {test_time}</p><p><strong>Email Configuration:</strong></p><ul><li>Host: {host}</li><li>Port: {port}</li><li>Username: {username}</li><li>From Email: {from_email}</li></ul><p>If you received this email, your email configuration is working correctly!</p></div><div class=\"footer\"><p>This is an automated test email from HRMS System</p></div></body></html>',1,'2025-08-03 13:47:30','2025-08-03 13:47:30');
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emp_login`
--

DROP TABLE IF EXISTS `emp_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `emp_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `emp_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_idx` (`email`),
  KEY `fk_emp_id` (`emp_id`),
  CONSTRAINT `emp_login_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`),
  CONSTRAINT `fk_emp_id` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_login`
--

LOCK TABLES `emp_login` WRITE;
/*!40000 ALTER TABLE `emp_login` DISABLE KEYS */;
INSERT INTO `emp_login` VALUES
(63,'md','bashid@communikmarketing.com','$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW','active','CME0001'),
(64,'usman.t','usman.t@communikmarketing.com','$2y$10$xChhjqdIBP93/eg7PKoEJeh6Gy0zyRjo5Ef2g3B3u48NQroeKW8CG','active','CME0002'),
(65,'arslan','arslan@communikmarketing.com','$2y$10$o6N6RBvoepjfKzUGB8/yoOK69Qp/4GoVK7/6VVDno5uiPAl/l1Xia','active','CME0003'),
(66,'sameer','sameer@communikmarketing.com','$2y$10$PTAovMtH95smAlLJH6yRrOjCDcHx7vcTSv6xsOd9I5IYHHD1aywpa','active','CME0004'),
(67,'hr','hr@communikmarketing.com','$2y$10$9e2MiXIAWCBtebcjcGIyjuFUinzmcM47IStHLlfzLwgXVCKtehYP2','active','CME0005'),
(68,'rehmat','rehmat@communikmarketing.com','$2y$10$DAc1h6FNqTG3cXzcl0qRVeJXeBH.J69yKNbanoD2iDIgw9M.mbloy','active','CME0006'),
(69,'yeakub.ali','yeakub.ali@communikmarketing.com','$2y$10$jMvf5bdapqyJXm4Pr//wk.WP8nXm50HdCU7e6pC.q8aFzo/4TMHTe','active','CME0007'),
(70,'ali.abbas','ali.abbas@communikmarketing.com','$2y$10$kKxTfqGieuWDE1Dhu7pefOxX2R8Ix1/dUsUm4sJhq/FL5j4HnCCO.','active','CME0008'),
(74,'rohit','rohit@communikmarketing.com','$2y$10$hjLy2Ap2YbbRoLl9aKzSHePSMfNsQcqF02hBUQW/ciq/CgBQN39am','active','CME0012'),
(75,'moazzam','moazzam@communikmarketing.com','$2y$10$pwklKSr6iJD4E/5Mb6s3R.ArrXXZy4gaKRgVs7ztV7iTgy9U3pQAa','active','CME0013'),
(76,'mohamed.azaam','mohamed.azaam@communikmarketing.com','$2y$10$a9WT7LInnoICtde..pAUQeBiABZ9rUm0aIGD.s2nSwdKhotOtw/lG','active','CME0014'),
(77,'asma.b','asma.b@communikmarketing.com','$2y$10$zihXisSN/S2tOw5.0QgyaOJLpne56auc/jQPZ5V44eEjvsMGQ8ar.','active','CME0015'),
(78,'cv','cv@communikmarketing.com','$2y$10$UIAVlKzKhX6wK78xqkIruefszjw10Xiz40Yw/blZK18dtGvk.HtEW','active','CME0016'),
(79,'rida','rida@communikmarketing.com','$2y$10$ccCY3lGNJ2n2NamMeRulc.ck3TIJ6P9vsbxwcfS34ykLKxGuezrGK','active','CME0017'),
(80,'nanda','nanda@communikmarketing.com','$2y$10$CW7CqOLDNKAP9u.2zjplje315B.YKOF3HWPbeXDkkc8f/F9JpeE8C','active','CME0018'),
(81,'jashan','jashan@communikmarketing.com','$2y$10$AXAP1qeADKSKo4V/KVCjvuBXN5XJLPnEPoDr.y7i8NoeOz8xT8q4W','active','CME0019'),
(82,'aman','aman@communikmarketing.com','$2y$10$D.CO4ci95dUcwvhJXw//vu1M09R/VfhGoThVoMKC2/z8YLUIa2mqG','active','CME0020'),
(83,'shumaila','shumaila@communikmarketing.com','$2y$10$Me6usfyDQa.tJsOONopEB.VUk0uaSHRui7uho2GhPzUU7rbID7Q1e','active','CME0021'),
(84,'muheet','muheet@communikmarketing.com','$2y$10$HnsWK/Zd/fU9S2T10f9IV.Xe7g2FFBvWrY2FtLOjezB7pbd8XbFJ6','active','CME0022'),
(85,'nauman','nauman@communikmarketing.com','$2y$10$/u/Kk4.3GWF2/.ZpmI9ZHunLf0mK/04bKMU4qy2xqzvN9IKTrbiiG','active','CME0023'),
(86,'ariba','ariba@communikmarketing.com','$2y$10$GqI3BP6PLsIPGD.lY4GQ.uAaCXoEC9y18chFYdlzaVryaDOR5Gm/a','active','CME0024'),
(87,'aizaz','aizaz@communikmarketing.com','$2y$10$mYQ0DoHlWalRATfClI/4V.pMHCQLVpyx8jFkVg5X57fiuO8A5q/IG','active','CME0025'),
(88,'saira','saira@communikmarketing.com','$2y$10$IMUnZmdzl29ffM0OJJiqM.HAsHd1dOaNMxY9CXSbiTulhQ.A27GYy','active','CME0026'),
(89,'shega','shega@communikmarketing.com','$2y$10$fH2WC5.M82o2zm6GWuJvxeQGTTie6qImr0p4MebyvsA5fy.5GN5E2','active','CME0027'),
(90,'muzammil','muzammil@communikmarketing.com','$2y$10$kD/xaVFEzoQ2qlYwtrtDq.D7NGBC.O5ymsAfby/bKRSOvDeRL0xSK','active','CME0028'),
(91,'zaminul','zaminul@communikmarketing.com','$2y$10$.PVhhmaohFcGhNH82lk7MeMvsw6JtNqpWUcWj/pO1XLlVHZVDuI5m','active','CME0029'),
(92,'sandeep','sandeep@communikmarketing.com','$2y$10$28bdpkLaMMTrHY06cTBIreLt4u.5nONSt0K8EwLUh8MH7ODIEQOKm','active','CME0030'),
(93,'ezzat','ezzat@communikmarketing.com','$2y$10$5.KglFy6CM3Hbv85Z8k7OOdA8gE/1NgtAY7CYpMNnRirXNF7co5Ee','active','CME0031'),
(94,'mukter','mukter@communikmarketing.com','$2y$10$y1ISfSRDmO9rlG5hh6OmPeVpv.7zDxyq7J/I19XHQlvK1AhALVrg.','active','CME0032'),
(95,'zahra','zahra.w@communikmarketing.com','$2y$10$NoAQSQu6/vjlD0iU6sHPy.iwmAT/wHwEcnEXTKZ/AkCK0XLkERT3q','active','CME0033'),
(96,'masrooq','masrooq@communikmarketing.com','$2y$10$RPigw4RPv1TV0nk6zhXPZO4YFtdmqMjKdbT1Rge1D8KmZN5dZMq82','active','CME0034'),
(97,'saumya','saumya@communikmarketing.com','$2y$10$dL63f1Teqhg/qw4CYAc/BOPKLBJv0AfFYOqMqnnn7y8qiIQov1HiW','active','CME0035'),
(98,'rana','rana@communikmarketing.com','$2y$10$m.0StZ8xHiIymkm4e0JaK.xi/1UtB8tgevtFp.PpLaN5YnxtQ6I.e','active','CME0036'),
(99,'naimat','naimat@communikmarketing.com','$2y$10$2gOE7frS8FVdWLo1pyKg.O86lRuvwhC0neMMMYUsnQU8THd3Yv.IK','active','CME0037'),
(100,'harry','harry@communikmarketing.com','$2y$10$5.W.RcCgVOJl8Rbz8kCceeXbiuTBBi2GvEWsyxgm6y1ceyUbk5zDm','active','CME0038'),
(101,'zohaib','zohaib@communikmarketing.com','$2y$10$YGSZ0Fr83VdyG140EnojpunysQsJ7itNyoxxizaQUMgc26CwMPHjG','active','CME0039'),
(102,'kalaikamal','kalaikamal@communikmarketing.com','$2y$10$5u3tlx6O8r5ViFKGKc7y7OmBQe/E/RQTRo9.eeTHq9T7ipm8uUmNG','active','CME0040'),
(103,'mehul.bajaj','mehul.bajaj@communikmarketing.com','$2y$10$LLMM2B5leskb9M8ZvPRdTe4YR3eEK1AOwFxXryMJ9yrnCDjJszip2','active','CME0041'),
(104,'support','support@communikmarketing.com','$2y$10$FcTO8Wyt8TcpZb.sTb13fO0yOBwm8HqCNlHdXarnO7qPjrT3d9egG','active','CME0042'),
(105,'Shuaibsaifimail','Shuaibsaifimail@gmail.com','$2y$10$eJpNQxTDXQWEon2IY0ZbTO3hyTeVjTLl1hZibZ6/yN1hoXexRWaMm','active','CME0043'),
(106,'syedfida7908','syedfida7908@gmail.com','$2y$10$Q3HVIFQCAiaJRacdG8NVue.MEtbGET4qjdiZ6plN38iUmvRR2OVxO','active','CME0044'),
(107,'dhanushthara07','dhanushthara07@gmail.com','$2y$10$tJo2d0KKeg1Um8O.Ha.tJO4eRSfzTg.Ancw/aMh9NdzU/OyZqjvAq','active','CME0045'),
(108,'sithik2912','sithik2912@gail.com','$2y$10$/dSdQbDjweE80VhytkctU.504xgTVogT4UypshUP0rQQQqhB3Zd.q','active','CME0046'),
(109,'suhailattar7799','suhailattar7799@gmail.com','$2y$10$PmyqfvJXIIgxA893sN6A.e1FSlXCn9qLX.YW9vjo2s/0qcQXHLZji','active','CME0047'),
(110,'pksoni707070','pksoni707070@gmail.com','$2y$10$wet9kg7bNd8QdP3AMNKdyeMXs12vR9NFRjGVfM7dLXKR6voifVhFe','active','CME0048'),
(111,'mina.nessiem15','mina.nessiem15@gmail.com','$2y$10$xX3O3O/AM4kYS/./gKtMRObrH9DsmawPIcnwh6QfWvkuwU.UGoEJi','active','CME0049'),
(112,'lakshmipathi98978','lakshmipathi98978@gmail.com','$2y$10$SPjtl6f8D1nQlwnS8cpCmOan8Q6R77VIa31KiML0WfhaYP4Ce2whG','active','CME0050'),
(113,'dksonu75','dksonu75@gmail.com','$2y$10$LBEklaTMwWd6rDN8ckfW1e3s2ETOJfxaaRQ.ilZ9./vpusk1kWFL.','active','CME0051'),
(114,'wilkinston.fernandes','wilkinston.fernandes@gmail.com','$2y$10$lTXyj4gHb527KYTb6fcc5OwT/i9ch6o0tX0u6HKeY5sBr76Tr8YHS','active','CME0052'),
(115,'heam23','heam23@live.com','$2y$10$gUKdemUvh8tlmgaZD0nZ6e8GIwmJCYBmBo4SoQlKWQHhbWH2iOg.2','active','CME0053'),
(116,'Chimrannaseem2','Chimrannaseem2@gmail.com','$2y$10$g5jETUHCDIBTkDBgRWHWKOK3i35Q9ynADwd92t0nduP4/A1dbodGu','active','CME0054'),
(117,'dilan','dilan@communikmarketing.com','$2y$10$Y.moVlGKkdq7tmgVi/z/5OcmPC2H26M8sG0zyMASc8fxbyWXiaGg6','active','CME0055'),
(118,'aishasalman460','aishasalman460@gmail.com','$2y$10$6JzxPb0lAsTzMfsCjswneeJayH9X1pqrvCda4ZWL430byK/2qbfkW','active','CME0056'),
(119,'azmath707','azmath707@gmail.com','$2y$10$OTiqeO61s7wf32qvmnnmF.2RJixh4A8EmY2BrU.vGI6tehQ/gA6gO','active','CME0057'),
(120,'AFRAZSHAIKH2122','AFRAZSHAIKH2122@GMAIL.COM','$2y$10$FaaDIcTnlogR6I.VJbCYHe1TqiPt3AdW7JFGoOFtJtLRF7gItwueW','active','CME0058'),
(121,'Vikram','Vikram@communikmarketing.com','$2y$10$DVBYuaoWU.Dx4M9F1hDjueOttTGHiAiluvgoZGaWK1YAknUgEIS2K','active','CME0059'),
(122,'jayroy1005','jayroy1005@gmail.com','$2y$10$i8OmiOgd41x/htgoRuzhdekpvTP/RrP.qd5rTMWjB.FmAt4EFZpGO','active','CME0060'),
(123,'kensecatin','kensecatin@gmail.com','$2y$10$eY7p8fBF.WWZBOkCbILceOCL9PZRV6S1HmHu/d7t/VAL2aX3j8IhK','active','CME0061'),
(124,'Josephlalnunthanga042','Josephlalnunthanga042@gmail.com','$2y$10$ey4Bz2EuHdUcL3a5FZE8buN8x480xPH3Gsdv7oEbhEqSOubg9tOEy','active','CME0062'),
(125,'anilkourani89','anilkourani89@gmail.com','$2y$10$eFZheyEukz4dGv8tOs8CHuE2.jZTqOKAPwYHuQk6KslNGEmQCb2Ei','active','CME0063'),
(126,'Dildilsha44','Dildilsha44@Gmail.com','$2y$10$FEpAhJ3/.HRs0hhCdIBtteWFbzW2CVL7ZMSFr/uZASWCDQ36pZcjm','active','CME0064'),
(127,'balda.narsimhulu2011','balda.narsimhulu2011@gmail.com','$2y$10$euVvFpyjT4aekjNBTUcyK.fI0PUyTvIFUiKrCSZfDF8Bf6iCoR0yi','active','CME0065'),
(128,'sobitpaul7624','sobitpaul7624@gmail.com','$2y$10$X/exOkq5x9jy7cmA3UgEFObllz.Ir0CicBsSVIHiRcJuFLjtzIX7e','active','CME0066'),
(129,'cmary.christina','cmary.christina@gmail.com','$2y$10$lrfT.XvCr92hAL.ZOOWH.ezWS.PFKiTJt7bWvXUGVlKK8ndQzNxpS','active','CME0067'),
(130,'aminamuhammad089','aminamuhammad089@gmail.com','$2y$10$63q7vQBT6hF5hgmSRlGWOOmsaB.YIFNvtsO20ALGuvj6mNxfasVcS','active','CME0068'),
(131,'noumanshahid69','noumanshahid69@gmail.com','$2y$10$/mtWHoOMYMuw0/6yVOJRuOVkK50Q0MHS8o1g2sPRq.YIkIv6mqDEO','active','CME0069'),
(132,'azeemalone83','azeemalone83@gmail.com','$2y$10$rr2CVjnumTZHHVR2.Bz5n.gS0RWrhbUKduutjqAGO4udBu6YR/8au','active','CME0070'),
(133,'nimranovel','nimranovel@gmail.com','$2y$10$Hpy2rGqSKxmHDXyBvW8hpO9pM9u9qYNsrjZqTnHdUH0Zk9zn08EvG','active','CME0071'),
(134,'navoda.piya8899','navoda.piya8899@icloud.com','$2y$10$kyj8E6.c/e3jj.P/VET0X.pBHXnYqcQCi1XTaOedHeEVWmOqwzlR.','active','CME0072'),
(135,'m.n4sser','m.n4sser@gmail.com','$2y$10$usmWbvIGBwCOTgNQLBA3GOxmqTjiZ3HP3J1VSQJApdKANLK6YjQOe','active','CME0073'),
(136,'pragati31102000','pragati31102000@gmail.com','$2y$10$BvE8thRvvFzaWbm8QSJ0nuEetx/qJs8T5XN1Q0nktdgfIsIUIGFjO','active','CME0074'),
(137,'abrarahmmedcm269','abrarahmmedcm269@gmail.com','$2y$10$pJGsrUdj2NrG0zWGsGxhsOgBUUlrcDkRbQVAayUcmN7frUhZSG8yW','active','CME0075'),
(138,'shaikraheem849','shaikraheem849@gmail.com','$2y$10$NmrgWo4SxfwzjgnPL0BKbOSQs8FzfhLGA8clcGhRh7KnjStx6s8D6','active','CME0076'),
(139,'nameeratabassumhrn','nameeratabassumhrn@gmail.com','$2y$10$ejOmJFaa9g2xmIzVffvzheJEtU2xbNSbU1sfn0P9ZuMeAwPyyJc6K','active','CME0077'),
(140,'haroonaly78q','haroonaly78q@gmail.com','$2y$10$gVp0pPGnI9b.Y9/eqtPc5u9pbVKRYXCSffnwzRoV1u7bfm2seOJEC','active','CME0078'),
(141,'saket.gambhir14','saket.gambhir14@gmail.com','$2y$10$JKTo6E3nRq8/nkmCW9HRsekZEL0yjnieK7ZjuETVHoK.RPdZfzJOq','active','CME0079'),
(142,'ibrarashraf05','ibrarashraf05@gmail.com','$2y$10$/Niv8yJKZtj.f/1qpCVmUelqm4MDKtizPQr7hZVrg940n3e8ZLcjK','active','CME0080'),
(143,'khanayan.3343','khanayan.3343@gmail.com','$2y$10$VJ5VRUybvbt8HtTTGImiqOYauPfva93DNWRwN2Vk5PWb7IJRlhuEe','active','CME0081'),
(146,'superadmin','superadmin@communikmarketing.com','$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW','active','CME082'),
(164,'accounts','accounts@communikmarketing.com','$2y$10$159q2VTxgwBIX/.BH6B.5u7V69OxxmpLkggB9DOqEQDFx2csvtLuW','active','CME0098'),
(165,'retention','retention@communikmarketing.com','$2y$10$zyXmNknXU8UfOqjDNpD88.qlYOgOVBcPpeY2J/ffyTDqqDbrpOxWm','active','CME0099'),
(166,'Shimaabdalmagid6','Shimaabdalmagid6@gmail.com','$2y$10$01/N9DH4RAAXkpDhmLexde8W8eWdEmT5x12yHR97tmLg3LJiczulK','active','CME0100'),
(167,'thisiskhalid','thisiskhalid@gmail.com','$2y$10$soYq5YvttF.PXDoPvNffzuDtR2qH6/kF7lF5QX9pxFnEWkLGz2pvW','active','CME0101'),
(168,'tusharchopade9970','tusharchopade9970@gmail.com','$2y$10$sB6jnGK3Icpo4V0v2Om5N.z.APZnlGmd6RdXtDkQqjsF0PtBJE0wC','active','CME0102'),
(169,'ftoohweezy','ftoohweezy@gmail.com','$2y$10$0n2EaDWC5orYjYn0WvhVQubEn19lufltGzrrwQXJd7FCgpLyNtp/u','active','CME0103'),
(170,'asif87chougule','asif87chougule@gmail.com','$2y$10$88L37koCg9X47om9QLjQ5eU2iB749.yuFOULLulbcDceYQ7FDwC0q','active','CME0104'),
(171,'irfansarwar219','irfansarwar219@gmail.com','$2y$10$luVURS1QIccPEpIL20tCLuiqcFChuaLNRy73d.tjmPSzY1lpwnVfy','active','CME0105'),
(172,'aqibhamid1996','aqibhamid1996@gmail.com','$2y$10$T..W2ee8aeo7BAqt/HTjoO/rI8pyySe.fbwOF1lI.PK19EinleGNu','active','CME0106'),
(173,'fkhan.xyz','fkhan.xyz@gmail.com','$2y$10$THd6RPzFXNl.bfxXTq7e3uqJerDS/eFX19CO7epfFir9KCu2GmSsq','active','CME0107'),
(174,'malikimran123275','malikimran123275@gmail.com','$2y$10$jYKXpiHhr6PF72bBYnabZu17Ffurtx4uwRJ9w4RNFo7lrm60d/KNa','active','CME0108'),
(175,'ksyasmin786','ksyasmin786@gmail.com','$2y$10$ElQGz3q4RZ274rVp4/zqTuciKWAfb4VfKuuSfmjnqcuy0eA1eOpf.','active','CME0109'),
(176,'nimeshkhandelia','nimeshkhandelia@gmail.com','$2y$10$scPXFtSJLE4wmhT1kCuJS.A70A6t/9dNLxtLGD7kZ58t5aykDc9JG','active','CME0110'),
(177,'reemibrahim346434','reemibrahim346434@gmail.com','$2y$10$T8U9IsnXxcsCIyRQw3G64uPf7km5bOyTyRyU0uiX2udH4Htf11Ztq','active','CME0111'),
(178,'ibtisamshugerii','ibtisamshugerii@gmail.com','$2y$10$ukDgTEaJUi.cW8DRjyTER.fnON6YGbC/B1PYSb1otqchgTLJmZNPy','active','CME0112'),
(179,'Azzaabdalnabi123456789','Azzaabdalnabi123456789@gmail.com','$2y$10$.Ah9c/LTaCkf0hbbktXRyuul8HHI4feJk4VP3//SH087PqBAhZQ7i','active','CME0113'),
(180,'khalifadaffallah539','khalifadaffallah539@gmail.com','$2y$10$LMGOa.s3blldA14PiCgRJejraDk/fwJ5ZjpvxgQrME2bXpPrP46x6','active','CME0114'),
(181,'mghenzdarby02','mghenzdarby02@gmail.com','$2y$10$jLA8UTylh/hoHqCFJgNhcexJJ66zfm6I.5xWpQptd9F.h.vPV1bS.','active','CME0115'),
(182,'nizemuddinjewel8_eeo','nizemuddinjewel8_eeo@indeedemail.com','$2y$10$O/.mnXyRHkt1ed.DyETX4OeOFfz2RMs.Te1mHRtDmAPrRW8QGnv/K','active','CME0116'),
(183,'abufahim71','abufahim71@gmail.com','$2y$10$9FCWzPeK5FjSZZGZ2Rla5etkaZ39yI/UpN3SIBVd68cCX0APHmrMG','active','CME0117'),
(184,'arshkhan0304','arshkhan0304@gmail.com','$2y$10$IBhpLqNCdzGiY86GYbC2iOfoRU89Z7TYRowjTgvNBIlJZ8.Qxzpp.','active','CME0118'),
(185,'vivek.vibhava','vivek.vibhava@gmail.com','$2y$10$5Qsc6amwMq23PRUchr1ncubKgd0Skuy3XGNVU2.ZuTYv4od6NWbRy','active','CME0119'),
(186,'mudassaraatif794','mudassaraatif794@gmail.com','$2y$10$fq50YdAGjA.S9d2jHD0r8Osjj.v8We/0mQai4C3aC3PbcH2aw0Fui','active','CME0120'),
(187,'gill69602','gill69602@gmail.com','$2y$10$4PcvG3eTEkDwkYHC8Z7xIOSYdv/KqlM7Nk7gFm7rYdobFFxljGTaq','active','CME0121'),
(188,'greatroyal','greatroyal@gmail.com','$2y$10$s30A95iyp7hvcvKaewwEGOGZ41CGtJDt5K/vmzIqdvf.S6B0Brkle','active','CME0122'),
(189,'nehajabeenuae','nehajabeenuae@gmail.com','$2y$10$S2Mz7gm1bqVk2AfWn/jY0ezhcT8tUp90JuC6FqtVlBhFRWRrxTXVG','active','CME0123'),
(190,'mohammedmisbahuae','mohammedmisbahuae@gmail.com','$2y$10$WDHfWs4Nu0acrmg46bK/zuT3no5t5zAXht8ZIuiN3CsL7i3m/HH/m','active','CME0124'),
(191,'aqueebahmed25','aqueebahmed25@gmail.com','$2y$10$o0FarswEQuOZ.LEUKksQ/uzcuqDoK4jG0p.iX2JVw0WkBMqb0zfim','active','CME0125'),
(192,'jagan.jaganathandxb9','jagan.jaganathandxb9@gmail.com','$2y$10$4BtANbvF62tQriWPYeNEz.jJq0grtzScQyguLyPh8Y3Wff/7uGV76','active','CME0126'),
(193,'mohdzainey1','mohdzainey1@gmail.com','$2y$10$hYVVczKX1qe9QsfJdgWY4uA6SXJFfueEfnPbqrrh4Rd98WWBFsVS.','active','CME0127'),
(194,'asadmahar161','asadmahar161@gmail.com','$2y$10$EgWIU9liGVyFt1qNd939aOutHtjulP.V2s8/FptgMyhnZdPcBCKHO','active','CME0128'),
(195,'ravindranaik9944','ravindranaik9944@gmail.com','$2y$10$IrTvjWP4FabYwGYguzDwAOAz8/MzMa2gTfk61SDhSPdHxYcvrQ0zG','active','CME0129'),
(196,'riyazvsnl','riyazvsnl@gmail.com','$2y$10$15E99QNoNkKLGprc0h569OXEKHN3fBVfF/04t3UAGHPd10nkKQUBG','active','CME0130'),
(197,'m.azair4747','m.azair4747@gmail.com','$2y$10$KwDhWyu4OP7fJYw.WkcwLeQeeGaM1J5BXlvLzjSq9PfeFnjkqfmgS','active','CME0131'),
(198,'ishagulshad085','ishagulshad085@gmail.com','$2y$10$/5HBtk0gg9odYpDb836ane/Y7XU49jwCncuItS1vXKva52c3k/cDG','active','CME0132'),
(199,'sowjanyaaerukonda426','sowjanyaaerukonda426@gmail.com','$2y$10$CP1OLGwpkORvdOHqlBOWx.9hVcx5fqQ5FJkaVWm1qxffwtiGa/Z4e','active','CME0133'),
(200,'analeen.m.pascual','analeen.m.pascual@gmail.com','$2y$10$3KPHduU5vesQBCQU987cj.OMaPeZrFn03KzoX5/Hml8/NrDzb/nWe','active','CME0134'),
(201,'muhamedshahbasazeez','muhamedshahbasazeez@gmail.com','$2y$10$m2Kzmyd9XXduxM5LTjAibOIVGf2FDFikADgdTzmppsptCDmPPm7KC','active','CME0135'),
(202,'tabiqureshi541','tabiqureshi541@gmail.com','$2y$10$UAs7MJuEWmgWXBtcj/2EUOfI.nWiHXvNjPqmPHk3BoTnMoA.iAbEC','active','CME0136'),
(203,'apshashanka','apshashanka@gmail.com','$2y$10$vMFHzpGzfchDRpdwJJbf/eYzsLoNCHmdfqVD2bml10BTieVVFW9s2','active','CME0137'),
(204,'aziz_iiuc31','aziz_iiuc31@yahoo.com','$2y$10$16XVL6EKdENqr3MRJTsYGO5EOdd4B1UmRHYvoTO7xRovejd5Y1dza','active','CME0138'),
(205,'ms22615','ms22615@gmail.com','$2y$10$OxXNQlnwQGEqqksxCuDmx.h/EA8rLEqCH7d5ex9lXtVbcGY6QZfKe','active','CME0139'),
(206,'shukranfaridi16','shukranfaridi16@gmail.com','$2y$10$pFjAUH8ccXp.HVyXYySHIedsYuw1MTbaALmqh1zrbUsOzKZM9Y0zK','active','CME0140'),
(207,'mohammed.asim','mohammed.asim@communikmarketing.com','$2y$10$29Zoq4nTFJC/h4aqmBuABu3Eq6URdNUXotpw7M/Yu2PXcFQmUz/p2','active','CME0141'),
(208,'dipak.wagh02','dipak.wagh02@gmail.com','$2y$10$KJ8Y7KM.LpWvnZB8vK24CuOnvbIoeSmAEGxXFcsyVhwoDxwncovxK','active','CME0142'),
(209,'mohdshahjad368','mohdshahjad368@gmail.com','$2y$10$fLyTsE1DCgLyzZkeW1mnlOtzZZHqhtEoyEyixxEW2Rbp3H1VJRHqq','active','CME0143'),
(210,'arunainairbsf2yfc_txw','arunainairbsf2yfc_txw@indeedemail.com','$2y$10$NMUEkgtGtOWm4FUzKeU9OOx3Wxyq9y4STLChysVQJferUpoDpNl6C','active','CME0144');
/*!40000 ALTER TABLE `emp_login` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `keep_active_status` BEFORE UPDATE ON `emp_login` FOR EACH ROW BEGIN
    IF NEW.status <> 'active' THEN
        SET NEW.status = 'active';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `emp_passport`
--

DROP TABLE IF EXISTS `emp_passport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `emp_passport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `Remarks` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_passport`
--

LOCK TABLES `emp_passport` WRITE;
/*!40000 ALTER TABLE `emp_passport` DISABLE KEYS */;
INSERT INTO `emp_passport` VALUES
(19,0,'a8745d74d','Diplomatic','2015-10-10','2030-11-12','India','pune','pune','puyne','pune','test',''),
(20,0,'a8745d74d','Diplomatic','2015-10-10','2030-11-12','India','pune','pune','puyne','pune','test',''),
(21,0,'a8745d74d','Diplomatic','2015-10-10','2030-11-12','India','pune','pune','puyne','pune','test',''),
(22,0,'7777777','Diplomatic','2025-01-29','2025-02-13','Afghanistan','af','af','af','af','af','');
/*!40000 ALTER TABLE `emp_passport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_appraisals`
--

DROP TABLE IF EXISTS `employee_appraisals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_appraisals` (
  `appraisal_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `period_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Self_Submitted','HOD_Reviewed','HR_Reviewed','Completed') DEFAULT 'Pending',
  `final_rating` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`appraisal_id`),
  KEY `employee_id` (`employee_id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_appraisals`
--

LOCK TABLES `employee_appraisals` WRITE;
/*!40000 ALTER TABLE `employee_appraisals` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_appraisals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_attendance`
--

DROP TABLE IF EXISTS `employee_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_attendance`
--

LOCK TABLES `employee_attendance` WRITE;
/*!40000 ALTER TABLE `employee_attendance` DISABLE KEYS */;
INSERT INTO `employee_attendance` VALUES
(1,'edi','gop','ads','sdf','sadf','sdafkj','jkl','jkl','','0000-00-00','0000-00-00','0000-00-00',0,0,0,0,0,0,0,0.00,'2025-04-28 08:18:08','2025-04-28 08:17:59','2025-04-28 08:18:08');
/*!40000 ALTER TABLE `employee_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(10) NOT NULL,
  `template_id` int(11) NOT NULL,
  `version` int(11) DEFAULT 1,
  `document_status` enum('pending','signed','rejected') DEFAULT 'pending',
  `file_path` varchar(255) DEFAULT NULL,
  `signature_type` enum('draw','type','both') DEFAULT NULL,
  `signature_data` text DEFAULT NULL,
  `signature_image` text DEFAULT NULL,
  `signed_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`doc_id`),
  KEY `emp_id` (`emp_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_documents`
--

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_family`
--

DROP TABLE IF EXISTS `employee_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_family` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `current_address` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eid` (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_family`
--

LOCK TABLES `employee_family` WRITE;
/*!40000 ALTER TABLE `employee_family` DISABLE KEYS */;
INSERT INTO `employee_family` VALUES
(1,'U03','Moni Singh','1986-09-10','Indian','O+','Female','Teacher','Spouse',NULL,NULL,NULL,'No',NULL,NULL,NULL,NULL,NULL,NULL),
(2,'U03','Sanjhi','2015-09-05','indian','O+','Female','Child','Daughter',NULL,NULL,NULL,'No',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `employee_family` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_goals`
--

DROP TABLE IF EXISTS `employee_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `goal_description` text NOT NULL,
  `target_date` date NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `progress` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_goals`
--

LOCK TABLES `employee_goals` WRITE;
/*!40000 ALTER TABLE `employee_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_leave_balance`
--

DROP TABLE IF EXISTS `employee_leave_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_leave_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(10) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `balance` decimal(6,2) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `last_updated` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_leave_balance`
--

LOCK TABLES `employee_leave_balance` WRITE;
/*!40000 ALTER TABLE `employee_leave_balance` DISABLE KEYS */;
INSERT INTO `employee_leave_balance` VALUES
(1,'7','Paternity Leave',2.00,2025,NULL),
(2,'CME0042','Hajj and Umrah Leave',23.00,2025,NULL),
(3,'CME0042','Hajj and Umrah Leave',13.00,2025,NULL),
(4,'0','Hajj and Umrah Leave',-44.00,2025,NULL),
(5,'0','Hajj and Umrah Leave',-121.00,2025,NULL),
(6,'78','Sick Leave Full pay',42.00,2025,NULL),
(7,'70','Unpaid',25.00,2025,NULL),
(8,'92','Annual Leave after 6 Months completed',8.00,2025,NULL),
(9,'64','Annual Leave after 12 Months completed',-1.00,2025,NULL),
(10,'165','Sick Leave Full pay',44.00,2025,NULL);
/*!40000 ALTER TABLE `employee_leave_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_onboarding`
--

DROP TABLE IF EXISTS `employee_onboarding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_onboarding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_onboarding_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_onboarding`
--

LOCK TABLES `employee_onboarding` WRITE;
/*!40000 ALTER TABLE `employee_onboarding` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_onboarding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_roles_backup`
--

DROP TABLE IF EXISTS `employee_roles_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_roles_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `eid` varchar(10) NOT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_roles_backup`
--

LOCK TABLES `employee_roles_backup` WRITE;
/*!40000 ALTER TABLE `employee_roles_backup` DISABLE KEYS */;
INSERT INTO `employee_roles_backup` VALUES
(63,'CME0001','HOD'),
(64,'CME0002','user'),
(65,'CME0003','user'),
(66,'CME0004','user'),
(67,'CME0005','hr'),
(68,'CME0006','user'),
(69,'CME0007','user'),
(70,'CME0008','user'),
(74,'CME0012','user'),
(75,'CME0013','user'),
(76,'CME0014','user'),
(77,'CME0015','user'),
(78,'CME0016','user'),
(79,'CME0017','user'),
(80,'CME0018','user'),
(81,'CME0019','user'),
(82,'CME0020','user'),
(83,'CME0021','user'),
(84,'CME0022','user'),
(85,'CME0023','user'),
(86,'CME0024','user'),
(87,'CME0025','user'),
(88,'CME0026','user'),
(89,'CME0027','user'),
(90,'CME0028','user'),
(91,'CME0029','user'),
(92,'CME0030','user'),
(93,'CME0031','user'),
(94,'CME0032','user'),
(95,'CME0033','user'),
(96,'CME0034','user'),
(97,'CME0035','user'),
(98,'CME0036','user'),
(99,'CME0037','user'),
(100,'CME0038','user'),
(101,'CME0039','user'),
(102,'CME0040','user'),
(103,'CME0041','user'),
(104,'CME0042','user'),
(105,'CME0043','user'),
(106,'CME0044','user'),
(107,'CME0045','user'),
(108,'CME0046','user'),
(109,'CME0047','user'),
(110,'CME0048','user'),
(111,'CME0049','user'),
(112,'CME0050','user'),
(113,'CME0051','user'),
(114,'CME0052','user'),
(115,'CME0053','user'),
(116,'CME0054','user'),
(117,'CME0055','user'),
(118,'CME0056','user'),
(119,'CME0057','user'),
(120,'CME0058','user'),
(121,'CME0059','user'),
(122,'CME0060','user'),
(123,'CME0061','user'),
(124,'CME0062','user'),
(125,'CME0063','user'),
(126,'CME0064','user'),
(127,'CME0065','user'),
(128,'CME0066','user'),
(129,'CME0067','user'),
(130,'CME0068','user'),
(131,'CME0069','user'),
(132,'CME0070','user'),
(133,'CME0071','user'),
(134,'CME0072','user'),
(135,'CME0073','user'),
(136,'CME0074','user'),
(137,'CME0075','user'),
(138,'CME0076','user'),
(139,'CME0077','user'),
(140,'CME0078','user'),
(141,'CME0079','user'),
(142,'CME0080','user'),
(143,'CME0081','user'),
(146,'CME082','super_admin');
/*!40000 ALTER TABLE `employee_roles_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `workflow_level` int(11) DEFAULT 4,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eid` (`eid`),
  UNIQUE KEY `visa_number` (`visa_number`),
  UNIQUE KEY `passport_number` (`passport_number`),
  UNIQUE KEY `visa_number_2` (`visa_number`),
  UNIQUE KEY `unique_document_number` (`document_number`),
  KEY `fk_department` (`department_id`),
  KEY `role_id` (`role_id`),
  KEY `fk_role_id` (`new_role_id`),
  FULLTEXT KEY `visa_doc` (`visa_doc`,`passport_doc`),
  CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_role_id` FOREIGN KEY (`new_role_id`) REFERENCES `roles` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES
(63,'CME0001','Abdul Bashid','Khan','Bashid Khan','md','bashid@communikmarketing.com','$2y$10$kVEw8em1DG5fW0goTPqYP.L/ViOfwNfdfeLk2V991OvKu0XVbmskC','1993-06-08','Male','Married','A+','0551762775','Dubai','',NULL,NULL,NULL,NULL,'active',0,NULL,'2018-02-01','Dubai','','','HOD',NULL,'Managing Director','','No','0000-00-00','','',NULL,'test',NULL,NULL,'201/2024/2214175',NULL,'2024-03-01','2026-02-28','V7732464','Regular','2021-12-05','2031-12-05','India','Mumbai','63_1746886577.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'68149857bb29868149857bb29b','2025-05-02 10:05:41',NULL,4,0,4),
(64,'CME0002','Muhammad Usman ','Tassawar','Muhammad Usman  Tassawar','usman.t','usman.t@communikmarketing.com','$2y$10$xChhjqdIBP93/eg7PKoEJeh6Gy0zyRjo5Ef2g3B3u48NQroeKW8CG','1987-08-29','Male','Married','A+','0543226604','Dubai','United Arab Emirates','Bachelor od commerce','2005-09-15','2008-09-15','University of Punjab','active',0,NULL,'2022-12-12','Duabi','','','user',NULL,'Operations Manager','CME0001','No',NULL,'',' MB296614618AE','CBD','1007160169','AE510230000001007160169 ',NULL,'20120232467274','Employement Visa','2025-07-08','2027-07-03','AU7894511','Ordinary','2022-09-13','2027-09-12','Pakistan','Lahore','uploads/profile_pics/profile_1746271544.jpg','uploads/documents/visa_1746271544.pdf','uploads/documents/passport_1746271544.jpeg',0,'127017152','2025-06-23','2027-06-22',NULL,NULL,'6815f8f4192ce6815f8f4192d0','2025-05-03 11:25:44',NULL,4,0,4),
(65,'CME0003','Muhammad ','Arslan','Muhammad  Arslan','arslan','arslan@communikmarketing.com','$2y$10$o6N6RBvoepjfKzUGB8/yoOK69Qp/4GoVK7/6VVDno5uiPAl/l1Xia','1997-11-09','Male','Married','A+','556402690','Dubai','United Arab Emirates','Master of Business Administration - MBA',NULL,NULL,NULL,'active',0,NULL,'2025-02-10','','','','HOD',NULL,'Sales Director','CME0001','',NULL,'','',NULL,'NA',NULL,NULL,'1234567',NULL,'2024-09-20','2026-09-19','FR1914162','Regular','2022-06-10','2027-06-09','Pakistan','Muzzafargrah','uploads/profile_pics/profile_1746273796.jpg','','uploads/documents/passport_1746273796.jpeg',0,NULL,NULL,NULL,NULL,NULL,'68160170d7c7068160170d7c73','2025-05-03 12:03:16',NULL,2,0,4),
(66,'CME0004','MD Sameer ','Alam','MD Sameer  Alam','sameer','sameer@communikmarketing.com','$2y$10$AsAiymDabZE4xh9Y9ybCIO0pizvyoZcvJVAKtbsOD7ZzXMIYpDTsa','1995-05-10','Male','Married','A+','523061867','Dubai','United Arab Emirates','Bachelor od commerce','2021-06-22','2023-06-23','Himalayan garhwal university ','active',0,NULL,'2025-07-07','','','','user',NULL,'Asst Sales Manager','CME0003','',NULL,'','MB293186334AE','EIB','3708450432501','AE820340003708450432501',NULL,'20220252164875','Employement Visa','2025-08-07','2027-07-07','P3723130','Regular','2016-07-11','2026-07-10','India','Ranchi','66_1746452419.PNG','uploads/documents/visa_1746276943.pdf','',0,'127723390','2025-08-24','2027-08-23',NULL,NULL,'681610db51ca2681610db51ca5','2025-05-03 12:55:43',NULL,2,0,4),
(67,'CME0005','Manobala ','Thathineni Sudharsanam','Manobala  Thathineni Sudharsanam','hr','hr@communikmarketing.com','$2y$10$PC1WfGwhzvQE8cwJlux0kuAwD6O/DGT6qVnaY88ON35OLHBziF64W','1988-08-22','Male','Single','A-','505908852','Dubai, UAE','United Arab Emirates','Master of science',NULL,NULL,'Staffordshire universtry','active',0,NULL,'2025-02-03','','','','hr',NULL,'HR Manager','CME0001','',NULL,'','','NA','NA','NA','NA','20220242890859',NULL,'2024-12-09','2026-12-08','Z5904796','Regular','2020-03-02','2030-03-01','India','Chenni','uploads/profile_pics/profile_1746444543.jpg','uploads/documents/visa_1746444543.pdf','uploads/documents/passport_1746444543.pdf',0,NULL,NULL,NULL,NULL,NULL,'68189f02aff3b68189f02aff3e','2025-05-05 11:29:03',NULL,1,0,4),
(68,'CME0006','Rehmat','Ali','Rehmat  Ali','rehmat','rehmat@communikmarketing.com','$2y$10$DAc1h6FNqTG3cXzcl0qRVeJXeBH.J69yKNbanoD2iDIgw9M.mbloy','1997-05-17','Male','Married','A+','525446788','Home Address Kohat Pakistan\r\nLocal Dubai Address Al Muteena Deria Dubai','United Arab Emirates','Bachelor of Science in Software Engineering','2015-09-20','2019-09-18','Gecos university Peshwar','active',0,NULL,'2024-01-11','','','','user',NULL,'Asst. Operation','CME0002','',NULL,'','',NULL,'NA',NULL,NULL,'20220232948222',NULL,'2024-03-02','2026-02-01','WQ1813512','Regular','2023-04-23','2033-04-25','Pakistan','Kohat','6876a99014d92_CME0006.jpeg','uploads/documents/visa_1746445670.pdf','',0,NULL,NULL,NULL,NULL,NULL,'6818a253a12396818a253a123c','2025-05-05 11:47:50',NULL,4,0,4),
(69,'CME0007','MD Yeakub','Ali','MD YEAKUB  ALI','yeakub.ali','yeakub.ali@communikmarketing.com','$2y$10$jMvf5bdapqyJXm4Pr//wk.WP8nXm50HdCU7e6pC.q8aFzo/4TMHTe','1996-10-01','Male','Married','A+','558405225',NULL,'United Arab Emirates','BACHELOR OF ENVIRONMENTAL SCIENCE',NULL,NULL,'BANGLADESH','active',0,NULL,'2025-02-21','','','','user',NULL,'Sales Team Leader','CME0004','',NULL,'','',NULL,'NA',NULL,NULL,'20220232007785',NULL,'2023-08-07','2025-08-06','EF02966721','Regular','2020-02-16','2025-02-15','Bangladesh','Dhaka','uploads/profile_pics/profile_1746446433.jpg','uploads/documents/visa_1746446433.jpg','uploads/documents/passport_1746446433.jpg',0,NULL,NULL,NULL,NULL,NULL,'6818a5e5dc1b26818a5e5dc1b4','2025-05-05 12:00:33',NULL,2,0,4),
(70,'CME0008','Ali ','Abbas ','Ali  Abbas ','ali.abbas','ali.abbas@communikmarketing.com','$2y$10$kKxTfqGieuWDE1Dhu7pefOxX2R8Ix1/dUsUm4sJhq/FL5j4HnCCO.','1996-05-12','Male','Married','A+','521316706',NULL,'United Arab Emirates','High School Diploma:','2016-10-01','2018-10-01','SLAMIA GOVT ,ARTS & COMMERCE COLLEGE','active',0,NULL,'2025-02-20','','','','user',NULL,'Sales Team Leader','CME0004','',NULL,'','',NULL,'NA',NULL,NULL,'2012024381625','Emp Change','2024-05-06','2026-05-05','UL1016111','Ordinary','2022-09-23','2027-09-26','Pakistan','Nagar','uploads/profile_pics/profile_1746448764.jpeg','uploads/documents/visa_1746448764.pdf','uploads/documents/passport_1746448764.pdf',0,NULL,NULL,NULL,NULL,NULL,'6818ac5f084486818ac5f0844a','2025-05-05 12:39:24',NULL,2,0,4),
(74,'CME0012','Rohit','Singh','Rohit Singh','rohit','rohit@communikmarketing.com','$2y$10$hjLy2Ap2YbbRoLl9aKzSHePSMfNsQcqF02hBUQW/ciq/CgBQN39am','1991-05-01','Male','Married','A+','0559358325','Dubai','United Arab Emirates','Master in Bussiiness Adminstration','2010-01-01','2013-01-01','JIWAJI University,','inactive',0,NULL,'2025-02-04','Dubai','','','user',NULL,'Sales Team Leader','CME0004','1','2025-07-17','','',NULL,'NA',NULL,NULL,'20120252543044','Employement Visa','2025-04-07','2027-04-06','V4188140','Regular','2022-02-08','2032-02-07','India','Bhopal','uploads/profile_pics/profile_1746512559.png','uploads/documents/visa_1746512559.pdf','uploads/documents/passport_1746512559.pdf',0,NULL,NULL,NULL,NULL,NULL,'6819a814c8c9a6819a814c8c9c','2025-05-06 06:22:39',NULL,2,0,4),
(75,'CME0013','Muhammad ','Moazzam','Muhammad  Moazzam','moazzam','moazzam@communikmarketing.com','$2y$10$pwklKSr6iJD4E/5Mb6s3R.ArrXXZy4gaKRgVs7ztV7iTgy9U3pQAa','1998-04-01','Male','Single','A+','556182738','Dubai','United Arab Emirates','Bachelor in Arts','2019-01-01','2021-01-01','Allama Iqbal Open universitry Lahore','active',0,NULL,'2025-03-15','Dubai','','','user',NULL,'Sales Team Leader','CME0004','',NULL,'','',NULL,'NA',NULL,NULL,'20120242253059',NULL,'2024-03-29','2026-03-28',' CG0911361','Regular','2021-09-29','2026-09-28','Pakistan',NULL,'75_1746886859.jpg','uploads/documents/visa_1746606224.pdf','',0,NULL,NULL,NULL,NULL,NULL,'681b163563f53681b163563f54','2025-05-07 08:23:44',NULL,2,0,4),
(76,'CME0014','Mohammed ','Azaam','Mohammed  Azaam','mohamed.azaam','mohamed.azaam@communikmarketing.com','$2y$10$a9WT7LInnoICtde..pAUQeBiABZ9rUm0aIGD.s2nSwdKhotOtw/lG','1987-06-10','Male','Married','','522335188','Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-02-20','Dubai','','','user',NULL,'Sales Team Leader','CME0004','',NULL,'','','','NA','','','2022023263685','Emp Change','2023-09-18','2025-09-17','NB134112','Ordinary','2021-03-13','2029-03-13','Sri Lanka','Hatton','uploads/profile_pics/profile_1746609706.PNG','uploads/documents/visa_1746609706.pdf','uploads/documents/passport_1746609706.jpg',0,'',NULL,NULL,NULL,NULL,'681b2334baa95681b2334baa96','2025-05-07 09:21:46',NULL,2,0,4),
(77,'CME0015','Asma ','Begum','Asma  Begum','asma.b','asma.b@communikmarketing.com','$2y$10$qNWJ55NyKyWd.jspIHEpr.mRXgp1TwIcyZLIhVD5AEDmMKnHAwOgO','1991-09-30','Female','Married','','544027621','Dubai','Bangladesh','Bachelor of Business  Administration ','2011-01-01','2014-01-01','Eden Mohila College  Dhaka','active',0,NULL,'2025-03-17','Dubai','','','user',NULL,'Asst. Operation','CME0002','',NULL,'','','','NA','','','20120203235577','Spouse Visa','2024-12-10','2026-12-09','A00020151','Ordinary','2020-09-23','2030-09-22','Bangladesh','Dhaka','uploads/profile_pics/profile_1746610278.jpg','uploads/documents/visa_1746610278.pdf','uploads/documents/passport_1746610278.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','681b265f2c467681b265f2c468','2025-05-07 09:31:18',NULL,4,0,4),
(78,'CME0016','Ayesha','Shamas','Ayesha Shamas','cv','cv@communikmarketing.com','$2y$10$bJRzeN8A6nulMO.jFPMlVuH/k0LdZG5DgET41LJTPFHKgtoFe/ijG','1996-04-30','Female','Married','A+','547786485','ome Address Fort Abbas Dist Bhawalpur \r\nlocal address Al muteena Deira Dubai','United Arab Emirates','Bachelor of Arts','2020-01-01','2022-01-01','Punjab University Lahore','active',0,NULL,'2025-03-18','Dubai','','','user',NULL,NULL,'CME0005','',NULL,'','',NULL,'NA',NULL,NULL,'20220242194916',NULL,'2024-10-08','2026-10-07','KK5161171','Regular','2023-09-13','2028-09-11','Pakistan','Chakwal','uploads/profile_pics/profile_1746610879.PNG','uploads/documents/visa_1746610879.pdf','uploads/documents/passport_1746610879.pdf',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','681b289fc8c8d681b289fc8c8f','2025-05-07 09:41:19',NULL,1,0,4),
(79,'CME0017','Rida','Arooj','Rida Arooj','rida','rida@communikmarketing.com','$2y$10$ccCY3lGNJ2n2NamMeRulc.ck3TIJ6P9vsbxwcfS34ykLKxGuezrGK','1997-08-12','Female','Single','A+','522875919','Dubai','United Arab Emirates','Master in Mathmatics','2020-01-01','2022-01-01','Punjab University Lahore','active',0,NULL,'2024-05-15','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB267222610AE','Emirates NBD','1015916289501','AE150260001015916289501','Rida Arooj','20120242875281','Employement Visa','2025-06-30','2027-06-30',' NB1982431','Regular','2023-11-16','2033-11-14','Pakistan','Sheikupura','uploads/profile_pics/profile_1746613945.jpeg','uploads/documents/visa_1746613945.pdf','uploads/documents/passport_1746613945.pdf',0,'115354479','2025-06-19','2026-06-18','Not Pregnant','0000-00-00','681b34c56c43e681b34c56c43f','2025-05-07 10:32:25',NULL,2,0,4),
(80,'CME0018','Nanda','Kirana','Nanda Kirana','nanda','nanda@communikmarketing.com','$2y$10$CW7CqOLDNKAP9u.2zjplje315B.YKOF3HWPbeXDkkc8f/F9JpeE8C','1988-04-10','Male','Married','A+','545237850','Local Country addressvKanada Karaiakna India\r\nLocal Dubai Address Al satwa Deira Dubai','Pakistan','Diploma Engireeing trade','2005-07-01','2007-07-01','Govt IT College','active',0,NULL,'2024-04-22','','','','user',NULL,'Relationship officer','CME0012','',NULL,'','MB265881431AE','ADCB','13546324910001','AE380030013546324910001','Nanda Kirana','20120242720212','Employement Visa','2024-05-28','2026-05-27','U2947341','Regular','2020-11-04','2030-11-03','India','Bengaluru','uploads/profile_pics/profile_1746614964.jpeg','uploads/documents/visa_1746614964.pdf','uploads/documents/passport_1746614964.pdf',0,'114550423','2025-05-20','2026-05-21',NULL,NULL,'681b373b279e6681b373b279e7','2025-05-07 10:49:24',NULL,2,0,4),
(81,'CME0019','Jashan','Ralh','Jashan Ralh','jashan','jashan@communikmarketing.com','$2y$10$AXAP1qeADKSKo4V/KVCjvuBXN5XJLPnEPoDr.y7i8NoeOz8xT8q4W','2001-04-26','Male','Single','A+','521235604','Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2024-06-04','','','','user',NULL,'Relationship officer','CME0012','',NULL,'','ST248215753AE','ADCB','13820568920001','AE970030013820568920001',NULL,'20220242056990','Employement Visa','2024-08-05','2026-08-04','X5435485','Regular','2023-04-15','2033-04-14','India','Jalandhar','uploads/profile_pics/profile_1746615580.JPG','uploads/documents/visa_1746615580.pdf','uploads/documents/passport_1746615580.pdf',0,'116250984','2025-07-24','2026-07-23',NULL,NULL,'681b3b833cf8f681b3b833cf90','2025-05-07 10:59:40',NULL,2,0,4),
(82,'CME0020','Aman','Ahmed','Aman Ahmed','aman','aman@communikmarketing.com','$2y$10$D.CO4ci95dUcwvhJXw//vu1M09R/VfhGoThVoMKC2/z8YLUIa2mqG','1989-01-01','Male','Married','A+','568266677','Home country address Bena Jabar Knpur nagar Uttar Pardesh India\r\nLocalAddress Dubai Tower Baniyas Deirra Dubai','United Arab Emirates','intermediate','2012-01-01','2013-01-01','BISE khanpur','active',0,NULL,'2024-08-15','','','','user',NULL,'Sales Team Leader','CME0012','',NULL,'','MB272365479AE','ADCB','975920141102','AE570260000975920141102',NULL,'20220242273448','Employement Visa','2024-08-27','2026-08-26','Y6173281','Regular','2023-09-15','2033-09-14','India','Lucknow','uploads/profile_pics/profile_1746617725.JPG','uploads/documents/visa_1746617725.pdf','uploads/documents/passport_1746617725.pdf',0,'117236994','2025-08-22','2026-08-21',NULL,NULL,'681b42a0a74f8681b42a0a74f9','2025-05-07 11:35:25',NULL,2,0,4),
(83,'CME0021','Shumaila ','Imran','Shumaila  Imran','shumaila','shumaila@communikmarketing.com','$2y$10$tlyMX95d6bXDBfwrr0p8bOptRJzYkDrlrdYym6gbO2JueoU4SXZwu','1979-11-10','Female','Married','A+','527240470','Dubai','United Arab Emirates','Master in Arts','2016-01-01','2018-01-01','Allama Iqbal Open university ','active',0,NULL,'2024-05-15','','','','user',NULL,'Relationship officer','CME0012','',NULL,'','MB275195389AE','Al Hilal ','17240470001','AE360530000017240470001',NULL,'20220242548551','Employement Visa','2024-11-05','2026-11-04','BU1224432','Regular','2022-05-21','2027-05-20','Pakistan','Sahiwal','uploads/profile_pics/profile_1746620334.JPG','uploads/documents/visa_1746620334.pdf','uploads/documents/passport_1746620334.jpeg',0,'118483150','2025-10-04','2026-10-03','Not Pregnant','0000-00-00','681b4d990eb43681b4d990eb45','2025-05-07 12:18:54',NULL,2,0,4),
(84,'CME0022','Muheet','Mirza','Muheet Mirza','muheet','muheet@communikmarketing.com','$2y$10$HnsWK/Zd/fU9S2T10f9IV.Xe7g2FFBvWrY2FtLOjezB7pbd8XbFJ6','2000-11-05','Male','Married','','586118535','Dubai','United Arab Emirates',' Intermediate (Science) ','2019-01-01','2021-01-01','Government Boys Degree College, New Karachi','inactive',0,NULL,'2024-02-09','','','','user',NULL,'Relationship officer','CME0012','1','2025-07-03','','','Emirates NBD','1015904621801','AE450260001015904621801','Muheet Mirza','20220242647504','Employement Visa','2024-11-04','2026-11-03','CE2922262','Ordinary','2024-05-24','2029-05-23','Pakistan','Karachi ','uploads/profile_pics/profile_1746621014.jpeg','uploads/documents/visa_1746621014.jpeg','uploads/documents/passport_1746621014.pdf',0,'',NULL,NULL,NULL,NULL,'681b5089ce85c681b5089ce85d','2025-05-07 12:30:14',NULL,2,0,4),
(85,'CME0023','Muhammad ','Nauman','Muhammad  Nauman','nauman','nauman@communikmarketing.com','$2y$10$/u/Kk4.3GWF2/.ZpmI9ZHunLf0mK/04bKMU4qy2xqzvN9IKTrbiiG','1989-08-10','Male','Married','A+','543354996','Dubai','United Arab Emirates','Intermediate','2007-01-01','2009-01-01','BISE Lahore','active',0,NULL,'2024-01-11','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','MB295281127AE','Al Hilal','1335499601','AE090530000013354996001','Muhammad Nouman','20120247943535','Employement Visa','2024-07-25','2026-07-24','ES9953421','Regular','2021-07-07','2026-07-06','Pakistan','Lahore','uploads/profile_pics/profile_1746621712.jpg','uploads/documents/visa_1746621712.pdf','uploads/documents/passport_1746621712.pdf',0,'120090524','2025-11-23','2026-11-22',NULL,NULL,'681b52b126715681b52b126716','2025-05-07 12:41:52',NULL,2,0,4),
(86,'CME0024','Ariba ','Atif','Ariba  Atif','ariba','ariba@communikmarketing.com','$2y$10$GqI3BP6PLsIPGD.lY4GQ.uAaCXoEC9y18chFYdlzaVryaDOR5Gm/a','2001-07-22','Female','Married','A+','547007453','Dubai','United Arab Emirates','Intermediate','2016-01-01','2018-01-01','BISE Lahore','active',0,NULL,'2025-02-20','','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB296608827AE','Emirates NBD','0125921025801',' AE480260000125921025801',NULL,'20022070143695','Family Visa','2024-06-05','2026-06-04','BA1486951','Regular','2022-06-14','2027-06-13','Pakistan','Sheikhupura','uploads/profile_pics/profile_1746624243.JPG','','uploads/documents/passport_1746624243.jpeg',0,'125780217','2025-04-29','2027-04-30','Not Pregnant','0000-00-00','681b5d5ec24db681b5d5ec24dc','2025-05-07 13:24:03',NULL,2,0,4),
(87,'CME0025','Aizaz ','Ahmed Malik','Aizaz  Ahmed Malik','aizaz','aizaz@communikmarketing.com','$2y$10$mYQ0DoHlWalRATfClI/4V.pMHCQLVpyx8jFkVg5X57fiuO8A5q/IG','1999-03-03','Male','Married','A+','585539656','Dubai','United Arab Emirates',' Matriculation','2013-01-01','2015-01-01','Government comprehensive School Sialkot Pakistan','active',0,NULL,'2025-02-20','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB291060736AE','Emirates NBD','1015886155001','AE74 0260 0010 1588 6155 001',NULL,'20120252991259','Employement Visa','2025-06-05','2027-06-04','FZ0768323','Regular','2024-07-17','2029-07-16','Pakistan','Sailkot','uploads/profile_pics/profile_1746699137.jpeg','uploads/documents/visa_1746699137.jpg','uploads/documents/passport_1746699137.jpeg',0,'126612996','2025-07-24','2027-07-23',NULL,NULL,'681c81b69c8e4681c81b69c8e5','2025-05-08 10:12:17',NULL,2,0,4),
(88,'CME0026','Saira ','Javaid','Saira  Javaid','saira','saira@communikmarketing.com','$2y$10$IMUnZmdzl29ffM0OJJiqM.HAsHd1dOaNMxY9CXSbiTulhQ.A27GYy','1997-08-27','Female','Single','A+','568406994','Dubai','Pakistan','Master of Commerce','2015-10-01','2017-01-01','National college of bussiness adminstartion lahore','active',0,NULL,'2025-02-11','','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB285423164AE','FAB BANK','1901006824480001','AE350351901006824480001',NULL,'20120252636666','Employement Visa','2025-03-11','2027-03-10','A88692622','Regular','2021-01-14','2026-01-13','Pakistan','Lahore','uploads/profile_pics/profile_1746699839.jpeg','uploads/documents/visa_1746699839.pdf','uploads/documents/passport_1746699839.pdf',0,'123751949','2025-03-06','2027-03-05','Not Pregnant','0000-00-00','681c843e67797681c843e67798','2025-05-08 10:23:59',NULL,2,0,4),
(89,'CME0027','Shega',' Ameena Nazar','Shega  Ameena Nazar','shega','shega@communikmarketing.com','$2y$10$fH2WC5.M82o2zm6GWuJvxeQGTTie6qImr0p4MebyvsA5fy.5GN5E2','2022-05-20','Female','Single','A+','585025370','Dubai','United Arab Emirates','Bachelor of English','2020-01-01','2022-01-01','Calicut university india','active',0,NULL,'2025-02-19','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','ST256142896AE','FAB BANK','938649679485900001','AE950359386496794859001',NULL,'20120252373564','Employement Visa','2025-03-04','2027-03-03','W0884745','Regular','2022-05-20','2032-05-19','India','Cochin','uploads/profile_pics/profile_1746700927.jpeg','uploads/documents/visa_1746700927.pdf','uploads/documents/passport_1746700927.jpeg',0,'123380791','2025-02-25','2027-02-24','Not Pregnant','0000-00-00','681c88bfb1e44681c88bfb1e47','2025-05-08 10:42:07',NULL,2,0,4),
(90,'CME0028','Ahammed','Muzammil','Ahammed Muzammil','muzammil','muzammil@communikmarketing.com','$2y$10$kD/xaVFEzoQ2qlYwtrtDq.D7NGBC.O5ymsAfby/bKRSOvDeRL0xSK','2001-10-15','Male','Single','','566679691','Dubai','United Arab Emirates','Intermediate',NULL,NULL,'GHSS BEKUR','inactive',0,NULL,'2025-02-02','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','501202420000058','Emp Change','2024-01-20','2026-01-09','V2203812','Ordinary','2021-08-30','2031-08-29','India','kozhikode','uploads/profile_pics/profile_1746701616.jpeg','uploads/documents/visa_1746701616.jpeg','uploads/documents/passport_1746701616.jpeg',0,'',NULL,NULL,NULL,NULL,'681c8b743fce2681c8b743fce4','2025-05-08 10:53:36',NULL,2,0,4),
(91,'CME0029','MD ZAMINUL ','HAQUE','MD ZAMINUL  HAQUE','zaminul','zaminul@communikmarketing.com','$2y$10$.PVhhmaohFcGhNH82lk7MeMvsw6JtNqpWUcWj/pO1XLlVHZVDuI5m','1983-05-04','Male','Married','','555811746','Dubai','United Arab Emirates','M.A in Philosophy',NULL,'2004-01-01','National university.Dhaka','inactive',0,NULL,'2024-02-24','','','','user',NULL,'Relationship officer','CME0014','1','2025-08-28','','','','NA','','','000000000','Emp Change','2024-07-11','2026-07-10','AD3219862','Ordinary','2022-02-23','2032-02-22','Bangladesh','Dhaka','uploads/profile_pics/profile_1746702973.jpg','','uploads/documents/passport_1746702973.jpeg',0,'',NULL,NULL,NULL,NULL,'681c8d775989b681c8d775989d','2025-05-08 11:16:13',NULL,2,0,4),
(92,'CME0030','Sandeep','Krishna','Sandeep Krishna ','sandeep','sandeep@communikmarketing.com','$2y$10$28bdpkLaMMTrHY06cTBIreLt4u.5nONSt0K8EwLUh8MH7ODIEQOKm','0000-00-00','Male','Single','A+','0585863652','Dubai','United Arab Emirates','Diploma in commerce','2004-01-01','2005-01-01',NULL,'active',0,NULL,'2025-03-03','','','','user',NULL,'Relationship officer','CME0008','',NULL,'','ST257178796AE','Mashreq','019101752027','AE160330000019101752027',NULL,'20120252569309','Employement Visa','2025-04-02','2027-04-01','V3778057','Regular','2021-10-28','2031-10-27','India','hyderabad','uploads/profile_pics/profile_1746788978.jpeg','uploads/documents/visa_1746788978.pdf','uploads/documents/passport_1746788979.pdf',0,'124588425','2025-03-25','2027-03-26',NULL,NULL,'681de155919c7681de155919c8','2025-05-09 11:09:39',NULL,2,0,4),
(93,'CME0031','Mohamed Ezzat ','Aboelyazzed Eltalawy','Mohamed Ezzat  Aboelyazzed Eltalawy','ezzat','ezzat@communikmarketing.com','$2y$10$5.KglFy6CM3Hbv85Z8k7OOdA8gE/1NgtAY7CYpMNnRirXNF7co5Ee','1995-01-12','Male','Single','A+','581137798','Dubai','United Arab Emirates','Bachelor of Science','2015-01-01','2017-01-01','Tanta University','active',0,NULL,'2025-03-03','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','MB285920540AE','FAB BANK','1901006849807001','AE830351901006849807001',NULL,'20120252466146','Employement Visa','2025-03-24','2027-03-23','A39053980','Regular','2024-09-25','2031-09-24','Egypt',NULL,'uploads/profile_pics/profile_1746789888.jpeg','uploads/documents/visa_1746789888.pdf','uploads/documents/passport_1746789888.pdf',0,'123986824','2025-03-11','2027-03-10',NULL,NULL,'681de2d1e206c681de2d1e206d','2025-05-09 11:24:48',NULL,2,0,4),
(94,'CME0032','Mukter','Ahmed','Mukter Ahmed','mukter','mukter@communikmarketing.com','$2y$10$y1ISfSRDmO9rlG5hh6OmPeVpv.7zDxyq7J/I19XHQlvK1AhALVrg.','1993-05-05','Male','Single','','528727451','Dubai','United Arab Emirates','Bachelor of Business  Administration ','2019-01-01','2021-01-01','SOUTHERN UNIVERSITY DHAKA','active',0,NULL,'2025-03-07','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','20120247572072','Employement Visa','2024-02-29','2026-02-27','B00633419','Ordinary','2022-07-31','2032-07-30','Bangladesh','Dhaka','uploads/profile_pics/profile_1746791414.jpeg','uploads/documents/visa_1746791414.pdf','uploads/documents/passport_1746791414.pdf',0,'',NULL,NULL,NULL,NULL,'681dea392d136681dea392d138','2025-05-09 11:50:14',NULL,2,0,4),
(95,'CME0033','Zahra ','Wahab','Zahra  Wahab','zahra','zahra.w@communikmarketing.com','$2y$10$j0nF2PXykKIlnuSLvQqW/.Usp/5yYdIq0acTuyBm1ZKt.AB6HEBmW','1992-11-11','Female','Married','A+','552857547','Dubai','United Arab Emirates','Bachelor of Commerce','2011-01-01','2013-01-01',' Punjab University','inactive',0,NULL,'2025-03-26','','','','user',NULL,'Relationship officer','CME0008','1','2025-09-27','','ST258157468AE','Emirates NBD','125935407701','AE21 0260 0001 2593 5407 701',NULL,'20120252774906','Employement Visa','2025-05-05','2027-05-04',' BB6135701','Regular','2023-12-27','2028-12-25','Pakistan','Lahore','uploads/profile_pics/profile_1746792167.JPG','uploads/documents/visa_1746792167.pdf','uploads/documents/passport_1746792167.pdf',0,'125640435','2025-04-27','2027-04-28','Not Pregnant','0000-00-00','681ded145ac93681ded145ac94','2025-05-09 12:02:47',NULL,2,0,4),
(96,'CME0034','Mohammed Masrooq ','Munner','Mohammed Masrooq  Munner','masrooq','masrooq@communikmarketing.com','$2y$10$RPigw4RPv1TV0nk6zhXPZO4YFtdmqMjKdbT1Rge1D8KmZN5dZMq82','1993-09-26','Male','Single','','547002445','Dubai','United Arab Emirates','Bachelor of engineering','2014-01-11','2016-01-01','paavai engineering college','active',0,NULL,'2025-02-20','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','201202411400197271','Tourist','2024-05-24','2024-07-22','R4193679','Ordinary','2017-11-13','2027-11-12','India','kozhikode','uploads/profile_pics/profile_1746853294.JPG','uploads/documents/visa_1746853294.pdf','uploads/documents/passport_1746853294.jpeg',0,'',NULL,NULL,NULL,NULL,'681edbf63ce3a681edbf63ce3b','2025-05-10 05:01:34',NULL,2,0,4),
(97,'CME0035','Saumya','Silva','Saumya Silva','saumya','saumya@communikmarketing.com','$2y$10$dL63f1Teqhg/qw4CYAc/BOPKLBJv0AfFYOqMqnnn7y8qiIQov1HiW','1994-06-26','Female','Single','','505021007','Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-02-24','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','201202411400254323','Tourist','2024-06-02','2024-07-31','N8088119','Ordinary','2016-12-17','2028-12-17','Sri Lanka','Kalijabowila','uploads/profile_pics/profile_1746854194.JPG','uploads/documents/visa_1746854194.jpeg','uploads/documents/passport_1746854194.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','681edf5abecd6681edf5abecd7','2025-05-10 05:16:34',NULL,2,0,4),
(98,'CME0036','Rana Maanzar ','Hussain','Rana Maanzar  Hussain','rana','rana@communikmarketing.com','$2y$10$m.0StZ8xHiIymkm4e0JaK.xi/1UtB8tgevtFp.PpLaN5YnxtQ6I.e','1987-09-16','Male','Married','','543343207','Dubai','United Arab Emirates','Intermediate',NULL,NULL,'','inactive',0,NULL,'2025-02-24','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','000000','Employement Visa','2023-11-17','2025-11-16','LV6894172','Ordinary','2023-05-12','2028-05-15','Pakistan','Faisalabad','uploads/profile_pics/profile_1746854706.jpeg','','uploads/documents/passport_1746854706.jpeg',0,'',NULL,NULL,NULL,NULL,'681ee17b3bcf9681ee17b3bcfa','2025-05-10 05:25:06',NULL,2,0,4),
(99,'CME0037','Naimat ','Ullah','Naimat  Ullah','naimat','naimat@communikmarketing.com','$2y$10$2gOE7frS8FVdWLo1pyKg.O86lRuvwhC0neMMMYUsnQU8THd3Yv.IK','1997-01-01','Male','Single','','527563131','Dubai','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2023-02-21','','','','user',NULL,'Relationship officer','CME0007','1','2025-07-03','','','','NA','','','20120252753048','Employement Visa','2025-05-01','2025-06-29','RH2745641','Ordinary','2022-10-21','2032-10-19','Pakistan','Karachi ','uploads/profile_pics/profile_1746855090.jpg','uploads/documents/visa_1746855090.pdf','uploads/documents/passport_1746855090.pdf',0,'',NULL,NULL,NULL,NULL,'681ee3a250e39681ee3a250e3a','2025-05-10 05:31:30',NULL,2,0,4),
(100,'CME0038','Harry','Bhatti','Harry Bhatti','harry','harry@communikmarketing.com','$2y$10$5.W.RcCgVOJl8Rbz8kCceeXbiuTBBi2GvEWsyxgm6y1ceyUbk5zDm','2000-02-21','Male','Single','A+','567463423','Dubai','United Arab Emirates','Intermediate','2012-01-01','2014-01-01','BISE Lahore','active',0,NULL,'2025-04-04','','','','user',NULL,'Relationship officer','CME0013','',NULL,'','ST258903184AE','Al Hilal ','0174 6342 3001','AE87 0530 0000 1746 3423 001',NULL,'20120252959573','Employement Visa','2025-06-04','2027-06-03','EH3177171','Regular','2022-10-13','2027-10-12','Pakistan','Lahore','uploads/profile_pics/profile_1746855543.jpg','uploads/documents/visa_1746855543.jpg','uploads/documents/passport_1746855543.pdf',0,'126344713','2025-05-27','2027-05-26',NULL,NULL,'681ee512736d9681ee512736da','2025-05-10 05:39:03',NULL,2,0,4),
(101,'CME0039','Zohaib ','Iqbal','Zohaib  Iqbal','zohaib','zohaib@communikmarketing.com','$2y$10$YGSZ0Fr83VdyG140EnojpunysQsJ7itNyoxxizaQUMgc26CwMPHjG','1995-01-15','Male','Single','','554310733','Dubai','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-04-04','','','','user',NULL,'Relationship officer','CME0007','1','2025-07-26','','','','NA','','','20120257086176','Emp Visa Change Status','2025-03-22','2027-03-21',' ZQ1338351','Ordinary','2023-04-03','2028-04-01','Pakistan','Peshwar','uploads/profile_pics/profile_1746855870.jpg','uploads/documents/visa_1746855870.pdf','uploads/documents/passport_1746855870.pdf',0,'',NULL,NULL,NULL,NULL,'681ee6a0918c4681ee6a0918c5','2025-05-10 05:44:30',NULL,2,0,4),
(102,'CME0040','Kalai Kamal','Sivagnanasundaram','KALAI KAMAL  SIVAGNANASUNDARAM','kalaikamal','kalaikamal@communikmarketing.com','$2y$10$5u3tlx6O8r5ViFKGKc7y7OmBQe/E/RQTRo9.eeTHq9T7ipm8uUmNG','1986-03-18','Male','Single','A+','568557536','Dubai','United Arab Emirates','G.C.E Advanced Level','2004-01-01','2005-01-01','Kotagala Tamil Maha Vidyalaya ','active',0,NULL,'2025-02-20','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','MB296134517AE','FIRST ABU DHABI BANK','1901006936264001','AE330351901006936264001',NULL,'20220252471686','Employement Visa','2025-08-14','2027-08-13','N11444768','Regular','2024-06-20','2034-06-20','Sri Lanka','NAWALAPITIYA','uploads/profile_pics/profile_1746856978.jpg','uploads/documents/visa_1746856978.pdf','uploads/documents/passport_1746856978.pdf',0,'129393246','2025-08-07','2027-08-06',NULL,NULL,'681ee970d3986681ee970d3987','2025-05-10 06:02:58',NULL,2,0,4),
(103,'CME0041','Bajaj Mehul ','Assuda','Bajaj Mehul  Assuda','mehul.bajaj','mehul.bajaj@communikmarketing.com','$2y$10$LLMM2B5leskb9M8ZvPRdTe4YR3eEK1AOwFxXryMJ9yrnCDjJszip2','1982-11-18','Male','Married','A+','585976696','Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-04-08','','','','user',NULL,'Asst Sales Manager','CME0003','1','2025-07-03','','',NULL,'NA',NULL,NULL,'20120232961573','Emp Visa Change Status','2023-08-17','2025-08-16','S2764043','Regular','2018-04-26','2028-04-25','India','Mumbai','uploads/profile_pics/profile_1746857787.jpg','uploads/documents/visa_1746857787.jpg','uploads/documents/passport_1746857787.jpg',0,NULL,NULL,NULL,NULL,NULL,'681eed661cf03681eed661cf05','2025-05-10 06:16:27',NULL,2,0,4),
(104,'CME0042','Anum','Riaz','Anum  Riaz ','support','support@communikmarketing.com','$2y$10$oaGJFPSHQfwc85Gnr1ebXuw2YiSUOd.3BE/czAOm4LeOJN7vZZlfu','1993-05-28','Female','Single','A+','585879883','Dubai','United Arab Emirates','Bachelor of Arts','2017-01-01','2018-01-01','Sindh board','inactive',0,NULL,'2025-04-10','','','','user',NULL,'Asst. Operation','CME0041','1','2025-07-03','','',NULL,'NA',NULL,NULL,'20120237372511','Emp Visa Change Status','2023-07-10','2025-07-09',' FV9911852','Regular','2021-08-18','2031-08-18','Pakistan','Karachi ','104_1746864079.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'681f065fdf78b681f065fdf78d','2025-05-10 07:59:22',NULL,1,0,4),
(105,'CME0043','Suheb',' Saifi','Suheb  Saifi','Shuaibsaifimail','suheb@communikmarketing.com','$2y$10$eJpNQxTDXQWEon2IY0ZbTO3hyTeVjTLl1hZibZ6/yN1hoXexRWaMm','1998-08-23','Male','Single','A+','561196258','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'active',0,NULL,'2025-04-21','','','','user',NULL,'Relationship officer','CME0013','',NULL,'','ST259812308AE','Mashreq','019010252302','AE760330000019010252302',NULL,'20220252125554','Employement Visa','2025-07-09','2027-08-07','W8591672','Regular','2023-01-03','2033-01-02','India','Delhi','105_1746868553.PNG','','',0,'127487927','2025-06-24','2027-06-23',NULL,NULL,'681f188805fba681f188805fbb','2025-05-10 09:14:58',NULL,2,0,4),
(106,'CME0044','Fida Hussain',' Shah','Fida Hussain  Shah','syedfida7908','fida@communikmarketing.com','$2y$10$Q3HVIFQCAiaJRacdG8NVue.MEtbGET4qjdiZ6plN38iUmvRR2OVxO','1993-12-24','Male','Single','A+','588745006','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'Punjab University Lahore','inactive',0,NULL,'2025-04-18','','','','user',NULL,'Relationship officer','CME0007','1','2025-08-12','','',NULL,'NA',NULL,NULL,'20120247030875','Emp Visa Change Status','2024-10-21','2026-10-20','ZE5152172','Regular','2024-12-24','2029-12-25','Pakistan','Islamabad','106_1746869248.PNG','','',0,NULL,NULL,NULL,NULL,NULL,'681f1a556ad41681f1a556ad42','2025-05-10 09:24:11',NULL,2,0,4),
(107,'CME0045','Danush ','Mani','Danush  Mani','dhanushthara07','danush@communikmarketing.com','$2y$10$tJo2d0KKeg1Um8O.Ha.tJO4eRSfzTg.Ancw/aMh9NdzU/OyZqjvAq','1996-06-12','Male','Single','A+','506871784','dubai','United Arab Emirates','B.Tech/B.E. ',NULL,NULL,'Sri Krishna College of Engineering and Technology, Coimbatore','active',0,NULL,'2025-04-29','','','','user',NULL,'Relationship officer','CME0013','',NULL,'','ST2598987965AE','Al Maryah Community Bank LLC','5433012190000001','AE920975433012190000001',NULL,'20220252153680','Employement Visa','2025-04-07','2027-07-03','Z7975295 ','Regular','2024-09-09','2034-09-08','India',' Coimbatore','107_1746871743.jpg','','',0,'127690046','2025-06-25','2027-06-24',NULL,NULL,'681f24ccead37681f24ccead39','2025-05-10 10:07:45',NULL,2,0,4),
(108,'CME0046','Mohammad ','Sithik ','Mohammad  Sithik ','sithik2912','sithik@communikmarketing.com','$2y$10$J2eJS2BZXG5xwCB1bomVUOHzL8Fgo4RqfElCfpNd.ml/6MxTng7aK','2003-12-29','Male','Single','A+','506043447','Dubai','United Arab Emirates','Diploma in Mechinaical Engireeing ',NULL,NULL,NULL,'active',0,NULL,'2025-04-18','','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','Emirates NBD','125935023301','Î‘Î•150260000125935023301',NULL,'20220252159874','Employement Visa','2025-07-04','2027-07-03','Y7189483','Regular','2023-08-10','2033-08-09','India','Madurai','108_1746872639.PNG','','',0,'127722318','2025-06-24','2027-06-23',NULL,NULL,'681f281731171681f281731173','2025-05-10 10:22:22',NULL,2,0,4),
(109,'CME0047',' Suhail','Attar',' Suhail Attar','suhailattar7799','suhail@communikmarketing.com','$2y$10$PmyqfvJXIIgxA893sN6A.e1FSlXCn9qLX.YW9vjo2s/0qcQXHLZji','1995-04-13','Male','Single','A+','507400143','Dubai','United Arab Emirates','Bachelor Degree',NULL,NULL,'Sir Krishna Devaraya Universities','active',0,NULL,'2025-04-29','','','','user',NULL,'Relationship officer','CME0013','',NULL,'','ST258804952AE','Emirates NBD','0125929886301','AE73 0260 0001 2592 9886 301',NULL,'20120252890322','Employement Visa','2025-05-27','2027-05-26','P6896272 ','Regular','2017-01-12','2027-01-11','India','hyderabad','109_1746873243.jpg','','',0,'126241859','2025-05-15','2027-05-14',NULL,NULL,'681f2a2d0bf3a681f2a2d0bf3b','2025-05-10 10:33:09',NULL,2,0,4),
(110,'CME0048','Parfool ','Soni ','Parfool  Soni ','pksoni707070','parfool@communikmarketing.com','$2y$10$wet9kg7bNd8QdP3AMNKdyeMXs12vR9NFRjGVfM7dLXKR6voifVhFe','2001-07-15','Male','Single','A+','558414475','Dubai','United Arab Emirates','Business in commerce','2019-06-06','2022-06-11','University of sindh','active',0,NULL,'2025-04-21','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','MB292818841AE',NULL,'NA',NULL,NULL,'20220252164077','Employement Visa','2025-07-29','2027-07-28','FG4224691','Regular','2022-06-09','2027-06-08','Pakistan','Mipurkhas','110_1746873740.jpg','','',0,'127742331','2025-06-26','2027-06-25',NULL,NULL,'681f2c89f1352681f2c89f1354','2025-05-10 10:41:33',NULL,2,0,4),
(111,'CME0049','MINA SAMIR ','NESSIM GIRGES ATTALAH','MINA SAMIR  NESSIM GIRGES ATTALAH','mina.nessiem15','mina@communikmarketing.com','$2y$10$xX3O3O/AM4kYS/./gKtMRObrH9DsmawPIcnwh6QfWvkuwU.UGoEJi','1993-04-15','Male','Single','A+','586634399','Dubai','United Arab Emirates','Bachelor of Commerce','2016-01-01',NULL,'Alexandria','inactive',0,NULL,'2025-03-19','','','','user',NULL,'Relationship officer','CME0012','1','2025-07-16','','',NULL,'NA',NULL,NULL,'30120251140034204','Tourist','2025-02-26','2025-05-26','A36195618 ','Regular','2024-01-08','2031-01-07','Egypt',NULL,'111_1746876523.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'681f35e742b74681f35e742b75','2025-05-10 11:21:07',NULL,2,1,4),
(112,'CME0050','LAKSHMIPATHI ','GORREPATI ','LAKSHMIPATHI  GORREPATI ','lakshmipathi98978','lakshmipathi98978@gmail.com','$2y$10$SPjtl6f8D1nQlwnS8cpCmOan8Q6R77VIa31KiML0WfhaYP4Ce2whG','2000-03-01','Male','Single','A+','503275341','Dubai','United Arab Emirates','Bachelor of Commerce','2017-06-01','2020-12-01','Bangalore university Bengaluru','inactive',0,NULL,'2025-04-28','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','',NULL,'NA',NULL,NULL,'201202511400424364','Tourist','2025-04-09','2025-06-07','W6855455','Regular','2022-11-10','2032-11-09','India','Bengaluru','112_1746876886.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'681f38b58e602681f38b58e603','2025-05-10 11:33:54',NULL,2,1,4),
(113,'CME0051','Dipendra ','Kumar SAH','Dipendra  Kumar SAH','dksonu75','dipendra@communikmarketing.com','$2y$10$LBEklaTMwWd6rDN8ckfW1e3s2ETOJfxaaRQ.ilZ9./vpusk1kWFL.','1992-08-27','Male','Single','A+','528531106','Dubai','United Arab Emirates','MBA (Finance and Marketing) ',NULL,NULL,'GURU GOVIND SINGH INDRAPRASTHA  UNIVERSITY','active',0,NULL,'2025-04-26','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','',NULL,'NA',NULL,NULL,'301202572102','Emp Visa Change Status','2025-02-05','2027-02-04','10453176','Regular','2017-07-04','2027-07-03','Nepal','Mofa','113_1746879615.PNG','','',0,NULL,NULL,NULL,NULL,NULL,'681f423fb6fa2681f423fb6fa4','2025-05-10 12:13:38',NULL,2,0,4),
(114,'CME0052','Wilkinson ','Fernandes','Wilkinson  Fernandes','wilkinston.fernandes','wilkinston.fernandes@gmail.com','$2y$10$lTXyj4gHb527KYTb6fcc5OwT/i9ch6o0tX0u6HKeY5sBr76Tr8YHS','1994-12-23','Male','Single','A+','503582201','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','',NULL,'NA',NULL,NULL,'201202511400446253','Tourist','2025-04-14','2025-06-12','B9612451','Regular','2024-01-11','2034-01-10','India','Panaji','114_1747821088.jpg','uploads/documents/visa_1747820876.pdf','',0,NULL,NULL,NULL,NULL,NULL,'682da01b33777682da01b33778','2025-05-21 09:47:56',NULL,2,1,4),
(115,'CME0053','Huyam',' Abbas','Huyam  Abbas','heam23','heam23@live.com','$2y$10$gUKdemUvh8tlmgaZD0nZ6e8GIwmJCYBmBo4SoQlKWQHhbWH2iOg.2','1983-01-01','Female','','','561073188','Dubai','United Arab Emirates',' B.Sc. In Computer Science',NULL,NULL,'Science Faculty Of: Computer Science and Information Technolog','inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','20120223327984','Spouse Visa','2022-08-21','2032-08-20',' P07019056','Ordinary','2020-08-30','2025-08-29','Sudan','Omarawapa','uploads/profile_pics/profile_1747822875.jpg','uploads/documents/visa_1747822875.pdf','uploads/documents/passport_1747822875.jpg',0,'',NULL,NULL,'Not Pregnant','0000-00-00','682da7e0e651f682da7e0e6522','2025-05-21 10:21:15',NULL,2,1,4),
(116,'CME0054','Muhammad imran',' Naeem','Muhammad imran  Naeem','Chimrannaseem2','Chimrannaseem2@gmail.com','$2y$10$g5jETUHCDIBTkDBgRWHWKOK3i35Q9ynADwd92t0nduP4/A1dbodGu','1974-03-25','Male','Married','','501092160','Dubai','United Arab Emirates','BSC Physics',NULL,NULL,'THE ISLAMIA UNVERSITY BAHAWALPUR','inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-07-03','','','','NA','','','20120252403601','Emp Visa Change Status','2025-04-05','2027-04-04','AG9854884','Ordinary','2021-03-17','2026-03-17','Pakistan','Bawalnagar','uploads/profile_pics/profile_1747823309.jpg','uploads/documents/visa_1747823309.jpg','uploads/documents/passport_1747823309.pdf',0,'',NULL,NULL,NULL,NULL,'682da965b3d73682da965b3d74','2025-05-21 10:28:29',NULL,2,1,4),
(117,'CME0055','Dilan ','Champaka','Dilan  Champaka','dilan','dilan@communikmarketing.com','$2y$10$Y.moVlGKkdq7tmgVi/z/5OcmPC2H26M8sG0zyMASc8fxbyWXiaGg6','1986-05-17','Male','Married','A+','505021007','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'active',0,NULL,'2025-05-05','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','ST259252763AE','ADCB','13878794910001','AE100030013878794910001',NULL,'20220252125302','Employement Visa','2025-08-04','2027-08-03','N11410221','Regular','2024-09-30','2026-09-29','Sri Lanka','Sri ','117_1747823926.PNG','uploads/documents/visa_1747823810.jpg','uploads/documents/passport_1747823810.jpg',0,'126756988','2025-07-15','2027-07-14',NULL,NULL,'682dab3e8c148682dab3e8c149','2025-05-21 10:36:50',NULL,2,0,4),
(118,'CME0056','Aisha ','Salman','Aisha  Salman','aishasalman460','aishasalman460@gmail.com','$2y$10$AWwbGxDxNLp4cGkpBzeWCuCcEVTLc1YM5Q.pxpxjtZ/Tphkjztg6S','1999-02-05','Female','Married','','552564134','','United Arab Emirates','masters in Arts & Social Sciences',NULL,NULL,'University of Karachi','inactive',0,NULL,'2025-05-14','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-07-21','','','','NA','','','20120257016842','Emp Visa Change Status','2025-01-21','2027-01-20','NG5751941','Ordinary','2022-06-16','2032-06-15','Pakistan','Kara','118_1747825966.PNG','uploads/documents/visa_1747825667.pdf','uploads/documents/passport_1747825667.pdf',0,'',NULL,NULL,'','0000-00-00','682db291ac306682db291ac307','2025-05-21 11:07:47',NULL,2,0,4),
(119,'CME0057','Azmat',' Pasha ','Azmat  Pasha ','azmath707','azmath707@gmail.com','$2y$10$OTiqeO61s7wf32qvmnnmF.2RJixh4A8EmY2BrU.vGI6tehQ/gA6gO','1985-11-08','Male','Married','','585708687','Dubai','United Arab Emirates','Bachelor of Commerce',NULL,NULL,'osmania University','inactive',0,NULL,'2025-05-12','','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','20120247310265','Emp Visa Change Status','2025-01-11','2027-01-10','Y3436706','Ordinary','2024-06-10','2034-06-04','India','hyderabad','uploads/profile_pics/profile_1747826296.jpg','','uploads/documents/passport_1747826296.pdf',0,'',NULL,NULL,NULL,NULL,'682db53d92844682db53d92845','2025-05-21 11:18:16',NULL,2,1,4),
(120,'CME0058','Afraz ','Shaikh','Afraz  Shaikh','AFRAZSHAIKH2122','AFRAZSHAIKH2122@GMAIL.COM','$2y$10$FaaDIcTnlogR6I.VJbCYHe1TqiPt3AdW7JFGoOFtJtLRF7gItwueW','1988-02-21','Male','Married','','566783347','Dubai','United Arab Emirates','Intermediate',NULL,NULL,'','inactive',0,NULL,'2025-05-08','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','20120242421595','Emp Visa Change Status','2024-05-25','2026-05-24','X7753198','Ordinary','2023-03-21','2033-03-20','India','MANGALORE','120_1747826875.PNG','uploads/documents/visa_1747826766.pdf','uploads/documents/passport_1747826766.jpg',0,'',NULL,NULL,NULL,NULL,'682db70884441682db70884443','2025-05-21 11:26:06',NULL,2,1,4),
(121,'CME0059','Vikram ','Melath R','Vikram  Melath R','Vikram','Vikram@communikmarketing.com','$2y$10$DVBYuaoWU.Dx4M9F1hDjueOttTGHiAiluvgoZGaWK1YAknUgEIS2K','0000-00-00','Male','','','566134254','','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-04-28','Dubai','','','user',NULL,'Asst Sales Manager','CME0041','',NULL,'','','','NA','','','NA','',NULL,NULL,'NA','',NULL,NULL,'','','','','',0,'',NULL,NULL,NULL,NULL,'682db9e71457b682db9e71457c','2025-05-21 11:35:43',NULL,2,0,4),
(122,'CME0060','Roy ','Jayanta','Roy  Jayanta','jayroy1005','roy@communikmarketing.com','$2y$10$i8OmiOgd41x/htgoRuzhdekpvTP/RrP.qd5rTMWjB.FmAt4EFZpGO','1997-07-14','Male','Single','A+','581799042','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,NULL,'inactive',0,NULL,'2025-05-20','Dubai','','','user',NULL,'Relationship officer','CME0014','1','2025-09-27','','',NULL,'NA',NULL,NULL,'201202511400536322','Tourist','2025-05-03','2025-07-01','V2551741','Regular','2021-10-25','2031-10-24','India','Kolkata','122_1748003628.PNG','uploads/documents/visa_1748003532.pdf','uploads/documents/passport_1748003532.pdf',0,NULL,NULL,NULL,NULL,NULL,'683069de35c80683069de35c82','2025-05-23 12:32:12',NULL,2,0,4),
(123,'CME0061','KEN MAR ','MOISES SECATIN','KEN MAR  MOISES SECATIN','kensecatin','kensecatin@gmail.com','$2y$10$eY7p8fBF.WWZBOkCbILceOCL9PZRV6S1HmHu/d7t/VAL2aX3j8IhK','1998-06-22','Female','Single','','562679852','','United Arab Emirates','Bachelor of Science in Hotel and  Restaurant Management ',NULL,NULL,'','inactive',0,NULL,'2025-05-19','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','20120252340122','Emp Visa Change Status','2025-02-25','2027-02-24',' P2284294B','Ordinary','2019-06-22','2029-06-21','Philippines','','123_1748080010.PNG','uploads/documents/visa_1748079877.pdf','uploads/documents/passport_1748079877.jpg',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68319402bed4968319402bed4a','2025-05-24 09:44:37',NULL,2,1,4),
(124,'CME0062','Joseph ','Lalnunthanga','Joseph  Lalnunthanga','Josephlalnunthanga042','Josephlalnunthanga042@gmail.com','$2y$10$ey4Bz2EuHdUcL3a5FZE8buN8x480xPH3Gsdv7oEbhEqSOubg9tOEy','2005-10-10','Male','Single','A+','503822396',NULL,'United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-05-16','Dubai','','','user',NULL,'Relationship officer','CME0014','1','2025-07-03','','',NULL,'NA',NULL,NULL,'201202511400422577','Tourist','2025-04-09','2025-06-07','C2913118','Regular','2024-11-19','2034-11-18','India',NULL,'124_1748080416.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'6831965dd4b846831965dd4b85','2025-05-24 09:52:38',NULL,2,1,4),
(125,'CME0063','ANIL ','KOURANI','ANIL  KOURANI','anilkourani89','anilkourani89@gmail.com','$2y$10$eFZheyEukz4dGv8tOs8CHuE2.jZTqOKAPwYHuQk6KslNGEmQCb2Ei','1996-03-21','Male','Married','A+','585107907',NULL,'United Arab Emirates','B.COM',NULL,NULL,NULL,'inactive',0,NULL,'2025-05-19','Dubai','','','user',NULL,'Sales Team Leader','CME0004','1','2025-07-03','','',NULL,'NA',NULL,NULL,'601202578071','Emp Visa Change Status','2025-02-07','2027-02-06','Y9539527','Regular','2023-10-26','2033-10-25','India','Rajastan','125_1748082542.jpg','','',0,NULL,NULL,NULL,NULL,NULL,'68319e846030c68319e846030d','2025-05-24 10:27:58',NULL,2,0,4),
(126,'CME0064',' MOHAMED ','IRSHAT',' MOHAMED  IRSHAT','Dildilsha44','Dildilsha44@Gmail.com','$2y$10$FEpAhJ3/.HRs0hhCdIBtteWFbzW2CVL7ZMSFr/uZASWCDQ36pZcjm','1994-03-05','Male','','','0502484063','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-22','Dubai','','','user',NULL,'Relationship officer','CME0014','1','2025-07-03','','','','NA','','','20120242443439','Emp Visa Change Status','2024-10-16','2026-10-15',' N9489367','Ordinary','2022-04-29','2032-04-29','Sri Lanka','','126_1748247377.PNG','uploads/documents/visa_1748247289.pdf','uploads/documents/passport_1748247289.pdf',0,'',NULL,NULL,NULL,NULL,'68342204432eb68342204432ec','2025-05-26 08:14:49',NULL,2,1,4),
(127,'CME0065','Balda ','Narsimhulu','Balda  Narsimhulu','balda.narsimhulu2011','balda.narsimhulu2011@gmail.com','$2y$10$9anjyejCX1s.baLou1jLEOGg.lEONvd80U5EYk9kfnJojJSGXHW8W','1984-06-05','Male','Married','','509614565','','United Arab Emirates','Bachelor of Arts',NULL,NULL,'Osmania University','inactive',0,NULL,'2025-05-02','','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','','','NA','','','201202511400360588','Tourist','2025-03-24','2025-05-22','R2076907','Ordinary','2017-09-14','2027-09-13','India','hyderabad','uploads/profile_pics/profile_1748421920.PNG','uploads/documents/visa_1748421920.pdf','uploads/documents/passport_1748421920.jpeg',0,'',NULL,NULL,NULL,NULL,'6836cbe94e2b06836cbe94e2b3','2025-05-28 08:45:20',NULL,2,1,4),
(128,'CME0066','SOBIT',' PAUL','SOBIT  PAUL','sobitpaul7624','sobitpaul7624@gmail.com','$2y$10$X/exOkq5x9jy7cmA3UgEFObllz.Ir0CicBsSVIHiRcJuFLjtzIX7e','1993-04-11','Male','Single','A+','568142510',NULL,'United Arab Emirates','Master in Bussiiness Adminstration',NULL,NULL,'T. John Institute of  Management & Science,  Bengaluru, India ','inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','',NULL,'NA',NULL,NULL,'3012025114/0071751','Tourist','2025-04-15','2025-06-15','Y4004471','Regular','2024-06-13','2034-06-12','India','kozhikode','uploads/profile_pics/profile_1748422284.jpg','uploads/documents/visa_1748422284.pdf','uploads/documents/passport_1748422284.jpeg',0,NULL,NULL,NULL,NULL,NULL,'6836cd921c89e6836cd921c89f','2025-05-28 08:51:24',NULL,2,1,4),
(129,'CME0067','MARY CHRISTINA ',' CHRISTY','MARY CHRISTINA   CHRISTY','cmary.christina','cmary.christina@gmail.com','$2y$10$lrfT.XvCr92hAL.ZOOWH.ezWS.PFKiTJt7bWvXUGVlKK8ndQzNxpS','1983-12-21','Male','','','561659737','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-01','Dubai','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','','','NA','','','201202511400494180','Tourist','2025-04-24','2025-06-22','B6690674','Ordinary','2023-10-27','2033-10-26','India','Telanganga','uploads/profile_pics/profile_1748422790.jpg','uploads/documents/visa_1748422790.pdf','uploads/documents/passport_1748422790.pdf',0,'',NULL,NULL,NULL,NULL,'6836cfa7ea3ee6836cfa7ea3ef','2025-05-28 08:59:50',NULL,2,1,4),
(130,'CME0068','Amina ','Mohammad ','Amina  Mohammad ','aminamuhammad089','amina@communikmarketing.com','$2y$10$63q7vQBT6hF5hgmSRlGWOOmsaB.YIFNvtsO20ALGuvj6mNxfasVcS','1980-01-01','Female','Single','A+','569868095',NULL,'United Arab Emirates','Bachelor of Arts',NULL,NULL,'Hazara University Mansehra.','active',0,NULL,'2025-06-08','Dubai','','','user',NULL,'Relationship officer','CME0041','',NULL,'','ST260271631AE',NULL,'NA',NULL,NULL,'20220252196181','Employement Visa','2025-07-02','2025-08-31','HF3098504','Regular','2023-08-15','2028-08-13','Pakistan','Manesra','uploads/profile_pics/profile_1748423185.jpg','uploads/documents/visa_1748423185.pdf','uploads/documents/passport_1748423185.pdf',0,'127932650','2025-08-30','2027-08-29','Not Pregnant','0000-00-00','6836d0d362cd06836d0d362cd1','2025-05-28 09:06:25',NULL,2,0,4),
(131,'CME0069','Nouman ','Shahid','Nouman  Shahid','noumanshahid69','noumanshahid69@gmail.com','$2y$10$/mtWHoOMYMuw0/6yVOJRuOVkK50Q0MHS8o1g2sPRq.YIkIv6mqDEO','1996-05-31','Male','','','569252049','','United Arab Emirates','Master in Law',NULL,NULL,'University of Punjab','inactive',0,NULL,'2025-05-15','Dubai','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','','','NA','','','20120232965471','Emp Visa Change Status','2023-10-06','2025-10-05','GR5145362','Ordinary','2022-01-27','2032-01-26','Pakistan','Lahore','uploads/profile_pics/profile_1748426984.jpeg','uploads/documents/visa_1748426984.pdf','uploads/documents/passport_1748426984.jpeg',0,'',NULL,NULL,NULL,NULL,'6836dff51bc266836dff51bc27','2025-05-28 10:09:44',NULL,2,1,4),
(132,'CME0070','Mohammad ','Azeem','Mohammad  Azeem','azeemalone83','azeemalone83@gmail.com','$2y$10$rr2CVjnumTZHHVR2.Bz5n.gS0RWrhbUKduutjqAGO4udBu6YR/8au','1997-05-25','Male','Single','A+','552217120',NULL,'United Arab Emirates','Bachelor of Arts',NULL,NULL,'ALIGARH MUSLIM UNIVERSITY','inactive',0,NULL,'2025-05-19','','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','',NULL,'NA',NULL,NULL,'201202511400581763','Tourist','2025-05-15','2025-07-13','V4408696','Regular','2021-11-16','2031-11-15','India','uttar pardesh','132_1748427632.PNG','uploads/documents/visa_1748427459.pdf','uploads/documents/passport_1748427459.pdf',0,NULL,NULL,NULL,NULL,NULL,'6836e1c99fad06836e1c99fad1','2025-05-28 10:17:39',NULL,2,1,4),
(133,'CME0071','Nimra',' Nasir ','Nimra  Nasir ','nimranovel','nimranovel@gmail.com','$2y$10$Hpy2rGqSKxmHDXyBvW8hpO9pM9u9qYNsrjZqTnHdUH0Zk9zn08EvG','1996-11-15','Female','Single','A+','554929149',NULL,'United Arab Emirates','BBA Hons.(Finance): ',NULL,NULL,'University of Punjab','inactive',0,NULL,'2025-05-06','Dubai','','','user',NULL,'Relationship officer','CME0041','1','2025-07-03','','',NULL,'NA',NULL,NULL,'201202511400051660','Tourist','2025-05-26','2025-07-24','NY9825441','Regular','2024-11-14','2029-11-13','Pakistan','Lahore','133_1748428250.PNG','uploads/documents/visa_1748428175.pdf','uploads/documents/passport_1748428175.jpg',0,NULL,NULL,NULL,'','0000-00-00','6836e4928e3c86836e4928e3c9','2025-05-28 10:29:35',NULL,2,1,4),
(134,'CME0072','Navoda ','Piyadarshini','Navoda  Piyadarshini','navoda.piya8899','navoda@communikmarketing.com','$2y$10$kyj8E6.c/e3jj.P/VET0X.pBHXnYqcQCi1XTaOedHeEVWmOqwzlR.','2000-10-08','Female','Single','A+','566281870',NULL,'United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-05-20','Dubai','','','user',NULL,'Relationship officer','CME0059','',NULL,'','',NULL,'NA',NULL,NULL,'20120237668108','Emp Visa Change Status','2024-01-17','2026-01-16','N10546391','Regular','2023-05-09','2033-05-09','Sri Lanka',NULL,'uploads/profile_pics/profile_1748428867.jpg','uploads/documents/visa_1748428867.jpeg','uploads/documents/passport_1748428867.pdf',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','6836e730000656836e73000066','2025-05-28 10:41:07',NULL,2,0,4),
(135,'CME0073','Mohammad ','Nasser','Mohammad  Nasser','m.n4sser','m.n4sser@gmail.com','$2y$10$usmWbvIGBwCOTgNQLBA3GOxmqTjiZ3HP3J1VSQJApdKANLK6YjQOe','1993-04-09','Male','','','586264058','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-19','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-07-03','','','','NA','','','20120237725681','Emp Visa Change Status','2024-01-24','2026-01-23','A03840973','Ordinary','2022-04-18','2032-04-17','Bangladesh','Dhaka','uploads/profile_pics/profile_1748429327.jpg','uploads/documents/visa_1748429327.pdf','uploads/documents/passport_1748429327.pdf',0,'',NULL,NULL,NULL,NULL,'6836e933ea7766836e933ea777','2025-05-28 10:48:47',NULL,2,1,4),
(136,'CME0074','Pragati ','kachhawaha','Pragati  kachhawaha','pragati31102000','pragati31102000@gmail.com','$2y$10$BvE8thRvvFzaWbm8QSJ0nuEetx/qJs8T5XN1Q0nktdgfIsIUIGFjO','2000-10-31','Female','','','586264058','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-19','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','30120251140088182','Tourist','2025-05-03','2025-07-01','W2080584','Ordinary','2022-06-28','2032-06-27','India','Rajastan','uploads/profile_pics/profile_1748429675.jpg','uploads/documents/visa_1748429675.pdf','uploads/documents/passport_1748429675.jpg',0,'',NULL,NULL,'Not Pregnant','0000-00-00','6836ea750cfa16836ea750cfa2','2025-05-28 10:54:35',NULL,2,1,4),
(137,'CME0075','Abrar ','Ahmed ','Abrar  Ahmed ','abrarahmmedcm269','abrarahmmedcm269@gmail.com','$2y$10$pJGsrUdj2NrG0zWGsGxhsOgBUUlrcDkRbQVAayUcmN7frUhZSG8yW','2004-10-26','Male','','','543839706','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-13','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-07-03','','','','NA','','','20220242523658','Emp Visa Change Status','2024-10-16','2026-10-15','Y6381614','Ordinary','2023-07-21','2033-07-20','India','Chenni','uploads/profile_pics/profile_1748431428.jpg','uploads/documents/visa_1748431428.pdf','uploads/documents/passport_1748431428.pdf',0,'',NULL,NULL,NULL,NULL,'6836f124ed3546836f124ed355','2025-05-28 11:23:48',NULL,2,1,4),
(138,'CME0076','Shaik Abdul ','Raheem ','Shaik Abdul  Raheem ','shaikraheem849','shaikraheem849@gmail.com','$2y$10$NmrgWo4SxfwzjgnPL0BKbOSQs8FzfhLGA8clcGhRh7KnjStx6s8D6','2001-10-31','Male','','','558728161','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-07-03','','','','NA','','','201202511400319166','Tourist','2025-03-16','2025-05-14',' U9199003','Ordinary','2021-03-30','2031-03-29','India','hyderabad','uploads/profile_pics/profile_1748431955.png','uploads/documents/visa_1748431955.pdf','uploads/documents/passport_1748431955.pdf',0,'',NULL,NULL,NULL,NULL,'6836f38e014ef6836f38e014f1','2025-05-28 11:32:35',NULL,2,1,4),
(139,'CME0077','Nameera ','Tabassum','Nameera  Tabassum','nameeratabassumhrn','Nameera@communikmarketing.com','$2y$10$ejOmJFaa9g2xmIzVffvzheJEtU2xbNSbU1sfn0P9ZuMeAwPyyJc6K','2001-07-09','Male','Single','A+','559674061',NULL,'United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-07-21','','',NULL,'NA',NULL,NULL,'30120251140090189','Tourist','2025-05-06','2025-07-04','W8733670','Regular','2022-12-20','2032-12-19','India','hyderabad','uploads/profile_pics/profile_1748435941.jpeg','uploads/documents/visa_1748435941.pdf','uploads/documents/passport_1748435941.jpeg',0,NULL,NULL,NULL,NULL,NULL,'683702fc23018683702fc23019','2025-05-28 12:39:01',NULL,2,0,4),
(140,'CME0078','HAROON ALI ','SHAIK','HAROON ALI  SHAIK','haroonaly78q','haroon@communikmarketing.com','$2y$10$gVp0pPGnI9b.Y9/eqtPc5u9pbVKRYXCSffnwzRoV1u7bfm2seOJEC','2003-01-13','Male','Single','A+','589884341','Home country 132 aman Nagar Tlab katta Hyderabad\r\nLocall Coutry Dubai Tower Baniyas Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-05-12','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','MB293279239AE','Emirates NBD','0125935897001','AE30 0260 0001 2593 5897 001',NULL,'20220252247816','Employement Visa','2025-07-22','2027-07-21','C5166249','Regular','2024-11-11','2034-11-10','India','hyderabad','uploads/profile_pics/profile_1748436567.jpeg','uploads/documents/visa_1748436567.pdf','uploads/documents/passport_1748436567.pdf',0,'110440095','2024-11-01','2025-10-31',NULL,NULL,'683704ac812c0683704ac812c1','2025-05-28 12:49:27',NULL,2,0,4),
(141,'CME0079',' Saket Sureshkumar ','Gambhir',' Saket Sureshkumar  Gambhir','saket.gambhir14','saket@communikmarketing.com','$2y$10$JKTo6E3nRq8/nkmCW9HRsekZEL0yjnieK7ZjuETVHoK.RPdZfzJOq','2001-01-26','Male','Single','A+','0585416477',NULL,'United Arab Emirates','Bacholer in Bussiiness Adminstration',NULL,NULL,NULL,'inactive',0,NULL,'2025-05-26','Dubai','','','user',NULL,'Digital Sales','CME0001','1','2025-09-10','','',NULL,'NA',NULL,NULL,'20220242446630','Emp Visa Change Status','2024-10-23','2026-10-22',' T2345362','Regular','2019-03-08','2029-03-07','India','Gujrat','uploads/profile_pics/profile_1748520436.png','uploads/documents/visa_1748520436.pdf','uploads/documents/passport_1748520436.pdf',0,NULL,NULL,NULL,NULL,NULL,'68384c7c3e19668384c7c3e197','2025-05-29 12:07:16',NULL,3,0,4),
(142,'CME0080','Ibrar ','Ashraf','Ibrar  Ashraf','ibrarashraf05','ibrarashraf05@gmail.com','$2y$10$/Niv8yJKZtj.f/1qpCVmUelqm4MDKtizPQr7hZVrg940n3e8ZLcjK','2000-08-05','Male','','','552620356','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-26','Dubai','','','user',NULL,'Relationship officer','CME0063','1','2025-08-12','','','','NA','','','20120232927570','Emp Visa Change Status','2023-08-24','2025-08-23','MW1846311','Ordinary','2024-05-16','2034-05-15','Pakistan','Gujrat','uploads/profile_pics/profile_1748681479.jpg','uploads/documents/visa_1748681479.jpg','uploads/documents/passport_1748681479.jpg',0,'',NULL,NULL,NULL,NULL,'683ac2123cb6b683ac2123cb6c','2025-05-31 08:51:19',NULL,2,0,4),
(143,'CME0081','Ayan ','Khan','Ayan  Khan','khanayan.3343','ayan@communikmarketing.com','$2y$10$VJ5VRUybvbt8HtTTGImiqOYauPfva93DNWRwN2Vk5PWb7IJRlhuEe','2001-11-03','Male','Single','A+','585870493',NULL,'United Arab Emirates',' Higher Secondary (Intermed',NULL,NULL,NULL,'active',0,NULL,'2025-05-01','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB296960259AE','Al Hilal Bank','015870493001','AE480530000015870493001',NULL,'20120242175672','Employement Visa','2024-03-15','2026-03-14',' V4761984','Regular','2021-12-30','2031-12-31','India','uttar pardesh','143_1748861884.PNG','uploads/documents/visa_1748861759.pdf','uploads/documents/passport_1748861759.pdf',0,'129678671','2025-08-15','2027-08-14',NULL,NULL,'683d82116ebd6683d82116ebd8','2025-06-02 10:55:59',NULL,2,0,4),
(146,'CME082','super admin','communik','super admin communik','superadmin','superadmin@communikmarketing.com','$2y$10$9VPPjCDeo/EG1FdeZEkxD.mmz4PNM85S3mfkMJBNbrXri/SJZ2gNW','0000-00-00','','',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'active',0,NULL,NULL,NULL,NULL,'','super_admin',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'','2025-06-09 10:52:59',NULL,NULL,0,4),
(147,'CME0083','Sultan ','Mehmood','Sultan  Mehmood','sultanmehmood158207','sultan@communikmarketing.com','$2y$10$5c1dYI7lagHaVasyeFRgauyi60cB93SCLRnFPLyushDTC14auAhh6','1998-09-01','Male','Single','A+','557609072','Dubai','United Arab Emirates','Bachelor of Science in Mathematics',NULL,NULL,'University of Gujrat','inactive',0,NULL,'2025-06-13','Dubai','','','user',NULL,'Relationship officer','CME0059','1','2025-07-21','','',NULL,'NA',NULL,NULL,'2020242874878','Emp Visa Change Status','2025-01-11','2027-01-10','HM0166242','Regular','2023-03-30','2034-03-29','Pakistan','Gujrat','147_1751544429.PNG','uploads/documents/visa_1751544242.jpg','uploads/documents/passport_1751544242.pdf',0,NULL,NULL,NULL,NULL,NULL,'6866705d0e9406866705d0e944','2025-07-03 12:04:02',NULL,2,0,4),
(148,'CME0084','SANIYA ','AHMED','SANIYA  AHMED','saniyaahmed57','saniyaahmed57@gmail.com','$2y$10$ECCoIwyeQefY/3F0Ijn6TOjmLHX4PfrOONMKEQ9HZnzy7aKE85PGO','1995-08-11','Female','Single','','585081158','Dubai','United Arab Emirates','Bachelor of Engineering',NULL,NULL,'Prestige Institute of Engineering,  Indore, India ','inactive',0,NULL,'2025-06-25','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-07-21','','','','NA','','','206202587223058','Tourist','2025-06-04','2025-08-02','P9914065','Ordinary','2017-06-20','2027-06-19','India','Madhya Pardesh','148_1751545163.PNG','uploads/documents/visa_1751544816.pdf','uploads/documents/passport_1751544816.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','686672dc891e2686672dc891e3','2025-07-03 12:13:36',NULL,2,1,4),
(149,'CME0085','HAFSA HUSSAIN ','ANSARI','HAFSA HUSSAIN  ANSARI','hafsaansari831','hafsaansari831@gmail.com','$2y$10$zxCsy1CBTmf.Fj9Wv4IQd.TZUet7WpGHer7iT0cAzLjVhmLHFgT.y','2003-07-21','Female','Single','A+','528513023',NULL,'United Arab Emirates','Bachelor Of Business Administration ',NULL,NULL,' Islamia degree college','active',0,NULL,'2025-06-28','Dubai','','','user',NULL,'Relationship officer','CME0014','',NULL,'','MB296937348AE','FIRST ABU DHABI BANK','1901006949098001','AE800351901006949098001',NULL,'201202511400569659','Employement Visa','2025-05-11','2025-07-09','Y2611633','Regular','2024-05-14','2026-05-13','India','hyderabad','149_1751545509.PNG','uploads/documents/visa_1751545455.pdf','uploads/documents/passport_1751545455.pdf',0,'129680183','2025-08-14','2027-08-13','Not Pregnant','0000-00-00','68667583f3afc68667583f3afd','2025-07-03 12:24:15',NULL,2,0,4),
(150,'CME0086','Harshdeep ','Singh','Harshdeep  Singh','h.happiiii','h.happiiii@gmail.com','$2y$10$1hhPtcG5CwOL5FcAO0XlIOMuUux/7QPRLxXhkSkinnVHCzDJYXptW','2002-11-17','Male','Single','','529553129','Dubai','United Arab Emirates','Bachelor of Commerce in Accounting & Finance',NULL,NULL,'Swami Vivekanand Subharti University, Meerut, Uttar Pradesh, India','inactive',0,NULL,'2025-06-24','Dubai','','','user',NULL,'Relationship officer','CME0007','1','2025-07-22','','','','NA','','','20120252558637','Emp Visa Change Status','2025-05-28','2027-05-27',' V9738122','Ordinary','2022-05-14','2032-05-13','India','Delhi','uploads/profile_pics/profile_1751546185.jpg','uploads/documents/visa_1751546185.pdf','uploads/documents/passport_1751546185.pdf',0,'',NULL,NULL,NULL,NULL,'686678228f5f8686678228f5f9','2025-07-03 12:36:25',NULL,2,1,4),
(151,'CME0087','Suman',' Barmashakha','Suman  Barmashakha','adambarmashakha','suman@communikmarketing.com','$2y$10$NZ3Btot1veJniIhkiu1MIOi9N8ieIrGtHjL30Uv0D78FNWrGCdjBS','2001-08-17','Male','Single','A+','544651538','Dubai','United Arab Emirates','Higher Secondary School Certificate ',NULL,NULL,' Udayashi English Secondary School â€“ Jaljale, Nepal','inactive',0,NULL,'2025-06-18','Dubai','','','user',NULL,'Relationship officer','CME0007','1','2025-07-21','','',NULL,'NA',NULL,NULL,'201202511400726283','Tourist','2025-06-17','2025-08-15','12239922','Regular','2021-05-03','2031-05-02','Nepal',NULL,'151_1751546933.PNG','uploads/documents/visa_1751546848.pdf','uploads/documents/passport_1751546848.pdf',0,NULL,NULL,NULL,NULL,NULL,'68667a878619868667a8786199','2025-07-03 12:47:28',NULL,2,0,4),
(152,'CME0088','Indika ','Senarath','Indika  Senarath','indika','indika@communikmarketing.com','$2y$10$PBm74UzbCYy1w8s9FCVcHO7ByLRwp0.j84WAGiiLyoZlrQKQgypAa','1981-07-29','Male','','','566997815','Dubai','United Arab Emirates','G.C.E. Advanced Level Examination',NULL,NULL,'','active',0,NULL,'2025-06-11','Dubai','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','2012025717287','Emp Visa Change Status','2023-10-19','2025-10-18','P0022246','Ordinary','2024-11-05','2034-11-05','Sri Lanka','','uploads/profile_pics/profile_1751547385.jpg','uploads/documents/visa_1751547385.jpg','uploads/documents/passport_1751547385.jpg',0,'',NULL,NULL,NULL,NULL,'68667cb30641868667cb30641a','2025-07-03 12:56:25',NULL,2,0,4),
(153,'CME0089','Agnes ','Bonsu','Agnes  Bonsu','agnes','agnes@communikmarketing.com','$2y$10$qFWRgm0yRTEAIQ88sPD1k.K5kao6XdAPi8iTJks3yRgINIsmwPQ1y','1993-02-05','Female','Single','A+','552299791','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'active',0,NULL,'2025-06-02','Dubai','','','user',NULL,'Relationship officer','CME0014','',NULL,'','MB297234566AE','FIRST ABU DHABI BANK','1901006943071001','AE370351901006943071001',NULL,'201202511400403737','Employement Visa','2025-06-04','2025-09-12',' C3081774','Regular','2024-09-20','2034-09-19','India','hyderabad','uploads/profile_pics/profile_1751547708.jpg','uploads/documents/visa_1751547708.pdf','uploads/documents/passport_1751547708.pdf',0,'129841702','2025-08-20','2027-08-19','','0000-00-00','68667e04c03db68667e04c03dc','2025-07-03 13:01:48',NULL,2,0,4),
(154,'CME0090','ZARRAR ','IBRAHIM','ZARRAR  IBRAHIM','zarrar','zarrar@communikmarketing.com','$2y$10$YT5SC.3AMlDikJzlTgHtue1QMTlR5PKzEAgZLDJHUJkEQdDqWY5XK','1996-03-26','Male','Single','A+','589102660',NULL,'United Arab Emirates',' MBA in Marketing',NULL,NULL,'Bharti Vidyapeeth University - Pune','active',0,NULL,'2025-07-02','Dubai','','','user',NULL,'Digital Sales','CME0001','',NULL,'','',NULL,'NA',NULL,NULL,'20120242910835','Emp Visa Change Status','2024-08-23','2026-08-22','U2541591','Regular','2019-12-18','2029-12-17','India','Mumbai','uploads/profile_pics/profile_1751624686.jpg','uploads/documents/visa_1751624686.pdf','uploads/documents/passport_1751624686.pdf',0,NULL,NULL,NULL,NULL,NULL,'6867aa65691cf6867aa65691d0','2025-07-04 10:24:46',NULL,3,0,4),
(155,'CME0091','Varun ','Sachideva','Varun  Sachideva','varun','varun@communikmarketing.com','$2y$10$FWVo2iA2VIlN52owTonGoenbZjuS0Pz5s4B0uDv4M9NfEPspao1sK','1985-10-04','Male','','','0524402818','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-05-26','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-07-17','','','','NA','','','30120251140096269','Tourist','2025-05-12','2025-07-10','B6457267','Ordinary','2023-10-19','2033-10-18','India','uttar pardesh','uploads/profile_pics/profile_1751625201.JPG','uploads/documents/visa_1751625201.pdf','uploads/documents/passport_1751625201.pdf',0,'',NULL,NULL,NULL,NULL,'6867ace31b4296867ace31b42a','2025-07-04 10:33:21',NULL,2,0,4),
(156,'CME0092','Anas ','Jariwala','Anas  Jariwala','anasjariwala51','anasjariwala51@gmail.com','$2y$10$KGi2tHtBK0EwG5NOZLbys.kov0OeuHmBGhOQcFRcJ7ve8H2VMOMM6','1994-09-22','Male','Single','A+','919537244022','Country Address 4th floor Jariwala Complex Surat city India\r\nLocal Address Gargish building 4 gold souq Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'active',0,NULL,'2026-06-25','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','MB297231812AE','Emirates NBD','125938763201','AE060260000125938763201',NULL,'20220252560825','Employement Visa','2025-08-28','2027-08-27','T7516906','Regular','2019-07-15','2029-07-14','India','Gujrat','uploads/profile_pics/profile_1751625693.jpg','uploads/documents/visa_1751625693.pdf','uploads/documents/passport_1751625693.jpg',0,'129843668','2025-08-20','2027-08-19',NULL,NULL,'6867aeb7e0a396867aeb7e0a3a','2025-07-04 10:41:33',NULL,2,0,4),
(157,'CME0093','Usman',' khan ','Usman  khan ','usman.khan','usman.khan@communikmarketing.com','$2y$10$nK.su4c0tfFGNjclXJHb6OjmuJDn3NXbFYkFjjXrNWlUeWi5Lzrx2','1983-01-10','Male','','','568110151','','United Arab Emirates','Bachelor of Arts',NULL,NULL,' Rohilkhand University Bareilly Uttar Pradesh India','inactive',0,NULL,'2026-06-02','Dubai','','','user',NULL,'Sales Team Leader','CME0004','1','2025-08-28','','','','NA','','','20120212463807','Tourist','2023-08-25','2025-08-24','Y1680455','Ordinary','2024-05-08','2034-05-07','India','uttar pardesh','uploads/profile_pics/profile_1751626047.jpg','uploads/documents/visa_1751626047.jpg','uploads/documents/passport_1751626047.pdf',0,'',NULL,NULL,NULL,NULL,'6867afe944c6c6867afe944c6d','2025-07-04 10:47:27',NULL,2,0,4),
(158,'CME0094','Atik ','Jariwala','Atik  Jariwala','Atikjariwala7006','Atikjariwala7006@gmail.com','$2y$10$VgICdqFEuTDqtMAVcDF/QuO736iXb5/Fc3Q67qwGnecgqbC2ACINa','1997-07-21','Male','','','523784374','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2026-06-25','','','','user',NULL,'Relationship officer','CME0093','1','2025-08-28','','','','NA','','','201202511400619465','Tourist','2025-05-21','2025-07-19','C2962763','Ordinary','2024-10-04','2034-10-03','India','Gujrat','uploads/profile_pics/profile_1751626354.jpg','uploads/documents/visa_1751626354.pdf','uploads/documents/passport_1751626354.pdf',0,'',NULL,NULL,NULL,NULL,'6867b1909d7176867b1909d718','2025-07-04 10:52:34',NULL,2,0,4),
(159,'CME0095','Ahmed ','Salih','Ahmed  Salih','ahmed.yamal1995','ahmed.yamal1995@gmail.com','$2y$10$8A9QAWpyLGL0cg2uxqGX6eUPHOegYRFfXud2qEwz/NldmMwOYMdU.','1995-11-10','Male','','','0586670401','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-06-26','Dubai','','','user',NULL,'Relationship officer','CME0093','1','2025-08-12','','','','NA','','','20120252668087','Emp Visa Change Status','2025-05-27','2027-05-26',' P13192462','Ordinary','2025-02-02','2035-02-01','Sudan','','uploads/profile_pics/profile_1751627992.jpg','uploads/documents/visa_1751627992.pdf','uploads/documents/passport_1751627992.pdf',0,'',NULL,NULL,NULL,NULL,'6867b7c2b5a386867b7c2b5a39','2025-07-04 11:19:52',NULL,2,1,4),
(160,'CME0096','Omyima ','Mhmoud','Omyima  Mhmoud','omyima','omyima@communikmarketing.com','$2y$10$AdxYh91ELntpjITYZoeVW.vPlRiMxX2N/9E7aMGJV/Zydgfu9rI3u','1977-01-01','Female','Married','','555079439','Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-05-16','Dubai','','','user',NULL,'Relationship officer','CME0059','',NULL,'','','','NA','','','20120222790178','Emp Visa Change Status','2024-07-23','2026-07-22','P04401241','Ordinary','2018-01-09','2023-01-08','Sudan','','uploads/profile_pics/profile_1751973661.jpg','uploads/documents/visa_1751973661.pdf','uploads/documents/passport_1751973661.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','686cfe068fc16686cfe068fc17','2025-07-08 11:21:01',NULL,2,0,4),
(161,'CME0097','Syed Minhaj ','Hassan','Syed Minhaj  Hassan','minhaj','minhaj@communikmarketing.com','$2y$10$AWi.Ani058sw55DlG0.wqeuALjQ3edBwEUGfT4s9VjtGG76cl2nta','1977-05-05','Male','','','0544899775','','United Arab Emirates','Bachelors in commerce',NULL,NULL,'University of Karachi -Pakistan ','inactive',0,NULL,'2025-07-16','Dubai','','','user',NULL,'Asst Sales Manager','CME0003','1','2025-08-28','','','','NA','','','20120257015024','Emp Visa Change Status','2025-01-16','2027-01-15','AA8800343','','2015-06-12','2025-06-09','Pakistan','Karachi ','161_1753080227.PNG','uploads/documents/visa_1753080067.pdf','uploads/documents/passport_1753080067.pdf',0,'',NULL,NULL,NULL,NULL,'687dded4e9238687dded4e9239','2025-07-21 06:41:07',NULL,2,0,4),
(164,'CME0098','Rincy ','Basheer','Rincy  Basheer','accounts','accounts@communikmarketing.com','$2y$10$159q2VTxgwBIX/.BH6B.5u7V69OxxmpLkggB9DOqEQDFx2csvtLuW','1992-02-22','Female','Married','','0544757315','','United Arab Emirates',' Masters degree in Commerce',NULL,NULL,'Mahatmagandhi University','active',0,NULL,'2025-07-07','Dubai','','','user',NULL,'Accountant','CME0001','',NULL,'','','','NA','','','20120223518069','Emp Visa Change Status','2025-06-09','2027-05-08','P3993181','Ordinary','2016-08-01','2026-07-31','India','Kerala','164_1753180546.PNG','uploads/documents/visa_1753180391.pdf','uploads/documents/passport_1753180391.pdf',0,'',NULL,NULL,'','0000-00-00','687f651cc4139687f651cc413a','2025-07-22 10:33:11',NULL,5,0,4),
(165,'CME0099','Shobana ','M','Shobana  M','retention','retention@communikmarketing.com','$2y$10$zyXmNknXU8UfOqjDNpD88.qlYOgOVBcPpeY2J/ffyTDqqDbrpOxWm','1992-06-24','Female','Married','','509216569','','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-07-14','Dubai','','','user',NULL,'Retention& Activation Officer','CME0001','',NULL,'','','','NA','','','2012024319269','Emp Visa Change Status','2024-12-10','2026-12-09','V1636286','','2021-11-17','2031-11-16','India','','uploads/profile_pics/profile_1753248560.jpg','uploads/documents/visa_1753248560.pdf','uploads/documents/passport_1753248560.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','6880725693929688072569392a','2025-07-23 05:29:20',NULL,2,0,4),
(166,'CME0100','SHIMA ','ABD ALMAGID','SHIMA  ABD ALMAGID','Shimaabdalmagid6','Shimaabdalmagid6@gmail.com','$2y$10$01/N9DH4RAAXkpDhmLexde8W8eWdEmT5x12yHR97tmLg3LJiczulK','2001-12-10','Female','Single','A+','545353868','Home country address Omdurman Sudan\r\nLocal Address Street c Al Karma Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-07-09','Dubai','','','user',NULL,'Relationship officer','CME0093','',NULL,'','',NULL,'NA',NULL,NULL,'784200149760702','Emp Visa Change Status','2025-02-28','2027-02-19','P11548598','Regular','2024-04-09','2034-04-08','Sudan',NULL,'166_1753249596.PNG','uploads/documents/visa_1753249143.pdf','uploads/documents/passport_1753249143.jpg',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','6880744a17c136880744a17c14','2025-07-23 05:39:03',NULL,2,0,4),
(167,'CME0101','Khalid ','Khan','Khalid  Khan','thisiskhalid','thisiskhalid@gmail.com','$2y$10$soYq5YvttF.PXDoPvNffzuDtR2qH6/kF7lF5QX9pxFnEWkLGz2pvW','2003-06-20','Male','Single','A+','0543774249',NULL,'United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-07-15','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-08-12','','',NULL,'NA',NULL,NULL,'206202587498027','Tourist','2025-07-07','2025-09-05','T0780585','Regular','2019-03-18','2029-03-17','India','Ranchi','167_1753250275.jpg','uploads/documents/visa_1753250199.pdf','uploads/documents/passport_1753250199.jpg',0,NULL,NULL,NULL,NULL,NULL,'688078c44cacd688078c44cace','2025-07-23 05:56:39',NULL,2,1,4),
(168,'CME0102','Tushar ','Chopade','Tushar  Chopade','tusharchopade9970','tusharchopade9970@gmail.com','$2y$10$sB6jnGK3Icpo4V0v2Om5N.z.APZnlGmd6RdXtDkQqjsF0PtBJE0wC','1990-08-27','Male','Single','A+','918433752339','Local Address Al qubaisi tower Sharjah\r\nHome country address Flat no 202 ulwe navi Mumbai India','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-07-05','Dubai','','','user',NULL,'Relationship officer','CME0093','',NULL,'','MB296078450AE','FIRST ABU DHABI BANK','2455001174126015','AE590352455001174126015',NULL,'20220252471675','Employement Visa','2025-08-19','2027-08-18','I0644869','Regular','2025-05-23','2035-05-22','India','Mumbai','168_1753268270.PNG','uploads/documents/visa_1753268030.pdf','uploads/documents/passport_1753268030.pdf',0,'129393517','2025-08-06','2027-08-07',NULL,NULL,'6880be2174cea6880be2174ceb','2025-07-23 10:53:50',NULL,2,0,4),
(169,'CME0103','FATHEL','RAHMAN','FATHEL RAHMAN','ftoohweezy','ftoohweezy@gmail.com','$2y$10$0n2EaDWC5orYjYn0WvhVQubEn19lufltGzrrwQXJd7FCgpLyNtp/u','1997-03-10','Male','','','508490942','','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-07-07','','','','user',NULL,'Relationship officer','CME0093','1','2025-08-28','','','','NA','','','30120251140051715','Tourist','2025-03-20','2025-07-17','P12377655','Ordinary','2024-09-01','2024-08-31','Sudan','','uploads/profile_pics/profile_1753268744.jpg','uploads/documents/visa_1753268744.pdf','uploads/documents/passport_1753268744.pdf',0,'',NULL,NULL,NULL,NULL,'6880c0f754b1a6880c0f754b1b','2025-07-23 11:05:44',NULL,2,0,4),
(170,'CME0104','Asif ','Kifayat Chougule','Asif  Kifayat Chougule','asif87chougule','asif87chougule@gmail.com','$2y$10$88L37koCg9X47om9QLjQ5eU2iB749.yuFOULLulbcDceYQ7FDwC0q','1987-12-31','Male','Married','A+','0526588186','Al Baraha, Dubai.','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-07-31','Dubai','','','user',NULL,NULL,'CME0005','',NULL,'','',NULL,'NA',NULL,NULL,'201202511400893020','Tourist','2025-07-26','2025-09-23','R8660652','Regular','2018-01-12','2028-01-11','India','Mumbai','170_1758784952.jpeg','uploads/documents/visa_1755848844.PDF','uploads/documents/passport_1755848844.pdf',0,NULL,NULL,NULL,NULL,NULL,'68a81f28a849268a81f28a8493','2025-08-22 07:47:24',NULL,1,0,4),
(171,'CME0105','Irfan ','Sarwar','Irfan  Sarwar','irfansarwar219','irfansarwar219@gmail.com','$2y$10$luVURS1QIccPEpIL20tCLuiqcFChuaLNRy73d.tjmPSzY1lpwnVfy','1990-10-18','Male','Married','A+','0543004412','Home country address Karachi Pakistan\r\nLocal Address Al muteena Deira Dubai','United Arab Emirates','Matric',NULL,NULL,'Science from Higher Secondary School Karachi.','active',0,NULL,'2025-07-14','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','',NULL,'NA',NULL,NULL,'201/2024/7751083','Emp Visa Change Status','0004-07-18','2026-07-17','BD5991393','Regular','2023-07-10','2028-07-08','Pakistan','Karachi ','uploads/profile_pics/profile_1756376139.jpg','uploads/documents/visa_1756376139.pdf','uploads/documents/passport_1756376139.pdf',0,NULL,NULL,NULL,NULL,NULL,'68b02a5d1520768b02a5d15208','2025-08-28 10:15:39',NULL,2,0,4),
(172,'CME0106','Aqib Hamid','Shah','Aqib Hamid Shah','aqibhamid1996','aqibhamid1996@gmail.com','$2y$10$T..W2ee8aeo7BAqt/HTjoO/rI8pyySe.fbwOF1lI.PK19EinleGNu','1996-09-10','Male','Married','A+','0581497863','Shah Studio 654321','United Arab Emirates','12th','2017-06-09','2018-05-09','The Jammu & Kashmir State Board School Education.','inactive',0,NULL,'2025-08-27','Dubai','Sales','','user',NULL,'Relationship officer','CME0004','1','2025-09-08','','',NULL,'NA',NULL,NULL,'201/2025/11400928495','Job Search Visa','2025-08-05','2025-10-03','U2960503','Regular','2021-01-13','2031-01-12','India','Srinagar','uploads/profile_pics/profile_1756390368.PNG','uploads/documents/visa_1756390368.pdf','uploads/documents/passport_1756390368.pdf',0,NULL,NULL,NULL,NULL,NULL,'68b05e6077d2868b05e6077d29','2025-08-28 14:12:48',NULL,2,1,4),
(173,'CME0107','Farhan Ahmed','Khan','Farhan Ahmed Khan','fkhan.xyz','fkhan.xyz@gmail.com','$2y$10$THd6RPzFXNl.bfxXTq7e3uqJerDS/eFX19CO7epfFir9KCu2GmSsq','1974-05-20','Male','Married','','0558429987','Villa 29, Hamriya Near Hamriya Park, Deira, Dubai.','United Arab Emirates','B.com','1992-06-12','1995-05-10','Karachi University','active',0,NULL,'2025-05-01','Dubai','Sales','','user',NULL,'Verification Officer, Bike Rider.','CME0003','',NULL,'','','','NA','','','201/2022/7334758','Employement Visa','2022-08-22','2025-08-06','UN4102213','Ordinary','2023-12-06','2033-12-04','Pakistan','Karachi','uploads/profile_pics/profile_1756470393.jpg','uploads/documents/visa_1756470393.jpg','uploads/documents/passport_1756470393.jpg',1,'',NULL,NULL,NULL,NULL,'68b195f301acb68b195f301acc','2025-08-29 12:26:33',NULL,2,0,4),
(174,'CME0108','Imran','Malik','Imran Malik','malikimran123275','malikimran123275@gmail.com','$2y$10$jYKXpiHhr6PF72bBYnabZu17Ffurtx4uwRJ9w4RNFo7lrm60d/KNa','2004-01-01','Male','Single','B+','0555917400','Residence Add: Room No- 302, Building No- 10/40, Near National Paint, Muhela, Sharjah.\r\nHome Country: At Post- Check No: 275JB, Pensara, Tal- Faisalabad Sadar, Dist- Faisalabad, Pakistan. ','United Arab Emirates','7th','2014-06-10','2015-05-08','Punjab Public Model School, Faisalabad, Pakistan.','active',0,NULL,'2025-05-31','Dubai','','','user',NULL,NULL,'CME0005','',NULL,'','',NULL,'NA',NULL,NULL,'202/2023/2300458','Employement Visa','2023-11-14','2025-11-13','PH1227431','Regular','2022-06-06','2027-06-05','Pakistan','Faisalabad','uploads/profile_pics/profile_1756473365.jpeg','uploads/documents/visa_1756473365.pdf','uploads/documents/passport_1756473365.jpg',0,NULL,NULL,NULL,NULL,NULL,'68b1a5df25add68b1a5df25ade','2025-08-29 13:16:05',NULL,1,0,4),
(175,'CME0109','Shema ','Yasmin','Shema  Yasmin','ksyasmin786','ksyasmin786@gmail.com','$2y$10$ElQGz3q4RZ274rVp4/zqTuciKWAfb4VfKuuSfmjnqcuy0eA1eOpf.','1983-03-01','Female','Married','','0529388160','Home Country Adderess Hajee Mohala jakaaura Bihar India\\r\\nLocal Adderess: near Al Gubaiba metro station Dubai ','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-08-25','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','784198355926290','Spouse Visa','2024-11-06','2026-11-05','Z6109179','Ordinary','2021-02-05','2031-02-04','India','Bihar','uploads/profile_pics/profile_1756551069.jpg','uploads/documents/visa_1756551069.pdf','uploads/documents/passport_1756551069.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68b2d56c8500f68b2d56c85014','2025-08-30 10:51:09',NULL,2,0,4),
(176,'CME0110','Nimesh ','Khandelia','Nimesh  Khandelia','nimeshkhandelia','nimeshkhandelia@gmail.com','$2y$10$scPXFtSJLE4wmhT1kCuJS.A70A6t/9dNLxtLGD7kZ58t5aykDc9JG','1992-06-14','Male','Married','A+','0585926114','Country address:Flat no 305 Balkempt Hyderabad\\r\\nLocal Address: Dubai Tower Baniyas square metro station Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-08-14','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-09-10','','',NULL,'NA',NULL,NULL,'3012025114 / 0166335','Tourist','2025-07-23','2025-09-20','T9310408','Regular','2019-11-04','2029-11-03','India','hyderabad','176_1756551719.PNG','uploads/documents/visa_1756551643.pdf','uploads/documents/passport_1756551643.pdf',0,NULL,NULL,NULL,NULL,NULL,'68b2d80c8d0d868b2d80c8d0dc','2025-08-30 11:00:43',NULL,2,1,4),
(177,'CME0111','Reem',' Ibrahim Yousif','Reem  Ibrahim Yousif','reemibrahim346434','reemibrahim346434@gmail.com','$2y$10$T8U9IsnXxcsCIyRQw3G64uPf7km5bOyTyRyU0uiX2udH4Htf11Ztq','1994-07-02','Female','','','0502462977','Local country Address Khartoum Saudan\\r\\nLocal Address:  Sharaja ','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-07-30','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-08-30','','','','NA','','','784199431896333','Spouse Visa','2025-01-14','2027-01-13','P10629217','Ordinary','2023-03-30','2027-01-13','Sudan','Khartoum','uploads/profile_pics/profile_1756552239.jpg','uploads/documents/visa_1756552239.jpg','uploads/documents/passport_1756552239.jpg',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68b2dacb44e7368b2dacb44e74','2025-08-30 11:10:39',NULL,2,1,4),
(178,'CME0112','Ibtisam',' Abdullah','Ibtisam  Abdullah','ibtisamshugerii','ibtisamshugerii@gmail.com','$2y$10$ukDgTEaJUi.cW8DRjyTER.fnON6YGbC/B1PYSb1otqchgTLJmZNPy','1978-11-12','Female','Single','A+','0554493721','Home Address Algardrif\\r\\nLocal Adddress Hor al Anz Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'inactive',0,NULL,'2025-08-14','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-08-30','','',NULL,'NA',NULL,NULL,'784198755918237','Spouse Visa','2024-12-24','2025-11-20','P10617811','Regular','2023-03-30','2033-03-29','Sudan','Elgrdrif','178_1756553101.PNG','uploads/documents/visa_1756552946.jpg','uploads/documents/passport_1756552946.jpg',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','68b2dcc88351368b2dcc883514','2025-08-30 11:22:26',NULL,2,1,4),
(179,'CME0113','Aaza ','Al-Nabi Zaki Jadallah','Aaza  Al-Nabi Zaki Jadallah','Azzaabdalnabi123456789','Azzaabdalnabi123456789@gmail.com','$2y$10$.Ah9c/LTaCkf0hbbktXRyuul8HHI4feJk4VP3//SH087PqBAhZQ7i','1992-01-01','Female','Single','','0565607380','Home Country address Hassahassa Sudan\\r\\nLocal Address 4 c street al  karma Dubai','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-07-16','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-08-30','','','','NA','','','30120251140106857','Tourist','2025-05-23','2025-07-21','P12487943','Ordinary','2024-09-19','2034-09-18','Sudan','Hassahassa','uploads/profile_pics/profile_1756553605.jpg','uploads/documents/visa_1756553605.jpg','uploads/documents/passport_1756553605.jpg',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68b2e002d35cf68b2e002d35d0','2025-08-30 11:33:25',NULL,2,1,4),
(180,'CME0114','Khalifa','Daffallah','Khalifa Daffallah','khalifadaffallah539','khalifadaffallah539@gmail.com','$2y$10$LMGOa.s3blldA14PiCgRJejraDk/fwJ5ZjpvxgQrME2bXpPrP46x6','2002-09-22','Male','Single','','0505909736','Dubai.','United Arab Emirates','12th','2024-06-12','2025-05-08','United Arab Emirates Ministry Of Education.','inactive',0,NULL,'2025-08-14','Dubai','','','user',NULL,'Relationship officer','CME0007','1','2025-10-10','','','','NA','','','241532126','Family Visa','2024-07-02','2026-07-01','P11391982','Ordinary','2023-12-19','2033-12-18','Sudan','Port Sudan','uploads/profile_pics/profile_1756564827.jpeg','uploads/documents/visa_1756564827.pdf','uploads/documents/passport_1756564827.pdf',0,'',NULL,NULL,NULL,NULL,'68b306c580d5d68b306c580d60','2025-08-30 14:40:27',NULL,2,0,4),
(181,'CME0115','Genes Darby','Melencion','Genes Darby Melencion','mghenzdarby02','mghenzdarby02@gmail.com','$2y$10$jLA8UTylh/hoHqCFJgNhcexJJ66zfm6I.5xWpQptd9F.h.vPV1bS.','0000-00-00','Female','Married','A+','0507535814','Residence Add- Gallery 23, Albarsha 2. Dubai.\\r\\nHome Country Add- Canmano, Sogbayan, Bohol, Philippines.','United Arab Emirates','12th','2000-06-10','2002-03-23','Holy Spirit School,','inactive',0,NULL,'2025-08-18','Dubai','','','user',NULL,'Relationship officer','CME0013','1','2025-09-08','','',NULL,'NA',NULL,NULL,'253279415','Employement Visa','2025-08-04','2025-10-03','P8944142C','Regular','2025-02-05','2035-02-04','Philippines','DFA Tagbilaran.','181_1756728848.JPG','uploads/documents/visa_1756567137.pdf','uploads/documents/passport_1756567137.pdf',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','68b30d9dd962168b30d9dd9622','2025-08-30 15:18:57',NULL,2,1,4),
(182,'CME0116','Nizam ','Uddin Jewel','Nizam  Uddin Jewel','nizemuddinjewel8_eeo','nizemuddinjewel8_eeo@indeedemail.com','$2y$10$O/.mnXyRHkt1ed.DyETX4OeOFfz2RMs.Te1mHRtDmAPrRW8QGnv/K','0000-00-00','Male','Married','AB+','0545254801','Salah un Din metro station Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-09-04','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','20120232620836','Emp Visa Change Status','2023-05-23','2025-05-22','A01674113','Ordinary','2021-06-27','2026-06-26','Bangladesh','Dhaka','uploads/profile_pics/profile_1757320128.jpg','uploads/documents/visa_1757320128.jpg','uploads/documents/passport_1757320128.jpg',0,'',NULL,NULL,NULL,NULL,'68be91fabf3f168be91fabf3f2','2025-09-08 08:28:48',NULL,2,1,4),
(183,'CME0117','Syed ','AbuThahir','Syed  AbuThahir','abufahim71','abufahim71@gmail.com','$2y$10$9FCWzPeK5FjSZZGZ2Rla5etkaZ39yI/UpN3SIBVd68cCX0APHmrMG','1979-02-12','Male','Married','','0547427791','Muteena Deira Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-09-08','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','101201820262041','Emp Visa Change Status','2024-12-21','2026-10-09','P6526916','Ordinary','2017-01-24','2027-01-23','India','Madurai','uploads/profile_pics/profile_1757404709.PNG','uploads/documents/visa_1757404709.jpg','uploads/documents/passport_1757404709.jpg',0,'','0000-00-00','0000-00-00',NULL,NULL,'68bfdca415cd068bfdca415cd1','2025-09-09 07:58:29',NULL,2,1,4),
(184,'CME0118','Arsh ','Firoz Khan','Arsh  Firoz Khan','arshkhan0304','arshkhan0304@gmail.com','$2y$10$IBhpLqNCdzGiY86GYbC2iOfoRU89Z7TYRowjTgvNBIlJZ8.Qxzpp.','2003-03-04','Male','Single','','0544147637','Al Nahda 2 Near Pak Darbar Dubai','United Arab Emirates','',NULL,NULL,'','inactive',0,NULL,'2025-09-15','Dubai','','','user',NULL,'Relationship officer','CME0020','1','2025-10-02','','','','NA','','','201202511400827407','Tourist','2025-07-11','2025-09-08','T4414231','Ordinary','2019-05-23','2029-05-22','India','','uploads/profile_pics/profile_1758006824.png','uploads/documents/visa_1758006824.pdf','uploads/documents/passport_1758006824.pdf',0,'',NULL,NULL,NULL,NULL,'68c90c2db97ca68c90c2db97cb','2025-09-16 07:13:44',NULL,2,1,4),
(185,'CME0119','Vivek','Rao','Vivek Rao','vivek.vibhava','vivek.vibhava@gmail.com','$2y$10$5Qsc6amwMq23PRUchr1ncubKgd0Skuy3XGNVU2.ZuTYv4od6NWbRy','1982-08-06','Male','Married','','0582953413','Dubai.\\r\\nB-56, New Friends Colony, Sec-23, Guldhar-2, Sanjay Nagar, Ghaziabad, Pin: 201002, Uttar Pradesh, India.','United Arab Emirates','B.com','2006-08-15','2009-08-27','CH. Charan Singh Univercity, Meerut. India.','inactive',0,NULL,'2025-08-22','','','','user',NULL,'Relationship officer','CME0059','1','2025-10-02','','','','NA','','','201/2025/11400876289','Job Search Visa','2025-07-22','2025-09-19','C5431154','Ordinary','2024-11-18','2034-11-17','India','Ghaziabad','uploads/profile_pics/profile_1758013912.jpeg','uploads/documents/visa_1758013912.pdf','uploads/documents/passport_1758013912.pdf',0,'',NULL,NULL,NULL,NULL,'68c927126b73c68c927126b73e','2025-09-16 09:11:52',NULL,2,0,4),
(186,'CME0120','Mudassara','Atif','Mudassara Atif','mudassaraatif794','mudassaraatif794@gmail.com','$2y$10$fq50YdAGjA.S9d2jHD0r8Osjj.v8We/0mQai4C3aC3PbcH2aw0Fui','2006-11-19','Female','','','0582591417','Dubai.\\r\\nGali Haji Ghulam Rasool Waali, Mohalla Basti Balochaan, Tal & Dist: Shaikho Poora, Pakistan.','United Arab Emirates','BA',NULL,NULL,'','active',0,NULL,'2025-07-14','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','','','NA','','','201/2024/3605205','Family Visa','2024-06-05','2026-06-04','AX1480181','Ordinary','2022-07-27','2027-07-26','Pakistan','Pakistan','','uploads/documents/visa_1758265506.pdf','uploads/documents/passport_1758265506.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68ccfd758c13d68ccfd758c13e','2025-09-19 07:05:06',NULL,2,0,4),
(187,'CME0121','Indarayas','Masih','Indarayas Masih','gill69602','gill69602@gmail.com','$2y$10$4PcvG3eTEkDwkYHC8Z7xIOSYdv/KqlM7Nk7gFm7rYdobFFxljGTaq','1994-04-04','Male','Married','B+','05447738801','Dubai.\\r\\nHouse No: NW-56, Street No: 3, Mohalla: Raja Sultan, Rawalpindi, Pakistan.','United Arab Emirates','12th','2015-07-15','2017-06-08','Board of Intermediate and Secondary Education Gujranwala. Pakistan.','active',0,NULL,'2025-08-18','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','','','NA','','','201/2024/7722518','Other','2024-05-21','2026-05-20','EN0846802','Ordinary','2021-09-14','2026-09-13','Pakistan','Pakistan','uploads/profile_pics/profile_1758267179.jpeg','uploads/documents/visa_1758267179.pdf','uploads/documents/passport_1758267179.pdf',0,'',NULL,NULL,NULL,NULL,'68cd024c1ce0a68cd024c1ce0b','2025-09-19 07:32:59',NULL,2,0,4),
(188,'CME0122','Riaz','Ahmad','Riaz Ahmad','greatroyal','greatroyal@gmail.com','$2y$10$s30A95iyp7hvcvKaewwEGOGZ41CGtJDt5K/vmzIqdvf.S6B0Brkle','1989-12-12','Male','Married','A+','0589786063','Dubai.\\r\\nHouse No: 26/2, Mohalla: Impress Road, Lahore, Pakistan.','United Arab Emirates','Diploma Of Associate Engineer. Electronics. (D.A.E.)','2005-08-20','2008-12-12','Punjab Board Of Technical Education Lahore.','active',0,NULL,'2025-08-12','','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB297536273AE',NULL,'NA',NULL,NULL,'206/2025/87596612','Employement Visa','2025-07-17','2025-09-14','XO1162602','Regular','2024-08-06','2029-08-05','Pakistan','Pakistan','uploads/profile_pics/profile_1758286221.jpg','uploads/documents/visa_1758286221.pdf','uploads/documents/passport_1758286221.pdf',0,'130191184','2025-09-01','2027-08-31',NULL,NULL,'68cd4d423e73568cd4d423e736','2025-09-19 12:50:21',NULL,2,0,4),
(189,'CME0123','Neha','Jabeen','Neha Jabeen','nehajabeenuae','nehajabeenuae@gmail.com','$2y$10$S2Mz7gm1bqVk2AfWn/jY0ezhcT8tUp90JuC6FqtVlBhFRWRrxTXVG','1997-08-19','Female','Married','','0561780504','Rolla Street, Bur Dubai, U.A.E.\\r\\n1-7-904/1, Mohan Nagar, Ramnagar, Hyderabad, Pin: 500020, Telangana, India.','United Arab Emirates','B-Ed ','2022-08-20','2024-06-15','Osmania University, Hyderabad, Telangana, India.','active',0,NULL,'2025-08-29','','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','201/2025/11400886922','Job Search Visa','2025-07-24','2025-09-21',' Y2031464','Ordinary','2024-04-13','2034-04-12','India','Hyderabad','uploads/profile_pics/profile_1758289146.jpeg','uploads/documents/visa_1758289146.pdf','uploads/documents/passport_1758289146.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68cd5af1db7ef68cd5af1db7f0','2025-09-19 13:39:06',NULL,2,0,4),
(190,'CME0124','Misbah Uddin','Mohammed','Misbah Uddin Mohammed','mohammedmisbahuae','mohammedmisbahuae@gmail.com','$2y$10$WDHfWs4Nu0acrmg46bK/zuT3no5t5zAXht8ZIuiN3CsL7i3m/HH/m','1997-09-29','Male','Married','','0543778599','Rolla Street, Bur Dubai, Dubai.\\r\\n1-7-38/3/1/A, Risala Azamabad, Musheerabad, Hyderabad, Pin: 500020, Telangana, India.','United Arab Emirates','MSC','2022-06-20','2024-10-08','BPP University','inactive',0,NULL,'2025-09-01','','','','user',NULL,'Relationship officer','CME0014','1','2025-09-27','','','','NA','','','201/2025/11400886902','Job Search Visa','2025-07-24','2025-09-21','S 1061766','Ordinary','2018-05-09','2028-05-08','India','Hyderabad','uploads/profile_pics/profile_1758290227.jpeg','uploads/documents/visa_1758290227.pdf','uploads/documents/passport_1758290227.pdf',0,'',NULL,NULL,NULL,NULL,'68cd5e292b12d68cd5e292b12e','2025-09-19 13:57:07',NULL,2,0,4),
(191,'CME0125','Aqueeb','Ahmed','Aqueeb Ahmed','aqueebahmed25','aqueebahmed25@gmail.com','$2y$10$o0FarswEQuOZ.LEUKksQ/uzcuqDoK4jG0p.iX2JVw0WkBMqb0zfim','2003-01-04','Male','Single','','0544924321','Dubai, UAE.\\r\\nHouse No: 8-3-73, Dharu Gally, Nizamabad, Pin: 503001, Telangana, India.','United Arab Emirates','BE-IT','2021-09-15','2025-05-27','Lords Institute Of Engineering And Technology.','active',0,NULL,'2025-09-01','','','','user',NULL,'IT Officer','CME0001','',NULL,'','','','NA','','','201/2025/11400786361','Job Search Visa','2025-07-01','2025-09-21','C2037933','Ordinary','2024-09-13','2034-09-12','India','Hyderabad','uploads/profile_pics/profile_1758291310.jpg','uploads/documents/visa_1758291310.pdf','uploads/documents/passport_1758291310.pdf',0,'',NULL,NULL,NULL,NULL,'68cd621f661f368cd621f661f4','2025-09-19 14:15:10',NULL,4,0,4),
(192,'CME0126','Jaganathan','Gopal','Jaganathan Gopal','jagan.jaganathandxb9','jagan.jaganathandxb9@gmail.com','$2y$10$4BtANbvF62tQriWPYeNEz.jJq0grtzScQyguLyPh8Y3Wff/7uGV76','1981-02-25','Male','Married','','0563408279','Satwa, Dubai, India.\\r\\nTC21/1787 Abin Nivas Nedumcaud Karamana Po, Trivandrum, Pin: 695002, Kerala, India.','United Arab Emirates','12th','1994-06-10','1996-03-15','Department Of General Education Kerala State, Government Of Kerala.','active',0,NULL,'2025-09-10','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB299360355AE','','NA','','','203/2024/2/62340','Employement Visa','2024-12-27','2026-12-26','V7962127','Ordinary','2022-06-14','2032-06-13','United Arab Emirates','Dubai','uploads/profile_pics/profile_1758357313.jpg','uploads/documents/visa_1758357313.pdf','uploads/documents/passport_1758357313.pdf',0,'',NULL,NULL,NULL,NULL,'68ce63ef5bbb868ce63ef5bbb9','2025-09-20 08:35:13',NULL,2,0,4),
(193,'CME0127','Mohammad','Zainey','Mohammad Zainey','mohdzainey1','mohdzainey1@gmail.com','$2y$10$hYVVczKX1qe9QsfJdgWY4uA6SXJFfueEfnPbqrrh4Rd98WWBFsVS.','1988-12-15','Male','Married','','0583044170','Dubai.\\r\\n294/53 Naubasta Road Girdhari Singh Enclave Near Char Minari Masjid, Lucknow, Pin: 226003, Uttar Pradesh, India.','United Arab Emirates','12th','2005-07-15','2007-03-10','Government School Of Uttar pradesh.','active',0,NULL,'2025-09-13','Dubai','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','201/2025/11400993551','Job Search Visa','2025-08-21','2025-10-19','X5552995','Ordinary','2023-06-27','2033-06-26','India','Lucknow','uploads/profile_pics/profile_1758358389.jpeg','uploads/documents/visa_1758358389.pdf','uploads/documents/passport_1758358389.pdf',0,'',NULL,NULL,NULL,NULL,'68ce6843c48d168ce6843c48d2','2025-09-20 08:53:09',NULL,2,0,4),
(194,'CME0128','Muhammad','Asad','Muhammad Asad','asadmahar161','asadmahar161@gmail.com','$2y$10$EgWIU9liGVyFt1qNd939aOutHtjulP.V2s8/FptgMyhnZdPcBCKHO','1999-12-14','Male','Single','','0551946492','Dubai.\\r\\nChaah Dost Wala, Daak Khana Koat Adoo, Kotla, Tal & Dist: Koat Adoo, Pakistan.','United Arab Emirates','Bsc. Agriculture Plant.','2018-07-15','2021-04-10','Pir Mehr ali Shah Arid Agriculture University.','active',0,NULL,'2025-09-09','Dubai','','','user',NULL,'Relationship officer','CME0008','',NULL,'','MB297443926AE','','NA','','','202/2025/2586054','Employement Visa','2025-09-16','2027-09-15','BW1877411','Ordinary','2023-09-19','2033-09-18','Pakistan','Pakistan','uploads/profile_pics/profile_1758632244.jpeg','uploads/documents/visa_1758632244.pdf','uploads/documents/passport_1758632244.pdf',0,'',NULL,NULL,NULL,NULL,'68d2921c3493368d2921c34934','2025-09-23 12:57:24',NULL,2,0,4),
(195,'CME0129','Ravindra ','Naik Meeravath ','Ravindra  Naik Meeravath ','ravindranaik9944','ravindranaik9944@gmail.com','$2y$10$IrTvjWP4FabYwGYguzDwAOAz8/MzMa2gTfk61SDhSPdHxYcvrQ0zG','1999-01-07','Male','Single','','0544992245','Al Nahnda 1 Deria Dubai','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-09-24','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','78199936597311','Student','2025-08-05','2026-08-04','V3156130','Ordinary','2021-10-06','2031-10-05','India','andar pardesh','uploads/profile_pics/profile_1758957675.jpg','uploads/documents/visa_1758957675.pdf','uploads/documents/passport_1758957675.pdf',0,'',NULL,NULL,NULL,NULL,'68d78a5143ec968d78a5143eca','2025-09-27 07:21:15',NULL,2,1,4),
(196,'CME0130','Riyaz','Koonamkandy Shamsu','Riyaz Koonamkandy Shamsu','riyazvsnl','riyazvsnl@gmail.com','$2y$10$15E99QNoNkKLGprc0h569OXEKHN3fBVfF/04t3UAGHPd10nkKQUBG','1983-12-26','Male','Married','A+','0524019790','Deira, Dubai.\\r\\nH.No.12/16, Cochin College Road, Panayappilly Kochi, Ernakulam, Pin: 682002, Kerala, India.','United Arab Emirates','Computer Hardware and Network Engineering','2002-09-23','2003-03-12','Accel IT Academy.','active',0,NULL,'2025-10-07','Dubai','','','user',NULL,'Relationship officer','CME0059','',NULL,'','',NULL,'NA',NULL,NULL,'201/2025/1140/1086180','Job Search Visa','0225-09-13','2025-11-11','R5237377','Regular','2017-10-25','2027-10-24','India','Cochin','196_1760365489.jpeg','','',0,NULL,NULL,NULL,NULL,NULL,'68ecc7549973768ecc75499738','2025-10-13 09:51:57',NULL,2,0,4),
(197,'CME0131','Muhammad','Azair','Muhammad Azair','m.azair4747','m.azair4747@gmail.com','$2y$10$KwDhWyu4OP7fJYw.WkcwLeQeeGaM1J5BXlvLzjSq9PfeFnjkqfmgS','1987-07-22','Male','Married','','0526711727','Al Rigga, Deira, Dubai.\\r\\nNaw Shahra Road, House No: 2, Street No: 1, Mohalla: Chirag Nagar, Gujraanwala, Tal & Dist: Gujraanwala, Pakistan.','United Arab Emirates','BA','2009-08-15','2011-03-12','University Of The Punjab, Pakistan.','active',0,NULL,'2025-10-07','','','','user',NULL,'Relationship officer','CME0059','',NULL,'','','','NA','','','101/2022/2/319587','Employement Visa',NULL,'2025-09-15','AE1890952','Ordinary','2021-10-25','0031-10-24','Pakistan','Pakistan','','uploads/documents/visa_1760351025.pdf','uploads/documents/passport_1760351025.pdf',0,'',NULL,NULL,NULL,NULL,'68ecce9f69f1768ecce9f69f18','2025-10-13 10:23:45',NULL,2,0,4),
(198,'CME0132','Isha','Gulshad','Isha Gulshad','ishagulshad085','ishagulshad085@gmail.com','$2y$10$/5HBtk0gg9odYpDb836ane/Y7XU49jwCncuItS1vXKva52c3k/cDG','2003-12-27','Female','Single','A+','0506895468','Al Hamriya, Near Shraf DG, Dubai.\\r\\n68, Cowies Ghat Road, Howrah, Pin: 711102, West Bengal, India.','United Arab Emirates','B.com','2022-09-12','2025-07-31','university Of Calcutta','active',0,NULL,'2025-10-09','Dubai','','','user',NULL,'Relationship officer','CME0007','',NULL,'','',NULL,'NA',NULL,NULL,'201/2025/11401144487','Job Search Visa','2025-09-25','2025-11-23','B9500016','Regular','0000-00-00','2034-01-29','India','Kolkata','198_1760363579.jpeg','','',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','68ecf9c363b6368ecf9c363b64','2025-10-13 13:32:49',NULL,2,0,4),
(199,'CME0133','Sowjanya','Gannerla','Sowjanya Gannerla','sowjanyaaerukonda426','sowjanyaaerukonda426@gmail.com','$2y$10$CP1OLGwpkORvdOHqlBOWx.9hVcx5fqQ5FJkaVWm1qxffwtiGa/Z4e','1988-04-30','Female','Married','','0521297836','315, concord 3rd Building, Muhaisnah, Dubai.\\r\\nHouse No: 1-13, Bogaram, Keesar Mandal, Medchal, Pin: 501301, Telangana, India.','United Arab Emirates','Master Of Arts.','2015-08-15','2017-06-10','Osmania University.','active',0,NULL,'2025-10-09','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','1235546512','',NULL,NULL,'C6730709','Ordinary','0224-12-18','2034-12-17','India','Hyderabad','uploads/profile_pics/profile_1760365389.jpeg','','uploads/documents/passport_1760365389.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68ed0aa37b2f368ed0aa37b2f4','2025-10-13 14:23:09',NULL,2,0,4),
(200,'CME0134','Analeen Molina','Pascual','Analeen Molina Pascual','analeen.m.pascual','analeen.m.pascual@gmail.com','$2y$10$3KPHduU5vesQBCQU987cj.OMaPeZrFn03KzoX5/Hml8/NrDzb/nWe','2000-11-18','Female','Single','','0561832285','Al Rigga, Dubai.\\r\\nNueva Ecija, Philippines.','United Arab Emirates','B.B.A','2020-08-15','2023-03-10','Central Luzaon State University Science City Of Munoz Nueva Ecija, Philippines.','active',0,NULL,'2025-10-13','Dubai','','','user',NULL,'Relationship officer','CME0014','',NULL,'','','','NA','','','12234545641','',NULL,NULL,'','',NULL,NULL,'','','','','',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68ef549ab4ec268ef549ab4ec3','2025-10-15 08:15:29',NULL,2,0,4),
(202,'CME0135','Muhamed Shahbas','','Muhamed Shahbas ','muhamedshahbasazeez','muhamedshahbasazeez@gmail.com','$2y$10$m2Kzmyd9XXduxM5LTjAibOIVGf2FDFikADgdTzmppsptCDmPPm7KC','1999-08-25','Male','Single','','0545484551','Sharjah, U.A.E\\r\\nPP V/486A, Sharjah Palace, OPP Government Hospital PO Padanna Kasaragod DT Kerala, 671312. India.','United Arab Emirates','B.com','2017-09-15','2020-06-10','Manipal Academy Of Higher Education.','active',0,NULL,'2025-10-13','Dubai','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','154531351486416','',NULL,NULL,'R8151690','Ordinary','2017-08-28','2027-08-27','United Arab Emirates','Dubai','uploads/profile_pics/profile_1760520367.JPG','','uploads/documents/passport_1760520367.pdf',0,'',NULL,NULL,NULL,NULL,'68ef667d9f6bf68ef667d9f6c1','2025-10-15 09:26:07',NULL,2,0,4),
(203,'CME0136','Tayyaba Kiran','Qureshi','Tayyaba Kiran Qureshi','tabiqureshi541','tabiqureshi541@gmail.com','$2y$10$UAs7MJuEWmgWXBtcj/2EUOfI.nWiHXvNjPqmPHk3BoTnMoA.iAbEC','1995-02-06','Female','Single','','0508672880','Moon Valley Hotel Apartment, Burjman, Dubai.\\r\\nHouse No: 2, Street No: 21, Mohalla Qureshi Mazang Janaaz gaah, Lahor, Pakistan.','United Arab Emirates','B.com','2013-08-15','2016-03-10','Lahore, Pakistan.','active',0,NULL,'2025-10-13','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','202/2025/2369302','Employement Visa','2025-08-26','2027-08-25','BN4907981','Ordinary','2023-08-02','2028-08-01','Pakistan','Pakistan','uploads/profile_pics/profile_1760521928.jpeg','uploads/documents/visa_1760521928.pdf','uploads/documents/passport_1760521928.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68ef6b08c9ee068ef6b08c9ee2','2025-10-15 09:52:08',NULL,2,0,4),
(204,'CME0137','Shashank','Anitha Parameshwarappa','Shashank Anitha Parameshwarappa','apshashanka','apshashanka@gmail.com','$2y$10$vMFHzpGzfchDRpdwJJbf/eYzsLoNCHmdfqVD2bml10BTieVVFW9s2','2004-10-02','Male','Single','A+','0563135733','Al Hamriya, Bur Dubai, Dubai.\\r\\nAgumbe Road, Thirthahalli, Shivamogga,Pin: 577432, Karnataka, India.','United Arab Emirates','BSS Diploma In Fire & Industrial Safety Management.','2023-03-15','2024-04-10','Bharat Sevak Samaj, National Development Agency Government Of India.','active',0,NULL,'2025-10-11','Dubai','','','user',NULL,'Relationship officer','CME0059','',NULL,'','',NULL,'NA',NULL,NULL,'201/2025/11400996329','Job Search Visa','2025-08-21','2025-10-19','B6800730','Regular','2023-10-19','2033-10-18','India','Bengaluru','204_1760599147.jpeg','uploads/documents/visa_1760523915.pdf','uploads/documents/passport_1760523915.pdf',0,NULL,NULL,NULL,NULL,NULL,'68ef72547c1ed68ef72547c1ee','2025-10-15 10:25:15',NULL,2,0,4),
(205,'CME0138','MD Abdul Aziz','','MD Abdul Aziz ','aziz_iiuc31','aziz_iiuc31@yahoo.com','$2y$10$16XVL6EKdENqr3MRJTsYGO5EOdd4B1UmRHYvoTO7xRovejd5Y1dza','1989-12-15','Male','Single','','0501480557','Al Talal Building, Al Wahda, Sharjah.\\r\\nAli Akbar Dail Chowdhury Para, Ward No: 01, Kutubdia, 4720, Cox\\\'s Bazar, Bangladesh.','United Arab Emirates','M.B.A In Finance And Banking.','2011-08-15','2013-03-10','International Islamic University Chittagong, Bangladesh.','active',0,NULL,'2025-10-10','Dubai','','','user',NULL,'Relationship officer','CME0007','',NULL,'','','','NA','','','202/2025/2569441','Job Search Visa','2025-08-23','2025-10-21','A00683352','Ordinary','2021-08-31','2031-08-30','Bangladesh','DIP/Dhaka.','uploads/profile_pics/profile_1760602267.jpeg','uploads/documents/visa_1760602267.pdf','uploads/documents/passport_1760602267.pdf',0,'',NULL,NULL,NULL,NULL,'68f0a4b95bf8068f0a4b95bf81','2025-10-16 08:11:07',NULL,2,0,4),
(206,'CME0139','Mansi','Sharma','Mansi Sharma','ms22615','ms22615@gmail.com','$2y$10$OxXNQlnwQGEqqksxCuDmx.h/EA8rLEqCH7d5ex9lXtVbcGY6QZfKe','1980-12-19','Female','Married','','0508035869','1001, Azizi candace Acacia, Al Furjan Jabel.\\r\\nB 344, 4th Floor, G D Colony, Mayur Vihar Phase 3, Delhi, Pin: 110096, Delhi, India.','United Arab Emirates','BA','2000-08-15','2002-03-10','University Of Delhi.','active',0,NULL,'2025-10-14','Dubai','','','user',NULL,'Relationship officer','CME0059','',NULL,'','','','NA','','','201/2025/3212369','Family Visa','2025-06-24','2027-06-23','C5604124','Ordinary','2024-12-05','2034-12-04','India','Delhi','uploads/profile_pics/profile_1760603845.jpg','uploads/documents/visa_1760603845.pdf','uploads/documents/passport_1760603845.pdf',0,'',NULL,NULL,'Not Pregnant','0000-00-00','68f0aa3aac2f168f0aa3aac2f2','2025-10-16 08:37:25',NULL,2,0,4),
(207,'CME0140','Shukran Ali','Faridi','Shukran Ali Faridi','shukranfaridi16','shukranfaridi16@gmail.com','$2y$10$pFjAUH8ccXp.HVyXYySHIedsYuw1MTbaALmqh1zrbUsOzKZM9Y0zK','1997-02-16','Male','Single','','0522126711','Al Nahda, Dubai.\\r\\n','United Arab Emirates','NA',NULL,NULL,'','active',0,NULL,'2025-10-07','Dubai','','','user',NULL,'Relationship officer','CME0013','',NULL,'','','','NA','','','15341841864','',NULL,NULL,'145135','',NULL,NULL,'','','','','',0,'',NULL,NULL,NULL,NULL,'68f0afd46e11d68f0afd46e11f','2025-10-16 08:49:57',NULL,2,0,4),
(208,'CME0141','Mohammad ','Asim','Mohammad  Asim','mohammed.asim','mohammed.asim@communikmarketing.com','$2y$10$29Zoq4nTFJC/h4aqmBuABu3Eq6URdNUXotpw7M/Yu2PXcFQmUz/p2','1998-03-14','Male','','','0566547572','Kartinka India','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-10-20','Dubai','','','user',NULL,'Sales Manager','CME0003','',NULL,'','','','NA','','','20220222463296','Emp Visa Change Status','2025-01-28','2027-01-27','P6363955','Ordinary','2016-11-29','2026-11-28','India','Bengaluru','uploads/profile_pics/profile_1761037675.jpg','uploads/documents/visa_1761037675.jpg','uploads/documents/passport_1761037675.pdf',0,'',NULL,NULL,NULL,NULL,'68f74bf1d02f068f74bf1d02f1','2025-10-21 09:07:55',NULL,2,0,4),
(209,'CME0142','Dipak','Wagh','Dipak Wagh','dipak.wagh02','dipak.wagh02@gmail.com','$2y$10$KJ8Y7KM.LpWvnZB8vK24CuOnvbIoeSmAEGxXFcsyVhwoDxwncovxK','1987-11-03','Male','','','0583008367','India','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2025-10-14','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','201202511401001052','Tourist','2025-08-22','2025-10-20','R8479808','Ordinary','2018-04-17','2028-04-16','India','Gujrat','uploads/profile_pics/profile_1761039092.jpg','uploads/documents/visa_1761039092.pdf','uploads/documents/passport_1761039092.pdf',0,'',NULL,NULL,NULL,NULL,'68f751802d33b68f751802d33c','2025-10-21 09:31:32',NULL,2,1,4),
(210,'CME0143','Mohammed ','Shahjad','Mohammed  Shahjad','mohdshahjad368','mohdshahjad368@gmail.com','$2y$10$fLyTsE1DCgLyzZkeW1mnlOtzZZHqhtEoyEyixxEW2Rbp3H1VJRHqq','1993-12-03','Male','','','0553786884','India','United Arab Emirates','',NULL,NULL,'','active',0,NULL,'2026-10-10','Dubai','','','user',NULL,'Relationship officer','CME0020','',NULL,'','','','NA','','','201202511401006628','Tourist','2025-08-25','2025-10-23','C7564484','Ordinary','2025-03-28','2035-03-27','India','uttar pardesh','uploads/profile_pics/profile_1761039652.jpg','uploads/documents/visa_1761039652.pdf','uploads/documents/passport_1761039652.pdf',0,'',NULL,NULL,NULL,NULL,'68f753bc2ca3368f753bc2ca34','2025-10-21 09:40:52',NULL,2,1,4),
(211,'CME0144','Aruna ','Nair BS','Aruna  Nair BS','arunainairbsf2yfc_txw','arunainairbsf2yfc_txw@indeedemail.com','$2y$10$NMUEkgtGtOWm4FUzKeU9OOx3Wxyq9y4STLChysVQJferUpoDpNl6C','1980-05-30','Female','Single','A+','0505883374','india','United Arab Emirates',NULL,NULL,NULL,NULL,'active',0,NULL,'2025-09-27','','','','user',NULL,'Relationship officer','CME0020','',NULL,'','',NULL,'NA',NULL,NULL,'784198093748311','Student','2024-10-31','2025-10-30','X9549675','Regular','2023-05-12','2033-05-11','India','Kerala','211_1761040515.PNG','uploads/documents/visa_1761040319.jpg','uploads/documents/passport_1761040319.pdf',0,NULL,NULL,NULL,'Not Pregnant','0000-00-00','68f756436168b68f756436168c','2025-10-21 09:51:59',NULL,2,1,4);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_comments`
--

DROP TABLE IF EXISTS `esign_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_comments`
--

LOCK TABLES `esign_comments` WRITE;
/*!40000 ALTER TABLE `esign_comments` DISABLE KEYS */;
INSERT INTO `esign_comments` VALUES
(1,3,4,'','2025-06-20 06:37:20'),
(2,3,2,'','2025-06-20 17:51:57'),
(3,4,4,'','2025-06-25 10:58:21');
/*!40000 ALTER TABLE `esign_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_documents`
--

DROP TABLE IF EXISTS `esign_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `expiry_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_documents`
--

LOCK TABLES `esign_documents` WRITE;
/*!40000 ALTER TABLE `esign_documents` DISABLE KEYS */;
INSERT INTO `esign_documents` VALUES
(2,'testtt','asdfasdf','../uploads/documents/684dd5c3b9bfd_1749931459.pdf','pdf',4,'2025-06-14 20:04:19','pending',1,2,0,'policy','2025-06-21'),
(3,'legal','test tst test tett ','../uploads/documents/685501672bb0c_1750401383.pdf','pdf',4,'2025-06-20 06:36:23','completed',1,0,0,'agreement','2025-06-22'),
(4,'test','test','../uploads/documents/685bd5c7977ca_1750848967.pdf','pdf',4,'2025-06-25 10:56:07','pending',1,0,0,'other','2025-06-28'),
(5,'tswtset','dfasdfsdf ','../uploads/documents/685be068036ea_1750851688.pdf','pdf',4,'2025-06-25 11:41:28','pending',2,0,0,'agreement','2025-06-28');
/*!40000 ALTER TABLE `esign_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_notifications`
--

DROP TABLE IF EXISTS `esign_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_notifications`
--

LOCK TABLES `esign_notifications` WRITE;
/*!40000 ALTER TABLE `esign_notifications` DISABLE KEYS */;
INSERT INTO `esign_notifications` VALUES
(1,3,4,'Your document has been fully approved',0,'2025-06-20 17:51:57');
/*!40000 ALTER TABLE `esign_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_settings`
--

DROP TABLE IF EXISTS `esign_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `signature_type` varchar(20) NOT NULL DEFAULT 'draw',
  `notification_email` tinyint(1) NOT NULL DEFAULT 1,
  `notification_sms` tinyint(1) NOT NULL DEFAULT 0,
  `default_expiry` int(11) NOT NULL DEFAULT 30,
  `auto_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_days` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_settings`
--

LOCK TABLES `esign_settings` WRITE;
/*!40000 ALTER TABLE `esign_settings` DISABLE KEYS */;
INSERT INTO `esign_settings` VALUES
(1,4,'upload',1,0,30,1,3,'2025-06-14 19:43:49','2025-06-14 19:43:49');
/*!40000 ALTER TABLE `esign_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_templates`
--

DROP TABLE IF EXISTS `esign_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_templates`
--

LOCK TABLES `esign_templates` WRITE;
/*!40000 ALTER TABLE `esign_templates` DISABLE KEYS */;
INSERT INTO `esign_templates` VALUES
(1,'fsdfasd','asfasdfasf','other','template_684dcaa2bcccd6.61546279.pdf',4,'2025-06-14 19:16:50','2025-06-14 19:16:50');
/*!40000 ALTER TABLE `esign_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esign_workflow`
--

DROP TABLE IF EXISTS `esign_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `esign_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `approver_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `approver_id` (`approver_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esign_workflow`
--

LOCK TABLES `esign_workflow` WRITE;
/*!40000 ALTER TABLE `esign_workflow` DISABLE KEYS */;
INSERT INTO `esign_workflow` VALUES
(1,2,0,2,0,'pending',NULL,NULL,NULL,NULL,'2025-06-14 20:04:19','2025-06-20 17:57:46','department_head'),
(2,2,1,78,0,'pending',NULL,NULL,NULL,NULL,'2025-06-14 20:04:19','2025-06-20 17:57:46','employee'),
(3,2,2,4,0,'','2025-06-15 06:46:57','draw','uploads/signatures/signature_2_4_1749970017.png','','2025-06-14 20:04:19','2025-06-20 17:57:46','department_head'),
(4,3,0,2,0,'approved','2025-06-20 17:51:57','draw','uploads/signatures/signature_3_2_1750441917.png','','2025-06-20 06:36:23','2025-06-20 17:57:46','department_head'),
(5,3,1,4,0,'approved','2025-06-20 06:37:20','draw','uploads/signatures/signature_3_4_1750401440.png','','2025-06-20 06:36:23','2025-06-20 17:57:46','department_head'),
(6,3,2,77,0,'pending',NULL,NULL,NULL,NULL,'2025-06-20 06:36:23','2025-06-20 17:57:46','employee'),
(7,4,0,2,0,'pending',NULL,NULL,NULL,NULL,'2025-06-25 10:56:07','2025-06-25 10:56:07','head'),
(8,4,1,4,0,'approved','2025-06-25 10:58:21','draw','uploads/signatures/signature_4_4_1750849101.png','','2025-06-25 10:56:07','2025-06-25 10:58:21','head'),
(9,4,2,141,0,'pending',NULL,NULL,NULL,NULL,'2025-06-25 10:56:07','2025-06-25 10:56:07','emp'),
(10,5,0,2,0,'approved','2025-06-29 19:53:32',NULL,'uploads/signatures/hr_signature_5_1751226812.png','fsdfsdfsdf','2025-06-25 11:41:28','2025-06-29 19:53:32','head'),
(11,5,1,4,0,'pending',NULL,NULL,NULL,NULL,'2025-06-25 11:41:28','2025-06-25 11:41:28','head'),
(12,5,2,104,0,'pending',NULL,NULL,NULL,NULL,'2025-06-25 11:41:28','2025-06-25 11:41:28','emp');
/*!40000 ALTER TABLE `esign_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_pt`
--

DROP TABLE IF EXISTS `event_pt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_pt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_starting_time` time DEFAULT NULL,
  `event_ending_time` time DEFAULT NULL,
  `venue_address` text DEFAULT NULL,
  `title_of_participation` varchar(255) DEFAULT NULL,
  `additional_information` text DEFAULT NULL,
  `admin_remark` varchar(100) DEFAULT 'Applied',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_pt`
--

LOCK TABLES `event_pt` WRITE;
/*!40000 ALTER TABLE `event_pt` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_pt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `starting_time` time NOT NULL,
  `ending_time` time NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_about_us`
--

DROP TABLE IF EXISTS `guest_about_us`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_about_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_about_us`
--

LOCK TABLES `guest_about_us` WRITE;
/*!40000 ALTER TABLE `guest_about_us` DISABLE KEYS */;
INSERT INTO `guest_about_us` VALUES
(1,'Our Mission','At Employeehub, we\'re all about making employee management a breeze. Our EMS Hub is designed to streamline tasks, boost productivity, and keep everyone on track.','65fc45a1aab5b_about-mission.jpg','ion-ios-speedometer-outline'),
(2,'Our Plan','From leave applications to project management, we\'ve got it all covered. Say goodbye to the tedious process and hello to seamless efficiency!','65fc45f7c4899_about-plan.jpg','ion-ios-list-outline'),
(3,'Our Vision','We on a mission to revolutionize the way businesses manage the employees. Our EMS Hub is the ultimate solution for efficient employee management.','65fc462290374_about-vision.jpg','ion-ios-eye-outline');
/*!40000 ALTER TABLE `guest_about_us` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_benefits`
--

DROP TABLE IF EXISTS `guest_benefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_benefits`
--

LOCK TABLES `guest_benefits` WRITE;
/*!40000 ALTER TABLE `guest_benefits` DISABLE KEYS */;
INSERT INTO `guest_benefits` VALUES
(1,'ion-ios-bookmarks-outline','Future of Employee Management ','Explore the future of employee management and the role of technology in shaping the workplace of tomorrow. '),
(2,'ion-ios-stopwatch-outline','Future of Employee Management','Explore the future of employee management and the role of technology in shaping the workplace of tomorrow.'),
(3,'ion-ios-heart-outline','ion-ios-heart-outline','Workspace\', \'With a focus on productivity, simplicity, and innovation, we\'re here to take your workforce management to the next level.');
/*!40000 ALTER TABLE `guest_benefits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_contact`
--

DROP TABLE IF EXISTS `guest_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `para` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_contact`
--

LOCK TABLES `guest_contact` WRITE;
/*!40000 ALTER TABLE `guest_contact` DISABLE KEYS */;
INSERT INTO `guest_contact` VALUES
(1,'ion-ios-location-outline','Address','Sec 23A,Gurugram,Haryana-122017, India'),
(2,'ion-ios-telephone-outline','Phone Number','+91 70200 78847'),
(3,'ion-ios-email-outline','Email','info@san-solutions.in');
/*!40000 ALTER TABLE `guest_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_facts`
--

DROP TABLE IF EXISTS `guest_facts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_facts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_facts`
--

LOCK TABLES `guest_facts` WRITE;
/*!40000 ALTER TABLE `guest_facts` DISABLE KEYS */;
INSERT INTO `guest_facts` VALUES
(1,'274','clients'),
(2,'421','projects'),
(3,'1,364','Hours Of Support '),
(4,'18','Hard Workers ');
/*!40000 ALTER TABLE `guest_facts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_header`
--

DROP TABLE IF EXISTS `guest_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_header` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_header`
--

LOCK TABLES `guest_header` WRITE;
/*!40000 ALTER TABLE `guest_header` DISABLE KEYS */;
INSERT INTO `guest_header` VALUES
(1,'index.php','HOME','emps/index.php'),
(6,'contact.php','CONTACT','contact.php'),
(7,'login.php','LOGIN','login.php');
/*!40000 ALTER TABLE `guest_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_our_clients`
--

DROP TABLE IF EXISTS `guest_our_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_our_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_our_clients`
--

LOCK TABLES `guest_our_clients` WRITE;
/*!40000 ALTER TABLE `guest_our_clients` DISABLE KEYS */;
INSERT INTO `guest_our_clients` VALUES
(1,'strider','client-1.png'),
(2,'runtastic','client-2.png'),
(3,'Editshare','client-3.png'),
(4,'InFocus','client-4.png'),
(5,'Gategroup','client-5.png'),
(6,'cadent','client-6.png'),
(7,'ceph','client-7.png'),
(8,'alitalia','client-8.png');
/*!40000 ALTER TABLE `guest_our_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_our_portfolio`
--

DROP TABLE IF EXISTS `guest_our_portfolio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_our_portfolio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_our_portfolio`
--

LOCK TABLES `guest_our_portfolio` WRITE;
/*!40000 ALTER TABLE `guest_our_portfolio` DISABLE KEYS */;
INSERT INTO `guest_our_portfolio` VALUES
(1,'Android Development','Java and kotlin','app1.jpg','app'),
(2,'Front-End','HTML & CSS','web3.jpg','web'),
(3,'Flutter Framework','dart','app2.jpg','app'),
(4,'Database','Oracle','card2.jpg','db'),
(5,'Framework','Laravel & Codegniter','web2.jpg','web'),
(6,'Diverse App Languages','Swift / Objective-C','app3.jpg','app'),
(7,'Database','SQL','card1.jpg','db'),
(8,'Database','MongoDB','card3.jpg','db'),
(9,'Back-End','Python, Java','web1.jpg','web');
/*!40000 ALTER TABLE `guest_our_portfolio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_our_skills`
--

DROP TABLE IF EXISTS `guest_our_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_our_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `progress` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_our_skills`
--

LOCK TABLES `guest_our_skills` WRITE;
/*!40000 ALTER TABLE `guest_our_skills` DISABLE KEYS */;
INSERT INTO `guest_our_skills` VALUES
(1,'php','100','success'),
(2,'javascript','90','info'),
(3,'java','75','warning'),
(4,'ruby','55','danger');
/*!40000 ALTER TABLE `guest_our_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_services`
--

DROP TABLE IF EXISTS `guest_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_services`
--

LOCK TABLES `guest_services` WRITE;
/*!40000 ALTER TABLE `guest_services` DISABLE KEYS */;
INSERT INTO `guest_services` VALUES
(1,'Request leaves','Enable the employees to conveniently request leaves online. It streamlines the leave application process, making it efficient and accessible. Employees can specify the type of leave, duration, and any additional notes for approval.','ion-ios-analytics-outline'),
(2,'Business Tour Request','Employees can submit travel requests for work-related trips. This streamlines the approval process for business tours, allowing staff to provide necessary details such as destination, purpose, and expected duration.','ion-ios-bookmarks-outline'),
(3,'Salary Information','Allows employees to access detailed information about their compensation. They can view their base salary, bonus details, and the total amount, providing transparency and clarity regarding their financial remuneration.','ion-ios-paper-outline'),
(4,'Task Management','Employees can efficiently manage their tasks and projects through this feature. It provides a centralized platform where staff can view their assigned tasks, track progress, and stay organized with their work responsibilities.','ion-ios-speedometer-outline'),
(5,'Employee Leaderboard','Fostering a sense of healthy competition, the Employee Leaderboard allows employees to view the names and points of their colleagues, encouraging friendly competition, recognition, and motivates employees to excel in their roles.','ion-ios-barcode-outline'),
(6,'View Project Details','Employees can access detailed information about their assigned projects, including project, descriptions, and other details. Administrators have the ability to assign the project information as needed for employees.','ion-ios-people-outline');
/*!40000 ALTER TABLE `guest_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_slider_images`
--

DROP TABLE IF EXISTS `guest_slider_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_slider_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `para` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_slider_images`
--

LOCK TABLES `guest_slider_images` WRITE;
/*!40000 ALTER TABLE `guest_slider_images` DISABLE KEYS */;
INSERT INTO `guest_slider_images` VALUES
(1,'we are professional','Employeeshub is the ultimate solution for efficient employee management.','1.jpg'),
(2,'Team Work','At Employeeshub, we\'re all about making employee management a breeze. Our Employeeshub is designed to streamline tasks, boost productivity, and keep everyone on track.','2.jpg'),
(3,'Employees leave','From leave applications to project management, we have all covered. Say goodbye to tedious admin work and hello to seamless efficiency!','3.jpg'),
(4,'Let\'s work together','Join us on this journey to revolutionize the way you manage your workforce.','4.jpg'),
(5,'Employees Hub','EMS Hub has transformed the way to manage the employees. It\'s a game-changer!','5.jpg');
/*!40000 ALTER TABLE `guest_slider_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_team`
--

DROP TABLE IF EXISTS `guest_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `position` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_team`
--

LOCK TABLES `guest_team` WRITE;
/*!40000 ALTER TABLE `guest_team` DISABLE KEYS */;
INSERT INTO `guest_team` VALUES
(1,'Gopal Singh','Chief Executive Officer','team-1.jpg'),
(2,'Sanjhi Suryavanshi','Product Manager','team-2.jpg'),
(3,'Moni Singh','Entrepreneur','team-3.jpg'),
(4,'Suryavanshi','Accountant','team-4.jpg');
/*!40000 ALTER TABLE `guest_team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_testimonial`
--

DROP TABLE IF EXISTS `guest_testimonial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_testimonial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person` varchar(200) NOT NULL,
  `position` varchar(200) NOT NULL,
  `para` varchar(200) NOT NULL,
  `img` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_testimonial`
--

LOCK TABLES `guest_testimonial` WRITE;
/*!40000 ALTER TABLE `guest_testimonial` DISABLE KEYS */;
INSERT INTO `guest_testimonial` VALUES
(1,'Saul Goodman','Ceo &amp; Founder','EMS Hub is a must-have for any organization looking to streamline employee management.','testimonial-1.jpg'),
(2,'Sara Wilsson','Designer','The EMS Hub has made our HR tasks a breeze. It\'s like having a personal assistant for every employee.','testimonial-2.jpg'),
(3,'Jena Karlis','Store Owner','EMS Hub is the ultimate solution for efficient employee management.','testimonial-3.jpg'),
(4,'Matt Brandon','Freelancer','EMS Hub has transformed the way we manage our employees.','testimonial-4.jpg'),
(5,'John Larson','Entrepreneur','I never knew employee management could be this fun and efficient.','testimonial-5.jpg');
/*!40000 ALTER TABLE `guest_testimonial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `labor_cards`
--

DROP TABLE IF EXISTS `labor_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `labor_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eid` varchar(50) DEFAULT NULL,
  `labor_card_no` varchar(50) NOT NULL,
  `labor_card_start_date` date NOT NULL,
  `labor_card_end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `eid` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labor_cards`
--

LOCK TABLES `labor_cards` WRITE;
/*!40000 ALTER TABLE `labor_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `labor_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_approval_flow`
--

DROP TABLE IF EXISTS `leave_approval_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_approval_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employee_role` varchar(50) DEFAULT NULL,
  `approver_role` varchar(50) DEFAULT NULL,
  `approval_level` int(11) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `leave_id` (`leave_id`),
  KEY `department_id` (`department_id`),
  KEY `employee_role` (`employee_role`),
  KEY `approver_role` (`approver_role`),
  CONSTRAINT `leave_approval_flow_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  CONSTRAINT `leave_approval_flow_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `leave_approval_flow_ibfk_3` FOREIGN KEY (`employee_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `leave_approval_flow_ibfk_4` FOREIGN KEY (`approver_role`) REFERENCES `roles` (`role_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_approval_flow`
--

LOCK TABLES `leave_approval_flow` WRITE;
/*!40000 ALTER TABLE `leave_approval_flow` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_approval_flow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_approval_history`
--

DROP TABLE IF EXISTS `leave_approval_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_approval_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_id` int(11) DEFAULT NULL,
  `action_by_id` varchar(20) DEFAULT NULL,
  `action_by_role` varchar(50) DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `leave_id` (`leave_id`),
  KEY `action_by_role` (`action_by_role`),
  KEY `action` (`action`),
  CONSTRAINT `leave_approval_history_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  CONSTRAINT `leave_approval_history_ibfk_2` FOREIGN KEY (`action_by_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `leave_approval_history_ibfk_3` FOREIGN KEY (`action`) REFERENCES `leave_status_types` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_approval_history`
--

LOCK TABLES `leave_approval_history` WRITE;
/*!40000 ALTER TABLE `leave_approval_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_approval_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_hierarchy`
--

DROP TABLE IF EXISTS `leave_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `recommender_id` int(11) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `type` enum('recommender','approver') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `recommender_id` (`recommender_id`),
  KEY `approver_id` (`approver_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `leave_hierarchy_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `leave_hierarchy_ibfk_2` FOREIGN KEY (`recommender_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `leave_hierarchy_ibfk_3` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `leave_hierarchy_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_hierarchy`
--

LOCK TABLES `leave_hierarchy` WRITE;
/*!40000 ALTER TABLE `leave_hierarchy` DISABLE KEYS */;
INSERT INTO `leave_hierarchy` VALUES
(1475,146,NULL,63,NULL,'approver'),
(1797,141,154,NULL,3,'recommender'),
(1798,141,NULL,63,3,'approver'),
(1812,159,NULL,63,2,'approver'),
(1850,106,NULL,63,2,'approver'),
(1861,142,NULL,63,2,'approver'),
(1872,167,NULL,63,2,'approver'),
(1955,164,67,NULL,5,'recommender'),
(1956,164,NULL,63,5,'approver'),
(1957,170,67,NULL,1,'recommender'),
(1958,170,NULL,63,1,'approver'),
(1959,78,67,NULL,1,'recommender'),
(1960,78,NULL,63,1,'approver'),
(1961,67,NULL,63,1,'approver'),
(1962,154,NULL,63,3,'approver'),
(1963,77,64,NULL,4,'recommender'),
(1964,77,NULL,63,4,'approver'),
(1965,64,NULL,63,4,'approver'),
(1966,68,64,NULL,4,'recommender'),
(1967,68,NULL,63,4,'approver'),
(1968,109,65,NULL,2,'recommender'),
(1969,109,NULL,63,2,'approver'),
(1970,120,65,NULL,2,'recommender'),
(1971,120,NULL,63,2,'approver'),
(1972,153,65,NULL,2,'recommender'),
(1973,153,NULL,63,2,'approver'),
(1974,90,NULL,63,2,'approver'),
(1975,87,66,NULL,2,'recommender'),
(1976,87,65,NULL,2,'recommender'),
(1977,87,NULL,63,2,'approver'),
(1978,70,66,NULL,2,'recommender'),
(1979,70,65,NULL,2,'recommender'),
(1980,70,NULL,63,2,'approver'),
(1981,82,66,NULL,2,'recommender'),
(1982,82,65,NULL,2,'recommender'),
(1983,82,NULL,63,2,'approver'),
(1984,130,66,NULL,2,'recommender'),
(1985,130,65,NULL,2,'recommender'),
(1986,130,NULL,63,2,'approver'),
(1987,156,66,NULL,2,'recommender'),
(1988,156,65,NULL,2,'recommender'),
(1989,156,NULL,63,2,'approver'),
(1990,86,66,NULL,2,'recommender'),
(1991,86,65,NULL,2,'recommender'),
(1992,86,NULL,63,2,'approver'),
(1993,158,66,NULL,2,'recommender'),
(1994,158,65,NULL,2,'recommender'),
(1995,158,NULL,63,2,'approver'),
(1996,143,66,NULL,2,'recommender'),
(1997,143,65,NULL,2,'recommender'),
(1998,143,NULL,63,2,'approver'),
(1999,119,NULL,63,2,'approver'),
(2000,107,66,NULL,2,'recommender'),
(2001,107,65,NULL,2,'recommender'),
(2002,107,NULL,63,2,'approver'),
(2003,117,66,NULL,2,'recommender'),
(2004,117,65,NULL,2,'recommender'),
(2005,117,NULL,63,2,'approver'),
(2006,113,66,NULL,2,'recommender'),
(2007,113,65,NULL,2,'recommender'),
(2008,113,NULL,63,2,'approver'),
(2009,169,66,NULL,2,'recommender'),
(2010,169,65,NULL,2,'recommender'),
(2011,169,NULL,63,2,'approver'),
(2012,149,66,NULL,2,'recommender'),
(2013,149,65,NULL,2,'recommender'),
(2014,149,NULL,63,2,'approver'),
(2015,140,66,NULL,2,'recommender'),
(2016,140,65,NULL,2,'recommender'),
(2017,140,NULL,63,2,'approver'),
(2018,100,66,NULL,2,'recommender'),
(2019,100,65,NULL,2,'recommender'),
(2020,100,NULL,63,2,'approver'),
(2021,115,NULL,63,2,'approver'),
(2022,152,66,NULL,2,'recommender'),
(2023,152,65,NULL,2,'recommender'),
(2024,152,NULL,63,2,'approver'),
(2025,81,66,NULL,2,'recommender'),
(2026,81,65,NULL,2,'recommender'),
(2027,81,NULL,63,2,'approver'),
(2028,102,66,NULL,2,'recommender'),
(2029,102,65,NULL,2,'recommender'),
(2030,102,NULL,63,2,'approver'),
(2031,123,NULL,63,2,'approver'),
(2032,112,NULL,63,2,'approver'),
(2033,66,65,NULL,2,'recommender'),
(2034,66,NULL,63,2,'approver'),
(2035,69,66,NULL,2,'recommender'),
(2036,69,65,NULL,2,'recommender'),
(2037,69,NULL,63,2,'approver'),
(2038,91,66,NULL,2,'recommender'),
(2039,91,65,NULL,2,'recommender'),
(2040,91,NULL,63,2,'approver'),
(2041,93,66,NULL,2,'recommender'),
(2042,93,65,NULL,2,'recommender'),
(2043,93,NULL,63,2,'approver'),
(2044,108,66,NULL,2,'recommender'),
(2045,108,65,NULL,2,'recommender'),
(2046,108,NULL,63,2,'approver'),
(2047,76,65,NULL,2,'recommender'),
(2048,76,NULL,63,2,'approver'),
(2049,96,66,NULL,2,'recommender'),
(2050,96,65,NULL,2,'recommender'),
(2051,96,NULL,63,2,'approver'),
(2052,65,NULL,63,2,'approver'),
(2053,75,66,NULL,2,'recommender'),
(2054,75,65,NULL,2,'recommender'),
(2055,75,NULL,63,2,'approver'),
(2056,85,66,NULL,2,'recommender'),
(2057,85,65,NULL,2,'recommender'),
(2058,85,NULL,63,2,'approver'),
(2059,94,66,NULL,2,'recommender'),
(2060,94,65,NULL,2,'recommender'),
(2061,94,NULL,63,2,'approver'),
(2062,80,66,NULL,2,'recommender'),
(2063,80,65,NULL,2,'recommender'),
(2064,80,NULL,63,2,'approver'),
(2065,134,66,NULL,2,'recommender'),
(2066,134,65,NULL,2,'recommender'),
(2067,134,NULL,63,2,'approver'),
(2068,160,66,NULL,2,'recommender'),
(2069,160,65,NULL,2,'recommender'),
(2070,160,NULL,63,2,'approver'),
(2071,110,66,NULL,2,'recommender'),
(2072,110,65,NULL,2,'recommender'),
(2073,110,NULL,63,2,'approver'),
(2074,79,66,NULL,2,'recommender'),
(2075,79,65,NULL,2,'recommender'),
(2076,79,NULL,63,2,'approver'),
(2077,122,NULL,63,2,'approver'),
(2078,88,66,NULL,2,'recommender'),
(2079,88,65,NULL,2,'recommender'),
(2080,88,NULL,63,2,'approver'),
(2081,92,66,NULL,2,'recommender'),
(2082,92,65,NULL,2,'recommender'),
(2083,92,NULL,63,2,'approver'),
(2084,97,66,NULL,2,'recommender'),
(2085,97,65,NULL,2,'recommender'),
(2086,97,NULL,63,2,'approver'),
(2087,89,66,NULL,2,'recommender'),
(2088,89,65,NULL,2,'recommender'),
(2089,89,NULL,63,2,'approver'),
(2090,166,66,NULL,2,'recommender'),
(2091,166,65,NULL,2,'recommender'),
(2092,166,NULL,63,2,'approver'),
(2093,165,64,NULL,2,'recommender'),
(2094,165,NULL,63,2,'approver'),
(2095,83,66,NULL,2,'recommender'),
(2096,83,65,NULL,2,'recommender'),
(2097,83,NULL,63,2,'approver'),
(2098,105,66,NULL,2,'recommender'),
(2099,105,65,NULL,2,'recommender'),
(2100,105,NULL,63,2,'approver'),
(2101,161,NULL,63,2,'approver'),
(2102,168,66,NULL,2,'recommender'),
(2103,168,65,NULL,2,'recommender'),
(2104,168,NULL,63,2,'approver'),
(2105,157,66,NULL,2,'recommender'),
(2106,157,65,NULL,2,'recommender'),
(2107,157,NULL,63,2,'approver'),
(2108,121,66,NULL,2,'recommender'),
(2109,121,65,NULL,2,'recommender'),
(2110,121,NULL,63,2,'approver'),
(2111,95,66,NULL,2,'recommender'),
(2112,95,65,NULL,2,'recommender'),
(2113,95,NULL,63,2,'approver');
/*!40000 ALTER TABLE `leave_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_notifications`
--

DROP TABLE IF EXISTS `leave_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `leave_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `leave_id` (`leave_id`),
  CONSTRAINT `leave_notifications_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_notifications`
--

LOCK TABLES `leave_notifications` WRITE;
/*!40000 ALTER TABLE `leave_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_policies`
--

DROP TABLE IF EXISTS `leave_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_type` varchar(50) DEFAULT NULL,
  `max_days` int(11) DEFAULT NULL,
  `min_service_months` int(11) DEFAULT NULL,
  `requires_certificate` tinyint(1) DEFAULT NULL,
  `gender_restriction` enum('all','male','female') DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT NULL,
  `monthly_accrual` decimal(4,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_policies`
--

LOCK TABLES `leave_policies` WRITE;
/*!40000 ALTER TABLE `leave_policies` DISABLE KEYS */;
INSERT INTO `leave_policies` VALUES
(1,'Annual Leave after 6 Months completed',15,6,0,'all',1,1.00,1),
(2,'Casual Leave',4,6,0,'all',1,2.00,0),
(3,'Sick Leave Full pay',45,2,1,'all',1,3.50,1),
(4,'Sick Leave Half Pay',30,2,1,'all',0,0.00,1),
(5,'Maternity Leave full pay',45,6,1,'female',1,3.50,1),
(6,'Paternity Leave',5,6,1,'male',1,0.00,1),
(7,'Hajj and Umrah Leave',30,0,0,'all',0,0.00,0),
(8,'Unpaid',30,0,0,'all',0,0.00,1),
(33,'Paid Leave',30,0,0,'',1,0.00,0),
(34,'Maternity Leave half pay',15,6,1,'female',1,1.00,1),
(35,'Annual Leave after 12 Months completed',15,12,0,'all',1,1.00,1),
(38,'1',30,0,0,NULL,1,0.00,1);
/*!40000 ALTER TABLE `leave_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_status_types`
--

DROP TABLE IF EXISTS `leave_status_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_status_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_code` varchar(20) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_code` (`status_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_status_types`
--

LOCK TABLES `leave_status_types` WRITE;
/*!40000 ALTER TABLE `leave_status_types` DISABLE KEYS */;
INSERT INTO `leave_status_types` VALUES
(1,'PENDING','Pending','Leave request is waiting for approval','2025-06-10 07:23:10'),
(2,'APPROVED','Approved','Leave request has been approved','2025-06-10 07:23:10'),
(3,'REJECTED','Rejected','Leave request has been rejected','2025-06-10 07:23:10'),
(4,'CANCELLED','Cancelled','Leave request has been cancelled by employee','2025-06-10 07:23:10'),
(5,'IN_PROGRESS','In Progress','Leave request is being reviewed','2025-06-10 07:23:10');
/*!40000 ALTER TABLE `leave_status_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES
(1,'Annual Leave','Regular annual leave entitlement','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(2,'Sick Leave','Leave for medical reasons','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(3,'Casual Leave','Short-term leave for personal matters','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(4,'Maternity Leave','Leave for female employees during pregnancy and childbirth','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(5,'Paternity Leave','Leave for male employees when their spouse gives birth','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(6,'Bereavement Leave','Leave due to death of immediate family member','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(7,'Unpaid Leave','Leave without pay for extended absences','2025-06-10 12:37:59','2025-06-10 12:37:59'),
(8,'Annual Leave','Regular annual leave entitlement','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(9,'Sick Leave','Leave for medical reasons','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(10,'Casual Leave','Short-term leave for personal matters','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(11,'Maternity Leave','Leave for female employees during pregnancy and childbirth','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(12,'Paternity Leave','Leave for male employees when their spouse gives birth','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(13,'Bereavement Leave','Leave due to death of immediate family member','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(14,'Unpaid Leave','Leave without pay for extended absences','2025-06-10 12:43:28','2025-06-10 12:43:28'),
(15,'Annual Leave','Regular annual leave entitlement','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(16,'Sick Leave','Leave for medical reasons','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(17,'Casual Leave','Short-term leave for personal matters','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(18,'Maternity Leave','Leave for female employees during pregnancy and childbirth','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(19,'Paternity Leave','Leave for male employees when their spouse gives birth','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(20,'Bereavement Leave','Leave due to death of immediate family member','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(21,'Unpaid Leave','Leave without pay for extended absences','2025-06-10 12:53:11','2025-06-10 12:53:11'),
(22,'Annual Leave','Regular annual leave entitlement','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(23,'Sick Leave','Leave for medical reasons','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(24,'Casual Leave','Short-term leave for personal matters','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(25,'Maternity Leave','Leave for female employees during pregnancy and childbirth','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(26,'Paternity Leave','Leave for male employees when their spouse gives birth','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(27,'Bereavement Leave','Leave due to death of immediate family member','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(28,'Unpaid Leave','Leave without pay for extended absences','2025-06-10 12:55:56','2025-06-10 12:55:56'),
(29,'Annual Leave','Regular annual leave entitlement','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(30,'Sick Leave','Leave for medical reasons','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(31,'Casual Leave','Short-term leave for personal matters','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(32,'Maternity Leave','Leave for female employees during pregnancy and childbirth','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(33,'Paternity Leave','Leave for male employees when their spouse gives birth','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(34,'Bereavement Leave','Leave due to death of immediate family member','2025-06-10 13:00:20','2025-06-10 13:00:20'),
(35,'Unpaid Leave','Leave without pay for extended absences','2025-06-10 13:00:20','2025-06-10 13:00:20');
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_workflow`
--

DROP TABLE IF EXISTS `leave_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `leave_id` (`leave_id`),
  KEY `department_id` (`department_id`),
  KEY `employee_role` (`employee_role`),
  KEY `current_approver_role` (`current_approver_role`),
  KEY `next_approver_role` (`next_approver_role`),
  KEY `status` (`status`),
  CONSTRAINT `leave_workflow_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`),
  CONSTRAINT `leave_workflow_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `leave_workflow_ibfk_3` FOREIGN KEY (`employee_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `leave_workflow_ibfk_4` FOREIGN KEY (`current_approver_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `leave_workflow_ibfk_5` FOREIGN KEY (`next_approver_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `leave_workflow_ibfk_6` FOREIGN KEY (`status`) REFERENCES `leave_status_types` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_workflow`
--

LOCK TABLES `leave_workflow` WRITE;
/*!40000 ALTER TABLE `leave_workflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `recommender_name` varchar(100) DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `approver_name` varchar(100) DEFAULT NULL,
  `recommender_remarks` text DEFAULT NULL,
  `approver_remarks` text DEFAULT NULL,
  `recommender_action_date` datetime DEFAULT NULL,
  `approver_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approver_action_date` datetime DEFAULT NULL,
  `approver_by` varchar(20) DEFAULT NULL,
  `recommender_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_current_approver` (`current_approver_role`),
  KEY `fk_next_approver` (`next_approver_role`),
  CONSTRAINT `fk_current_approver` FOREIGN KEY (`current_approver_role`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `fk_next_approver` FOREIGN KEY (`next_approver_role`) REFERENCES `roles` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES
(42,'CME0005','Manobala  Thathineni Sudharsanam','test','Sick Leave Half Pay','2025-07-31','2025-07-31',0,1,'2025-07-30 09:57:36','Rejected',63,'0',NULL,'pending',NULL,NULL,NULL,'','test','2025-07-30 18:00:47','CME0001',1,3,NULL,NULL,63,NULL,0,'Bashid Khan','g',NULL,'2025-07-30 23:48:02','Pending',NULL,NULL,'Not Recommended'),
(51,'78','Ayesha Shamas','ttttttttttttt','Sick Leave Full pay','2025-08-14','2025-08-16',0,3,'2025-08-11 11:28:24','Rejected',2,'Manobala  Thathineni Sudharsanam',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',67,'Manobala  Thathineni Sudharsanam',63,'Bashid Khan','','dsasd','2025-08-11 22:52:04','Approved','2025-08-11 17:58:53','CME0001',NULL),
(52,'70','Ali  Abbas ','I am planning to travel and would like to apply for leave during this period.\"21/08/2025 to 25/08/20','Unpaid','2025-08-21','2025-08-25',0,5,'2025-08-16 03:58:03','Approved',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',66,'MD Sameer  Alam',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
(53,'95','Zahra  Wahab','For your mother health treatment','Unpaid','2025-09-08','2025-09-22',0,15,'2025-09-03 05:51:38','Rejected',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',66,'MD Sameer  Alam',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
(54,'92','Sandeep Krishna ','\r\nSubject: Request for Leave for Pitru Paksha\r\n\r\nDear Sir,\r\n\r\nI am writing to request leave for Pitr','Annual Leave after 6 Months completed','2025-09-16','2025-09-22',7,7,'2025-09-13 06:30:03','Approved',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',66,'MD Sameer  Alam',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
(55,'64','Muhammad Usman  Tassawar','Annual Leave','Annual Leave after 12 Months completed','2025-10-27','2025-11-11',0,16,'2025-09-15 11:47:01','Approved',5,'Bashid Khan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',0,'',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
(56,'165','Shobana  M','I am not feeling well. Head ache and stomach pain','Sick Leave Full pay','2025-09-24','2025-09-24',0,1,'2025-09-24 01:01:23','Approved',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',64,'Muhammad Usman  Tassawar',63,'Bashid Khan','',NULL,'2025-09-25 15:21:23','Pending',NULL,NULL,'Recommended'),
(57,'78','Ayesha Shamas','Important work ','Annual Leave after 6 Months completed','2025-10-01','2025-10-07',0,10,'2025-09-25 03:36:23','Recommende',2,'Manobala  Thathineni Sudharsanam',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',67,'Manobala  Thathineni Sudharsanam',63,'Bashid Khan','',NULL,'2025-09-25 15:17:51','Pending',NULL,NULL,NULL),
(58,'82','Aman Ahmed','Annual leave','Annual Leave after 12 Months completed','2025-11-29','2025-12-12',0,10,'2025-10-08 08:43:10','Pending',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',66,'MD Sameer  Alam',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
(59,'165','Shobana  M','Fever','Sick Leave Full pay','2025-10-17','2025-10-17',0,1,'2025-10-17 01:33:06','Pending',6,'Muhammad  Arslan',NULL,'pending',NULL,NULL,NULL,'pending',NULL,NULL,NULL,1,2,'HOD','MANAGER',64,'Muhammad Usman  Tassawar',63,'Bashid Khan',NULL,NULL,NULL,'Pending',NULL,NULL,NULL);
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_leave_days_on_update` BEFORE UPDATE ON `leaves` FOR EACH ROW BEGIN
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

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_leave_status_update` AFTER UPDATE ON `leaves` FOR EACH ROW BEGIN
    DECLARE current_balance DECIMAL(10,2);
    
    IF NEW.status = 'Approved' AND OLD.status != 'Approved' THEN
        
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
        
        
        INSERT INTO employee_leave_balance (emp_id, leave_type, year, balance)
        VALUES (NEW.emp_id, NEW.type_of_leave, YEAR(CURRENT_DATE), 
                current_balance - NEW.total_days)
        ON DUPLICATE KEY UPDATE
            balance = balance - NEW.total_days;
            
    ELSEIF (NEW.status = 'Cancelled' OR NEW.status = 'Surrendered') 
           AND OLD.status = 'Approved' THEN
        
        UPDATE employee_leave_balance
        SET balance = balance + OLD.total_days
        WHERE emp_id = OLD.emp_id 
        AND leave_type = OLD.type_of_leave
        AND year = YEAR(CURRENT_DATE);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_leave_days_on_delete` AFTER DELETE ON `leaves` FOR EACH ROW BEGIN
                DECLARE yearly_leave_sum INT;
                SELECT IFNULL(SUM(total_days), 0) INTO yearly_leave_sum
                FROM leaves
                WHERE emp_id = OLD.emp_id
                AND YEAR(start_date) = YEAR(CURDATE());
                UPDATE leaves
                SET total_days = yearly_leave_sum
                WHERE emp_id = OLD.emp_id
                AND YEAR(start_date) = YEAR(CURDATE());
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','archived') NOT NULL DEFAULT 'unread',
  `type` enum('document_signed','system','other') NOT NULL DEFAULT 'document_signed',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `idx_emp_id_status` (`emp_id`,`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` varchar(100) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `leader_name` varchar(100) NOT NULL,
  `leader_email` varchar(50) NOT NULL,
  `p_description` varchar(200) NOT NULL,
  `due_date` date NOT NULL,
  `sub_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `points` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Inactive',
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `calculate_bonus_trigger` AFTER UPDATE ON `projects` FOR EACH ROW BEGIN
    DECLARE bonus FLOAT;
    
    
    IF NEW.points IS NULL OR NEW.points = 0 THEN
        SET bonus = 0;
    ELSE
        
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
            SET bonus = 10 * 15000; 
        END IF;
    END IF;
    
    
    UPDATE salary
    SET bonus = CONCAT(NEW.points,'%'),
        total_salary = 15000 + bonus
    WHERE emp_id = NEW.leader_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `registration_attempts`
--

DROP TABLE IF EXISTS `registration_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `registration_attempts` (
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration_attempts`
--

LOCK TABLES `registration_attempts` WRITE;
/*!40000 ALTER TABLE `registration_attempts` DISABLE KEYS */;
INSERT INTO `registration_attempts` VALUES
('::1','2025-03-17 07:21:27');
/*!40000 ALTER TABLE `registration_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_hierarchy`
--

DROP TABLE IF EXISTS `role_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(50) DEFAULT NULL,
  `parent_role_id` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `workflow_level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `role_id` (`role_id`),
  KEY `parent_role_id` (`parent_role_id`),
  CONSTRAINT `role_hierarchy_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `role_hierarchy_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_code`),
  CONSTRAINT `role_hierarchy_ibfk_3` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_hierarchy`
--

LOCK TABLES `role_hierarchy` WRITE;
/*!40000 ALTER TABLE `role_hierarchy` DISABLE KEYS */;
INSERT INTO `role_hierarchy` VALUES
(1,'HR_COORDINATOR','HR_MANAGER',1,1,'2025-06-10 07:17:39'),
(2,'HR_MANAGER','MANAGING_DIRECTOR',1,2,'2025-06-10 07:17:39'),
(3,'SALES_TEAM_LEADER','SALES_MANAGER',2,1,'2025-06-10 07:17:39'),
(4,'RELATIONSHIP_OFFICER','SALES_MANAGER',2,1,'2025-06-10 07:17:39'),
(5,'SALES_MANAGER','HEAD_OF_SALES',2,2,'2025-06-10 07:17:39'),
(6,'HEAD_OF_SALES','MANAGING_DIRECTOR',2,3,'2025-06-10 07:17:39'),
(7,'MARKETING_TEAM','HR_MANAGER',3,1,'2025-06-10 07:17:39'),
(8,'OPERATIONS_TEAM','OPERATIONS_MANAGER',4,1,'2025-06-10 07:17:39'),
(9,'OPERATIONS_MANAGER','HEAD_OF_SALES',4,2,'2025-06-10 07:17:39');
/*!40000 ALTER TABLE `role_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_code` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_level` int(11) NOT NULL COMMENT '1=Top Level, 2=Second Level, etc.',
  `can_recommend` tinyint(1) DEFAULT 0,
  `can_apply` tinyint(1) DEFAULT 1,
  `can_approve` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_code` (`role_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2845 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'MANAGING_DIRECTOR','Managing Director',1,0,0,1,1,'2025-06-10 07:17:39'),
(2,'HR_MANAGER','HR Manager',2,1,1,1,1,'2025-06-10 07:17:39'),
(3,'HEAD_OF_SALES','Head of Sales/Sales Director',2,1,1,1,1,'2025-06-10 07:17:39'),
(4,'OPERATIONS_MANAGER','Operations Manager',3,1,1,0,1,'2025-06-10 07:17:39'),
(5,'SALES_MANAGER','Sales Manager',3,1,1,0,1,'2025-06-10 07:17:39'),
(6,'HR_COORDINATOR','HR Coordinator',3,0,1,0,1,'2025-06-10 07:17:39'),
(7,'MARKETING_TEAM','Marketing Team Member',4,0,1,0,1,'2025-06-10 07:17:39'),
(8,'SALES_TEAM_LEADER','Sales Team Leader',4,0,1,0,1,'2025-06-10 07:17:39'),
(9,'RELATIONSHIP_OFFICER','Relationship Officer',4,0,1,0,1,'2025-06-10 07:17:39'),
(10,'OPERATIONS_TEAM','Operations Team Member',4,0,1,0,1,'2025-06-10 07:17:39'),
(11,'HOD','Head of Department',0,1,1,1,1,'2025-06-13 09:22:16'),
(12,'MANAGER','Manager',0,1,1,0,1,'2025-06-13 09:22:16'),
(13,'EMPLOYEE','Employee',0,0,1,0,1,'2025-06-13 09:22:16'),
(473,'DEPT_HEAD','Department Head',2,1,1,1,1,'2025-06-20 17:58:12');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sal`
--

DROP TABLE IF EXISTS `sal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `vendor_visa_remarks` text DEFAULT NULL,
  `deduction_remarks` text DEFAULT NULL,
  `performance_points` int(11) DEFAULT NULL,
  `payable_salary` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `total_salary` decimal(10,2) DEFAULT NULL,
  `salary_date` date DEFAULT NULL,
  `pay_mode` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `sal_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sal`
--

LOCK TABLES `sal` WRITE;
/*!40000 ALTER TABLE `sal` DISABLE KEYS */;
INSERT INTO `sal` VALUES
(40,'CME0002',3030.00,1010.00,1010.00,0.00,3500.00,30,30.00,0.00,80.00,0.00,80.00,0.00,0.00,0.00,0.00,'','',0,8550.00,80.00,8470.00,'2025-05-31','Cash Memo'),
(41,'CME0005',2800.00,600.00,600.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-05-01','Cash Memo'),
(42,'CME0006',1800.00,600.00,600.00,570.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3570.00,0.00,3570.00,'2025-05-01','Cash Memo'),
(43,'CME0018',500.00,250.00,250.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,1000.00,'','',0,1000.00,1000.00,0.00,'2025-05-01','Cash Memo'),
(44,'CME0017',800.00,100.00,100.00,10700.00,0.00,31,30.00,0.00,120.00,0.00,120.00,1400.00,500.00,0.00,850.00,'Integrated Solution Employment services','Deduction :\r\n50 - RO visit. 120 LTO & Leaves.\r\nHold Amount Not Activated :\r\nAPTN78440070734900019\r\nAPTN784P2505230001680017\r\nClawback - Card Rebooked April 25 - APTN78440084426471015 - 800 AED',0,11700.00,2870.00,8830.00,'2025-05-31','WPS'),
(45,'CME0021',600.00,200.00,200.00,8900.00,0.00,31,30.00,0.00,120.00,0.00,120.00,1400.00,0.00,0.00,25.00,'Integrated Solution Employment services','Decuction : LTO & RO visiit. \r\nHold Amount Not Activated :\r\nAPTN784P2505140000780017\r\nAWOR784P2505220001220013',0,9900.00,1545.00,8355.00,'2025-05-31','WPS'),
(46,'CME0019',500.00,350.00,350.00,2000.00,0.00,31,30.00,0.00,40.00,0.00,40.00,0.00,0.00,0.00,0.00,'','',0,3200.00,40.00,3160.00,'2025-05-31','Cash Memo'),
(47,'CME0020',800.00,100.00,100.00,2538.00,1500.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment services','Basit Cash	800\r\nAzhar card	800\r\nNA	700\r\nPetty Cash	478\r\nAWOR784P2503160000830010 - Santosh Kumar - Payee	400\r\nTL Incentiv	360\r\n1500 AED - Payee Transfer.',0,5038.00,0.00,5038.00,'2025-05-31','WPS'),
(48,'CME0024',800.00,0.00,0.00,10100.00,0.00,30,30.00,0.00,60.00,0.00,60.00,2900.00,5000.00,0.00,1000.00,'','',0,10900.00,8960.00,1940.00,'2025-05-01','Cash Memo'),
(49,'CME0023',1500.00,750.00,750.00,8900.00,0.00,30,30.00,0.00,40.00,0.00,40.00,2500.00,4000.00,0.00,25.00,'integrated Solution Employment services','',0,11900.00,6565.00,5335.00,'2025-05-31','WPS'),
(50,'CME0012',1750.00,750.00,750.00,1750.00,0.00,30,18.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,5000.00,'','His training cost was covered by his salary.',0,5000.00,5000.00,0.00,'2025-05-31','Cash Memo'),
(51,'CME0003',17500.00,3750.00,3750.00,17180.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,870.00,'','',0,42180.00,870.00,41310.00,'2025-05-31','Cash Memo'),
(52,'CME0007',800.00,0.00,0.00,5310.00,0.00,30,30.00,0.00,80.00,0.00,80.00,520.00,0.00,0.00,130.00,'','',0,6110.00,730.00,5380.00,'2025-05-31','Cash Memo'),
(53,'CME0008',800.00,0.00,0.00,8200.00,0.00,30,30.00,0.00,40.00,0.00,40.00,0.00,0.00,0.00,510.00,'','',0,9000.00,550.00,8450.00,'2025-05-01','Cash Memo'),
(54,'CME0014',800.00,0.00,0.00,7000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,300.00,60.00,0.00,300.00,'','Deduction LTO + Leave \r\n\r\nCard Closure : Clawback.\r\nCard rebooked within 365 days  closure\r\nAPTN78440069267650512\r\nAPTN78440070976800018\r\n\r\nHold Amount PS Call not done :\r\nAWOR784P00201900513\r\nAWOR784P2505280002600019',0,7800.00,660.00,7140.00,'2025-05-31','Cash Memo'),
(55,'CME0025',800.00,0.00,0.00,9200.00,0.00,330,30.00,0.00,80.00,0.00,80.00,3000.00,5000.00,0.00,1000.00,'','Deduction LTO + Leave.\r\nCard Closure : Clawback.\r\nCard rebooked within 365 days  closure\r\nAPTN78410062055430012\r\n\r\nHold Amount Activation Record :\r\nAPTN784P2504290002690010\r\nAWOR784P2505100001050018\r\nAPTN784P2505210003650010\r\nAPTN78440075890080010\r\nAPTN784P2505240000610014\r\nAPTN78440072534180016',0,10000.00,9080.00,920.00,'2025-05-31','Cash Memo'),
(56,'CME0026',800.00,0.00,0.00,2400.00,0.00,30,30.00,0.00,60.00,0.00,60.00,0.00,1500.00,0.00,900.00,'Integrated Solution Employment services','Card Closure : Clawback.\r\nCard rebooked within 365 days  closure\r\nAPTN78404002810012',0,3200.00,2460.00,740.00,'2025-05-31','WPS'),
(57,'CME0027',700.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment services','',0,700.00,0.00,700.00,'2025-05-31','WPS'),
(58,'CME0034',800.00,0.00,0.00,6900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1000.00,3500.00,0.00,170.00,'','Deduction: LTO + Leaves.\r\nHold Amount Activation Record :\r\nATTN784P2406260000641512\r\nATTN784P2408300000680512\r\nAPTN78440079115220012\r\n\r\nCarry Fwd April 2025\r\nAPTN784P2504270000680015',0,7700.00,4670.00,3030.00,'2025-05-31','Cash Memo'),
(59,'CME0040',800.00,0.00,0.00,10000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,500.00,5700.00,0.00,0.00,'','Hold Amount Activation Record :\r\nATTN784P2505130002570012',0,10800.00,6200.00,4600.00,'2025-05-31','Cash Memo'),
(60,'CME0029',800.00,0.00,0.00,3600.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2500.00,0.00,25.00,'','',0,4400.00,2525.00,1875.00,'2025-05-31','Cash Memo'),
(61,'CME0031',800.00,0.00,0.00,700.00,400.00,30,30.00,0.00,100.00,0.00,100.00,375.00,1000.00,0.00,25.00,'Integrated Solution Employment services','',0,1900.00,1500.00,400.00,'2025-05-31','WPS'),
(62,'CME0038',800.00,0.00,0.00,12800.00,400.00,30,30.00,0.00,60.00,0.00,60.00,2400.00,7100.00,0.00,1450.00,'','Hold Amount Activation Record :\r\nAPTN784P2505070001420018\r\nAPTN784P2505210001930018\r\nAPTN784P2505260002010013\r\nAPTN784P2505270003300016\r\nAPTN78440084770670014\r\n\r\nHold Amount PS Call not done :\r\nATTN784P2503130001650022\r\n\r\nCarry fwd Activation : April 2025\r\nAPTN784P2504110001170014\r\n\r\n500 Cash Gave',0,14000.00,11010.00,2990.00,'2025-05-31','Cash Memo'),
(63,'CME0047',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-05-31','Cash Memo'),
(64,'CME0051',800.00,0.00,0.00,1600.00,0.00,30,30.00,0.00,50.00,0.00,50.00,300.00,1000.00,0.00,0.00,'','Hold Amount Activation Record :\r\nATTN78440075064011510',0,2400.00,1350.00,1050.00,'2025-05-31','Cash Memo'),
(65,'CME0043',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,50.00,0.00,50.00,0.00,500.00,0.00,0.00,'','',0,800.00,550.00,250.00,'2025-05-31','Cash Memo'),
(66,'CME0044',800.00,0.00,0.00,800.00,0.00,30,30.00,0.00,50.00,0.00,50.00,0.00,1000.00,0.00,0.00,'','',0,1600.00,1050.00,550.00,'2025-05-01','Cash Memo'),
(67,'CME0048',800.00,0.00,0.00,6200.00,0.00,31,30.00,0.00,40.00,0.00,40.00,800.00,3000.00,0.00,25.00,'','Deduction LTO + Leaves\r\nHold Amount Activation Record :\r\nAPTN784P2505300001060013\r\nAPTN784P2505300001660010',0,7000.00,3865.00,3135.00,'2025-05-31','Cash Memo'),
(68,'CME0046',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,90.00,0.00,90.00,485.00,1600.00,0.00,25.00,'','Hold Amount Activation Record :\r\nATTN784P2504150001530024\r\nATTN784P2505240000810010',0,2200.00,2200.00,0.00,'2025-05-31','Cash Memo'),
(69,'CME0045',800.00,0.00,0.00,600.00,0.00,30,30.00,0.00,40.00,0.00,40.00,160.00,1200.00,0.00,0.00,'','Hold Amount Activation Record :\r\nATTN78421097240400013',0,1400.00,1400.00,0.00,'2025-05-31','Cash Memo'),
(70,'CME0054',800.00,0.00,0.00,700.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,700.00,0.00,0.00,'','',0,1500.00,700.00,800.00,'2025-05-31','Cash Memo'),
(71,'CME0056',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-05-31','Cash Memo'),
(72,'CME0081',800.00,0.00,0.00,11000.00,0.00,30,30.00,0.00,60.00,0.00,60.00,1900.00,6000.00,0.00,75.00,'','',0,11800.00,8035.00,3765.00,'2025-05-31','Cash Memo'),
(73,'CME0080',800.00,0.00,0.00,800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,1600.00,0.00,1600.00,'2025-05-31','Cash Memo'),
(74,'CME0060',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,300.00,500.00,0.00,0.00,'','',0,800.00,800.00,0.00,'2025-05-31','Cash Memo'),
(75,'CME0068',800.00,0.00,0.00,3400.00,0.00,30,30.00,0.00,20.00,0.00,20.00,1250.00,0.00,0.00,0.00,'','',0,4200.00,1270.00,2930.00,'2025-05-31','Cash Memo'),
(76,'CME0077',700.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,700.00,500.00,200.00,'2025-05-31','Cash Memo'),
(77,'CME0075',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-05-31','Cash Memo'),
(78,'CME0069',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,800.00,0.00,800.00,'2025-05-31','Cash Memo'),
(79,'CME0070',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,100.00,'','',0,800.00,600.00,200.00,'2025-05-31','Cash Memo'),
(80,'CME0039',800.00,0.00,0.00,6300.00,0.00,30,30.00,0.00,20.00,0.00,20.00,400.00,4500.00,0.00,25.00,'','Hold Amount Activation Record :\r\nAPTN784P2505260001820016',0,7100.00,4945.00,2155.00,'2025-05-31','Cash Memo'),
(81,'CME0059',120.00,0.00,0.00,0.00,0.00,30,30.00,0.00,80.00,0.00,80.00,0.00,0.00,0.00,0.00,'','',0,120.00,80.00,40.00,'2025-05-31','Cash Memo'),
(82,'CME0013',800.00,0.00,0.00,2450.00,0.00,30,30.00,0.00,60.00,0.00,60.00,0.00,0.00,0.00,120.00,'','',0,3250.00,180.00,3070.00,'2025-05-31','Cash Memo'),
(83,'CME0032',800.00,0.00,0.00,2400.00,0.00,30,30.00,0.00,90.00,0.00,90.00,0.00,2000.00,0.00,0.00,'','',0,3200.00,2090.00,1110.00,'2025-05-31','Cash Memo'),
(84,'CME0030',800.00,0.00,0.00,5300.00,400.00,30,30.00,0.00,40.00,0.00,40.00,300.00,3500.00,0.00,0.00,'Integrated Solution Employment services','Hold Amount Activation Record :\r\nAPTN784P2505060001240011\r\n\r\n\r\nActivation Carry Fwd - April 2025\r\nATTN784P2504190002170015',0,6500.00,3840.00,2660.00,'2025-05-31','WPS'),
(85,'CME0015',800.00,0.00,0.00,1200.00,0.00,30,30.00,0.00,20.00,0.00,20.00,0.00,0.00,0.00,0.00,'','',0,2000.00,20.00,1980.00,'2025-05-31','Cash Memo'),
(86,'CME0016',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2200.00,0.00,2200.00,'2025-05-31','Cash Memo'),
(87,'CME0049',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,100.00,'','',0,800.00,100.00,700.00,'2025-05-31','Cash Memo'),
(88,'CME0004',800.00,0.00,0.00,19600.00,0.00,30,30.00,0.00,60.00,0.00,60.00,0.00,0.00,0.00,810.00,'','',0,20400.00,870.00,19530.00,'2025-05-31','Cash Memo'),
(89,'CME0033',800.00,0.00,0.00,800.00,0.00,30,30.00,0.00,40.00,0.00,40.00,560.00,1000.00,0.00,0.00,'Integrated Solution Employment services','Deduction LTO + Leaves.\r\nHold Amount Activation Record :\r\nAPTN784P2505020003510010\r\nAPTN784P2505260001140019',0,1600.00,1600.00,0.00,'2025-05-31','WPS'),
(90,'CME0041',800.00,0.00,0.00,955.00,0.00,30,30.00,0.00,150.00,0.00,150.00,0.00,0.00,0.00,0.00,'','',0,1755.00,150.00,1605.00,'2025-05-31','Cash Memo'),
(91,'CME0042',4000.00,0.00,0.00,0.00,0.00,30,30.00,0.00,200.00,0.00,200.00,0.00,0.00,0.00,0.00,'','',0,4000.00,200.00,3800.00,'2025-05-31','Cash Memo'),
(92,'CME0078',300.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,300.00,0.00,300.00,'2025-05-31','Cash Memo'),
(93,'CME0076',300.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,300.00,0.00,300.00,'2025-05-31','Cash Memo'),
(94,'CME0066',300.00,0.00,0.00,0.00,0.00,30,30.00,0.00,40.00,0.00,40.00,0.00,0.00,0.00,0.00,'','',0,300.00,40.00,260.00,'2025-05-31','Cash Memo'),
(95,'CME0065',800.00,0.00,0.00,2300.00,0.00,30,30.00,0.00,40.00,0.00,40.00,1150.00,0.00,0.00,0.00,'','Deduction LTO + Leaves\r\nHold Amount Activation Record :\r\nATTN784P2505130001240012\r\nAPTN784P2505270002170014\r\nATTN784P2505290000990015',0,3100.00,1190.00,1910.00,'2025-05-31','Cash Memo'),
(96,'CME0067',400.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,400.00,0.00,400.00,'2025-05-01','Cash Memo'),
(97,'CME0096',700.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,350.00,0.00,0.00,0.00,'','',0,700.00,350.00,350.00,'2025-05-31','Cash Memo'),
(98,'CME0002',3030.00,1010.00,1010.00,0.00,2500.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,7550.00,0.00,7550.00,'2025-04-30','Cash Memo'),
(99,'CME0005',2800.00,600.00,600.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-04-30','Cash Memo'),
(100,'CME0018',500.00,250.00,250.00,3100.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,0.00,'Integrate Solution Employment Services','',0,4100.00,2000.00,2100.00,'2025-04-30','WPS'),
(101,'CME0017',800.00,100.00,100.00,5200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,0.00,'Integrated Solution Employment Services','',0,6200.00,2000.00,4200.00,'2025-04-30','WPS'),
(102,'CME0021',600.00,200.00,200.00,5700.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,0.00,'Integrated Solution Employment Services','',0,6700.00,2000.00,4700.00,'2025-04-30','WPS'),
(103,'CME0019',500.00,350.00,350.00,4100.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,0.00,'Integrated Solution Employment Services','',0,5300.00,2000.00,3300.00,'2025-04-30','WPS'),
(104,'CME0020',800.00,100.00,100.00,3500.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1500.00,0.00,0.00,'','',0,4500.00,1500.00,3000.00,'2025-04-30','Cash Memo'),
(105,'CME0006',800.00,100.00,100.00,2000.00,382.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3382.00,0.00,3382.00,'2025-04-01','Cash Memo'),
(106,'CME0023',1000.00,0.00,0.00,8000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,4000.00,0.00,0.00,'','',0,9000.00,4000.00,5000.00,'2025-04-30','Cash Memo'),
(107,'CME0008',800.00,0.00,0.00,4920.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,5720.00,0.00,5720.00,'2025-04-30','Cash Memo'),
(108,'CME0007',800.00,0.00,0.00,3750.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,150.00,'','',0,4550.00,150.00,4400.00,'2025-04-30','Cash Memo'),
(109,'CME0024',800.00,0.00,0.00,9100.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,0.00,'Integrate Solution Employment Services','',0,9900.00,5000.00,4900.00,'2025-04-30','WPS'),
(110,'CME0014',800.00,0.00,0.00,8200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,150.00,'','',0,9000.00,150.00,8850.00,'2025-04-30','Cash Memo'),
(111,'CME0026',800.00,0.00,0.00,4600.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2500.00,0.00,0.00,'Integrate Solution Employment Services','',0,5400.00,2500.00,2900.00,'2025-04-30','WPS'),
(112,'CME0025',800.00,0.00,0.00,9200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,0.00,'','',0,10000.00,5000.00,5000.00,'2025-04-30','Cash Memo'),
(113,'CME0027',800.00,0.00,0.00,900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','',0,1700.00,1000.00,700.00,'2025-04-30','WPS'),
(114,'CME0034',800.00,0.00,0.00,4500.00,0.00,30,30.00,0.00,0.00,0.00,0.00,2200.00,2000.00,0.00,0.00,'','',0,5300.00,4200.00,1100.00,'2025-04-30','Cash Memo'),
(115,'CME0040',800.00,0.00,0.00,5200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,3500.00,0.00,900.00,'','',0,6000.00,4400.00,1600.00,'2025-04-30','Cash Memo'),
(116,'CME0004',800.00,0.00,0.00,16030.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,180.00,'','',0,16830.00,180.00,16650.00,'2025-04-30','Cash Memo'),
(117,'CME0003',17500.00,3750.00,3750.00,1180.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,26180.00,0.00,26180.00,'2025-04-30','Cash Memo'),
(118,'CME0035',800.00,0.00,0.00,17200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1800.00,13200.00,0.00,2400.00,'','',0,18000.00,17400.00,600.00,'2025-04-30','Cash Memo'),
(119,'CME0012',1750.00,750.00,750.00,1750.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,500.00,'','',0,5000.00,500.00,4500.00,'2025-04-30','Cash Memo'),
(120,'CME0030',800.00,0.00,0.00,2700.00,0.00,30,30.00,0.00,0.00,0.00,0.00,400.00,2000.00,0.00,0.00,'','',0,3500.00,2400.00,1100.00,'2025-04-30','Cash Memo'),
(121,'CME0031',800.00,0.00,0.00,11200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1000.00,6000.00,0.00,0.00,'Integrate Solution Employment Services','',0,12000.00,7000.00,5000.00,'2025-04-30','WPS'),
(122,'CME0036',800.00,0.00,0.00,1000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,900.00,'','',0,1800.00,1400.00,400.00,'2025-04-30','Cash Memo'),
(123,'CME0029',800.00,0.00,0.00,3300.00,0.00,30,30.00,0.00,0.00,0.00,0.00,800.00,1900.00,0.00,0.00,'','',0,4100.00,2700.00,1400.00,'2025-04-30','Cash Memo'),
(124,'CME0032',800.00,0.00,0.00,1000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'','',0,1800.00,1000.00,800.00,'2025-04-30','Cash Memo'),
(125,'CME0015',800.00,0.00,0.00,1200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2000.00,0.00,2000.00,'2025-04-30','Cash Memo'),
(126,'CME0013',800.00,0.00,0.00,12420.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5800.00,0.00,0.00,'','',0,13220.00,5800.00,7420.00,'2025-04-30','Cash Memo'),
(127,'CME0041',800.00,0.00,0.00,3200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,1200.00,'','',0,4000.00,1200.00,2800.00,'2025-04-30','Cash Memo'),
(128,'CME0042',800.00,0.00,0.00,3200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,2533.33,'','',0,4000.00,2533.33,1466.67,'2025-04-30','Cash Memo'),
(129,'CME0016',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2200.00,0.00,2200.00,'2025-04-30','Cash Memo'),
(130,'CME0033',800.00,0.00,0.00,1900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1500.00,0.00,0.00,'','',0,2700.00,1500.00,1200.00,'2025-04-30','Cash Memo'),
(131,'CME0039',800.00,0.00,0.00,5400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,800.00,3200.00,0.00,0.00,'','',0,6200.00,4000.00,2200.00,'2025-04-30','Cash Memo'),
(132,'CME0038',800.00,0.00,0.00,6200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,800.00,4000.00,0.00,0.00,'','',0,7000.00,4800.00,2200.00,'2025-04-30','Cash Memo'),
(133,'CME0043',800.00,0.00,0.00,100.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,900.00,500.00,400.00,'2025-04-30','Cash Memo'),
(134,'CME0002',3030.00,1010.00,1010.00,2500.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,7550.00,0.00,7550.00,'2025-03-31','Cash Memo'),
(135,'CME0005',2800.00,600.00,600.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-03-31','Cash Memo'),
(136,'CME0018',500.00,250.00,250.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,600.00,0.00,0.00,'Integrated Solution Employment Services','',0,1000.00,600.00,400.00,'2025-03-31','WPS'),
(137,'CME0017',500.00,250.00,250.00,9800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1000.00,4500.00,0.00,0.00,'Integrated Solution Employment Services','',0,10800.00,5500.00,5300.00,'2025-03-31','WPS'),
(138,'CME0021',600.00,200.00,200.00,2600.00,600.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1800.00,0.00,0.00,'Integrated Employment Solution Services','',0,4200.00,1800.00,2400.00,'2025-03-31','WPS'),
(139,'CME0019',600.00,300.00,300.00,4000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2500.00,0.00,0.00,'Integrated Solution Employment Services','',0,5200.00,2500.00,2700.00,'2025-03-31','WPS'),
(140,'CME0020',800.00,100.00,100.00,4400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,400.00,3000.00,0.00,0.00,'','',0,5400.00,3400.00,2000.00,'2025-03-31','Cash Memo'),
(141,'CME0006',800.00,100.00,100.00,2000.00,386.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3386.00,0.00,3386.00,'2025-03-31','Cash Memo'),
(142,'CME0023',1000.00,0.00,0.00,3400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,700.00,'Integrated Solution Employment Services','Card Closure - Ajith Rehman - Jan 2025, APTN784P2502240000380017.',0,4400.00,2700.00,1700.00,'2025-03-31','WPS'),
(143,'CME0008',800.00,0.00,0.00,6700.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Slott 50+ - Payee applicable 150 Per card','',0,7500.00,0.00,7500.00,'2025-03-31','Cash Memo'),
(144,'CME0007',800.00,0.00,0.00,6850.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2500.00,0.00,0.00,'','',0,7650.00,2500.00,5150.00,'2025-03-01','Cash Memo'),
(145,'CME0024',800.00,0.00,0.00,19800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,10000.00,0.00,0.00,'','',0,20600.00,10000.00,10600.00,'2025-03-31','Cash Memo'),
(146,'CME0002',3030.00,1010.00,1010.00,3500.00,2210.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'integrated Solution Employment services','',0,10760.00,0.00,10760.00,'2025-06-30','WPS'),
(147,'CME0014',800.00,0.00,0.00,7000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,7800.00,0.00,7800.00,'2025-03-31','Cash Memo'),
(148,'CME0026',800.00,0.00,0.00,9200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1500.00,4500.00,0.00,0.00,'Integrated Solution Employment Solution','',0,10000.00,6000.00,4000.00,'2025-03-31','WPS'),
(149,'CME0027',800.00,0.00,0.00,1000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','',0,1800.00,1000.00,800.00,'2025-03-31','WPS'),
(150,'CME0040',800.00,0.00,0.00,18000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,9000.00,0.00,0.00,'Integrated Solution Employment Services','',0,18800.00,9000.00,9800.00,'2025-03-31','WPS'),
(151,'CME0004',800.00,0.00,0.00,15760.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,16560.00,0.00,16560.00,'2025-03-31','Cash Memo'),
(152,'CME0003',17500.00,3750.00,3750.00,340.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,3500.00,0.00,0.00,'','',0,25340.00,3500.00,21840.00,'2025-03-31','Cash Memo'),
(153,'CME0012',1750.00,750.00,750.00,1750.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,306.00,'','',0,5000.00,306.00,4694.00,'2025-03-31','Cash Memo'),
(154,'CME0030',800.00,0.00,0.00,1800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1500.00,0.00,0.00,'','',0,2600.00,1500.00,1100.00,'2025-03-31','Cash Memo'),
(155,'CME0031',800.00,0.00,0.00,10000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,0.00,'','',0,10800.00,5000.00,5800.00,'2025-03-31','Cash Memo'),
(156,'CME0029',800.00,0.00,0.00,900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'','',0,1700.00,1000.00,700.00,'2025-03-31','Cash Memo'),
(157,'CME0032',800.00,0.00,0.00,1000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,1800.00,500.00,1300.00,'2025-03-31','Cash Memo'),
(158,'CME0015',800.00,0.00,0.00,66.67,0.00,30,16.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,866.67,0.00,866.67,'2025-03-31','Cash Memo'),
(159,'CME0016',800.00,0.00,0.00,153.33,0.00,30,13.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,953.33,0.00,953.33,'2025-03-31','Cash Memo'),
(160,'CME0013',800.00,0.00,0.00,6370.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,3400.00,0.00,0.00,'','',0,7170.00,3400.00,3770.00,'2025-03-31','Cash Memo'),
(161,'CME0002',3030.00,1010.00,1010.00,2950.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,2300.00,0.00,0.00,'TXM','',0,8000.00,2300.00,5700.00,'2025-02-28','WPS'),
(162,'CME0018',500.00,250.00,250.00,700.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,126.00,'Integrated Solution Employment Services','AED 126 ILOE Deduction',0,1700.00,626.00,1074.00,'2025-02-28','WPS'),
(163,'CME0017',800.00,100.00,100.00,6285.00,0.00,28,28.00,0.00,0.00,0.00,0.00,900.00,2000.00,0.00,126.00,'Integrated Solution Employment Services','AED 126 ILOE Deduction',0,7285.00,3026.00,4259.00,'2025-02-28','WPS'),
(164,'CME0021',600.00,200.00,200.00,4300.00,0.00,28,28.00,0.00,0.00,0.00,0.00,600.00,2600.00,0.00,528.86,'Integrated Solution Employment Services','2 Cards Hold Amount 600 AED - Deem Payout : SHEENA PURI Rohit Booked - Feb 2025 E4 Shumaila Feb 26, 2025 / Hafiz Zaheer Ahmad Rohit Booked - Feb 2025 E1 Shumaila Feb 28, 2025',0,5300.00,3728.86,1571.14,'2025-02-28','WPS'),
(165,'CME0019',500.00,350.00,350.00,3000.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,2500.00,0.00,126.00,'Integrated Solution Employment Services','AED 126 ILOE Deduction',0,4200.00,2626.00,1574.00,'2025-02-28','WPS'),
(166,'CME0020',800.00,100.00,100.00,3500.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,126.00,'Integrated Solution Employment Services ','AED 126 ILOE Deduction',0,4500.00,2126.00,2374.00,'2025-02-28','WPS'),
(167,'CME0006',800.00,100.00,100.00,2000.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3000.00,0.00,3000.00,'2025-02-28','Cash Memo'),
(168,'CME0023',1000.00,0.00,0.00,2000.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,126.00,'Integrated Solution Employment Services','AED 126 ILOE Deduction',0,3000.00,126.00,2874.00,'2025-02-28','WPS'),
(169,'CME0005',2800.00,600.00,600.00,0.00,0.00,28,24.00,4.00,0.00,533.33,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,533.33,3466.67,'2025-02-28','Cash Memo'),
(170,'CME0012',1750.00,750.00,750.00,3250.00,0.00,28,19.00,10.00,0.00,2166.67,0.00,0.00,0.00,0.00,0.00,'','',0,6500.00,2166.67,4333.33,'2025-02-28','Cash Memo'),
(171,'CME0003',17500.00,3750.00,3750.00,0.00,0.00,28,17.00,12.00,0.00,10000.00,0.00,0.00,0.00,0.00,0.00,'','',0,25000.00,10000.00,15000.00,'2025-02-28','Cash Memo'),
(172,'CME0038',800.00,0.00,0.00,3600.00,0.00,30,27.00,0.00,0.00,0.00,0.00,1400.00,2600.00,0.00,0.00,'integrated Solution Employment services','Card Payout Non Activation :\r\nAPTN784P2506040000260011\r\nATTN784P2506090001080017\r\nAPTN78421093983700016\r\nAPTN784P2506230001010016',0,4400.00,4000.00,400.00,'2025-06-30','WPS'),
(173,'CME0047',800.00,0.00,0.00,3600.00,0.00,30,30.00,0.00,0.00,0.00,0.00,400.00,2000.00,0.00,0.00,'integrated Solution Employment services','APTN784P2506290000230015',0,4400.00,2400.00,2000.00,'2025-06-30','WPS'),
(174,'CME0043',800.00,0.00,0.00,0.00,0.00,30,28.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'integrated Solution Employment services','',0,800.00,500.00,300.00,'2025-06-30','WPS'),
(175,'CME0046',800.00,0.00,0.00,1600.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1600.00,0.00,25.00,'','',0,2400.00,1625.00,775.00,'2025-06-01','Cash Memo'),
(176,'CME0045',800.00,0.00,0.00,0.00,0.00,30,24.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-06-30','Cash Memo'),
(177,'CME0054',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,300.00,500.00,0.00,0.00,'','',0,800.00,800.00,0.00,'2025-06-30','Cash Memo'),
(178,'CME0013',800.00,0.00,0.00,1120.00,0.00,30,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,1920.00,0.00,1920.00,'2025-06-30','Cash Memo'),
(179,'CME0007',800.00,0.00,0.00,4270.00,585.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1400.00,0.00,795.00,'','',0,5655.00,2195.00,3460.00,'2025-06-30','Cash Memo'),
(180,'CME0051',800.00,0.00,0.00,8900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,25.00,'','',0,9700.00,5025.00,4675.00,'2025-06-30','Cash Memo'),
(181,'CME0027',800.00,0.00,0.00,800.00,100.00,30,27.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'integrated Solution Employment services','100 -Â Â Added - May 2025',0,1700.00,1000.00,700.00,'2025-06-30','WPS'),
(182,'CME0034',0.00,0.00,0.00,0.00,700.00,30,13.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,500.00,'','Clawback : Mashroq\r\nAPTN78440072469890019 - 500 AED\r\nActivation Amount :\r\nATTN784P2406260000641512\r\nAPTN78440079115220012',0,700.00,500.00,200.00,'2025-06-30','Cash Memo'),
(183,'CME0044',800.00,0.00,0.00,4500.00,0.00,30,30.00,0.00,20.00,0.00,20.00,0.00,3000.00,0.00,25.00,'','',0,5300.00,3045.00,2255.00,'2025-06-30','Cash Memo'),
(184,'CME0048',800.00,0.00,0.00,7200.00,0.00,30,29.00,0.00,70.00,0.00,70.00,800.00,4500.00,0.00,0.00,'','Not Activated :\r\nATTN784P2506120001310013\r\nATTN784P2506020001980015\r\nActivation Amount\r\nAPTN784P2505300001660010\r\n',0,8000.00,5370.00,2630.00,'2025-06-30','Cash Memo'),
(185,'CME0088',800.00,0.00,0.00,0.00,0.00,20,19.00,0.00,0.00,0.00,0.00,0.00,700.00,0.00,0.00,'','',0,800.00,700.00,100.00,'2025-06-30','Cash Memo'),
(186,'CME0087',700.00,0.00,0.00,0.00,0.00,16,16.00,0.00,0.00,0.00,0.00,200.00,500.00,0.00,0.00,'','',0,700.00,700.00,0.00,'2025-06-30','Cash Memo'),
(187,'CME0008',800.00,0.00,0.00,7300.00,810.00,30,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,960.00,'','',0,8910.00,960.00,7950.00,'2025-06-30','Cash Memo'),
(188,'CME0017',800.00,100.00,100.00,10900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1500.00,4000.00,0.00,0.00,'integrated Solution Employment services','',0,11900.00,5500.00,6400.00,'2025-06-30','WPS'),
(189,'CME0024',800.00,0.00,0.00,11100.00,0.00,30,30.00,0.00,0.00,0.00,0.00,900.00,6000.00,0.00,0.00,'integrated Solution Employment services','',0,11900.00,6900.00,5000.00,'2025-06-30','WPS'),
(190,'CME0026',800.00,0.00,0.00,7300.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,4500.00,0.00,0.00,'integrated Solution Employment services','',0,8100.00,4500.00,3600.00,'2025-06-30','WPS'),
(191,'CME0030',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'integrated Solution Employment services','',0,800.00,500.00,300.00,'2025-06-30','WPS'),
(192,'CME0033',800.00,0.00,0.00,2400.00,0.00,30,25.00,0.00,250.00,0.00,250.00,0.00,2000.00,0.00,0.00,'integrated Solution Employment services','',0,3200.00,2250.00,950.00,'2025-06-30','WPS'),
(193,'CME0081',800.00,0.00,0.00,6200.00,0.00,30,28.00,0.00,0.00,0.00,0.00,500.00,3500.00,0.00,25.00,'','',0,7000.00,4025.00,2975.00,'2025-06-01','Cash Memo'),
(194,'CME0007',800.00,0.00,0.00,2970.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3770.00,0.00,3770.00,'2025-02-28','Cash Memo'),
(195,'CME0008',800.00,100.00,100.00,2510.00,0.00,28,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3510.00,0.00,3510.00,'2025-02-28','Cash Memo'),
(196,'CME0024',800.00,0.00,0.00,15900.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,8100.00,0.00,0.00,'','',0,16700.00,8100.00,8600.00,'2025-02-28','Cash Memo'),
(197,'CME0014',800.00,0.00,0.00,2320.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3120.00,0.00,3120.00,'2025-02-28','Cash Memo'),
(198,'CME0026',800.00,0.00,0.00,1000.00,0.00,28,28.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'','',0,1800.00,1000.00,800.00,'2025-02-28','Cash Memo'),
(199,'CME0027',800.00,0.00,0.00,0.00,0.00,28,15.00,15.00,0.00,400.00,0.00,0.00,0.00,0.00,0.00,'','',0,800.00,400.00,400.00,'2025-02-28','Cash Memo'),
(200,'CME0040',800.00,0.00,0.00,5200.00,0.00,28,28.00,0.00,0.00,0.00,0.00,800.00,3500.00,0.00,0.00,'','2 Cards Hold Amount 800 AED - Deem Payout : \r\nRUTURAJ VILAS Azaam Booked - Feb 2025 E2 Kamal Feb 18, 2025 Not Activated UMAR MUKTHAR Azaam Booked - Feb 2025 E1 Kamal Feb 20, 2025 Not Activated',0,6000.00,4300.00,1700.00,'2025-02-28','Cash Memo'),
(201,'CME0029',200.00,0.00,0.00,0.00,0.00,28,5.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,200.00,0.00,200.00,'2025-02-01','Cash Memo'),
(202,'CME0031',800.00,0.00,0.00,0.00,0.00,28,28.00,0.00,0.00,0.00,0.00,400.00,0.00,0.00,0.00,'','',0,800.00,400.00,400.00,'2025-02-28','Cash Memo'),
(203,'CME0034',800.00,0.00,0.00,100.00,0.00,28,7.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,900.00,0.00,900.00,'2025-02-01','Cash Memo'),
(204,'CME0059',800.00,0.00,0.00,1240.00,0.00,30,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,40.00,'','',0,2040.00,40.00,2000.00,'2025-06-30','Cash Memo'),
(205,'CME0080',800.00,0.00,0.00,6300.00,0.00,30,29.00,0.00,50.00,0.00,50.00,0.00,4000.00,0.00,0.00,'','',0,7100.00,4050.00,3050.00,'2025-06-30','Cash Memo'),
(206,'CME0096',800.00,0.00,0.00,2700.00,350.00,30,28.00,0.00,120.00,0.00,120.00,0.00,1500.00,0.00,25.00,'','Activation Amount :\r\nAWOR784P2505200001980014\r\n',0,3850.00,1645.00,2205.00,'2025-06-30','Cash Memo'),
(207,'CME0018',500.00,250.00,250.00,3400.00,0.00,30,30.00,0.00,25.00,0.00,25.00,0.00,2500.00,0.00,0.00,'integrated Solution Employment services','',0,4400.00,2525.00,1875.00,'2025-06-30','WPS'),
(208,'CME0077',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-06-30','Cash Memo'),
(209,'CME0078',800.00,0.00,0.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-06-30','Cash Memo'),
(210,'CME0091',700.00,0.00,0.00,0.00,0.00,30,29.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,700.00,500.00,200.00,'2025-06-30','Cash Memo'),
(211,'CME0075',800.00,0.00,0.00,0.00,0.00,30,21.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,800.00,500.00,300.00,'2025-06-30','Cash Memo'),
(212,'CME0093',120.00,0.00,0.00,0.00,0.00,29,29.00,1.00,0.00,4.00,0.00,0.00,0.00,0.00,0.00,'','',0,120.00,4.00,116.00,'2025-06-01','Cash Memo'),
(213,'CME0089',800.00,0.00,0.00,800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'','',0,1600.00,1000.00,600.00,'2025-06-01','Cash Memo'),
(214,'CME0014',800.00,0.00,0.00,9400.00,1020.00,30,30.00,0.00,0.00,0.00,0.00,1020.00,0.00,0.00,150.00,'','',0,11220.00,1170.00,10050.00,'2025-06-30','Cash Memo'),
(215,'CME0040',800.00,0.00,0.00,11100.00,500.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5500.00,0.00,25.00,'integrated Solution Employment services','',0,12400.00,5525.00,6875.00,'2025-06-30','WPS'),
(216,'CME0032',800.00,0.00,0.00,3500.00,0.00,30,28.00,0.00,140.00,0.00,140.00,400.00,2500.00,0.00,900.00,'','',0,4300.00,3940.00,360.00,'2025-06-01','Cash Memo'),
(217,'CME0060',800.00,0.00,0.00,5500.00,300.00,30,30.00,0.00,40.00,0.00,40.00,400.00,3500.00,0.00,0.00,'','',0,6600.00,3940.00,2660.00,'2025-06-01','Cash Memo'),
(218,'CME0035',800.00,0.00,0.00,7200.00,0.00,30,0.00,0.00,0.00,0.00,0.00,700.00,4500.00,0.00,0.00,'','',0,8000.00,5200.00,2800.00,'2025-06-01','Cash Memo'),
(219,'CME0062',800.00,0.00,0.00,800.00,0.00,25,25.00,0.00,0.00,0.00,0.00,600.00,1000.00,0.00,0.00,'','',0,1600.00,1600.00,0.00,'2025-06-01','Cash Memo'),
(220,'CME0064',800.00,0.00,0.00,0.00,0.00,30,1.00,0.00,0.00,0.00,0.00,300.00,500.00,0.00,0.00,'','',0,800.00,800.00,0.00,'2025-06-01','Cash Memo'),
(221,'CME0021',600.00,200.00,200.00,8900.00,0.00,30,30.00,0.00,0.00,0.00,0.00,1000.00,2000.00,0.00,0.00,'integrated Solution Employment services','Activation Status\r\nNot Activated\r\nAPTN784P2506170001780012\r\nAPTN784P2506230001630011\r\n',0,9900.00,3000.00,6900.00,'2025-06-01','WPS'),
(222,'CME0019',600.00,300.00,300.00,3300.00,0.00,30,29.00,0.00,50.00,0.00,50.00,500.00,2000.00,0.00,0.00,'integrated Solution Employment services','Not Activated :\r\nATTN784P2506140000690017\r\n',0,4500.00,2550.00,1950.00,'2025-06-30','WPS'),
(223,'CME0018',500.00,250.00,250.00,0.00,1500.00,31,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,500.00,'Integrated Solution Employment Services','1-E3 - 900, 2-E4 - 1600',0,2500.00,500.00,2000.00,'2025-01-31','WPS'),
(224,'CME0017',800.00,0.00,0.00,4500.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,5300.00,0.00,5300.00,'2025-01-31','WPS'),
(225,'CME0019',500.00,0.00,0.00,0.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,500.00,0.00,500.00,'2025-01-31','WPS'),
(226,'CME0020',800.00,100.00,100.00,4000.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Soultions Employemnt Services','1-E1 - 900, 1-EUN - 800 + Feb 25 Advance Payout ( ATTN78421104187390015 + ATTN78403844350518 )',0,5000.00,0.00,5000.00,'2025-01-31','WPS'),
(227,'CME0021',600.00,200.00,200.00,10600.00,0.00,31,30.00,0.00,0.00,0.00,0.00,500.00,4500.00,0.00,0.00,'Integrated Solution Employment Service','Activation Hold Amount :\r\nAWOR784P2507230002590016',11,11600.00,5000.00,6600.00,'2025-07-31','WPS'),
(228,'CME0023',1000.00,0.00,0.00,2000.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,3000.00,0.00,3000.00,'2025-01-31','WPS'),
(229,'CME0002',3030.00,1010.00,1010.00,100.00,200.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,3000.00,'TXM','',0,5350.00,3000.00,2350.00,'2025-01-31','WPS'),
(230,'CME0006',800.00,100.00,100.00,1500.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2500.00,0.00,2500.00,'2025-01-31','Cash Memo'),
(231,'CME0005',2800.00,600.00,600.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-06-30','Cash Memo'),
(232,'CME0006',800.00,100.00,100.00,2000.00,555.00,30,13.00,0.00,1700.00,0.00,1700.00,0.00,0.00,0.00,0.00,'','',0,3555.00,1700.00,1855.00,'2025-06-30','Cash Memo'),
(233,'CME0023',1000.00,0.00,0.00,30200.00,1250.00,30,30.00,0.00,0.00,0.00,0.00,0.00,14500.00,0.00,0.00,'integrated Solution Employment services','\"250 - BONUS\r\n29 X TOP TIER\r\nMAY ACTIVATION  2025\r\nAPTN78421100930210013\r\nAPTN784P2505050001790016\"',0,32450.00,14500.00,17950.00,'2025-06-30','WPS'),
(234,'CME0004',7200.00,2400.00,2400.00,5640.00,1790.00,30,30.00,0.00,0.00,0.00,0.00,1790.00,0.00,0.00,720.00,'integrated Solution Employment services','Clawback : \r\nAPTN78440072469890019\r\nAPTN784P2502270001910017\r\nAPTN784P2503180002580018\r\nAPTN784P2503190000420017\r\nAPTN784P2503240002520016\r\nAWOR784P2503030001530011\r\nAWOR784P2503190002440013\r\nAPTN784P2502280000850015\r\n\r\n90*196 - CARDS SM VISIT',0,19430.00,2510.00,16920.00,'2025-06-30','WPS'),
(235,'CME0003',17500.00,3750.00,3750.00,12800.00,2160.00,30,30.00,0.00,0.00,0.00,0.00,2160.00,0.00,0.00,1120.00,'','',0,39960.00,3280.00,36680.00,'2025-06-30','Cash Memo'),
(236,'CME0031',800.00,0.00,0.00,1200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,200.00,1000.00,0.00,0.00,'integrated Solution Employment services','\"Activation : \r\nAPTN78410039559630016\r\nAPTN784P2504210003300013\"',0,2000.00,1200.00,800.00,'2025-06-30','WPS'),
(237,'CME0015',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2200.00,0.00,2200.00,'2025-06-30','Cash Memo'),
(238,'CME0016',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2200.00,0.00,2200.00,'2025-07-31','Cash Memo'),
(239,'CME0079',800.00,0.00,0.00,3700.00,700.00,30,30.00,0.00,350.00,0.00,350.00,0.00,0.00,0.00,0.00,'','',0,5200.00,350.00,4850.00,'2025-06-30','Cash Memo'),
(240,'CME0007',800.00,0.00,0.00,1960.00,585.00,31,30.00,0.00,50.00,0.00,50.00,0.00,0.00,0.00,650.00,'','Clawback :\r\n438513\r\n438855\r\n439158\r\n441237\r\n4007642948\r\n',23,3345.00,700.00,2645.00,'2025-07-31','Cash Memo'),
(241,'CME0027',800.00,0.00,0.00,1600.00,0.00,31,26.00,0.00,100.00,0.00,100.00,300.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','Activation Hold Amount :\r\nATTN784P2505300001390022\r\n',3,2400.00,1900.00,500.00,'2025-07-31','WPS'),
(242,'CME0051',800.00,0.00,0.00,9500.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,25.00,'','RO Visit:\r\nATTN784P2502190000610527\r\n',10,10300.00,5025.00,5275.00,'2025-07-31','Cash Memo'),
(243,'CME0048',800.00,0.00,0.00,4900.00,0.00,31,31.00,0.00,0.00,0.00,0.00,400.00,2500.00,0.00,126.00,'Integrated Solution Employment Service','AED 126 Deduction For ILOE Subscription\r\nPS calls Not Done:\r\nAWOR784P2507060001130012\r\nAWOR784P2507110001250011\r\n',5,5700.00,3026.00,2674.00,'2025-07-31','WPS'),
(244,'CME0088',800.00,0.00,0.00,800.00,0.00,31,30.00,0.00,20.00,0.00,20.00,400.00,500.00,0.00,0.00,'','Activation Hold Amount :\r\nAPTN784P2507280001100019\r\n',2,1600.00,920.00,680.00,'2025-07-31','Cash Memo'),
(245,'CME0008',800.00,0.00,0.00,8050.00,810.00,31,20.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,150.00,'','Special Promo Imcentive + 150*59.\r\n438901 - Clawback\r\n',59,9660.00,150.00,9510.00,'2025-07-31','Cash Memo'),
(246,'CME0081',500.00,0.00,0.00,0.00,0.00,31,31.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','',0,500.00,100.00,400.00,'2025-07-31','Cash Memo'),
(247,'CME0017',800.00,100.00,100.00,19450.00,0.00,31,27.00,0.00,0.00,0.00,0.00,0.00,6000.00,0.00,100.00,'Integrated Solution Employment Services','Carry fwd Activation :\r\nAWOR784P2506120002610015\r\nAPTN784P2505230001680017\r\n\r\nRO Visit:\r\nATTN784P2507230000530014\r\nAPTN784P2507170000180015\r\nAPTN784P2507080002950010\r\nATTN784P2507070001460012\r\n',19,20450.00,6100.00,14350.00,'2025-07-31','WPS'),
(248,'CME0024',800.00,0.00,0.00,9000.00,400.00,31,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,0.00,'Integrated Solution Employment Services','',0,10200.00,5000.00,5200.00,'2025-07-31','WPS'),
(249,'CME0026',800.00,0.00,0.00,14750.00,0.00,31,29.00,0.00,0.00,0.00,0.00,550.00,7500.00,0.00,925.00,'Integrated Solution Employment Services','Clawback:\r\n438901\r\n\r\nActivation Pending\r\nATTN784P2506300001840017\r\n\r\nRO Visit:\r\nAPTN784P2507110000340011\r\n',15,15550.00,8975.00,6575.00,'2025-07-31','WPS'),
(250,'CME0025',800.00,0.00,0.00,9600.00,0.00,31,21.00,0.00,0.00,0.00,0.00,0.00,4000.00,0.00,528.00,'Integrated Solution Employment Services','AED 500 Last month Carryforward Amt\r\nAED 402 Deduction for ILOE fines + AED 126 Decduction for ILOE Subscription\r\nCarry fwd Activation :\r\nAPTN784P2504290002690010\r\nActivation Hold Amount :\r\nAWOR784P2507280000590013\r\nAPTN78440079999850017',10,10400.00,4528.00,5872.00,'2025-07-31','WPS'),
(251,'CME0030',800.00,0.00,0.00,1500.00,0.00,31,31.00,0.00,40.00,0.00,40.00,400.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','Activation Hold Amount :\r\nAPTN784P2405160001871012',3,2300.00,1440.00,860.00,'2025-07-31','WPS'),
(252,'CME0033',800.00,0.00,0.00,800.00,0.00,31,27.00,0.00,100.00,0.00,100.00,0.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','',2,1600.00,1100.00,500.00,'2025-07-31','WPS'),
(253,'CME0013',800.00,0.00,0.00,6700.00,0.00,31,31.00,0.00,20.00,0.00,20.00,0.00,0.00,0.00,630.00,'','Clawback:Â 439031Â \r\n',50,7500.00,650.00,6850.00,'2025-07-31','Cash Memo'),
(254,'CME0068',800.00,0.00,0.00,4950.00,0.00,31,26.00,0.00,100.00,0.00,100.00,0.00,1250.00,0.00,1051.00,'','AED 900 deduction for over stay Fines & ILOE Subscription AED 126\r\nRO Visit:\r\nATTN784P2507300001190016\r\nAED 100 Leave Deduction\r\n',6,5750.00,2401.00,3349.00,'2025-07-31','Cash Memo'),
(255,'CME0038',800.00,0.00,0.00,11900.00,0.00,31,26.00,0.00,0.00,0.00,0.00,1000.00,1900.00,0.00,578.00,'Integrated Solution Employment Services','AED 402 Deduction for ILOE fine + AED 126 ILOE Subscription\r\nPS calls Not Done:\r\nAPTN784P2507040002170019\r\nAPTN784P2507290000990013\r\nRO Visit:\r\nAPTN784P2507170002580015\r\nAPTN784P2507080002470019\r\nLast month Carryforward Activation Amt 1000',12,12700.00,3478.00,9222.00,'2025-07-31','WPS'),
(256,'CME0047',800.00,0.00,0.00,6900.00,0.00,31,31.00,0.00,60.00,0.00,60.00,0.00,4000.00,0.00,151.00,'Integrated Solution Employment Services','AED 126 Deduction For ILOE Subscription + AED 60 LTO\r\n\r\nRO Visit:\r\nAWOR78440072216450018\r\n',0,7700.00,4211.00,3489.00,'2025-07-31','WPS'),
(257,'CME0043',800.00,0.00,0.00,1600.00,0.00,31,29.00,0.00,50.00,0.00,50.00,0.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','Leave Deduction 50\r\n',3,2400.00,1550.00,850.00,'2025-07-31','WPS'),
(258,'CME0046',800.00,0.00,0.00,9800.00,0.00,31,31.00,0.00,0.00,0.00,0.00,1200.00,5850.00,0.00,201.00,'Integrated Solution Employment Solutions','AED 126 Deduction For ILOE Subscription\r\nAED 75 RO Visit Deduction \r\nActivation Hold Amount :\r\nAPTN784P2506300002100015\r\nAPTN784P2502050001900527\r\nAWOR784P2507170002170014\r\nRO Visit:\r\nAWOR784P2507170002170014\r\nAPTN78440089165270015\r\nATTN784P2507010001660013\r\n',11,10600.00,7251.00,3349.00,'2025-07-31','WPS'),
(259,'CME0045',800.00,0.00,0.00,1600.00,0.00,31,31.00,0.00,0.00,0.00,0.00,400.00,1000.00,0.00,151.00,'Integrated solution Employment Services','AED 126 Deduction for ILOE Subscription\r\nAED 25 RO Visit Deduction\r\nActivation Hold Amount :\r\nAPTN784P2507180000280012\r\nRO Visit:\r\nAPTN784P2507050001010017\r\n',3,2400.00,1551.00,849.00,'2025-07-31','WPS'),
(260,'CME0018',500.00,250.00,250.00,4900.00,0.00,31,29.00,0.00,100.00,0.00,100.00,300.00,3500.00,0.00,427.00,'Integrated Solution Employment Services','Deduction for ILOE fine;   AED 402 \r\nLeave Deduction;   AED100\r\nRO Deduction;   AED25\r\nActivation Hold Amount :\r\nATTN784P2403210001281514\r\nRO Visit:\r\nAPTN784P2507040000830010',7,5900.00,4327.00,1573.00,'2025-07-31','WPS'),
(261,'CME0078',800.00,0.00,0.00,1400.00,0.00,31,27.00,0.00,100.00,0.00,100.00,0.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','AED 100 Leave Deduction',0,2200.00,1600.00,600.00,'2025-07-31','WPS'),
(262,'CME0077',800.00,0.00,0.00,1500.00,0.00,31,20.00,0.00,100.00,0.00,100.00,0.00,1500.00,0.00,0.00,'','',3,2300.00,1600.00,700.00,'2025-07-31','Cash Memo'),
(263,'CME0092',800.00,0.00,0.00,0.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',1,800.00,0.00,800.00,'2025-07-31','Cash Memo'),
(264,'CME0094',800.00,0.00,0.00,800.00,0.00,31,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,25.00,'','RO Visit:\r\nAPTN784P2507260000670014',0,1600.00,25.00,1575.00,'2025-07-31','Cash Memo'),
(265,'CME0102',800.00,0.00,0.00,1500.00,0.00,31,31.00,0.00,0.00,0.00,0.00,750.00,0.00,0.00,0.00,'','Activation Hold Amount :\r\nAPTN784P2507110002630013\r\nAPTN784P2507090001760021',3,2300.00,750.00,1550.00,'2025-07-31','Cash Memo'),
(266,'CME0103',800.00,0.00,0.00,0.00,0.00,31,22.00,0.00,0.00,0.00,0.00,0.00,700.00,0.00,0.00,'','',1,800.00,700.00,100.00,'2025-07-31','Cash Memo'),
(267,'CME0100',800.00,0.00,0.00,0.00,0.00,31,19.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,25.00,'','',1,800.00,25.00,775.00,'2025-07-31','Cash Memo'),
(268,'CME0091',800.00,0.00,0.00,0.00,0.00,31,14.00,0.00,260.00,0.00,260.00,0.00,0.00,0.00,0.00,'','',1,800.00,260.00,540.00,'2025-07-31','Cash Memo'),
(269,'CME0014',800.00,0.00,0.00,10300.00,1020.00,31,27.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',74,12120.00,0.00,12120.00,'2025-07-31','Cash Memo'),
(270,'CME0040',800.00,0.00,0.00,12100.00,0.00,31,31.00,0.00,0.00,0.00,0.00,500.00,6500.00,0.00,100.00,'','Activation Hold Amount :\r\nAPTN784P2507070002800018\r\nRO Visit:\r\nAPTN784P2507190001470017\r\nATTN784P2507260001650015\r\nAPTN784P2507070002800018\r\nAPTN784P2506300000870015\r\n',13,12900.00,7100.00,5800.00,'2025-07-31','Cash Memo'),
(271,'CME0032',800.00,0.00,0.00,6100.00,0.00,31,28.00,0.00,100.00,0.00,100.00,0.00,2500.00,0.00,25.00,'','RO Visit:\r\nATTN784P2504070000710022\r\n',7,6900.00,2625.00,4275.00,'2025-07-31','Cash Memo'),
(272,'CME0085',800.00,0.00,0.00,800.00,0.00,31,29.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,1600.00,500.00,1100.00,'2025-07-01','Cash Memo'),
(273,'CME0023',1000.00,0.00,0.00,22400.00,0.00,31,31.00,0.00,0.00,0.00,0.00,550.00,11000.00,0.00,75.00,'Integrated Solution Employment Services','Carry fwd Activation :\r\nAPTN784P2505150001220012\r\nATTN78421079107950017\r\n\r\nActivation Hold Amount :\r\nAPTN784P2507230000970011\r\n\r\nRO Visit:\r\nATTN784P00177820513\r\nATTN784P2507020000020010\r\nATTN784P2507040001760018\r\n',22,23400.00,11625.00,11775.00,'2025-07-31','WPS'),
(274,'CME0031',800.00,0.00,0.00,3500.00,0.00,31,30.00,0.00,100.00,0.00,100.00,0.00,2500.00,0.00,126.00,'Integrated Solution Employment Services','AED 126 Deduction for ILOE Subscription\r\n',5,4300.00,2726.00,1574.00,'2025-07-31','WPS'),
(275,'CME0055',800.00,1200.00,3000.00,10850.00,0.00,31,22.00,0.00,0.00,0.00,0.00,0.00,7500.00,0.00,500.00,'Integrated Solution Employment Services','AED 500 - 1st Installment\r\n',15,15850.00,8000.00,7850.00,'2025-07-31','WPS'),
(276,'CME0021',600.00,200.00,200.00,2600.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,3600.00,0.00,3600.00,'2025-01-31','WPS'),
(277,'CME0019',500.00,350.00,350.00,10500.00,0.00,31,28.00,0.00,0.00,0.00,0.00,500.00,6000.00,0.00,0.00,'Integrated Solution Employment Services','Activation Hold Amount :\r\nAWOR784P2507230001960011\r\n',12,11700.00,6500.00,5200.00,'2025-07-31','WPS'),
(278,'CME0059',800.00,0.00,0.00,2970.00,0.00,31,27.00,0.00,70.00,0.00,70.00,0.00,0.00,0.00,0.00,'','',0,3770.00,70.00,3700.00,'2025-07-31','Cash Memo'),
(279,'CME0096',800.00,0.00,0.00,1600.00,0.00,31,30.00,0.00,20.00,0.00,20.00,0.00,1500.00,0.00,0.00,'','',3,2400.00,1520.00,880.00,'2025-07-31','Cash Memo'),
(280,'CME0072',800.00,0.00,0.00,1600.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','',3,2400.00,100.00,2300.00,'2025-07-31','Cash Memo'),
(281,'CME0002',3030.00,1010.00,1010.00,5450.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,10500.00,0.00,10500.00,'2025-07-31','WPS'),
(282,'CME0005',2800.00,600.00,600.00,0.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-07-31','Cash Memo'),
(283,'CME0004',7200.00,2400.00,2400.00,11500.00,1790.00,31,23.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,5290.00,'Integrated Solution Employment Services','Special Promo Imcentive.\r\nClawback : 438901 / 438513 / 438855 / 439158 / 441237 / 4007642948 / 439031	/ 439805\r\n',235,25290.00,5290.00,20000.00,'2025-07-31','WPS'),
(284,'CME0003',17500.00,3750.00,3750.00,27400.00,2160.00,31,27.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,1120.00,'','',262,54560.00,1120.00,53440.00,'2025-07-31','Cash Memo'),
(285,'CME0015',800.00,0.00,0.00,1200.00,0.00,31,29.00,0.00,120.00,0.00,120.00,0.00,0.00,0.00,0.00,'','AED 100 for Leave Deduction',0,2000.00,120.00,1880.00,'2025-07-31','Cash Memo'),
(286,'CME0016',800.00,0.00,0.00,1400.00,0.00,31,31.00,0.00,20.00,0.00,20.00,0.00,0.00,0.00,0.00,'','',0,2200.00,20.00,2180.00,'2025-08-31','Cash Memo'),
(287,'CME0079',800.00,0.00,0.00,1984.00,0.00,31,21.00,0.00,160.00,0.00,160.00,0.00,0.00,0.00,348.00,'','AED 348 (Deducted for 3 days Absence) & AED 160 (Deducted for LTO)',0,2784.00,508.00,2276.00,'2025-07-31','Cash Memo'),
(288,'CME0098',800.00,0.00,0.00,1700.00,0.00,31,24.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,250.00,'','First 5 days half day salary Deducted - Training Period\r\n',0,2500.00,350.00,2150.00,'2025-07-31','Cash Memo'),
(289,'CME0090',2500.00,0.00,0.00,2500.00,0.00,31,23.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,5000.00,0.00,5000.00,'2025-07-31','Cash Memo'),
(290,'CME0006',800.00,100.00,100.00,2000.00,967.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3967.00,0.00,3967.00,'2025-07-31','Cash Memo'),
(291,'CME0099',800.00,0.00,0.00,610.00,0.00,31,18.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,235.00,'','First 6 days half day salary Deducted - Training Period',0,1410.00,235.00,1175.00,'2025-07-31','Cash Memo'),
(292,'CME0060',800.00,0.00,0.00,3900.00,0.00,31,18.00,0.00,90.00,0.00,90.00,450.00,1500.00,0.00,0.00,'','',0,4700.00,2040.00,2660.00,'2025-07-31','Cash Memo'),
(293,'CME0105',800.00,0.00,0.00,3600.00,0.00,31,29.00,0.00,0.00,0.00,0.00,0.00,3200.00,0.00,0.00,'','',5,4400.00,3200.00,1200.00,'2025-07-31','Cash Memo'),
(294,'CME0020',800.00,100.00,100.00,14275.00,0.00,31,31.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','Claw back:\r\n439805 - Deducted 900\r\n\r\n\r\nVoucher - 10400\r\n19 x 15 - 285 (Voucher commission)\r\n900 - Case \r\n130 * 23 - 2990\r\n350 - June 2025 balance payment\r\n300 - Haroon \r\n550 - Office Medical Minhaj\r\n403 - Office Stationery\r\n',23,15275.00,100.00,15175.00,'2025-07-31','WPS'),
(295,'CME0089',800.00,0.00,0.00,3500.00,0.00,31,28.00,0.00,20.00,0.00,20.00,0.00,2000.00,0.00,25.00,'','RO Visit:\r\nATTN784P2507270000430012',5,4300.00,2045.00,2255.00,'2025-07-31','Cash Memo'),
(296,'CME0108',800.00,0.00,0.00,950.00,0.00,31,30.00,0.00,20.00,0.00,20.00,0.00,0.00,0.00,0.00,'','',0,1750.00,20.00,1730.00,'2025-07-31','Cash Memo'),
(297,'CME0107',800.00,0.00,0.00,2200.00,0.00,31,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3000.00,0.00,3000.00,'2025-07-31','Cash Memo'),
(298,'CME0002',3030.00,1010.00,1010.00,7600.00,240.00,31,31.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,12890.00,100.00,12790.00,'2025-08-01','WPS'),
(299,'CME0017',800.00,100.00,100.00,18600.00,0.00,31,28.00,0.00,0.00,0.00,0.00,0.00,7000.00,0.00,75.00,'Integrated Solution Employment Services','RO Visit Deduction AED75\r\nAPTN784P2508150000640019\r\nATTN784P2508060001570019\r\nAPTN784P2508150000640019\r\n\r\nAED 250 Added - Previous Balance',19,19600.00,7075.00,12525.00,'2025-08-31','WPS'),
(300,'CME0021',600.00,500.00,500.00,7900.00,0.00,31,30.00,0.00,0.00,0.00,0.00,1000.00,4500.00,0.00,0.00,'Integrated Employment Solution Services','Card Not Activated:\r\nAPTN784P2508230001640018\r\nPS Call\r\nAWOR784P2507310000440015\r\nCarry forward:\r\nAWOR784P2507230002590016\r\nCard closed:\r\nAPTN784P2508110000850018',10,9500.00,5500.00,4000.00,'2025-08-31','WPS'),
(301,'CME0019',500.00,350.00,350.00,10200.00,0.00,31,28.00,0.00,0.00,0.00,0.00,0.00,4000.00,0.00,25.00,'Integrated Employment Solution Services','Card Not Activated:\r\nATTN784P2508150001250016\r\nCarry forward:\r\nAWOR784P2507230001960011\r\nRO Visit: AED 25\r\nAWOR784P2508270001240013',11,11400.00,4025.00,7375.00,'2025-08-31','WPS'),
(302,'CME0023',1000.00,0.00,0.00,23950.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,7000.00,0.00,50.00,'Integrated Solution Employment Services','RO Visit:\r\nATTN784P2412260001420512\r\nATTN784P2508010001130016\r\nLast month carried forward Amt: AED 550',24,24950.00,7050.00,17900.00,'2025-08-31','WPS'),
(303,'CME0008',800.00,0.00,0.00,9850.00,0.00,31,27.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,150.00,'','Clawback:\r\nAPTN784P2503250001730028',71,10650.00,150.00,10500.00,'2025-08-01','Cash Memo'),
(304,'CME0007',800.00,0.00,0.00,3230.00,0.00,31,30.00,0.00,130.00,0.00,130.00,0.00,0.00,0.00,260.00,'','Clawback:\r\nAPTN784P2505210002570012\r\nAWOR784P2505060000640013',31,4030.00,390.00,3640.00,'2025-08-01','Cash Memo'),
(305,'CME0024',800.00,0.00,0.00,7300.00,0.00,31,31.00,0.00,40.00,0.00,40.00,0.00,2500.00,0.00,1000.00,'integrated Solution Employment Services','Clawback:\r\nAPTN784P2503250001730028\r\n\r\nAED 126 ILOE',9,8100.00,3540.00,4560.00,'2025-08-01','WPS'),
(306,'CME0014',800.00,0.00,0.00,8950.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',65,9750.00,0.00,9750.00,'2025-08-01','Cash Memo'),
(307,'CME0026',800.00,0.00,0.00,5500.00,0.00,31,29.00,0.00,100.00,0.00,100.00,900.00,0.00,0.00,0.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAWOR784P2508250000240016\r\nAPTN78410041105320018',7,6300.00,1000.00,5300.00,'2025-08-01','WPS'),
(308,'CME0025',800.00,0.00,0.00,15050.00,0.00,31,31.00,0.00,0.00,0.00,0.00,550.00,7500.00,0.00,0.00,'Integrated Solution Employment Services','PS Call\r\nATTN78440091856180017\r\nCarry forward:\r\nAPTN78440079999850017',15,15850.00,8050.00,7800.00,'2025-08-31','WPS'),
(309,'CME0027',800.00,0.00,0.00,9300.00,0.00,31,30.00,0.00,0.00,0.00,0.00,1500.00,4000.00,0.00,25.00,'Integarated Solution Employment Services','Card Not Activated:\r\nATTN784P2508190002040016\r\nAPTN78440078271160020\r\nPS Call\r\nATTN784P2508190000220016\r\nCarry forward:\r\nATTN784P2505300001390022',10,10100.00,5525.00,4575.00,'2025-08-01','WPS'),
(310,'CME0040',3000.00,1000.00,1000.00,8800.00,0.00,31,30.00,0.00,0.00,0.00,0.00,400.00,7000.00,0.00,25.00,'TXM','Card Not Activated:\r\nAWOR784P2507280001530026\r\nRO Visit:\r\nATTN784P2504180001870020\r\nAED4500 Paid through WPS\r\nAED 1875 Paid in cash',14,13800.00,7425.00,6375.00,'2025-08-31','WPS'),
(311,'CME0030',800.00,0.00,0.00,1200.00,0.00,31,31.00,0.00,100.00,0.00,100.00,0.00,1000.00,0.00,126.00,'Integrated Employment Solution Services','Carry forward:\r\nAPTN784P2405160001871012\r\nAED 126 ILOE Not Deducted last Month\r\nLast Month Carryforward AED 400',2,2000.00,1226.00,774.00,'2025-08-31','WPS'),
(312,'CME0031',800.00,0.00,0.00,1600.00,0.00,31,31.00,0.00,20.00,0.00,20.00,0.00,0.00,0.00,0.00,'Integrated Employment Solution Services','',3,2400.00,20.00,2380.00,'2025-08-01','WPS'),
(313,'CME0032',800.00,0.00,0.00,2300.00,0.00,31,30.00,0.00,100.00,0.00,100.00,200.00,2000.00,0.00,0.00,'','PS Call\r\nATTN784P2508270003230012',4,3100.00,2300.00,800.00,'2025-08-01','Cash Memo'),
(314,'CME0013',800.00,0.00,0.00,4620.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,260.00,'','AED 1000 added - Pending from July 25 Salary \r\n\r\nClawback:\r\nAPTN784P2505080002430015\r\nAPTN784P2507050001010017',34,5420.00,360.00,5060.00,'2025-08-01','Cash Memo'),
(315,'CME0038',800.00,0.00,0.00,7100.00,0.00,31,29.00,0.00,100.00,0.00,100.00,1300.00,500.00,0.00,1138.48,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2508260001460018\r\n\r\nPS Call\r\nAPTN784P2507210002060011\r\nAPTN784P2508060001650018\r\n\r\nCarry forward:\r\nAPTN784P2507040002170019\r\nAPTN784P2507290000990013\r\n\r\nClawback:\r\nAPTN784P2505080002430015\r\n\r\nAED 138.49 Deducted for  Tawjeeh Fine',8,7900.00,3038.48,4861.52,'2025-08-01','WPS'),
(316,'CME0047',800.00,0.00,0.00,4600.00,0.00,31,30.00,0.00,20.00,0.00,20.00,450.00,0.00,0.00,25.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN78440077885150013\r\nRO Visit:\r\nATTN784P2506160000440023',6,5400.00,495.00,4905.00,'2025-08-01','WPS'),
(317,'CME0051',800.00,0.00,0.00,9100.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,4500.00,0.00,0.00,'','',10,9900.00,4500.00,5400.00,'2025-08-01','Cash Memo'),
(318,'CME0046',800.00,0.00,0.00,5900.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,3500.00,0.00,75.00,'Integrated Solution Employment Services','Carry forward:\r\nAPTN784P2506300002100015\r\nAPTN784P2502050001900527\r\nRO Visit:\r\nAPTN78440080888550016\r\nATTN784P2507180001600028\r\nAPTN784P2505290001260020',7,6700.00,3575.00,3125.00,'2025-08-31','WPS'),
(319,'CME0043',800.00,0.00,0.00,3950.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,2500.00,0.00,126.00,'Integrated Solution Employment Services','AED 250 Added - Salary pending of May 2025\r\nAED 126 ILOE',5,4750.00,2726.00,2024.00,'2025-08-01','WPS'),
(320,'CME0055',800.00,1200.00,3000.00,4900.00,0.00,31,30.00,0.00,0.00,0.00,0.00,400.00,5000.00,0.00,500.00,'Integrated Solution Employment Service','Card Not Activated:\r\nAPTN784P2505200001310022\r\n\r\nAED 500 Instalment Deducted',10,9900.00,5900.00,4000.00,'2025-08-31','WPS'),
(321,'CME0048',800.00,0.00,0.00,2600.00,0.00,31,30.00,0.00,100.00,0.00,100.00,350.00,1500.00,0.00,25.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2507160000680023\r\n\r\nCarry forward:\r\nAWOR784P2507110001250011',4,3400.00,1975.00,1425.00,'2025-08-01','WPS'),
(322,'CME0045',800.00,0.00,0.00,4000.00,0.00,31,26.00,0.00,100.00,0.00,100.00,400.00,2000.00,0.00,1000.00,'Integrated Solution Employment Service','Card Not Activated:\r\nAPTN78440081391540015\r\n\r\nClawback:\r\nAPTN784P2507050001010017\r\n\r\nCarry forward:\r\nAPTN784P2507180000280012',5,4800.00,3500.00,1300.00,'2025-08-31','WPS'),
(323,'CME0068',800.00,0.00,0.00,700.00,0.00,31,27.00,0.00,100.00,0.00,100.00,0.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','',2,1500.00,1100.00,400.00,'2025-08-31','WPS'),
(324,'CME0089',1260.00,420.00,420.00,7600.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,4000.00,0.00,25.00,'TXM','RO Visit\r\nAWOR78440075427420010 \r\nAED980 paid through WPS\r\nAED4695 Paid by Cash memo',10,9700.00,4025.00,5675.00,'2025-08-01','WPS'),
(325,'CME0088',800.00,0.00,0.00,800.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,500.00,0.00,0.00,'','',2,1600.00,600.00,1000.00,'2025-08-01','Cash Memo'),
(326,'CME0085',608.00,202.67,202.66,0.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'TXM','',0,1013.33,0.00,1013.33,'2025-08-31','WPS'),
(327,'CME0020',800.00,100.00,100.00,20848.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,100.00,'Integrated Solution Employment Services','Added Petty Cash amount AED 398.61',39,21848.00,100.00,21748.00,'2025-08-31','WPS'),
(328,'CME0018',500.00,250.00,250.00,4500.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,3400.00,0.00,25.00,'Integrated Solution Employment Services','Carry forward:\r\nATTN784P2403210001281514\r\nRO Visit:\r\nATTN784P2405230000800518',0,5500.00,3525.00,1975.00,'2025-08-31','WPS'),
(329,'CME0078',800.00,0.00,0.00,1500.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','',0,2300.00,1600.00,700.00,'2025-08-31','WPS'),
(330,'CME0092',1260.00,420.00,420.00,900.00,0.00,31,30.00,0.00,90.00,0.00,90.00,350.00,0.00,0.00,0.00,'TXM','AED 980 Paid Through WPS \r\nAED 1580 Paid by Cash Memo',4,3000.00,440.00,2560.00,'2025-08-01','WPS'),
(331,'CME0102',960.00,320.00,320.00,2350.00,0.00,31,27.00,0.00,100.00,0.00,100.00,0.00,1400.00,0.00,0.00,'TXM','Carry forward:\r\nAPTN784P2507110002630013\r\nAPTN784P2507090001760021\r\nAED 1493.33 Paid through WPS\r\nAED 956.67 Paid by Cash Memo\r\n',4,3950.00,1500.00,2450.00,'2025-08-01','WPS'),
(332,'CME0100',800.00,0.00,0.00,700.00,0.00,31,29.00,0.00,100.00,0.00,100.00,0.00,800.00,0.00,0.00,'','',2,1500.00,900.00,600.00,'2025-08-01','Cash Memo'),
(333,'CME0105',800.00,0.00,0.00,10000.00,0.00,31,31.00,0.00,0.00,0.00,0.00,700.00,7500.00,0.00,0.00,'','Card Not Activated:\r\nAPTN784P2508190001610017\r\n\r\nPS Call\r\nAWOR784P2508270002960015\r\nAPTN784P2508190001610017',0,10800.00,8200.00,2600.00,'2025-08-01','Cash Memo'),
(334,'CME0114',800.00,0.00,0.00,700.00,0.00,31,16.00,0.00,0.00,0.00,0.00,0.00,500.00,0.00,0.00,'','',0,1500.00,500.00,1000.00,'2025-08-01','Cash Memo'),
(335,'CME0109',800.00,0.00,0.00,4300.00,0.00,31,6.00,0.00,0.00,0.00,0.00,400.00,0.00,0.00,50.00,'','PS Call\r\nAPTN784P2508250000420014\r\nRO Visit:\r\nAPTN784P2508290002580010\r\nAPTN784P2508250002070015',6,5100.00,450.00,4650.00,'2025-08-01','Cash Memo'),
(336,'CME0094',800.00,0.00,0.00,0.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',1,800.00,0.00,800.00,'2025-08-01','Cash Memo'),
(337,'CME0120',800.00,0.00,0.00,2300.00,0.00,31,31.00,0.00,100.00,0.00,100.00,50.00,2000.00,0.00,0.00,'','RO Visit:\r\nAPTN784P2508040000280017\r\nAPTN784P2502240001090532',4,3100.00,2150.00,950.00,'2025-08-01','Cash Memo'),
(338,'CME0081',960.00,320.00,320.00,10200.00,0.00,31,23.00,0.00,0.00,0.00,0.00,400.00,5500.00,0.00,0.00,'TXM','AED 1013.33 Paid through WPS\r\nAED 4886.67 Paid by Cash memo\r\nCard Not Activated:\r\nAPTN784P2508120000650011',12,11800.00,5900.00,5900.00,'2025-08-01','WPS'),
(339,'CME0059',800.00,0.00,0.00,2080.00,0.00,31,26.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,120.00,'','',24,2880.00,220.00,2660.00,'2025-08-01','Cash Memo'),
(340,'CME0096',800.00,0.00,0.00,800.00,0.00,31,28.00,0.00,100.00,0.00,100.00,0.00,500.00,0.00,0.00,'','',0,1600.00,600.00,1000.00,'2025-08-01','Cash Memo'),
(341,'CME0072',800.00,0.00,0.00,0.00,0.00,31,28.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','',1,800.00,100.00,700.00,'2025-08-01','Cash Memo'),
(342,'CME0122',462.00,154.00,154.00,1530.00,0.00,31,21.00,0.00,0.00,0.00,0.00,400.00,500.00,0.00,0.00,'TXM','Card Not Activated:\r\nAPTN784P2508130002570019\r\nAED 770 Paid through WPS Via Al Ansari Exchange (SIF File Number :1301092001350328)\r\nAED 630 Paid by Cash Memo',3,2300.00,900.00,1400.00,'2025-08-01','WPS'),
(343,'CME0033',800.00,0.00,0.00,1600.00,0.00,31,26.00,0.00,100.00,0.00,100.00,400.00,0.00,0.00,0.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2508250000780011',3,2400.00,500.00,1900.00,'2025-08-01','WPS'),
(344,'CME0098',800.00,0.00,0.00,2200.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3000.00,0.00,3000.00,'2025-08-01','Cash Memo'),
(345,'CME0005',2800.00,600.00,600.00,0.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-08-01','Cash Memo'),
(346,'CME0104',800.00,0.00,0.00,1550.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2350.00,0.00,2350.00,'2025-08-01','Cash Memo'),
(347,'CME0090',2500.00,0.00,0.00,2333.43,0.00,31,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4833.43,0.00,4833.43,'2025-08-01','Cash Memo'),
(348,'CME0006',800.00,100.00,100.00,2390.00,562.50,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3952.00,0.00,3952.50,'2025-08-01','Cash Memo'),
(349,'CME0015',800.00,0.00,0.00,1200.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2000.00,0.00,2000.00,'2025-08-01','Cash Memo'),
(350,'CME0107',800.00,0.00,0.00,2200.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,200.00,'','',0,3000.00,200.00,2800.00,'2025-08-01','Cash Memo'),
(351,'CME0004',7200.00,2400.00,2400.00,15486.30,0.00,31,27.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,600.00,'Integrated Solution Employment Services','CLAWBACK :\r\nAPTN784P2503250001730028\r\nAPTN784P2505210002570012\r\nAWOR784P2505060000640013\r\nAPTN784P2505080002430015\r\nAPTN784P2507050001010017\r\nAPTN784P2508110000850018',225,27486.30,700.00,26786.30,'2025-08-01','WPS'),
(352,'CME0003',17500.00,3750.00,3750.00,27800.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,1075.00,'','',0,52800.00,1075.00,51725.00,'2025-08-01','Cash Memo'),
(353,'CME0108',800.00,0.00,0.00,950.00,0.00,31,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,1750.00,0.00,1750.00,'2025-08-01','Cash Memo'),
(354,'CME0099',800.00,0.00,0.00,1550.00,0.00,31,31.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2350.00,0.00,2350.00,'2025-08-01','Cash Memo'),
(355,'CME0002',3030.00,1010.00,1010.00,7140.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','Salary - 5050 \r\nIncentive - 4500 (290 Cards) \r\nSpecial Promo - AED 2640',0,12190.00,0.00,12190.00,'2025-09-30','WPS'),
(356,'CME0005',2800.00,600.00,600.00,0.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,4000.00,0.00,4000.00,'2025-09-01','Cash Memo'),
(357,'CME0018',500.00,250.00,250.00,5200.00,0.00,30,30.00,0.00,100.00,0.00,100.00,0.00,3500.00,0.00,25.00,'Integrated Solution Employment Services','',7,6200.00,3625.00,2575.00,'2025-09-01','WPS'),
(358,'CME0017',800.00,100.00,100.00,9600.00,0.00,30,28.00,0.00,0.00,0.00,0.00,900.00,4500.00,0.00,525.00,'Integrated Solution Employment Services','Card Not Activated\r\nATTN784P2505100001100029\r\nPA Call Not done\r\nAPTN784P2509190000590012\r\nClawback:\r\nATTN784P2506190000170015',11,10600.00,5925.00,4675.00,'2025-09-01','WPS'),
(359,'CME0021',800.00,100.00,100.00,1900.00,0.00,30,29.00,0.00,100.00,0.00,100.00,0.00,2000.00,0.00,0.00,'Integrated Solution Employment Services','Carryforward:\r\nAPTN784P2508230001640018',3,2900.00,2100.00,800.00,'2025-09-01','WPS'),
(360,'CME0019',500.00,350.00,350.00,4100.00,0.00,30,29.00,0.00,100.00,0.00,100.00,1200.00,2500.00,0.00,0.00,'Integrated Solution Employment Services','PS Call Not done:\r\nAWOR784P2509090001250013\r\nAPTN784P2509240001140017\r\nAPTN784P2509260000740013',6,5300.00,3800.00,1500.00,'2025-09-01','WPS'),
(361,'CME0006',800.00,100.00,100.00,2180.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','Salary - AED 2500\r\nSarah Incentive - AED 520\r\nAout Incentive - AED 80 \r\nAOUT Incentive - AED 80 for Aug 2024',0,3180.00,0.00,3180.00,'2025-09-01','Cash Memo'),
(362,'CME0023',1000.00,0.00,0.00,21400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,5000.00,0.00,50.00,'Integrated Solution Employment Services','Added AED 1600 - Ajman & Maweik Cards',20,22400.00,5050.00,17350.00,'2025-09-01','WPS'),
(363,'CME0008',800.00,0.00,0.00,8950.00,0.00,30,26.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,875.00,'','Clawback:\r\nATTN784P2506190000170015',0,9750.00,875.00,8875.00,'2025-09-01','Cash Memo'),
(364,'CME0007',800.00,0.00,0.00,4140.00,0.00,30,29.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,390.00,'','Clawback:\r\nAPTN784P2505250000220011\r\nAPTN784P2502170000670515\r\nAPTN784P2506020001470017',0,4940.00,490.00,4450.00,'2025-09-01','Cash Memo'),
(365,'CME0024',800.00,0.00,0.00,2900.00,0.00,30,30.00,0.00,100.00,0.00,100.00,1000.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2509060000430015\r\nAPTN784P2509150002650015\r\nAPTN78440091685170519\r\nCarry forward: \r\nATTN784P250623000183001',4,3700.00,2600.00,1100.00,'2025-09-01','WPS'),
(366,'CME0014',800.00,0.00,0.00,10150.00,0.00,30,29.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,600.00,'','Clawback:\r\nAPTN784P2506100000810015\r\nATTN784P2505260000850014',0,10950.00,600.00,10350.00,'2025-09-01','Cash Memo'),
(367,'CME0026',800.00,0.00,0.00,5400.00,0.00,30,28.00,0.00,100.00,0.00,100.00,0.00,3000.00,0.00,0.00,'Integrated Solution Employment Services','Carryforward:\r\nAWOR784P2508250000240016\r\nAPTN78410041105320018',6,6200.00,3100.00,3100.00,'2025-09-01','WPS'),
(368,'CME0040',3000.00,1000.00,1000.00,6400.00,0.00,30,26.00,0.00,0.00,0.00,0.00,0.00,4100.00,0.00,2000.00,'TXM','Clawback:\r\nATTN784P2505260000850014\r\nOne card Deducted for Inactive visa cost:\r\nATTN784P2508280001170011\r\nCarry Forward:\r\nAWOR784P2507280001530026\r\nAED 5000 Paid through WPS \r\nAED 300 Paid in cash ',0,11400.00,6100.00,5300.00,'2025-09-01','WPS'),
(369,'CME0003',17500.00,3750.00,3750.00,33000.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,1400.00,'','Clawback:\r\nAPTN784P2505250000220011\r\nAPTN784P2506100000810015\r\nAPTN784P2502170000670515\r\nATTN784P2505260000850014\r\nATTN784P2506190000170015\r\nAPTN784P2506020001470017',0,58000.00,1400.00,56600.00,'2025-09-01','Cash Memo'),
(370,'CME0030',800.00,0.00,0.00,800.00,0.00,30,17.00,3.00,0.00,160.00,0.00,0.00,1000.00,0.00,0.00,'Integrated Solution Employment Services','',0,1600.00,1160.00,440.00,'2025-09-01','WPS'),
(371,'CME0031',800.00,0.00,0.00,3200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,25.00,'Integrated Solution Employment Services','',5,4000.00,25.00,3975.00,'2025-09-01','WPS'),
(372,'CME0032',800.00,0.00,0.00,4700.00,0.00,30,28.00,0.00,0.00,0.00,0.00,0.00,3000.00,0.00,25.00,'','Carry Forward:\r\nATTN784P2508270003230012',6,5500.00,3025.00,2475.00,'2025-09-01','Cash Memo'),
(373,'CME0015',800.00,0.00,0.00,1200.00,0.00,30,29.00,1.00,0.00,66.67,0.00,0.00,0.00,0.00,0.00,'','',0,2000.00,66.67,1933.33,'2025-09-01','Cash Memo'),
(374,'CME0004',7200.00,2400.00,2400.00,0.00,0.00,30,21.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',0,12000.00,0.00,12000.00,'2025-09-01','WPS'),
(375,'CME0013',800.00,0.00,0.00,2320.00,0.00,30,29.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','Special Promo Pending - June 2025',0,3120.00,100.00,3020.00,'2025-09-01','Cash Memo'),
(376,'CME0016',800.00,0.00,0.00,1400.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2200.00,0.00,2200.00,'2025-09-01','Cash Memo'),
(377,'CME0025',800.00,0.00,0.00,18300.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,4000.00,0.00,25.00,'Integrated Solution Employment Services','Carry Forward:\r\nATTN78440091856180017\r\nAWOR784P2507280000590013\r\nAED 300 Added - Pending Amt',17,19100.00,4025.00,15075.00,'2025-09-01','WPS'),
(378,'CME0038',800.00,0.00,0.00,5400.00,0.00,30,28.00,0.00,100.00,0.00,100.00,0.00,3000.00,0.00,25.00,'Integrated Solution Employment Services','Carry Forward:\r\nAPTN784P2508060001650018\r\nAPTN784P2508260001460018',6,6200.00,3125.00,3075.00,'2025-09-01','WPS'),
(379,'CME0059',800.00,0.00,0.00,640.00,0.00,30,24.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','',0,1440.00,100.00,1340.00,'2025-09-01','Cash Memo'),
(380,'CME0047',800.00,0.00,0.00,4096.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,2000.00,0.00,0.00,'Integrated Solution Employment Services','ADDED:\r\nAED 126 - Deducted last Month 2nd time\r\nAED 900 - Missed on last Month Salary\r\nAED 20 - Deducted LTO (Without considering 3 LTO Wavier)\r\nCarry forward:\r\nAPTN784P2506290000230015\r\nAPTN78440077885150013',4,4896.00,2000.00,2896.00,'2025-09-01','WPS'),
(381,'CME0055',800.00,1200.00,3000.00,19050.00,0.00,30,28.00,0.00,0.00,0.00,0.00,1400.00,13800.00,0.00,1950.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2509180000070015\r\nATTN784P2509190002160011\r\nATTN784P2509240000860011\r\nPS call Not done:\r\nAWOR784P2509220000050011\r\nOne card Deducted for Inactive visa cost:\r\nAPTN784P2508290002320010',23,24050.00,17150.00,6900.00,'2025-09-01','WPS'),
(382,'CME0051',800.00,0.00,0.00,15800.00,0.00,30,30.00,0.00,0.00,0.00,0.00,2175.00,7500.00,0.00,925.00,'','Clawback:\r\nAPTN784P2505250000220011\r\nCard Not Activated:\r\nAPTN78440074147610017\r\nAPTN784P2509290001220012\r\nPS call Not done:\r\nAPTN784P2508300001570019\r\nAPTN78440075393330011\r\nAPTN784P2509290001220012',16,16600.00,10600.00,6000.00,'2025-09-01','Cash Memo'),
(383,'CME0048',800.00,0.00,0.00,7800.00,0.00,30,29.00,0.00,100.00,0.00,100.00,1100.00,3400.00,0.00,900.00,'Integrated Solution Employment Services','PS call Not done:\r\nAPTN784P2507260000860029\r\nAPTN784P2509120002030013\r\nAPTN784P2509240001320015\r\nClawback:\r\nAPTN784P2502170000670515\r\nCarry Forward:\r\nAPTN784P2507160000680023\r\nAWOR784P2507060001130012',9,8600.00,5500.00,3100.00,'2025-09-01','WPS'),
(384,'CME0043',800.00,0.00,0.00,0.00,0.00,30,28.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,25.00,'Integrated Solution Employment Services','',1,800.00,25.00,775.00,'2025-09-01','WPS'),
(385,'CME0046',800.00,0.00,0.00,5700.00,0.00,30,30.00,0.00,100.00,0.00,100.00,400.00,3000.00,0.00,0.00,'Integrated Solution Employment Services','Card Not Activated:\r\nAPTN784P2406010001030516\r\nCarry forward:\r\nAWOR784P2507170002170014',7,6500.00,3500.00,3000.00,'2025-09-01','WPS'),
(386,'CME0045',800.00,0.00,0.00,2800.00,0.00,30,24.00,6.00,100.00,0.00,100.00,1000.00,1500.00,0.00,0.00,'Integrated Solution Employment Services','Carry Forward:\r\nAPTN78440081391540015\r\nCard Not Activated:\r\nAPTN784P2509190001320012\r\nAPTN784P2312070000871016\r\nAPTN78421081160240013',4,3600.00,2600.00,1000.00,'2025-09-01','WPS'),
(387,'CME0081',960.00,320.00,320.00,3600.00,0.00,30,23.00,7.00,100.00,0.00,100.00,0.00,2500.00,0.00,0.00,'TXM','AED 1600 paid through WPS\r\nAED 1000 Paid in Cash',6,5200.00,2600.00,2600.00,'2025-09-01','WPS'),
(388,'CME0108',800.00,0.00,0.00,1000.00,0.00,30,29.00,1.00,0.00,60.00,0.00,0.00,0.00,0.00,0.00,'','',0,1800.00,60.00,1740.00,'2025-09-01','Cash Memo'),
(389,'CME0098',800.00,0.00,0.00,2200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3000.00,0.00,3000.00,'2025-09-01','Cash Memo'),
(390,'CME0068',800.00,0.00,0.00,900.00,0.00,30,23.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'Integrated Solution Employment Services','',2,1700.00,100.00,1600.00,'2025-09-01','WPS'),
(391,'CME0078',800.00,0.00,0.00,10100.00,0.00,30,28.00,0.00,0.00,0.00,0.00,0.00,5600.00,0.00,0.00,'Integrated Solution Employment Services','',0,10900.00,5600.00,5300.00,'2025-09-01','WPS'),
(392,'CME0096',800.00,0.00,0.00,1500.00,0.00,30,27.00,0.00,100.00,0.00,100.00,300.00,1000.00,0.00,25.00,'','Card Not Activated:\r\nAWOR784P2509200001560011',0,2300.00,1425.00,875.00,'2025-09-01','Cash Memo'),
(393,'CME0072',0.00,0.00,0.00,0.00,0.00,30,24.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,0.00,0.00,0.00,'2025-09-01','Cash Memo'),
(394,'CME0092',1260.00,0.00,0.00,240.00,0.00,30,23.00,7.00,0.00,0.00,0.00,0.00,1000.00,0.00,0.00,'TXM','',2,1500.00,1000.00,500.00,'2025-09-01','WPS'),
(395,'CME0089',1260.00,420.00,420.00,2300.00,0.00,30,28.00,0.00,100.00,0.00,100.00,0.00,2500.00,0.00,0.00,'TXM','',5,4400.00,2600.00,1800.00,'2025-09-01','WPS'),
(396,'CME0102',960.00,320.00,320.00,2700.00,0.00,30,24.00,6.00,0.00,0.00,0.00,0.00,1900.00,0.00,0.00,'TXM','AED 1600 Paid through WPS\r\nAED 800 Paid in Cash',5,4300.00,1900.00,2400.00,'2025-09-01','WPS'),
(397,'CME0088',400.00,0.00,0.00,0.00,0.00,30,22.00,0.00,100.00,0.00,100.00,0.00,0.00,0.00,0.00,'','Carry forward:\r\nAPTN784P2507280001100019',0,400.00,100.00,300.00,'2025-09-01','Cash Memo'),
(398,'CME0107',800.00,0.00,0.00,2200.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,3000.00,0.00,3000.00,'2025-09-01','Cash Memo'),
(399,'CME0085',960.00,320.00,320.00,0.00,0.00,30,30.00,0.00,100.00,0.00,100.00,0.00,500.00,0.00,0.00,'TXM','',2,1600.00,600.00,1000.00,'2025-09-01','WPS'),
(400,'CME0090',2500.00,0.00,0.00,0.00,0.00,30,30.00,0.00,180.00,0.00,180.00,0.00,0.00,0.00,0.00,'','',0,2500.00,180.00,2320.00,'2025-09-01','Cash Memo'),
(401,'CME0099',800.00,0.00,0.00,1550.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2350.00,0.00,2350.00,'2025-09-01','Cash Memo'),
(402,'CME0120',800.00,0.00,0.00,2400.00,0.00,30,30.00,0.00,100.00,0.00,100.00,400.00,1500.00,0.00,0.00,'','',4,3200.00,2000.00,1200.00,'2025-09-01','Cash Memo'),
(403,'CME0104',800.00,0.00,0.00,1550.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,2350.00,0.00,2350.00,'2025-09-01','Cash Memo'),
(404,'CME0122',1260.00,420.00,420.00,1500.00,0.00,30,27.00,3.00,100.00,0.00,100.00,400.00,1500.00,0.00,25.00,'TXM','Card Not Activated:\r\nATTN784P2508150000940021\r\nCarry forward:\r\nAPTN784P2508130002570019',4,3600.00,2025.00,1575.00,'2025-09-01','WPS'),
(405,'CME0121',800.00,0.00,0.00,700.00,0.00,30,18.00,0.00,0.00,0.00,0.00,0.00,1000.00,0.00,25.00,'','',0,1500.00,1025.00,475.00,'2025-09-01','Cash Memo'),
(406,'CME0106',800.00,0.00,0.00,1450.00,0.00,30,22.00,8.00,0.00,600.00,0.00,0.00,0.00,0.00,0.00,'','',0,2250.00,600.00,1650.00,'2025-09-01','Cash Memo'),
(407,'CME0105',800.00,0.00,0.00,21130.00,0.00,30,30.00,0.00,0.00,0.00,0.00,0.00,12600.00,0.00,75.00,'','Added AED 80 - Last month deducted for LTO\r\nCarry Forward:\r\nAPTN784P2508190001610017\r\nAWOR784P2508270002960015',21,21930.00,12675.00,9255.00,'2025-09-01','Cash Memo'),
(408,'CME0109',800.00,0.00,0.00,5600.00,0.00,30,20.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,50.00,'','Carry Forward:\r\nAPTN784P2508250000420014',7,6400.00,50.00,6350.00,'2025-09-01','Cash Memo'),
(409,'CME0123',700.00,0.00,0.00,0.00,0.00,30,22.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,25.00,'','',0,700.00,25.00,675.00,'2025-09-01','Cash Memo'),
(410,'CME0126',800.00,0.00,0.00,0.00,0.00,30,17.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'TXM','',1,800.00,0.00,800.00,'2025-09-01','WPS'),
(411,'CME0127',800.00,0.00,0.00,1600.00,0.00,30,19.00,0.00,0.00,0.00,0.00,0.00,1300.00,0.00,0.00,'','',0,2400.00,1300.00,1100.00,'2025-09-01','Cash Memo'),
(412,'CME0128',1260.00,420.00,420.00,200.00,0.00,30,21.00,0.00,0.00,0.00,0.00,350.00,500.00,0.00,0.00,'TXM','Card Not Activated:\r\nATTN784P2507210003160026',0,2300.00,850.00,1450.00,'2025-09-01','WPS'),
(413,'CME0001',5000.00,0.00,0.00,0.00,0.00,31,22.00,9.00,0.00,200.00,0.00,0.00,0.00,0.00,0.00,'','',0,5000.00,200.00,4800.00,'2026-05-01','Cash Memo'),
(414,'CME0001',10000.00,0.00,0.00,0.00,0.00,30,25.00,5.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'','',0,10000.00,0.00,10000.00,'2026-04-01','Cash Memo');
/*!40000 ALTER TABLE `sal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary`
--

DROP TABLE IF EXISTS `salary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(255) NOT NULL,
  `base_salary` float NOT NULL DEFAULT 0,
  `bonus` float DEFAULT 0,
  `total_salary` float NOT NULL,
  `salary_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary`
--

LOCK TABLES `salary` WRITE;
/*!40000 ALTER TABLE `salary` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_slips`
--

DROP TABLE IF EXISTS `salary_slips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_slips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slip_no` int(11) NOT NULL,
  `salary_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1466 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_slips`
--

LOCK TABLES `salary_slips` WRITE;
/*!40000 ALTER TABLE `salary_slips` DISABLE KEYS */;
INSERT INTO `salary_slips` VALUES
(71,1,40,'2025-05-21 12:26:47'),
(72,2,40,'2025-05-21 12:59:19'),
(73,3,40,'2025-05-21 13:17:16'),
(74,4,40,'2025-05-23 07:26:12'),
(75,5,40,'2025-06-20 06:43:21'),
(76,6,40,'2025-06-25 11:44:12'),
(77,7,40,'2025-06-28 19:56:28'),
(78,8,40,'2025-06-28 19:56:44'),
(79,9,40,'2025-06-28 19:56:58'),
(80,10,40,'2025-06-29 16:19:04'),
(81,11,40,'2025-07-02 07:04:33'),
(82,12,40,'2025-07-02 07:06:22'),
(83,13,40,'2025-07-03 05:24:29'),
(84,14,41,'2025-07-04 13:14:56'),
(85,15,40,'2025-07-04 13:17:50'),
(86,16,40,'2025-07-04 13:21:02'),
(87,17,45,'2025-07-04 13:33:41'),
(88,18,47,'2025-07-04 13:37:59'),
(89,19,44,'2025-07-04 13:39:44'),
(90,20,40,'2025-07-04 13:56:55'),
(91,21,41,'2025-07-04 14:50:02'),
(92,22,49,'2025-07-13 11:31:32'),
(93,23,49,'2025-07-15 19:19:54'),
(94,24,41,'2025-07-15 19:22:11'),
(95,25,58,'2025-07-15 19:23:11'),
(96,26,52,'2025-07-15 19:24:06'),
(97,27,127,'2025-07-16 12:03:29'),
(98,28,40,'2025-07-17 10:59:59'),
(99,29,146,'2025-07-17 11:01:37'),
(100,30,172,'2025-07-18 08:00:20'),
(101,31,146,'2025-07-18 08:13:48'),
(102,32,178,'2025-07-18 08:13:51'),
(103,33,174,'2025-07-18 08:13:57'),
(104,34,176,'2025-07-18 08:14:01'),
(105,35,175,'2025-07-18 08:14:04'),
(106,36,173,'2025-07-18 08:14:07'),
(107,37,175,'2025-07-18 08:17:42'),
(108,38,177,'2025-07-18 08:18:18'),
(109,39,179,'2025-07-18 08:47:34'),
(110,40,181,'2025-07-18 08:48:18'),
(111,41,182,'2025-07-18 08:48:21'),
(112,42,183,'2025-07-18 08:49:27'),
(113,43,184,'2025-07-18 08:50:30'),
(114,44,180,'2025-07-18 08:50:34'),
(115,45,186,'2025-07-18 08:51:28'),
(116,46,185,'2025-07-18 08:51:31'),
(117,47,193,'2025-07-18 09:18:18'),
(118,48,192,'2025-07-18 09:43:42'),
(119,49,191,'2025-07-18 09:44:02'),
(120,50,190,'2025-07-18 09:44:24'),
(121,51,189,'2025-07-18 09:44:29'),
(122,52,188,'2025-07-18 09:44:57'),
(123,53,187,'2025-07-18 09:46:27'),
(124,54,192,'2025-07-18 14:01:12'),
(125,55,206,'2025-07-19 06:37:41'),
(126,56,204,'2025-07-19 06:42:52'),
(127,57,205,'2025-07-19 06:42:54'),
(128,58,222,'2025-07-19 11:24:10'),
(129,59,179,'2025-07-21 09:01:47'),
(130,60,179,'2025-07-21 11:27:36'),
(131,61,146,'2025-07-21 11:30:42'),
(132,62,229,'2025-07-21 11:32:27'),
(133,63,161,'2025-07-21 11:33:42'),
(134,64,179,'2025-07-21 11:48:53'),
(135,65,187,'2025-07-21 11:49:14'),
(136,66,178,'2025-07-21 11:49:18'),
(137,67,214,'2025-07-21 11:49:23'),
(138,68,188,'2025-07-21 11:49:29'),
(139,69,207,'2025-07-21 11:49:32'),
(140,70,222,'2025-07-21 11:49:40'),
(141,71,221,'2025-07-21 11:49:42'),
(142,72,189,'2025-07-21 11:49:45'),
(143,73,190,'2025-07-21 11:49:47'),
(144,74,181,'2025-07-21 11:49:50'),
(145,75,191,'2025-07-21 11:49:53'),
(146,76,191,'2025-07-21 11:49:59'),
(147,77,216,'2025-07-21 11:50:02'),
(148,78,182,'2025-07-21 11:50:04'),
(149,79,218,'2025-07-21 11:50:07'),
(150,80,172,'2025-07-21 11:50:10'),
(151,81,215,'2025-07-21 11:50:13'),
(152,82,174,'2025-07-21 11:50:15'),
(153,83,183,'2025-07-21 11:50:19'),
(154,84,176,'2025-07-21 11:50:22'),
(155,85,175,'2025-07-21 11:50:27'),
(156,86,173,'2025-07-21 11:50:30'),
(157,87,184,'2025-07-21 11:50:32'),
(158,88,180,'2025-07-21 11:55:15'),
(159,89,204,'2025-07-21 11:55:17'),
(160,90,204,'2025-07-21 11:55:24'),
(161,91,211,'2025-07-21 11:56:21'),
(162,92,205,'2025-07-21 11:57:39'),
(163,93,193,'2025-07-21 11:57:41'),
(164,94,185,'2025-07-21 11:58:18'),
(165,95,213,'2025-07-21 11:58:21'),
(166,96,206,'2025-07-21 11:58:27'),
(167,97,205,'2025-07-21 12:21:24'),
(168,98,184,'2025-07-21 12:22:03'),
(169,99,173,'2025-07-21 12:22:06'),
(170,100,175,'2025-07-21 12:22:09'),
(171,101,176,'2025-07-21 12:22:59'),
(172,102,192,'2025-07-21 12:23:39'),
(173,103,52,'2025-07-21 12:28:01'),
(174,104,50,'2025-07-21 12:28:03'),
(175,105,82,'2025-07-21 12:28:06'),
(176,106,82,'2025-07-21 12:28:17'),
(177,107,54,'2025-07-21 12:28:25'),
(178,108,53,'2025-07-21 12:29:09'),
(179,109,44,'2025-07-21 12:29:47'),
(180,110,46,'2025-07-21 12:29:50'),
(181,111,45,'2025-07-21 12:30:26'),
(182,112,48,'2025-07-21 12:30:39'),
(183,113,55,'2025-07-21 12:30:42'),
(184,114,49,'2025-07-21 12:31:23'),
(185,115,57,'2025-07-21 12:33:18'),
(186,116,60,'2025-07-21 12:33:21'),
(187,117,56,'2025-07-21 12:34:03'),
(188,118,84,'2025-07-21 12:34:25'),
(189,119,61,'2025-07-21 12:34:28'),
(190,120,81,'2025-07-21 12:35:07'),
(191,121,64,'2025-07-21 12:35:27'),
(192,122,67,'2025-07-21 12:35:30'),
(193,123,63,'2025-07-21 12:36:11'),
(194,124,68,'2025-07-21 12:36:13'),
(195,125,69,'2025-07-21 12:36:15'),
(196,126,65,'2025-07-21 12:37:31'),
(197,127,59,'2025-07-21 12:37:53'),
(198,128,62,'2025-07-21 12:38:15'),
(199,129,89,'2025-07-21 12:38:19'),
(200,130,83,'2025-07-21 12:38:21'),
(201,131,89,'2025-07-21 12:42:18'),
(202,132,89,'2025-07-21 12:44:34'),
(203,133,83,'2025-07-21 12:44:37'),
(204,134,97,'2025-07-21 12:46:44'),
(205,135,72,'2025-07-21 12:48:04'),
(206,136,73,'2025-07-21 12:48:07'),
(207,137,72,'2025-07-21 12:50:52'),
(208,138,68,'2025-07-21 13:01:30'),
(209,139,107,'2025-07-21 13:14:39'),
(210,140,108,'2025-07-21 13:15:36'),
(211,141,126,'2025-07-21 13:17:47'),
(212,142,110,'2025-07-21 13:17:50'),
(213,143,108,'2025-07-21 13:18:47'),
(214,144,107,'2025-07-21 13:19:10'),
(215,145,126,'2025-07-21 13:20:24'),
(216,146,144,'2025-07-22 10:36:44'),
(217,147,143,'2025-07-22 10:36:51'),
(218,148,160,'2025-07-22 10:37:08'),
(219,149,147,'2025-07-22 10:38:06'),
(220,150,137,'2025-07-22 10:38:14'),
(221,151,139,'2025-07-22 10:38:48'),
(222,152,140,'2025-07-22 10:38:52'),
(223,153,138,'2025-07-22 10:39:50'),
(224,154,142,'2025-07-22 10:39:51'),
(225,155,145,'2025-07-22 10:39:54'),
(226,156,148,'2025-07-22 10:40:02'),
(227,157,149,'2025-07-22 10:40:05'),
(228,158,157,'2025-07-22 10:41:02'),
(229,159,155,'2025-07-22 10:41:04'),
(230,160,154,'2025-07-22 10:41:07'),
(231,161,150,'2025-07-22 10:41:49'),
(232,162,108,'2025-07-22 10:44:41'),
(233,163,107,'2025-07-22 10:44:43'),
(234,164,126,'2025-07-22 10:44:54'),
(235,165,110,'2025-07-22 10:44:58'),
(236,166,110,'2025-07-22 10:45:46'),
(237,167,101,'2025-07-22 10:46:14'),
(238,168,103,'2025-07-22 10:46:17'),
(239,169,102,'2025-07-22 10:46:29'),
(240,170,106,'2025-07-22 10:46:32'),
(241,171,109,'2025-07-22 10:46:34'),
(242,172,112,'2025-07-22 10:47:50'),
(243,173,111,'2025-07-22 10:47:52'),
(244,174,113,'2025-07-22 10:47:57'),
(245,175,120,'2025-07-22 10:48:34'),
(246,176,121,'2025-07-22 10:48:37'),
(247,177,133,'2025-07-22 10:49:11'),
(248,178,115,'2025-07-22 10:49:26'),
(249,179,132,'2025-07-22 10:49:41'),
(250,180,130,'2025-07-22 10:50:06'),
(251,181,124,'2025-07-22 10:50:09'),
(252,182,57,'2025-07-22 11:07:49'),
(253,183,65,'2025-07-22 12:24:27'),
(254,184,204,'2025-07-22 13:14:46'),
(255,185,204,'2025-07-22 13:17:11'),
(256,186,146,'2025-07-23 10:22:51'),
(257,187,172,'2025-07-23 10:23:13'),
(258,188,146,'2025-07-23 10:52:06'),
(259,189,40,'2025-07-23 10:52:14'),
(260,190,91,'2025-07-23 11:04:47'),
(261,191,128,'2025-07-23 11:04:56'),
(262,192,40,'2025-07-23 11:45:50'),
(263,193,40,'2025-07-23 11:48:47'),
(264,194,115,'2025-07-24 11:17:38'),
(265,195,59,'2025-07-24 11:17:53'),
(266,196,215,'2025-07-24 11:18:14'),
(267,197,51,'2025-07-26 16:21:34'),
(268,198,235,'2025-07-26 16:25:13'),
(269,199,117,'2025-07-26 16:27:30'),
(270,200,152,'2025-07-26 16:27:43'),
(271,201,171,'2025-07-26 16:27:54'),
(272,202,229,'2025-07-26 16:28:03'),
(273,203,151,'2025-07-26 16:29:01'),
(274,204,116,'2025-07-26 16:29:11'),
(275,205,88,'2025-07-26 16:29:23'),
(276,206,234,'2025-07-26 16:30:08'),
(277,207,229,'2025-07-28 05:27:49'),
(278,208,230,'2025-07-28 05:27:58'),
(279,209,229,'2025-07-28 05:28:09'),
(280,210,224,'2025-07-28 05:28:19'),
(281,211,223,'2025-07-28 05:28:29'),
(282,212,225,'2025-07-28 05:28:36'),
(283,213,226,'2025-07-28 05:28:44'),
(284,214,227,'2025-07-28 05:28:49'),
(285,215,228,'2025-07-28 05:28:56'),
(286,216,161,'2025-07-28 05:29:18'),
(287,217,171,'2025-07-28 05:29:25'),
(288,218,169,'2025-07-28 05:29:31'),
(289,219,167,'2025-07-28 05:29:37'),
(290,220,194,'2025-07-28 05:29:44'),
(291,221,195,'2025-07-28 05:29:52'),
(292,222,170,'2025-07-28 05:29:58'),
(293,223,197,'2025-07-28 05:30:04'),
(294,224,163,'2025-07-28 05:30:11'),
(295,225,162,'2025-07-28 05:30:17'),
(296,226,165,'2025-07-28 05:30:26'),
(297,227,166,'2025-07-28 05:30:34'),
(298,228,164,'2025-07-28 05:30:41'),
(299,229,168,'2025-07-28 05:30:48'),
(300,230,196,'2025-07-28 05:30:54'),
(301,231,198,'2025-07-28 05:31:01'),
(302,232,199,'2025-07-28 05:31:07'),
(303,233,201,'2025-07-28 05:31:12'),
(304,234,202,'2025-07-28 05:31:18'),
(305,235,203,'2025-07-28 05:31:26'),
(306,236,200,'2025-07-28 05:31:33'),
(307,237,134,'2025-07-28 05:31:46'),
(308,238,152,'2025-07-28 05:31:52'),
(309,239,151,'2025-07-28 05:31:57'),
(310,240,135,'2025-07-28 05:32:05'),
(311,241,141,'2025-07-28 05:32:11'),
(312,242,144,'2025-07-28 05:32:17'),
(313,243,143,'2025-07-28 05:32:24'),
(314,244,153,'2025-07-28 05:32:30'),
(315,245,160,'2025-07-28 05:32:38'),
(316,246,147,'2025-07-28 05:32:44'),
(317,247,158,'2025-07-28 05:32:50'),
(318,248,159,'2025-07-28 05:32:57'),
(319,249,137,'2025-07-28 05:33:03'),
(320,250,136,'2025-07-28 05:33:11'),
(321,251,139,'2025-07-28 05:33:17'),
(322,252,140,'2025-07-28 05:33:23'),
(323,253,138,'2025-07-28 05:33:28'),
(324,254,142,'2025-07-28 05:33:35'),
(325,255,145,'2025-07-28 05:33:41'),
(326,256,148,'2025-07-28 05:33:46'),
(327,257,149,'2025-07-28 05:33:52'),
(328,258,156,'2025-07-28 05:33:59'),
(329,259,154,'2025-07-28 05:34:07'),
(330,260,155,'2025-07-28 05:34:12'),
(331,261,157,'2025-07-28 05:34:20'),
(332,262,150,'2025-07-28 05:34:38'),
(333,263,98,'2025-07-28 05:34:48'),
(334,264,117,'2025-07-28 05:34:53'),
(335,265,116,'2025-07-28 05:34:59'),
(336,266,99,'2025-07-28 05:35:04'),
(337,267,105,'2025-07-28 05:35:11'),
(338,268,108,'2025-07-28 05:35:16'),
(339,269,107,'2025-07-28 05:35:22'),
(340,270,119,'2025-07-28 05:35:29'),
(341,271,126,'2025-07-28 05:35:35'),
(342,272,110,'2025-07-28 05:35:42'),
(343,273,125,'2025-07-28 05:35:50'),
(344,274,129,'2025-07-28 05:35:56'),
(345,275,101,'2025-07-28 05:36:02'),
(346,276,100,'2025-07-28 05:38:08'),
(347,277,103,'2025-07-28 05:38:16'),
(348,278,104,'2025-07-28 05:38:23'),
(349,279,102,'2025-07-28 05:38:30'),
(350,280,106,'2025-07-28 05:38:36'),
(351,281,109,'2025-07-28 05:38:44'),
(352,282,112,'2025-07-28 05:38:49'),
(353,283,111,'2025-07-28 05:38:55'),
(354,284,113,'2025-07-28 05:39:02'),
(355,285,123,'2025-07-28 05:39:08'),
(356,286,120,'2025-07-28 05:39:14'),
(357,287,121,'2025-07-28 05:39:19'),
(358,288,124,'2025-07-28 05:39:30'),
(359,289,130,'2025-07-28 05:39:36'),
(360,290,114,'2025-07-28 05:39:41'),
(361,291,118,'2025-07-28 05:39:46'),
(362,292,122,'2025-07-28 05:39:51'),
(363,293,132,'2025-07-28 05:39:58'),
(364,294,131,'2025-07-28 05:40:03'),
(365,295,115,'2025-07-28 05:40:10'),
(366,296,127,'2025-07-28 05:40:16'),
(367,297,128,'2025-07-28 05:40:21'),
(368,298,133,'2025-07-28 05:40:27'),
(369,299,40,'2025-07-28 05:40:41'),
(370,300,51,'2025-07-28 05:40:46'),
(371,301,88,'2025-07-28 05:40:53'),
(372,302,41,'2025-07-28 05:40:59'),
(373,303,42,'2025-07-28 05:41:05'),
(374,304,52,'2025-07-28 05:41:10'),
(375,305,53,'2025-07-28 05:41:17'),
(376,306,50,'2025-07-28 05:41:25'),
(377,307,82,'2025-07-28 05:41:30'),
(378,308,54,'2025-07-28 05:41:36'),
(379,309,85,'2025-07-28 05:41:42'),
(380,310,86,'2025-07-28 05:41:47'),
(381,311,44,'2025-07-28 05:41:53'),
(382,312,43,'2025-07-28 05:41:59'),
(383,313,46,'2025-07-28 05:42:06'),
(384,314,47,'2025-07-28 05:42:12'),
(385,315,45,'2025-07-28 05:42:18'),
(386,316,49,'2025-07-28 05:42:25'),
(387,317,48,'2025-07-28 05:42:30'),
(388,318,55,'2025-07-28 05:42:35'),
(389,319,56,'2025-07-28 05:42:40'),
(390,320,57,'2025-07-28 05:42:46'),
(391,321,60,'2025-07-28 05:42:52'),
(392,322,84,'2025-07-28 05:42:59'),
(393,323,61,'2025-07-28 05:43:04'),
(394,324,83,'2025-07-28 05:43:20'),
(395,325,89,'2025-07-28 05:43:25'),
(396,326,58,'2025-07-28 05:43:36'),
(397,327,62,'2025-07-28 05:43:41'),
(398,328,80,'2025-07-28 05:43:47'),
(399,329,59,'2025-07-28 05:43:54'),
(400,330,90,'2025-07-28 05:43:59'),
(401,331,91,'2025-07-28 05:44:04'),
(402,332,65,'2025-07-28 05:44:11'),
(403,333,66,'2025-07-28 05:44:18'),
(404,334,69,'2025-07-28 05:44:24'),
(405,335,68,'2025-07-28 05:44:29'),
(406,336,63,'2025-07-28 05:44:35'),
(407,337,67,'2025-07-28 05:46:07'),
(408,338,87,'2025-07-28 05:46:12'),
(409,339,64,'2025-07-28 05:46:19'),
(410,340,70,'2025-07-28 05:46:25'),
(411,341,71,'2025-07-28 05:46:31'),
(412,342,81,'2025-07-28 05:46:37'),
(413,343,74,'2025-07-28 05:46:43'),
(414,344,95,'2025-07-28 05:46:49'),
(415,345,94,'2025-07-28 05:46:54'),
(416,346,96,'2025-07-28 05:47:00'),
(417,347,75,'2025-07-28 05:47:05'),
(418,348,78,'2025-07-28 05:47:11'),
(419,349,79,'2025-07-28 05:47:21'),
(420,350,77,'2025-07-28 05:47:26'),
(421,351,93,'2025-07-28 05:47:32'),
(422,352,76,'2025-07-28 05:47:38'),
(423,353,92,'2025-07-28 05:47:43'),
(424,354,73,'2025-07-28 05:47:48'),
(425,355,72,'2025-07-28 05:47:55'),
(426,356,97,'2025-07-28 05:48:01'),
(427,357,146,'2025-07-28 05:48:18'),
(428,358,235,'2025-07-28 05:48:28'),
(429,359,234,'2025-07-28 05:48:38'),
(430,360,231,'2025-07-28 05:48:43'),
(431,361,232,'2025-07-28 05:48:49'),
(432,362,179,'2025-07-28 05:48:55'),
(433,363,187,'2025-07-28 05:49:00'),
(434,364,178,'2025-07-28 05:49:05'),
(435,365,214,'2025-07-28 05:49:12'),
(436,366,237,'2025-07-28 05:49:19'),
(437,367,238,'2025-07-28 05:49:24'),
(438,368,188,'2025-07-28 05:49:31'),
(439,369,207,'2025-07-28 05:49:36'),
(440,370,222,'2025-07-28 05:49:42'),
(441,371,221,'2025-07-28 05:49:49'),
(442,372,233,'2025-07-28 05:49:55'),
(443,373,189,'2025-07-28 05:50:03'),
(444,374,190,'2025-07-28 05:50:08'),
(445,375,181,'2025-07-28 05:50:14'),
(446,376,191,'2025-07-28 05:50:19'),
(447,377,236,'2025-07-28 05:50:24'),
(448,378,216,'2025-07-28 05:50:30'),
(449,379,192,'2025-07-28 05:50:36'),
(450,380,182,'2025-07-28 05:50:41'),
(451,381,218,'2025-07-28 05:50:47'),
(452,382,172,'2025-07-28 05:50:57'),
(453,383,215,'2025-07-28 05:51:02'),
(454,384,174,'2025-07-28 05:51:07'),
(455,385,183,'2025-07-28 05:51:12'),
(456,386,176,'2025-07-28 05:51:17'),
(457,387,175,'2025-07-28 05:51:23'),
(458,388,173,'2025-07-28 05:51:33'),
(459,389,184,'2025-07-28 05:51:38'),
(460,390,180,'2025-07-28 05:51:44'),
(461,391,177,'2025-07-28 05:51:51'),
(462,392,204,'2025-07-28 05:51:56'),
(463,393,217,'2025-07-28 05:52:02'),
(464,394,219,'2025-07-28 05:52:08'),
(465,395,220,'2025-07-28 05:52:13'),
(466,396,211,'2025-07-28 05:52:19'),
(467,397,208,'2025-07-28 05:52:26'),
(468,398,209,'2025-07-28 05:52:31'),
(469,399,239,'2025-07-28 05:52:36'),
(470,400,205,'2025-07-28 05:52:41'),
(471,401,193,'2025-07-28 05:52:46'),
(472,402,186,'2025-07-28 05:52:52'),
(473,403,185,'2025-07-28 05:52:58'),
(474,404,213,'2025-07-28 05:53:03'),
(475,405,210,'2025-07-28 05:53:09'),
(476,406,212,'2025-07-28 05:53:15'),
(477,407,206,'2025-07-28 05:53:23'),
(478,408,229,'2025-07-28 12:46:27'),
(479,409,229,'2025-08-02 07:16:08'),
(480,410,42,'2025-08-09 07:21:59'),
(481,411,42,'2025-08-10 15:40:37'),
(482,412,178,'2025-08-12 11:41:47'),
(483,413,180,'2025-08-12 12:24:08'),
(484,414,64,'2025-08-12 12:24:30'),
(485,415,161,'2025-08-12 13:03:40'),
(486,416,146,'2025-08-12 13:04:54'),
(487,417,64,'2025-08-12 19:43:24'),
(488,418,180,'2025-08-12 19:45:03'),
(489,419,152,'2025-08-16 09:55:04'),
(490,420,144,'2025-08-16 09:55:14'),
(491,421,152,'2025-08-18 06:52:58'),
(492,422,151,'2025-08-18 06:55:59'),
(493,423,152,'2025-08-18 07:11:56'),
(494,424,152,'2025-08-18 07:13:18'),
(495,425,153,'2025-08-18 07:13:54'),
(496,426,159,'2025-08-18 07:14:25'),
(497,427,229,'2025-08-18 07:17:03'),
(498,428,129,'2025-08-18 07:18:33'),
(499,429,152,'2025-08-18 07:19:39'),
(500,430,152,'2025-08-18 07:20:35'),
(501,431,152,'2025-08-18 07:22:05'),
(502,432,75,'2025-08-18 09:56:33'),
(503,433,75,'2025-08-18 09:56:47'),
(504,434,266,'2025-08-18 10:51:44'),
(505,435,190,'2025-08-18 11:58:50'),
(506,436,198,'2025-08-18 11:59:01'),
(507,437,247,'2025-08-18 13:19:16'),
(508,438,247,'2025-08-18 13:19:19'),
(509,439,247,'2025-08-18 13:19:34'),
(510,440,240,'2025-08-18 13:20:18'),
(511,441,245,'2025-08-18 13:20:23'),
(512,442,253,'2025-08-18 13:20:27'),
(513,443,269,'2025-08-18 13:20:31'),
(514,444,247,'2025-08-18 13:23:35'),
(515,445,247,'2025-08-18 13:25:59'),
(516,446,260,'2025-08-18 13:44:02'),
(517,447,277,'2025-08-18 13:44:07'),
(518,448,276,'2025-08-18 13:44:12'),
(519,449,273,'2025-08-18 13:44:15'),
(520,450,248,'2025-08-18 13:44:19'),
(521,451,250,'2025-08-18 13:44:22'),
(522,452,249,'2025-08-18 13:44:28'),
(523,453,241,'2025-08-18 13:44:31'),
(524,454,251,'2025-08-18 13:44:34'),
(525,455,274,'2025-08-18 13:44:37'),
(526,456,271,'2025-08-18 13:45:48'),
(527,457,253,'2025-08-18 14:00:22'),
(528,458,223,'2025-08-18 14:22:03'),
(529,459,162,'2025-08-18 14:23:29'),
(530,460,162,'2025-08-18 14:23:42'),
(531,461,260,'2025-08-18 14:24:55'),
(532,462,162,'2025-08-18 14:52:54'),
(533,463,240,'2025-08-19 07:56:35'),
(534,464,245,'2025-08-19 08:02:31'),
(535,465,253,'2025-08-19 08:02:36'),
(536,466,269,'2025-08-19 08:02:40'),
(537,467,247,'2025-08-19 08:02:43'),
(538,468,260,'2025-08-19 08:02:47'),
(539,469,277,'2025-08-19 08:02:50'),
(540,470,276,'2025-08-19 08:02:54'),
(541,471,273,'2025-08-19 08:03:00'),
(542,472,248,'2025-08-19 08:03:04'),
(543,473,250,'2025-08-19 08:03:07'),
(544,474,257,'2025-08-19 08:05:01'),
(545,475,132,'2025-08-19 09:58:50'),
(546,476,62,'2025-08-19 09:58:56'),
(547,477,172,'2025-08-19 09:58:58'),
(548,478,62,'2025-08-19 09:59:10'),
(549,479,62,'2025-08-20 07:57:51'),
(550,480,229,'2025-08-22 12:14:06'),
(551,481,230,'2025-08-22 12:14:10'),
(552,482,224,'2025-08-22 12:14:14'),
(553,483,223,'2025-08-22 12:14:18'),
(554,484,225,'2025-08-22 12:14:21'),
(555,485,226,'2025-08-22 12:14:24'),
(556,486,227,'2025-08-22 12:14:28'),
(557,487,228,'2025-08-22 12:14:47'),
(558,488,281,'2025-08-25 06:17:54'),
(559,489,284,'2025-08-25 06:17:57'),
(560,490,283,'2025-08-25 06:17:59'),
(561,491,282,'2025-08-25 06:18:04'),
(562,492,290,'2025-08-25 06:18:07'),
(563,493,240,'2025-08-25 06:18:12'),
(564,494,245,'2025-08-25 06:18:15'),
(565,495,253,'2025-08-25 06:18:17'),
(566,496,269,'2025-08-25 06:18:20'),
(567,497,285,'2025-08-25 06:18:25'),
(568,498,286,'2025-08-25 06:18:27'),
(569,499,247,'2025-08-25 06:18:30'),
(570,500,260,'2025-08-25 06:18:43'),
(571,501,277,'2025-08-25 06:18:46'),
(572,502,276,'2025-08-25 06:18:49'),
(573,503,273,'2025-08-25 06:18:51'),
(574,504,248,'2025-08-25 06:18:56'),
(575,505,250,'2025-08-25 06:18:58'),
(576,506,249,'2025-08-25 06:19:01'),
(577,507,241,'2025-08-25 06:19:05'),
(578,508,251,'2025-08-25 06:19:07'),
(579,509,274,'2025-08-25 06:19:11'),
(580,510,271,'2025-08-25 06:19:14'),
(581,511,252,'2025-08-25 06:19:18'),
(582,512,255,'2025-08-25 06:19:21'),
(583,513,270,'2025-08-25 06:19:52'),
(584,514,257,'2025-08-25 06:19:59'),
(585,515,259,'2025-08-25 06:20:03'),
(586,516,258,'2025-08-25 06:20:07'),
(587,517,256,'2025-08-25 06:20:29'),
(588,518,243,'2025-08-25 06:20:33'),
(589,519,242,'2025-08-25 06:20:36'),
(590,520,275,'2025-08-25 06:20:39'),
(591,521,278,'2025-08-25 06:20:49'),
(592,522,292,'2025-08-25 06:20:55'),
(593,523,254,'2025-08-25 06:20:57'),
(594,524,280,'2025-08-25 06:20:59'),
(595,525,262,'2025-08-25 06:21:02'),
(596,526,261,'2025-08-25 06:21:04'),
(597,527,287,'2025-08-25 06:21:08'),
(598,528,246,'2025-08-25 06:21:11'),
(599,529,272,'2025-08-25 06:21:13'),
(600,530,244,'2025-08-25 06:21:18'),
(601,531,289,'2025-08-25 06:21:21'),
(602,532,268,'2025-08-25 06:21:23'),
(603,533,263,'2025-08-25 06:21:25'),
(604,534,264,'2025-08-25 06:21:27'),
(605,535,279,'2025-08-25 06:21:33'),
(606,536,288,'2025-08-25 06:21:35'),
(607,537,291,'2025-08-25 06:21:37'),
(608,538,267,'2025-08-25 06:21:44'),
(609,539,265,'2025-08-25 06:21:47'),
(610,540,266,'2025-08-25 06:21:50'),
(611,541,230,'2025-08-25 08:36:51'),
(612,542,281,'2025-08-25 08:38:17'),
(613,543,223,'2025-08-25 08:42:07'),
(614,544,288,'2025-08-25 12:59:20'),
(615,545,290,'2025-08-26 05:41:56'),
(616,546,48,'2025-08-26 07:43:52'),
(617,547,48,'2025-08-26 07:44:02'),
(618,548,248,'2025-08-26 07:49:22'),
(619,549,248,'2025-08-26 07:49:28'),
(620,550,189,'2025-08-26 07:49:31'),
(621,551,109,'2025-08-26 07:49:34'),
(622,552,145,'2025-08-26 07:49:38'),
(623,553,196,'2025-08-26 07:49:40'),
(624,554,288,'2025-08-26 14:05:38'),
(625,555,229,'2025-08-26 14:59:32'),
(626,556,229,'2025-08-26 15:00:22'),
(627,557,229,'2025-08-26 17:43:44'),
(628,558,229,'2025-08-27 08:14:54'),
(629,559,229,'2025-08-27 08:44:28'),
(630,560,230,'2025-08-27 08:44:40'),
(631,561,230,'2025-08-27 08:52:07'),
(632,562,229,'2025-08-27 08:52:34'),
(633,563,229,'2025-08-27 09:16:51'),
(634,564,229,'2025-08-27 09:29:40'),
(635,565,229,'2025-08-27 09:44:23'),
(636,566,230,'2025-08-27 09:44:48'),
(637,567,224,'2025-08-27 09:45:25'),
(638,568,224,'2025-08-27 10:00:20'),
(639,569,229,'2025-08-27 10:01:12'),
(640,570,229,'2025-08-27 10:16:15'),
(641,571,229,'2025-08-27 13:15:50'),
(642,572,86,'2025-08-27 13:16:44'),
(643,573,86,'2025-08-27 13:25:32'),
(644,574,238,'2025-08-27 13:26:18'),
(645,575,226,'2025-08-27 13:30:16'),
(646,576,230,'2025-08-27 13:32:21'),
(647,577,260,'2025-08-28 10:21:40'),
(648,578,223,'2025-08-28 12:28:43'),
(649,579,260,'2025-08-28 12:29:55'),
(650,580,260,'2025-08-28 12:49:14'),
(651,581,291,'2025-08-28 13:52:47'),
(652,582,290,'2025-08-29 05:48:15'),
(653,583,227,'2025-08-29 07:09:26'),
(654,584,63,'2025-08-29 12:55:22'),
(655,585,173,'2025-08-29 12:55:58'),
(656,586,256,'2025-08-29 12:56:30'),
(657,587,238,'2025-08-30 05:28:58'),
(658,588,229,'2025-08-30 06:59:43'),
(659,589,230,'2025-08-30 06:59:49'),
(660,590,224,'2025-08-30 06:59:53'),
(661,591,223,'2025-08-30 06:59:57'),
(662,592,225,'2025-08-30 07:00:00'),
(663,593,281,'2025-08-30 07:00:36'),
(664,594,284,'2025-08-30 07:00:41'),
(665,595,283,'2025-08-30 07:00:44'),
(666,596,282,'2025-08-30 07:00:46'),
(667,597,290,'2025-08-30 07:00:49'),
(668,598,240,'2025-08-30 07:00:52'),
(669,599,245,'2025-08-30 07:01:00'),
(670,600,253,'2025-08-30 07:01:02'),
(671,601,269,'2025-08-30 07:01:06'),
(672,602,285,'2025-08-30 07:01:09'),
(673,603,286,'2025-08-30 07:01:12'),
(674,604,247,'2025-08-30 07:01:14'),
(675,605,260,'2025-08-30 07:01:18'),
(676,606,277,'2025-08-30 07:01:21'),
(677,607,294,'2025-08-30 07:01:24'),
(678,608,227,'2025-08-30 07:01:27'),
(679,609,273,'2025-08-30 07:01:30'),
(680,610,248,'2025-08-30 07:01:33'),
(681,611,250,'2025-08-30 07:01:35'),
(682,612,249,'2025-08-30 07:01:39'),
(683,613,241,'2025-08-30 07:01:42'),
(684,614,251,'2025-08-30 07:01:45'),
(685,615,274,'2025-08-30 07:01:48'),
(686,616,271,'2025-08-30 07:01:52'),
(687,617,252,'2025-08-30 07:01:54'),
(688,618,255,'2025-08-30 07:02:53'),
(689,619,270,'2025-08-30 07:02:57'),
(690,620,257,'2025-08-30 07:03:00'),
(691,621,259,'2025-08-30 07:03:03'),
(692,622,258,'2025-08-30 07:03:07'),
(693,623,256,'2025-08-30 07:03:10'),
(694,624,243,'2025-08-30 07:03:14'),
(695,625,242,'2025-08-30 07:03:17'),
(696,626,275,'2025-08-30 07:03:21'),
(697,627,278,'2025-08-30 07:03:24'),
(698,628,292,'2025-08-30 07:03:28'),
(699,629,254,'2025-08-30 07:03:30'),
(700,630,280,'2025-08-30 07:03:34'),
(701,631,262,'2025-08-30 07:03:37'),
(702,632,261,'2025-08-30 07:03:39'),
(703,633,287,'2025-08-30 07:03:43'),
(704,634,246,'2025-08-30 07:03:46'),
(705,635,272,'2025-08-30 07:03:49'),
(706,636,244,'2025-08-30 07:03:52'),
(707,637,295,'2025-08-30 07:03:55'),
(708,638,289,'2025-08-30 07:03:58'),
(709,639,268,'2025-08-30 07:04:01'),
(710,640,263,'2025-08-30 07:04:04'),
(711,641,264,'2025-08-30 07:04:08'),
(712,642,279,'2025-08-30 07:04:10'),
(713,643,288,'2025-08-30 07:04:24'),
(714,644,291,'2025-08-30 07:04:26'),
(715,645,267,'2025-08-30 07:04:29'),
(716,646,265,'2025-08-30 07:04:31'),
(717,647,266,'2025-08-30 07:04:34'),
(718,648,293,'2025-08-30 07:04:36'),
(719,649,297,'2025-08-30 07:04:40'),
(720,650,296,'2025-08-30 07:04:42'),
(721,651,282,'2025-08-30 07:13:27'),
(722,652,253,'2025-08-30 07:31:51'),
(723,653,248,'2025-08-30 07:48:25'),
(724,654,271,'2025-08-30 07:58:34'),
(725,655,283,'2025-08-30 08:22:41'),
(726,656,282,'2025-08-30 08:24:14'),
(727,657,151,'2025-09-07 19:00:23'),
(728,658,151,'2025-09-07 19:01:20'),
(729,659,49,'2025-09-08 06:20:37'),
(730,660,233,'2025-09-08 06:20:56'),
(731,661,273,'2025-09-08 06:21:10'),
(732,662,119,'2025-09-08 12:48:25'),
(733,663,151,'2025-09-08 15:15:46'),
(734,664,287,'2025-09-09 14:20:51'),
(735,665,287,'2025-09-09 14:32:05'),
(736,666,287,'2025-09-09 14:40:17'),
(737,667,45,'2025-09-09 18:28:51'),
(738,668,221,'2025-09-09 18:30:30'),
(739,669,227,'2025-09-09 18:31:16'),
(740,670,132,'2025-09-16 14:19:09'),
(741,671,132,'2025-09-16 14:19:09'),
(742,672,172,'2025-09-16 14:19:13'),
(743,673,130,'2025-09-17 07:57:25'),
(744,674,89,'2025-09-17 07:57:43'),
(745,675,192,'2025-09-17 07:57:55'),
(746,676,252,'2025-09-17 07:58:08'),
(747,677,281,'2025-09-17 13:38:36'),
(748,678,284,'2025-09-17 13:38:42'),
(749,679,283,'2025-09-17 13:38:45'),
(750,680,282,'2025-09-17 13:38:48'),
(751,681,290,'2025-09-17 13:38:55'),
(752,682,240,'2025-09-17 13:40:09'),
(753,683,245,'2025-09-17 13:40:12'),
(754,684,253,'2025-09-17 13:40:19'),
(755,685,269,'2025-09-17 13:40:25'),
(756,686,285,'2025-09-17 13:40:29'),
(757,687,286,'2025-09-17 13:40:36'),
(758,688,238,'2025-09-17 13:40:43'),
(759,689,247,'2025-09-17 13:40:46'),
(760,690,260,'2025-09-17 13:40:53'),
(761,691,277,'2025-09-17 13:40:57'),
(762,692,294,'2025-09-17 13:41:00'),
(763,693,227,'2025-09-17 13:41:04'),
(764,694,273,'2025-09-17 13:41:07'),
(765,695,248,'2025-09-17 13:41:11'),
(766,696,250,'2025-09-17 13:41:14'),
(767,697,249,'2025-09-17 13:41:17'),
(768,698,241,'2025-09-17 13:41:20'),
(769,699,251,'2025-09-17 13:41:23'),
(770,700,274,'2025-09-17 13:41:26'),
(771,701,271,'2025-09-17 13:41:50'),
(772,702,252,'2025-09-17 13:42:24'),
(773,703,255,'2025-09-17 13:42:26'),
(774,704,270,'2025-09-17 13:42:35'),
(775,705,257,'2025-09-17 13:42:39'),
(776,706,259,'2025-09-17 13:42:43'),
(777,707,258,'2025-09-17 13:42:46'),
(778,708,256,'2025-09-17 13:42:50'),
(779,709,243,'2025-09-17 13:42:54'),
(780,710,242,'2025-09-17 13:42:57'),
(781,711,275,'2025-09-17 13:43:00'),
(782,712,278,'2025-09-17 13:43:05'),
(783,713,292,'2025-09-17 13:43:08'),
(784,714,254,'2025-09-17 13:43:11'),
(785,715,280,'2025-09-17 13:43:16'),
(786,716,262,'2025-09-17 13:43:19'),
(787,717,261,'2025-09-17 13:43:21'),
(788,718,287,'2025-09-17 13:43:25'),
(789,719,246,'2025-09-17 13:43:28'),
(790,720,272,'2025-09-17 13:43:31'),
(791,721,244,'2025-09-17 13:43:33'),
(792,722,295,'2025-09-17 13:43:36'),
(793,723,289,'2025-09-17 13:43:39'),
(794,724,268,'2025-09-17 13:43:42'),
(795,725,263,'2025-09-17 13:43:46'),
(796,726,264,'2025-09-17 13:43:49'),
(797,727,279,'2025-09-17 13:43:58'),
(798,728,288,'2025-09-17 13:44:00'),
(799,729,291,'2025-09-17 13:44:03'),
(800,730,267,'2025-09-17 13:44:08'),
(801,731,265,'2025-09-17 13:44:11'),
(802,732,266,'2025-09-17 13:44:14'),
(803,733,293,'2025-09-17 13:44:18'),
(804,734,279,'2025-09-17 13:48:30'),
(805,735,288,'2025-09-17 13:48:33'),
(806,736,291,'2025-09-17 13:48:36'),
(807,737,267,'2025-09-17 13:48:39'),
(808,738,265,'2025-09-17 13:48:48'),
(809,739,266,'2025-09-17 13:48:52'),
(810,740,297,'2025-09-17 13:48:55'),
(811,741,296,'2025-09-17 13:49:02'),
(812,742,260,'2025-09-18 04:44:26'),
(813,743,227,'2025-09-18 08:08:30'),
(814,744,311,'2025-09-18 10:12:48'),
(815,745,318,'2025-09-18 12:01:27'),
(816,746,301,'2025-09-18 12:40:21'),
(817,747,332,'2025-09-19 13:38:46'),
(818,748,152,'2025-09-22 10:43:38'),
(819,749,298,'2025-09-24 07:01:23'),
(820,750,352,'2025-09-24 07:02:05'),
(821,751,351,'2025-09-24 07:02:11'),
(822,752,345,'2025-09-24 07:02:14'),
(823,753,348,'2025-09-24 07:02:18'),
(824,754,304,'2025-09-24 07:02:21'),
(825,755,303,'2025-09-24 07:02:30'),
(826,756,314,'2025-09-24 07:02:34'),
(827,757,306,'2025-09-24 07:02:43'),
(828,758,349,'2025-09-24 07:02:53'),
(829,759,286,'2025-09-24 07:02:56'),
(830,760,299,'2025-09-24 07:02:59'),
(831,761,328,'2025-09-24 07:03:04'),
(832,762,301,'2025-09-24 07:03:07'),
(833,763,327,'2025-09-24 07:03:15'),
(834,764,300,'2025-09-24 07:03:21'),
(835,765,302,'2025-09-24 07:03:25'),
(836,766,305,'2025-09-24 07:03:30'),
(837,767,308,'2025-09-24 07:04:01'),
(838,768,307,'2025-09-24 07:04:12'),
(839,769,309,'2025-09-24 07:04:16'),
(840,770,311,'2025-09-24 07:04:20'),
(841,771,312,'2025-09-24 07:04:24'),
(842,772,313,'2025-09-24 07:04:33'),
(843,773,343,'2025-09-24 07:04:37'),
(844,774,315,'2025-09-24 07:06:18'),
(845,775,310,'2025-09-24 07:06:24'),
(846,776,319,'2025-09-24 07:06:28'),
(847,777,322,'2025-09-24 07:06:32'),
(848,778,318,'2025-09-24 07:06:42'),
(849,779,316,'2025-09-24 07:06:45'),
(850,780,321,'2025-09-24 07:06:49'),
(851,781,317,'2025-09-24 07:06:52'),
(852,782,320,'2025-09-24 07:07:12'),
(853,783,339,'2025-09-24 07:08:02'),
(854,784,323,'2025-09-24 07:08:09'),
(855,785,341,'2025-09-24 07:08:11'),
(856,786,329,'2025-09-24 07:08:15'),
(857,787,329,'2025-09-24 07:09:48'),
(858,788,338,'2025-09-24 07:09:52'),
(859,789,326,'2025-09-24 07:09:57'),
(860,790,325,'2025-09-24 07:10:00'),
(861,791,324,'2025-09-24 07:10:04'),
(862,792,347,'2025-09-24 07:10:07'),
(863,793,330,'2025-09-24 07:10:10'),
(864,794,336,'2025-09-24 07:10:46'),
(865,795,340,'2025-09-24 07:10:49'),
(866,796,344,'2025-09-24 07:10:52'),
(867,797,354,'2025-09-24 07:10:55'),
(868,798,332,'2025-09-24 07:10:58'),
(869,799,331,'2025-09-24 07:11:01'),
(870,800,342,'2025-09-24 07:11:15'),
(871,801,337,'2025-09-24 07:11:22'),
(872,802,334,'2025-09-24 07:11:25'),
(873,803,335,'2025-09-24 07:11:29'),
(874,804,353,'2025-09-24 07:11:50'),
(875,805,350,'2025-09-24 07:11:53'),
(876,806,333,'2025-09-24 07:11:57'),
(877,807,346,'2025-09-24 07:12:08'),
(878,808,281,'2025-09-24 07:18:49'),
(879,809,284,'2025-09-24 07:18:58'),
(880,810,283,'2025-09-24 07:19:01'),
(881,811,282,'2025-09-24 07:19:04'),
(882,812,290,'2025-09-24 07:19:24'),
(883,813,240,'2025-09-24 07:19:30'),
(884,814,245,'2025-09-24 07:19:35'),
(885,815,253,'2025-09-24 07:19:39'),
(886,816,269,'2025-09-24 07:19:42'),
(887,817,285,'2025-09-24 07:19:57'),
(888,818,238,'2025-09-24 07:20:01'),
(889,819,247,'2025-09-24 07:20:04'),
(890,820,260,'2025-09-24 07:20:06'),
(891,821,277,'2025-09-24 07:20:10'),
(892,822,294,'2025-09-24 07:20:23'),
(893,823,227,'2025-09-24 07:20:28'),
(894,824,273,'2025-09-24 07:20:33'),
(895,825,248,'2025-09-24 07:20:36'),
(896,826,250,'2025-09-24 07:20:45'),
(897,827,249,'2025-09-24 07:20:49'),
(898,828,241,'2025-09-24 07:20:52'),
(899,829,251,'2025-09-24 07:20:55'),
(900,830,274,'2025-09-24 07:21:07'),
(901,831,271,'2025-09-24 07:21:11'),
(902,832,252,'2025-09-24 07:21:14'),
(903,833,255,'2025-09-24 07:21:26'),
(904,834,270,'2025-09-24 07:21:29'),
(905,835,257,'2025-09-24 07:21:32'),
(906,836,259,'2025-09-24 07:21:36'),
(907,837,258,'2025-09-24 07:21:47'),
(908,838,256,'2025-09-24 07:21:50'),
(909,839,243,'2025-09-24 07:21:54'),
(910,840,242,'2025-09-24 07:21:57'),
(911,841,275,'2025-09-24 07:22:19'),
(912,842,278,'2025-09-24 07:22:23'),
(913,843,292,'2025-09-24 07:22:26'),
(914,844,254,'2025-09-24 07:22:29'),
(915,845,280,'2025-09-24 07:22:34'),
(916,846,262,'2025-09-24 07:22:40'),
(917,847,261,'2025-09-24 07:22:44'),
(918,848,261,'2025-09-24 07:22:45'),
(919,849,287,'2025-09-24 07:22:51'),
(920,850,246,'2025-09-24 07:23:09'),
(921,851,272,'2025-09-24 07:23:12'),
(922,852,244,'2025-09-24 07:23:16'),
(923,853,295,'2025-09-24 07:23:18'),
(924,854,289,'2025-09-24 07:23:21'),
(925,855,268,'2025-09-24 07:23:25'),
(926,856,263,'2025-09-24 07:23:29'),
(927,857,264,'2025-09-24 07:23:31'),
(928,858,279,'2025-09-24 07:23:36'),
(929,859,288,'2025-09-24 07:23:50'),
(930,860,291,'2025-09-24 07:23:53'),
(931,861,267,'2025-09-24 07:23:56'),
(932,862,265,'2025-09-24 07:23:59'),
(933,863,266,'2025-09-24 07:24:02'),
(934,864,293,'2025-09-24 07:24:05'),
(935,865,297,'2025-09-24 07:24:08'),
(936,866,296,'2025-09-24 07:24:12'),
(937,867,316,'2025-09-24 07:58:11'),
(938,868,316,'2025-09-24 08:06:55'),
(939,869,316,'2025-09-24 08:36:42'),
(940,870,281,'2025-09-24 09:57:10'),
(941,871,284,'2025-09-24 09:57:14'),
(942,872,283,'2025-09-24 09:57:17'),
(943,873,282,'2025-09-24 09:57:20'),
(944,874,290,'2025-09-24 09:57:24'),
(945,875,240,'2025-09-24 09:57:28'),
(946,876,245,'2025-09-24 09:57:32'),
(947,877,253,'2025-09-24 09:57:36'),
(948,878,269,'2025-09-24 09:57:41'),
(949,879,285,'2025-09-24 09:57:50'),
(950,880,238,'2025-09-24 09:57:54'),
(951,881,247,'2025-09-24 09:57:57'),
(952,882,260,'2025-09-24 09:58:01'),
(953,883,277,'2025-09-24 09:58:04'),
(954,884,294,'2025-09-24 09:58:11'),
(955,885,227,'2025-09-24 09:58:15'),
(956,886,273,'2025-09-24 09:58:19'),
(957,887,248,'2025-09-24 09:58:23'),
(958,888,250,'2025-09-24 09:58:32'),
(959,889,249,'2025-09-24 09:58:36'),
(960,890,241,'2025-09-24 09:58:39'),
(961,891,251,'2025-09-24 09:58:42'),
(962,892,274,'2025-09-24 09:58:45'),
(963,893,271,'2025-09-24 09:58:48'),
(964,894,252,'2025-09-24 09:58:51'),
(965,895,255,'2025-09-24 10:13:42'),
(966,896,270,'2025-09-24 10:13:45'),
(967,897,257,'2025-09-24 10:13:49'),
(968,898,259,'2025-09-24 10:13:52'),
(969,899,258,'2025-09-24 10:14:02'),
(970,900,256,'2025-09-24 10:14:06'),
(971,901,243,'2025-09-24 10:14:08'),
(972,902,242,'2025-09-24 10:14:11'),
(973,903,275,'2025-09-24 10:14:19'),
(974,904,278,'2025-09-24 10:14:29'),
(975,905,292,'2025-09-24 10:14:32'),
(976,906,254,'2025-09-24 10:14:35'),
(977,907,280,'2025-09-24 10:14:39'),
(978,908,262,'2025-09-24 10:14:41'),
(979,909,261,'2025-09-24 10:14:44'),
(980,910,287,'2025-09-24 10:14:47'),
(981,911,246,'2025-09-24 10:14:56'),
(982,912,272,'2025-09-24 10:14:59'),
(983,913,244,'2025-09-24 10:15:03'),
(984,914,295,'2025-09-24 10:15:06'),
(985,915,289,'2025-09-24 10:15:09'),
(986,916,268,'2025-09-24 10:15:12'),
(987,917,263,'2025-09-24 10:15:16'),
(988,918,264,'2025-09-24 10:15:19'),
(989,919,279,'2025-09-24 10:15:22'),
(990,920,288,'2025-09-24 10:15:30'),
(991,921,291,'2025-09-24 10:15:33'),
(992,922,267,'2025-09-24 10:15:37'),
(993,923,265,'2025-09-24 10:15:40'),
(994,924,266,'2025-09-24 10:15:43'),
(995,925,293,'2025-09-24 10:15:46'),
(996,926,297,'2025-09-24 10:15:50'),
(997,927,296,'2025-09-24 10:15:53'),
(998,928,298,'2025-09-24 10:18:11'),
(999,929,352,'2025-09-24 10:18:14'),
(1000,930,351,'2025-09-24 10:18:18'),
(1001,931,345,'2025-09-24 10:18:20'),
(1002,932,348,'2025-09-24 10:18:23'),
(1003,933,304,'2025-09-24 10:18:26'),
(1004,934,303,'2025-09-24 10:18:29'),
(1005,935,314,'2025-09-24 10:18:32'),
(1006,936,306,'2025-09-24 10:18:42'),
(1007,937,349,'2025-09-24 10:18:45'),
(1008,938,286,'2025-09-24 10:18:48'),
(1009,939,299,'2025-09-24 10:18:51'),
(1010,940,328,'2025-09-24 10:18:54'),
(1011,941,301,'2025-09-24 10:18:58'),
(1012,942,327,'2025-09-24 10:19:01'),
(1013,943,300,'2025-09-24 10:19:12'),
(1014,944,302,'2025-09-24 10:19:15'),
(1015,945,305,'2025-09-24 10:19:18'),
(1016,946,308,'2025-09-24 10:19:21'),
(1017,947,307,'2025-09-24 10:19:25'),
(1018,948,309,'2025-09-24 10:19:33'),
(1019,949,311,'2025-09-24 10:19:36'),
(1020,950,312,'2025-09-24 10:19:39'),
(1021,951,313,'2025-09-24 10:19:43'),
(1022,952,343,'2025-09-24 10:19:46'),
(1023,953,315,'2025-09-24 10:19:57'),
(1024,954,310,'2025-09-24 10:20:05'),
(1025,955,319,'2025-09-24 10:20:08'),
(1026,956,322,'2025-09-24 10:20:11'),
(1027,957,318,'2025-09-24 10:20:14'),
(1028,958,316,'2025-09-24 10:20:17'),
(1029,959,321,'2025-09-24 10:20:29'),
(1030,960,317,'2025-09-24 10:20:32'),
(1031,961,320,'2025-09-24 10:20:34'),
(1032,962,339,'2025-09-24 10:20:38'),
(1033,963,323,'2025-09-24 10:20:41'),
(1034,964,341,'2025-09-24 10:20:44'),
(1035,965,329,'2025-09-24 10:20:48'),
(1036,966,338,'2025-09-24 10:20:52'),
(1037,967,326,'2025-09-24 10:20:55'),
(1038,968,325,'2025-09-24 10:21:03'),
(1039,969,324,'2025-09-24 10:21:07'),
(1040,970,347,'2025-09-24 10:21:18'),
(1041,971,330,'2025-09-24 10:21:21'),
(1042,972,336,'2025-09-24 10:21:24'),
(1043,973,340,'2025-09-24 10:21:27'),
(1044,974,344,'2025-09-24 10:21:31'),
(1045,975,354,'2025-09-24 10:21:36'),
(1046,976,332,'2025-09-24 10:21:39'),
(1047,977,331,'2025-09-24 10:21:42'),
(1048,978,346,'2025-09-24 10:21:51'),
(1049,979,333,'2025-09-24 10:21:53'),
(1050,980,350,'2025-09-24 10:21:57'),
(1051,981,353,'2025-09-24 10:22:02'),
(1052,982,335,'2025-09-24 10:22:07'),
(1053,983,334,'2025-09-24 10:22:11'),
(1054,984,337,'2025-09-24 10:22:14'),
(1055,985,342,'2025-09-24 10:22:17'),
(1056,986,298,'2025-09-24 11:07:54'),
(1057,987,352,'2025-09-24 11:07:57'),
(1058,988,351,'2025-09-24 11:08:00'),
(1059,989,345,'2025-09-24 11:08:05'),
(1060,990,348,'2025-09-24 11:08:08'),
(1061,991,304,'2025-09-24 11:08:12'),
(1062,992,303,'2025-09-24 11:08:17'),
(1063,993,314,'2025-09-24 11:08:22'),
(1064,994,306,'2025-09-24 11:08:25'),
(1065,995,349,'2025-09-24 11:08:28'),
(1066,996,286,'2025-09-24 11:08:33'),
(1067,997,299,'2025-09-24 11:08:36'),
(1068,998,328,'2025-09-24 11:08:43'),
(1069,999,301,'2025-09-24 11:08:47'),
(1070,1000,327,'2025-09-24 11:08:52'),
(1071,1001,300,'2025-09-24 11:08:57'),
(1072,1002,302,'2025-09-24 11:09:02'),
(1073,1003,305,'2025-09-24 11:09:07'),
(1074,1004,308,'2025-09-24 11:09:12'),
(1075,1005,307,'2025-09-24 11:09:18'),
(1076,1006,309,'2025-09-24 11:09:22'),
(1077,1007,311,'2025-09-24 11:09:26'),
(1078,1008,312,'2025-09-24 11:09:34'),
(1079,1009,313,'2025-09-24 11:09:38'),
(1080,1010,343,'2025-09-24 11:09:42'),
(1081,1011,315,'2025-09-24 11:10:05'),
(1082,1012,310,'2025-09-24 11:10:09'),
(1083,1013,319,'2025-09-24 11:10:13'),
(1084,1014,322,'2025-09-24 11:10:18'),
(1085,1015,318,'2025-09-24 11:10:23'),
(1086,1016,316,'2025-09-24 11:10:29'),
(1087,1017,321,'2025-09-24 11:10:35'),
(1088,1018,317,'2025-09-24 11:10:41'),
(1089,1019,320,'2025-09-24 11:10:45'),
(1090,1020,339,'2025-09-24 11:10:48'),
(1091,1021,323,'2025-09-24 11:10:52'),
(1092,1022,341,'2025-09-24 11:10:56'),
(1093,1023,329,'2025-09-24 11:10:59'),
(1094,1024,338,'2025-09-24 11:11:03'),
(1095,1025,326,'2025-09-24 11:11:07'),
(1096,1026,325,'2025-09-24 11:11:10'),
(1097,1027,324,'2025-09-24 11:11:14'),
(1098,1028,347,'2025-09-24 11:11:18'),
(1099,1029,330,'2025-09-24 11:11:21'),
(1100,1030,336,'2025-09-24 11:11:26'),
(1101,1031,340,'2025-09-24 11:11:29'),
(1102,1032,344,'2025-09-24 11:11:33'),
(1103,1033,354,'2025-09-24 11:11:36'),
(1104,1034,332,'2025-09-24 11:11:40'),
(1105,1035,331,'2025-09-24 11:11:44'),
(1106,1036,346,'2025-09-24 11:11:54'),
(1107,1037,333,'2025-09-24 11:11:57'),
(1108,1038,350,'2025-09-24 11:12:01'),
(1109,1039,353,'2025-09-24 11:12:05'),
(1110,1040,335,'2025-09-24 11:12:10'),
(1111,1041,334,'2025-09-24 11:12:15'),
(1112,1042,337,'2025-09-24 11:12:19'),
(1113,1043,342,'2025-09-24 11:12:24'),
(1114,1044,281,'2025-09-24 11:15:01'),
(1115,1045,284,'2025-09-24 11:15:06'),
(1116,1046,283,'2025-09-24 11:15:09'),
(1117,1047,282,'2025-09-24 11:15:12'),
(1118,1048,290,'2025-09-24 11:15:17'),
(1119,1049,240,'2025-09-24 11:15:21'),
(1120,1050,245,'2025-09-24 11:15:26'),
(1121,1051,253,'2025-09-24 11:15:30'),
(1122,1052,269,'2025-09-24 11:15:36'),
(1123,1053,285,'2025-09-24 11:15:40'),
(1124,1054,238,'2025-09-24 11:15:44'),
(1125,1055,247,'2025-09-24 11:15:46'),
(1126,1056,260,'2025-09-24 11:15:51'),
(1127,1057,277,'2025-09-24 11:15:56'),
(1128,1058,294,'2025-09-24 11:16:07'),
(1129,1059,227,'2025-09-24 11:16:11'),
(1130,1060,273,'2025-09-24 11:16:14'),
(1131,1061,248,'2025-09-24 11:16:19'),
(1132,1062,250,'2025-09-24 11:16:23'),
(1133,1063,249,'2025-09-24 11:16:30'),
(1134,1064,241,'2025-09-24 11:16:35'),
(1135,1065,251,'2025-09-24 11:16:39'),
(1136,1066,274,'2025-09-24 11:16:43'),
(1137,1067,271,'2025-09-24 11:16:47'),
(1138,1068,252,'2025-09-24 11:16:52'),
(1139,1069,255,'2025-09-24 11:17:16'),
(1140,1070,270,'2025-09-24 11:17:22'),
(1141,1071,257,'2025-09-24 11:17:27'),
(1142,1072,259,'2025-09-24 11:17:30'),
(1143,1073,258,'2025-09-24 11:17:34'),
(1144,1074,256,'2025-09-24 11:17:38'),
(1145,1075,243,'2025-09-24 11:17:41'),
(1146,1076,242,'2025-09-24 11:17:50'),
(1147,1077,278,'2025-09-24 11:18:04'),
(1148,1078,292,'2025-09-24 11:18:10'),
(1149,1079,254,'2025-09-24 11:18:14'),
(1150,1080,280,'2025-09-24 11:18:21'),
(1151,1081,262,'2025-09-24 11:18:24'),
(1152,1082,261,'2025-09-24 11:18:28'),
(1153,1083,287,'2025-09-24 11:18:32'),
(1154,1084,246,'2025-09-24 11:18:36'),
(1155,1085,272,'2025-09-24 11:18:40'),
(1156,1086,244,'2025-09-24 11:18:43'),
(1157,1087,295,'2025-09-24 11:18:48'),
(1158,1088,289,'2025-09-24 11:18:53'),
(1159,1089,268,'2025-09-24 11:18:57'),
(1160,1090,263,'2025-09-24 11:19:00'),
(1161,1091,264,'2025-09-24 11:19:05'),
(1162,1092,279,'2025-09-24 11:19:12'),
(1163,1093,288,'2025-09-24 11:19:27'),
(1164,1094,291,'2025-09-24 11:19:30'),
(1165,1095,267,'2025-09-24 11:19:33'),
(1166,1096,265,'2025-09-24 11:19:36'),
(1167,1097,266,'2025-09-24 11:19:44'),
(1168,1098,293,'2025-09-24 11:19:47'),
(1169,1099,297,'2025-09-24 11:19:50'),
(1170,1100,296,'2025-09-24 11:19:55'),
(1171,1101,146,'2025-09-24 11:21:05'),
(1172,1102,235,'2025-09-24 11:21:09'),
(1173,1103,234,'2025-09-24 11:21:12'),
(1174,1104,231,'2025-09-24 11:21:15'),
(1175,1105,232,'2025-09-24 11:21:18'),
(1176,1106,179,'2025-09-24 11:21:22'),
(1177,1107,187,'2025-09-24 11:21:25'),
(1178,1108,178,'2025-09-24 11:21:30'),
(1179,1109,214,'2025-09-24 11:21:34'),
(1180,1110,237,'2025-09-24 11:21:38'),
(1181,1111,188,'2025-09-24 11:21:42'),
(1182,1112,207,'2025-09-24 11:21:47'),
(1183,1113,222,'2025-09-24 11:21:51'),
(1184,1114,221,'2025-09-24 11:21:56'),
(1185,1115,233,'2025-09-24 11:21:59'),
(1186,1116,189,'2025-09-24 11:22:03'),
(1187,1117,190,'2025-09-24 11:22:07'),
(1188,1118,181,'2025-09-24 11:22:14'),
(1189,1119,191,'2025-09-24 11:22:18'),
(1190,1120,236,'2025-09-24 11:22:24'),
(1191,1121,216,'2025-09-24 11:22:29'),
(1192,1122,192,'2025-09-24 11:22:33'),
(1193,1123,182,'2025-09-24 11:22:37'),
(1194,1124,218,'2025-09-24 11:22:41'),
(1195,1125,172,'2025-09-24 11:22:45'),
(1196,1126,215,'2025-09-24 11:23:18'),
(1197,1127,174,'2025-09-24 11:23:21'),
(1198,1128,183,'2025-09-24 11:23:24'),
(1199,1129,176,'2025-09-24 11:23:27'),
(1200,1130,175,'2025-09-24 11:23:30'),
(1201,1131,173,'2025-09-24 11:23:35'),
(1202,1132,184,'2025-09-24 11:23:44'),
(1203,1133,180,'2025-09-24 11:23:49'),
(1204,1134,177,'2025-09-24 11:23:53'),
(1205,1135,204,'2025-09-24 11:23:56'),
(1206,1136,217,'2025-09-24 11:24:01'),
(1207,1137,219,'2025-09-24 11:24:05'),
(1208,1138,220,'2025-09-24 11:24:13'),
(1209,1139,211,'2025-09-24 11:24:16'),
(1210,1140,208,'2025-09-24 11:24:21'),
(1211,1141,209,'2025-09-24 11:24:24'),
(1212,1142,239,'2025-09-24 11:24:28'),
(1213,1143,205,'2025-09-24 11:24:32'),
(1214,1144,193,'2025-09-24 11:24:36'),
(1215,1145,186,'2025-09-24 11:24:39'),
(1216,1146,185,'2025-09-24 11:24:43'),
(1217,1147,213,'2025-09-24 11:24:47'),
(1218,1148,210,'2025-09-24 11:24:50'),
(1219,1149,212,'2025-09-24 11:24:54'),
(1220,1150,206,'2025-09-24 11:24:57'),
(1221,1151,40,'2025-09-24 11:26:15'),
(1222,1152,51,'2025-09-24 11:26:19'),
(1223,1153,88,'2025-09-24 11:26:22'),
(1224,1154,41,'2025-09-24 11:26:25'),
(1225,1155,42,'2025-09-24 11:26:30'),
(1226,1156,52,'2025-09-24 11:26:33'),
(1227,1157,53,'2025-09-24 11:26:37'),
(1228,1158,50,'2025-09-24 11:26:57'),
(1229,1159,82,'2025-09-24 11:27:03'),
(1230,1160,54,'2025-09-24 11:27:06'),
(1231,1161,85,'2025-09-24 11:27:15'),
(1232,1162,86,'2025-09-24 11:27:20'),
(1233,1163,44,'2025-09-24 11:27:25'),
(1234,1164,43,'2025-09-24 11:27:34'),
(1235,1165,46,'2025-09-24 11:27:39'),
(1236,1166,47,'2025-09-24 11:27:42'),
(1237,1167,45,'2025-09-24 11:27:47'),
(1238,1168,49,'2025-09-24 11:27:51'),
(1239,1169,48,'2025-09-24 11:27:57'),
(1240,1170,55,'2025-09-24 11:28:00'),
(1241,1171,56,'2025-09-24 11:28:05'),
(1242,1172,57,'2025-09-24 11:28:09'),
(1243,1173,60,'2025-09-24 11:28:14'),
(1244,1174,84,'2025-09-24 11:28:17'),
(1245,1175,61,'2025-09-24 11:28:21'),
(1246,1176,83,'2025-09-24 11:28:40'),
(1247,1177,89,'2025-09-24 11:28:43'),
(1248,1178,58,'2025-09-24 11:28:46'),
(1249,1179,62,'2025-09-24 11:28:49'),
(1250,1180,80,'2025-09-24 11:28:54'),
(1251,1181,59,'2025-09-24 11:28:59'),
(1252,1182,90,'2025-09-24 11:29:02'),
(1253,1183,91,'2025-09-24 11:29:07'),
(1254,1184,65,'2025-09-24 11:29:11'),
(1255,1185,66,'2025-09-24 11:29:15'),
(1256,1186,69,'2025-09-24 11:29:20'),
(1257,1187,68,'2025-09-24 11:29:23'),
(1258,1188,63,'2025-09-24 11:29:27'),
(1259,1189,67,'2025-09-24 11:29:31'),
(1260,1190,87,'2025-09-24 11:29:43'),
(1261,1191,64,'2025-09-24 11:29:47'),
(1262,1192,70,'2025-09-24 11:29:51'),
(1263,1193,71,'2025-09-24 11:29:55'),
(1264,1194,81,'2025-09-24 11:30:00'),
(1265,1195,74,'2025-09-24 11:30:03'),
(1266,1196,95,'2025-09-24 11:30:08'),
(1267,1197,94,'2025-09-24 11:30:13'),
(1268,1198,96,'2025-09-24 11:30:17'),
(1269,1199,75,'2025-09-24 11:30:22'),
(1270,1200,78,'2025-09-24 11:30:25'),
(1271,1201,79,'2025-09-24 11:30:34'),
(1272,1202,77,'2025-09-24 11:30:38'),
(1273,1203,93,'2025-09-24 11:30:43'),
(1274,1204,76,'2025-09-24 11:30:46'),
(1275,1205,92,'2025-09-24 11:30:49'),
(1276,1206,73,'2025-09-24 11:30:53'),
(1277,1207,72,'2025-09-24 11:30:56'),
(1278,1208,97,'2025-09-24 11:30:59'),
(1279,1209,98,'2025-09-24 11:36:13'),
(1280,1210,117,'2025-09-24 11:36:17'),
(1281,1211,116,'2025-09-24 11:36:22'),
(1282,1212,99,'2025-09-24 11:36:30'),
(1283,1213,105,'2025-09-24 11:36:35'),
(1284,1214,108,'2025-09-24 11:36:40'),
(1285,1215,107,'2025-09-24 11:36:44'),
(1286,1216,119,'2025-09-24 11:36:47'),
(1287,1217,126,'2025-09-24 11:36:51'),
(1288,1218,110,'2025-09-24 11:36:54'),
(1289,1219,125,'2025-09-24 11:37:01'),
(1290,1220,129,'2025-09-24 11:37:06'),
(1291,1221,101,'2025-09-24 11:37:19'),
(1292,1222,100,'2025-09-24 11:37:23'),
(1293,1223,103,'2025-09-24 11:37:27'),
(1294,1224,104,'2025-09-24 11:37:30'),
(1295,1225,102,'2025-09-24 11:37:35'),
(1296,1226,106,'2025-09-24 11:37:38'),
(1297,1227,109,'2025-09-24 11:37:42'),
(1298,1228,112,'2025-09-24 11:37:47'),
(1299,1229,111,'2025-09-24 11:37:51'),
(1300,1230,113,'2025-09-24 11:37:54'),
(1301,1231,123,'2025-09-24 11:37:59'),
(1302,1232,120,'2025-09-24 11:38:04'),
(1303,1233,121,'2025-09-24 11:38:09'),
(1304,1234,124,'2025-09-24 11:38:22'),
(1305,1235,130,'2025-09-24 11:38:26'),
(1306,1236,114,'2025-09-24 11:38:29'),
(1307,1237,118,'2025-09-24 11:38:32'),
(1308,1238,122,'2025-09-24 11:38:35'),
(1309,1239,132,'2025-09-24 11:38:38'),
(1310,1240,131,'2025-09-24 11:38:41'),
(1311,1241,115,'2025-09-24 11:38:45'),
(1312,1242,127,'2025-09-24 11:38:49'),
(1313,1243,128,'2025-09-24 11:38:55'),
(1314,1244,133,'2025-09-24 11:38:59'),
(1315,1245,134,'2025-09-24 11:40:18'),
(1316,1246,152,'2025-09-24 11:40:21'),
(1317,1247,151,'2025-09-24 11:40:24'),
(1318,1248,135,'2025-09-24 11:40:28'),
(1319,1249,141,'2025-09-24 11:40:31'),
(1320,1250,144,'2025-09-24 11:40:35'),
(1321,1251,143,'2025-09-24 11:40:39'),
(1322,1252,153,'2025-09-24 11:40:43'),
(1323,1253,160,'2025-09-24 11:40:47'),
(1324,1254,147,'2025-09-24 11:40:50'),
(1325,1255,158,'2025-09-24 11:40:55'),
(1326,1256,159,'2025-09-24 11:40:58'),
(1327,1257,137,'2025-09-24 11:41:03'),
(1328,1258,136,'2025-09-24 11:41:09'),
(1329,1259,139,'2025-09-24 11:41:13'),
(1330,1260,140,'2025-09-24 11:41:18'),
(1331,1261,138,'2025-09-24 11:41:22'),
(1332,1262,142,'2025-09-24 11:41:26'),
(1333,1263,145,'2025-09-24 11:41:32'),
(1334,1264,148,'2025-09-24 11:41:36'),
(1335,1265,149,'2025-09-24 11:41:40'),
(1336,1266,156,'2025-09-24 11:41:45'),
(1337,1267,154,'2025-09-24 11:41:50'),
(1338,1268,155,'2025-09-24 11:41:54'),
(1339,1269,157,'2025-09-24 11:41:58'),
(1340,1270,150,'2025-09-24 11:42:07'),
(1341,1271,161,'2025-09-24 11:43:15'),
(1342,1272,171,'2025-09-24 11:43:19'),
(1343,1273,169,'2025-09-24 11:43:22'),
(1344,1274,167,'2025-09-24 11:43:25'),
(1345,1275,194,'2025-09-24 11:43:28'),
(1346,1276,195,'2025-09-24 11:43:31'),
(1347,1277,170,'2025-09-24 11:43:34'),
(1348,1278,197,'2025-09-24 11:43:38'),
(1349,1279,163,'2025-09-24 11:43:43'),
(1350,1280,162,'2025-09-24 11:43:47'),
(1351,1281,165,'2025-09-24 11:43:52'),
(1352,1282,166,'2025-09-24 11:43:57'),
(1353,1283,164,'2025-09-24 11:44:01'),
(1354,1284,168,'2025-09-24 11:44:05'),
(1355,1285,196,'2025-09-24 11:44:09'),
(1356,1286,198,'2025-09-24 11:44:13'),
(1357,1287,199,'2025-09-24 11:44:17'),
(1358,1288,201,'2025-09-24 11:44:23'),
(1359,1289,202,'2025-09-24 11:44:27'),
(1360,1290,203,'2025-09-24 11:44:31'),
(1361,1291,200,'2025-09-24 11:44:35'),
(1362,1292,229,'2025-09-24 11:47:01'),
(1363,1293,230,'2025-09-24 11:47:05'),
(1364,1294,224,'2025-09-24 11:47:08'),
(1365,1295,223,'2025-09-24 11:47:11'),
(1366,1296,225,'2025-09-24 11:47:14'),
(1367,1297,226,'2025-09-24 11:47:23'),
(1368,1298,276,'2025-09-24 11:47:27'),
(1369,1299,228,'2025-09-24 11:47:32'),
(1370,1300,190,'2025-09-25 09:02:45'),
(1371,1301,307,'2025-09-25 09:04:09'),
(1372,1302,249,'2025-09-25 09:05:53'),
(1373,1303,95,'2025-09-26 07:55:07'),
(1374,1304,50,'2025-09-26 10:03:27'),
(1375,1305,348,'2025-09-29 19:40:53'),
(1376,1306,319,'2025-10-02 14:44:17'),
(1377,1307,319,'2025-10-02 14:44:22'),
(1378,1308,63,'2025-10-02 14:54:18'),
(1379,1309,173,'2025-10-02 14:54:24'),
(1380,1310,256,'2025-10-02 14:54:27'),
(1381,1311,316,'2025-10-02 14:54:31'),
(1382,1312,316,'2025-10-02 14:54:36'),
(1383,1313,145,'2025-10-04 10:53:11'),
(1384,1314,142,'2025-10-04 10:54:06'),
(1385,1315,147,'2025-10-04 11:17:19'),
(1386,1316,160,'2025-10-08 12:36:49'),
(1387,1317,322,'2025-10-16 08:00:24'),
(1388,1318,322,'2025-10-16 08:09:39'),
(1389,1319,259,'2025-10-16 08:15:50'),
(1390,1320,328,'2025-10-17 09:09:47'),
(1391,1321,323,'2025-10-17 10:32:21'),
(1392,1322,231,'2025-10-17 13:23:02'),
(1393,1323,282,'2025-10-17 13:23:18'),
(1394,1324,345,'2025-10-17 13:23:38'),
(1395,1325,351,'2025-10-20 08:08:23'),
(1396,1326,283,'2025-10-20 08:11:29'),
(1397,1327,283,'2025-10-20 08:25:50'),
(1398,1328,283,'2025-10-20 08:28:05'),
(1399,1329,229,'2025-10-20 08:36:25'),
(1400,1330,283,'2025-10-20 08:36:44'),
(1401,1331,351,'2025-10-20 08:38:58'),
(1402,1332,374,'2025-10-20 08:39:53'),
(1403,1333,351,'2025-10-20 11:10:48'),
(1404,1334,351,'2025-10-20 11:13:29'),
(1405,1335,283,'2025-10-20 11:13:47'),
(1406,1336,355,'2025-10-21 08:32:43'),
(1407,1337,369,'2025-10-21 08:32:47'),
(1408,1338,374,'2025-10-21 08:32:50'),
(1409,1339,356,'2025-10-21 08:32:53'),
(1410,1340,361,'2025-10-21 08:32:58'),
(1411,1341,364,'2025-10-21 08:33:01'),
(1412,1342,363,'2025-10-21 08:33:04'),
(1413,1343,375,'2025-10-21 08:33:10'),
(1414,1344,366,'2025-10-21 08:33:15'),
(1415,1345,373,'2025-10-21 08:33:21'),
(1416,1346,376,'2025-10-21 08:33:25'),
(1417,1347,358,'2025-10-21 08:33:31'),
(1418,1348,357,'2025-10-21 08:33:34'),
(1419,1349,360,'2025-10-21 08:33:40'),
(1420,1350,359,'2025-10-21 08:33:46'),
(1421,1351,362,'2025-10-21 08:33:50'),
(1422,1352,365,'2025-10-21 08:33:54'),
(1423,1353,377,'2025-10-21 08:34:00'),
(1424,1354,370,'2025-10-21 08:34:11'),
(1425,1355,371,'2025-10-21 08:34:16'),
(1426,1356,372,'2025-10-21 08:34:20'),
(1427,1357,378,'2025-10-21 08:34:24'),
(1428,1358,368,'2025-10-21 08:34:35'),
(1429,1359,384,'2025-10-21 08:34:53'),
(1430,1360,367,'2025-10-21 08:36:29'),
(1431,1361,386,'2025-10-21 08:36:57'),
(1432,1362,385,'2025-10-21 08:37:01'),
(1433,1363,380,'2025-10-21 08:37:04'),
(1434,1364,383,'2025-10-21 08:37:09'),
(1435,1365,382,'2025-10-21 08:37:12'),
(1436,1366,381,'2025-10-21 08:37:17'),
(1437,1367,379,'2025-10-21 08:37:21'),
(1438,1368,390,'2025-10-21 08:37:24'),
(1439,1369,393,'2025-10-21 08:37:28'),
(1440,1370,391,'2025-10-21 08:37:35'),
(1441,1371,387,'2025-10-21 08:37:40'),
(1442,1372,399,'2025-10-21 08:37:44'),
(1443,1373,397,'2025-10-21 08:37:47'),
(1444,1374,395,'2025-10-21 08:37:50'),
(1445,1375,400,'2025-10-21 08:38:02'),
(1446,1376,394,'2025-10-21 08:38:09'),
(1447,1377,392,'2025-10-21 08:38:12'),
(1448,1378,389,'2025-10-21 08:38:15'),
(1449,1379,401,'2025-10-21 08:38:22'),
(1450,1380,396,'2025-10-21 08:38:25'),
(1451,1381,403,'2025-10-21 08:38:28'),
(1452,1382,407,'2025-10-21 08:38:31'),
(1453,1383,406,'2025-10-21 08:38:37'),
(1454,1384,398,'2025-10-21 08:38:41'),
(1455,1385,388,'2025-10-21 08:38:44'),
(1456,1386,408,'2025-10-21 08:38:59'),
(1457,1387,402,'2025-10-21 08:39:02'),
(1458,1388,405,'2025-10-21 08:39:06'),
(1459,1389,404,'2025-10-21 08:39:09'),
(1460,1390,409,'2025-10-21 08:39:12'),
(1461,1391,410,'2025-10-21 08:39:16'),
(1462,1392,411,'2025-10-21 08:39:19'),
(1463,1393,412,'2025-10-21 08:39:24'),
(1464,1394,229,'2025-10-27 13:36:21'),
(1465,1395,414,'2026-05-21 08:03:10');
/*!40000 ALTER TABLE `salary_slips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription`
--

DROP TABLE IF EXISTS `subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription`
--

LOCK TABLES `subscription` WRITE;
/*!40000 ALTER TABLE `subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_logs`
--

DROP TABLE IF EXISTS `sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sync_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(50) DEFAULT NULL,
  `sync_time` datetime DEFAULT NULL,
  `records_count` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `status` enum('success','error') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_logs`
--

LOCK TABLES `sync_logs` WRITE;
/*!40000 ALTER TABLE `sync_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
INSERT INTO `system_logs` VALUES
(1,'System Initialize','System logs table created','superadmin@communikmarketing.com','super_admin','2025-06-09 11:06:17',NULL),
(2,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-06-09 11:52:14','::1'),
(3,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-06-09 14:28:08','::1'),
(4,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-06-09 14:54:39','::1'),
(5,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-06-09 14:54:44','::1'),
(6,'Logout','User logged out','hr@communikmarketing.com','HR','2025-06-09 18:46:33','::1'),
(7,'Logout','User logged out','hr@communikmarketing.com','HR','2025-06-10 19:14:59','::1'),
(8,'Logout','User logged out','hr@communikmarketing.com','HR','2025-06-11 17:52:04','::1'),
(9,'Logout','User logged out','communikadmin@communikmarketing.com','admin','2025-06-15 19:35:22','::1'),
(10,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-06-25 10:42:08','157.51.232.62'),
(11,'Logout','User logged out','communikadmin@communikmarketing.com','admin','2025-08-22 08:35:38','91.74.47.116'),
(12,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-10-22 15:30:44','122.177.243.69'),
(13,'Logout','Super admin logged out','superadmin@communikmarketing.com','super_admin','2025-10-22 15:34:06','122.177.243.69');
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) NOT NULL DEFAULT 'Employeeshub',
  `site_email` varchar(100) NOT NULL DEFAULT 'admin@employeeshub.com',
  `site_contact` varchar(20) NOT NULL DEFAULT '+1234567890',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES
(1,'Communik - HR Matrix','admin@hrmatrix.san-solutions.in','+917020078847','2025-06-09 14:45:41','2025-06-09 14:46:40');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `token1`
--

DROP TABLE IF EXISTS `token1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `token1` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) DEFAULT NULL,
  `s_time` datetime DEFAULT NULL,
  `token` varchar(1000) DEFAULT NULL,
  `otp` int(6) DEFAULT NULL,
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `token1`
--

LOCK TABLES `token1` WRITE;
/*!40000 ALTER TABLE `token1` DISABLE KEYS */;
/*!40000 ALTER TABLE `token1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tours`
--

DROP TABLE IF EXISTS `tours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `Status` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tours`
--

LOCK TABLES `tours` WRITE;
/*!40000 ALTER TABLE `tours` DISABLE KEYS */;
/*!40000 ALTER TABLE `tours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainees`
--

DROP TABLE IF EXISTS `trainees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainee_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `education_start` date DEFAULT NULL,
  `education_end` date DEFAULT NULL,
  `institute` varchar(150) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `reporting_manager` int(11) DEFAULT NULL,
  `training_duration` varchar(50) DEFAULT NULL,
  `visa_number` varchar(50) DEFAULT NULL,
  `visa_type` varchar(50) DEFAULT NULL,
  `visa_issue_date` date DEFAULT NULL,
  `visa_expiry_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainee_id` (`trainee_id`),
  KEY `department_id` (`department_id`),
  KEY `reporting_manager` (`reporting_manager`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainees`
--

LOCK TABLES `trainees` WRITE;
/*!40000 ALTER TABLE `trainees` DISABLE KEYS */;
INSERT INTO `trainees` VALUES
(14,'trainee_id','first_name','last_name','full_name','email','0000-00-00','gender','marital_status','blood','','address','country','degree','0000-00-00','0000-00-00','institute','0000-00-00','location',0,'designation',0,'training_duration','visa_number','visa_type','0000-00-00','0000-00-00','status','0000-00-00 00:00:00'),
(15,'TRN001','Suheb',' Saifi','Suheb  Saifi','Shuaibsaifimail@gmail.com','2036-03-00','Male','Single','A+','561196258','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'0000-00-00','',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(16,'TRN002','Fida Hussain',' Shah','Fida Hussain  Shah','syedfida7908@gmail.com','0000-00-00','Male','Single','A+','588745006','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'Punjab University Lahore','0000-00-00','',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(17,'TRN003','Danush ','Mani','Danush  Mani','dhanushthara07@gmail.com','0000-00-00','Male','Single','A+','506871784','dubai','United Arab Emirates','B.Tech/B.E. ',NULL,NULL,'Sri Krishna College of Engineering and Technology, Coimbatore','0000-00-00','',2,'Relationship officer',0,'6','3012025114/0080060','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(18,'TRN004','Mohammad ','Sithik ','Mohammad  Sithik ','sithik2912@gail.com','0000-00-00','Male','Single','A+','506043447','Dubai','United Arab Emirates','Diploma in Mechinaical Engireeing ',NULL,NULL,NULL,'0000-00-00','',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(19,'TRN005',' Suhail','Attar',' Suhail Attar','suhailattar7799@gmail.com','0000-00-00','Male','Single','A+','507400143','Dubai','United Arab Emirates','Bachelor Degree',NULL,NULL,'Sir Krishna Devaraya Universities','0000-00-00','',2,'Relationship officer',0,'6','3012025114/0046570','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(20,'TRN006','Parfool ','Soni ','Parfool  Soni ','pksoni707070@gmail.com','2037-08-07','Male','Single','A+','558414475','Dubai','United Arab Emirates','Business in commerce','0000-00-00','0000-00-00','University of sindh','0000-00-00','',2,'Relationship officer',0,'6','2.01202E+13',NULL,'2045-10-02','0000-00-00','active','0000-00-00 00:00:00'),
(21,'TRN007','MINA SAMIR ','NESSIM GIRGES ATTALAH','MINA SAMIR  NESSIM GIRGES ATTALAH','mina.nessiem15@gmail.com','2034-07-04','Male','Single','A+','586634399','Dubai','United Arab Emirates','Bachelor of Commerce','0000-00-00',NULL,'Alexandria','0000-00-00','',2,'Relationship officer',0,'6','3.01203E+16','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(22,'TRN008','LAKSHMIPATHI ','GORREPATI ','LAKSHMIPATHI  GORREPATI ','lakshmipathi98978@gmail.com','0000-00-00','Male','Single','A+','503275341','Dubai','United Arab Emirates','Bachelor of Commerce','0000-00-00','0000-00-00','Bangalore university Bengaluru','0000-00-00','',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(23,'TRN009','Dipendra ','Kumar SAH','Dipendra  Kumar SAH','dksonu75@gmail.com','0000-00-00','Male','Single','A+','528531106','Dubai','United Arab Emirates','MBA (Finance and Marketing) ',NULL,NULL,'GURU GOVIND SINGH INDRAPRASTHA  UNIVERSITY','0000-00-00','',2,'Relationship officer',0,'6','3.01203E+11','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(24,'TRN010','Wilkinson ','Fernandes','Wilkinson  Fernandes','wilkinston.fernandes@gmail.com','0000-00-00','Male','Single','A+','503582201','Dubai','United Arab Emirates','Intermediate',NULL,NULL,NULL,'0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(25,'TRN011','Huyam',' Abbas','Huyam  Abbas','heam23@live.com','0000-00-00','Female','Single','','561073188','Dubai','United Arab Emirates',' B.Sc. In Computer Science',NULL,NULL,'Science Faculty Of: Computer Science and Information Technolog','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Spouse Visa','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(26,'TRN012','Muhammad imran',' Naeem','Muhammad imran  Naeem','Chimrannaseem2@gmail.com','2027-11-03','Male','Married','','501092160','Dubai','United Arab Emirates','BSC Physics',NULL,NULL,'THE ISLAMIA UNVERSITY BAHAWALPUR','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(27,'TRN013','Azmat',' Pasha ','Azmat  Pasha ','azmath707@gmail.com','0000-00-00','Male','Married','','585708687','Dubai','United Arab Emirates','Bachelor of Commerce',NULL,NULL,'osmania University','0000-00-00','',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(28,'TRN014','Afraz ','Shaikh','Afraz  Shaikh','AFRAZSHAIKH2122@GMAIL.COM','0000-00-00','Male','Married','','566783347','Dubai','United Arab Emirates','Intermediate',NULL,NULL,'','0000-00-00','',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(29,'TRN015','Roy ','Jayanta','Roy  Jayanta','jayroy1005@gmail.com','0000-00-00','Male','Single','','581799042','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(30,'TRN016','KEN MAR ','MOISES SECATIN','KEN MAR  MOISES SECATIN','kensecatin@gmail.com','0000-00-00','Female','Single','','562679852','Dubai','United Arab Emirates','Bachelor of Science in Hotel and  Restaurant Management ',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+13','Emp Visa Change Status','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(31,'TRN017','Joseph ','Lalnunthanga','Joseph  Lalnunthanga','Josephlalnunthanga042@gmail.com','0000-00-00','Male','Single','A+','503822396','Dubai','United Arab Emirates',NULL,NULL,NULL,NULL,'0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(32,'TRN018',' MOHAMED ','IRSHAT',' MOHAMED  IRSHAT','Dildilsha44@Gmail.com','0000-00-00','Male','','','502484063','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(33,'TRN019','Balda ','Narsimhulu','Balda  Narsimhulu','balda.narsimhulu2011@gmail.com','0000-00-00','Male','Married','','509614565','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'Osmania University','0000-00-00','',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(34,'TRN020','SOBIT',' PAUL','SOBIT  PAUL','sobitpaul7624@gmail.com','2034-07-00','Male','Single','A+','568142510','Dubai','United Arab Emirates','Master in Bussiiness Adminstration',NULL,NULL,'T. John Institute of  Management & Science,  Bengaluru, India ','0000-00-00','Dubai',2,'Relationship officer',0,'6','3012025114/0071751','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(35,'TRN021','MARY CHRISTINA ',' CHRISTY','MARY CHRISTINA   CHRISTY','cmary.christina@gmail.com','0000-00-00','Male','','','561659737','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(36,'TRN022','Amina ','Mohammad ','Amina  Mohammad ','aminamuhammad089@gmail.com','0000-00-00','Female','','','569868095','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'Hazara University Mansehra.','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(37,'TRN023','Nouman ','Shahid','Nouman  Shahid','noumanshahid69@gmail.com','0000-00-00','Male','','','569252049','Dubai','United Arab Emirates','Master in Law',NULL,NULL,'University of Punjab','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(38,'TRN024','Mohammad ','Azeem','Mohammad  Azeem','azeemalone83@gmail.com','0000-00-00','Male','Single','A+','552217120','Dubai','United Arab Emirates','Bachelor of Arts',NULL,NULL,'ALIGARH MUSLIM UNIVERSITY','0000-00-00','',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(39,'TRN025','Nimra',' Nasir ','Nimra  Nasir ','nimranovel@gmail.com','0000-00-00','Female','Single','A+','554929149','Dubai','United Arab Emirates','BBA Hons.(Finance): ',NULL,NULL,'University of Punjab','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(40,'TRN026','Navoda ','Piyadarshini','Navoda  Piyadarshini','navoda.piya8899@icloud.com','0000-00-00','Female','','','566281870','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','2046-03-08','active','0000-00-00 00:00:00'),
(41,'TRN027','Mohammad ','Nasser','Mohammad  Nasser','m.n4sser@gmail.com','2034-06-08','Male','','','586264058','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','2046-04-05','active','0000-00-00 00:00:00'),
(42,'TRN028','Pragati ','kachhawaha','Pragati  kachhawaha','pragati31102000@gmail.com','0000-00-00','Female','','','586264058','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','3.01203E+16','Tourist','0000-00-00','0000-00-00','inactive','0000-00-00 00:00:00'),
(43,'TRN029','Abrar ','Ahmed ','Abrar  Ahmed ','abrarahmmedcm269@gmail.com','0000-00-00','Male','','','543839706','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.02202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(44,'TRN030','Shaik Abdul ','Raheem ','Shaik Abdul  Raheem ','shaikraheem849@gmail.com','0000-00-00','Male','','','558728161','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(45,'TRN031','Nameera ','Tabassum','Nameera  Tabassum','nameeratabassumhrn@gmail.com','2037-08-01','Male','','','559674061','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','3.01203E+16','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(46,'TRN032','HAROON ALI ','SHAIK','HAROON ALI  SHAIK','haroonaly78q@gmail.com','0000-00-00','Male','','','589884341','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01203E+17','Tourist','0000-00-00','0000-00-00','active','0000-00-00 00:00:00'),
(47,'TRN033','Ibrar ','Ashraf','Ibrar  Ashraf','ibrarashraf05@gmail.com','0000-00-00','Male','','','552620356','Dubai','United Arab Emirates','',NULL,NULL,'','0000-00-00','Dubai',2,'Relationship officer',0,'6','2.01202E+13','Emp Visa Change Status','0000-00-00','0000-00-00','active','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `trainees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_logs`
--

DROP TABLE IF EXISTS `work_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `is_compensated` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_logs`
--

LOCK TABLES `work_logs` WRITE;
/*!40000 ALTER TABLE `work_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-21  8:38:22
