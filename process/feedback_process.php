<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action === '') {
    header('Location: /emps/login.php');
    exit();
}

if ($action === 'submit_feedback') {
    require_once dirname(__DIR__) . '/user_panel/session.php';

    $department = trim((string) ($_POST['department'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($department === '' || $message === '') {
        $_SESSION['error'] = 'Please select a department and enter your feedback.';
        header('Location: /emps/views/feedback/submit_feedback.php');
        exit();
    }

    $sql = "INSERT INTO anonymous_feedback (department, message, status) VALUES (?, ?, 'pending')";
    $stmt = mysqli_prepare($con, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $department, $message);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Feedback submitted successfully!';
        } else {
            $_SESSION['error'] = 'Error submitting feedback: ' . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Error preparing statement: ' . mysqli_error($con);
    }

    header('Location: /emps/views/feedback/submit_feedback.php');
    exit();
}

if ($action === 'update_status' || $action === 'update_feedback') {
    require_once dirname(__DIR__) . '/admin_panel/session.php';

    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
        header('Location: /emps/login.php');
        exit();
    }

    $feedbackId = (int) ($_POST['feedback_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'pending');
    $adminComment = trim((string) ($_POST['admin_comment'] ?? ''));
    $allowedStatuses = ['pending', 'in_review', 'resolved'];

    if ($feedbackId <= 0 || !in_array($status, $allowedStatuses, true)) {
        $_SESSION['error'] = 'Invalid feedback update request.';
        header('Location: /emps/admin_panel/hod_feedback.php');
        exit();
    }

    $sql = 'UPDATE anonymous_feedback SET status = ?, admin_comment = ?, updated_at = NOW() WHERE feedback_id = ?';
    $stmt = mysqli_prepare($con, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $adminComment, $feedbackId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Feedback updated successfully.';
        } else {
            $_SESSION['error'] = 'Error updating feedback: ' . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Error preparing update: ' . mysqli_error($con);
    }

    $redirect = ($action === 'update_feedback')
        ? '/emps/admin_panel/view_feedback.php'
        : '/emps/admin_panel/hod_feedback.php';

    if (!empty($_GET['status']) && $action === 'update_feedback') {
        $redirect .= '?status=' . urlencode((string) $_GET['status']);
    }

    header('Location: ' . $redirect);
    exit();
}

header('Location: /emps/login.php');
exit();
