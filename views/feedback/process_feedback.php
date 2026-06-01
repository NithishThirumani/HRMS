<?php
include('../../connection.php');
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: /emps/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $department = mysqli_real_escape_string($con, $_POST['department']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    
    if (empty($department) || empty($message)) {
        $_SESSION['error'] = "Both department and message are required.";
        header("Location: submit_feedback.php");
        exit();
    }

    // Insert feedback into anonymous_feedback table
    $query = "INSERT INTO anonymous_feedback (department, message, status, created_at) 
              VALUES (?, ?, 'pending', NOW())";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $department, $message);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Your feedback has been submitted successfully. Thank you for your input!";
    } else {
        $_SESSION['error'] = "Error submitting feedback. Please try again.";
    }
    
    $stmt->close();
    header("Location: /emps/views/feedback/submit_feedback.php");
    exit();
} else {
    // If someone tries to access this file directly without POST
    header("Location: /emps/views/feedback/submit_feedback.php");
    exit();
}
