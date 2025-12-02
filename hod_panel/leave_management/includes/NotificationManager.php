<?php
class NotificationManager {
    private $con;
    
    public function __construct($con) {
        $this->con = $con;
    }
    
    // Send notification to recommender/approver
    public function sendNotification($user_id, $leave_id, $message, $type) {
        $query = "INSERT INTO leave_notifications (user_id, leave_id, message, type) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("siss", $user_id, $leave_id, $message, $type);
        return $stmt->execute();
    }
    
    // Get notifications for a user
    public function getUserNotifications($user_id) {
        $query = "SELECT n.*, l.type_of_leave, l.start_date, l.end_date, 
                        e.full_name as employee_name
                 FROM leave_notifications n
                 JOIN leaves l ON n.leave_id = l.id
                 JOIN employees e ON l.emp_id = e.eid
                 WHERE n.user_id = ?
                 ORDER BY n.created_at DESC
                 LIMIT 10";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get unread notification count
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM leave_notifications 
                 WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
    
    // Mark notification as read
    public function markAsRead($notification_id) {
        $query = "UPDATE leave_notifications SET is_read = TRUE 
                 WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $notification_id);
        return $stmt->execute();
    }

    // Mark all notifications as read for a user
    public function markAllAsRead($user_id) {
        $query = "UPDATE leave_notifications SET is_read = TRUE 
                 WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $user_id);
        return $stmt->execute();
    }

    // Get notifications by type
    public function getNotificationsByType($user_id, $type) {
        $query = "SELECT n.*, l.type_of_leave, l.start_date, l.end_date, 
                        e.full_name as employee_name
                 FROM leave_notifications n
                 JOIN leaves l ON n.leave_id = l.id
                 JOIN employees e ON l.emp_id = e.eid
                 WHERE n.user_id = ? AND n.type = ?
                 ORDER BY n.created_at DESC";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ss", $user_id, $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Example usage in header.php:
$notificationManager = new NotificationManager($con);
$unread_count = $notificationManager->getUnreadCount($_SESSION['eid']);
$notifications = $notificationManager->getUserNotifications($_SESSION['eid']);
?>

<!-- Notification Bell in Header -->
<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <?php if ($unread_count > 0): ?>
            <span class="badge badge-danger badge-counter"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </a>
    <!-- Dropdown - Alerts -->
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
        aria-labelledby="alertsDropdown">
        <h6 class="dropdown-header">
            Leave Notifications
        </h6>
        <?php if (empty($notifications)): ?>
            <a class="dropdown-item text-center small text-gray-500" href="#">No notifications</a>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <a class="dropdown-item d-flex align-items-center" 
                   href="leave_management/view_leave.php?id=<?php echo $notification['leave_id']; ?>">
                    <div class="mr-3">
                        <div class="icon-circle bg-<?php echo getNotificationColor($notification['type']); ?>">
                            <i class="fas <?php echo getNotificationIcon($notification['type']); ?> text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">
                            <?php echo date('F d, Y', strtotime($notification['created_at'])); ?>
                        </div>
                        <span class="<?php echo $notification['is_read'] ? 'text-gray-600' : 'font-weight-bold'; ?>">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
            <a class="dropdown-item text-center small text-gray-500" href="leave_management/all_notifications.php">
                Show All Notifications
            </a>
        <?php endif; ?>
    </div>
</li>

<?php
// Helper functions for notification styling
function getNotificationColor($type) {
    switch ($type) {
        case 'new_leave':
            return 'primary';
        case 'approved':
            return 'success';
        case 'rejected':
            return 'danger';
        case 'recommended':
            return 'info';
        default:
            return 'secondary';
    }
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'new_leave':
            return 'fa-file-alt';
        case 'approved':
            return 'fa-check';
        case 'rejected':
            return 'fa-times';
        case 'recommended':
            return 'fa-thumbs-up';
        default:
            return 'fa-bell';
    }
}
?>

<!-- Add JavaScript for real-time updates -->
<script>
$(document).ready(function() {
    // Mark notification as read when clicked
    $('.dropdown-item').click(function() {
        const notificationId = $(this).data('notification-id');
        $.post('leave_management/ajax/mark_notification_read.php', {
            notification_id: notificationId
        });
    });

    // Mark all as read button
    $('#markAllRead').click(function() {
        $.post('leave_management/ajax/mark_all_read.php', function() {
            $('.badge-counter').hide();
            $('.dropdown-item span').removeClass('font-weight-bold');
        });
    });

    // Check for new notifications every 30 seconds
    setInterval(function() {
        $.get('leave_management/ajax/check_notifications.php', function(data) {
            if (data.count > 0) {
                updateNotificationBadge(data.count);
                updateNotificationList(data.notifications);
            }
        });
    }, 30000);
});
</script>