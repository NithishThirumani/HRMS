CREATE TABLE IF NOT EXISTS `esign_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `signature_type` varchar(20) NOT NULL DEFAULT 'draw',
  `notification_email` tinyint(1) NOT NULL DEFAULT 1,
  `notification_sms` tinyint(1) NOT NULL DEFAULT 0,
  `default_expiry` int(11) NOT NULL DEFAULT 30,
  `auto_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_days` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 