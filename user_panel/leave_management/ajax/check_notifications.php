<?php
include('../../includes/session.php');
include('../includes/NotificationManager.php');

$notificationManager = new NotificationManager($con);
$count = $notificationManager->getUnreadCount($_SESSION['eid']);
$notifications = $notificationManager->getUserNotifications($_SESSION['eid']);

echo json_encode([
    'count' => $count,
    'notifications' => $notifications
]);