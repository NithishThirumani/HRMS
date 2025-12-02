const socket = new WebSocket('ws://your-websocket-server');

socket.onmessage = function(event) {
    const notification = JSON.parse(event.data);
    
    // Update notification badge
    const badge = document.querySelector('#alertsDropdown .badge-counter');
    const currentCount = parseInt(badge.textContent || '0');
    badge.textContent = currentCount + 1;
    
    // Show notification toast
    showNotificationToast(notification);
    
    // Add notification to dropdown
    addNotificationToDropdown(notification);
};

function showNotificationToast(notification) {
    Swal.fire({
        title: 'New Notification',
        text: notification.message,
        icon: 'info',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
}