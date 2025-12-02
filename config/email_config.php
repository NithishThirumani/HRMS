<?php
/**
 * Email Configuration for HRMS System
 * Update these settings based on your email provider
 */

require_once dirname(__FILE__) . '/../PHPMailer/Exception.php';
require_once dirname(__FILE__) . '/../PHPMailer/SMTP.php';
require_once dirname(__FILE__) . '/../PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email Provider Settings
$email_config = array(
    // Option 1: Gmail (requires App Password)
    'gmail' => array(
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        'username' => 'hrms@communikmarketing.com',
        'password' => 'Communik@12345', // Replace with App Password
        'from_email' => 'hrms@communikmarketing.com',
        'from_name' => 'HRMS System'
    ),
    
    // Option 2: Gmail with SSL
    'gmail_ssl' => array(
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'secure' => 'ssl',
        'username' => 'hrms@communikmarketing.com',
        'password' => 'Communik@12345', // Replace with App Password
        'from_email' => 'hrms@communikmarketing.com',
        'from_name' => 'HRMS System'
    ),
    
    // Option 3: Outlook
    'outlook' => array(
        'host' => 'smtp-mail.outlook.com',
        'port' => 587,
        'secure' => 'tls',
        'username' => 'your-email@outlook.com',
        'password' => 'your-password',
        'from_email' => 'your-email@outlook.com',
        'from_name' => 'HRMS System'
    ),
    
    // Option 4: Yahoo
    'yahoo' => array(
        'host' => 'smtp.mail.yahoo.com',
        'port' => 587,
        'secure' => 'tls',
        'username' => 'your-email@yahoo.com',
        'password' => 'your-app-password',
        'from_email' => 'your-email@yahoo.com',
        'from_name' => 'HRMS System'
    ),
    
    // Option 5: Your hosting provider's SMTP
    'hosting' => array(
        'host' => 'mail.yourdomain.com', // Replace with your hosting SMTP
        'port' => 587,
        'secure' => 'tls',
        'username' => 'noreply@yourdomain.com',
        'password' => 'your-password',
        'from_email' => 'noreply@yourdomain.com',
        'from_name' => 'HRMS System'
    )
);

// Current active configuration (change this to use different providers)
$active_config = 'gmail'; // Options: gmail, gmail_ssl, outlook, yahoo, hosting

// Get current email settings
function getEmailConfig() {
    global $email_config, $active_config;
    return $email_config[$active_config];
}

// Test email configuration
function testEmailSettings($config_name = null) {
    global $email_config, $active_config;
    
    if ($config_name && isset($email_config[$config_name])) {
        $config = $email_config[$config_name];
    } else {
        $config = $email_config[$active_config];
    }
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        
        if ($config['secure'] == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress('gopal.singh1678@gmail.com'); // Test email
        
        $mail->isHTML(true);
        $mail->Subject = 'HRMS Email Test - ' . $config_name;
        $mail->Body = '<h2>Email Test</h2><p>This is a test from ' . $config_name . ' configuration.</p>';
        
        $mail->send();
        return array('success' => true, 'message' => 'Email sent successfully!');
    } catch (Exception $e) {
        return array('success' => false, 'message' => 'Email failed: ' . $e->getMessage());
    }
}
?> 