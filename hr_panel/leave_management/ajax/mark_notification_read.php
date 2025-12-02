<?php
include('../../includes/session.php');
include('../includes/NotificationManager.php');

if (isset($_POST['notification_id'])) {
    $notificationManager = new NotificationManager($con);
    $notificationManager->markAsRead($_POST['notification_id']);
    echo json_encode(['success' => true]);
}