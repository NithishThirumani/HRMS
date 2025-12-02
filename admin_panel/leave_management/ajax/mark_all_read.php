<?php
include('../../includes/session.php');
include('../includes/NotificationManager.php');

$notificationManager = new NotificationManager($con);
$notificationManager->markAllAsRead($_SESSION['eid']);
echo json_encode(['success' => true]);
