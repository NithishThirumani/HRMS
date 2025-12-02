<?php
require_once '../user_panel/connection.php';
require_once '../user_panel/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    $department = mysqli_real_escape_string($con, $_POST['department']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    
    // Insert into anonymous_feedback table
    $sql = "INSERT INTO anonymous_feedback (department, message) VALUES (?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $department, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Feedback submitted successfully!";
        } else {
            $_SESSION['error'] = "Error submitting feedback: " . mysqli_error($con);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Error preparing statement: " . mysqli_error($con);
    }
    
    header("Location: ../views/feedback/submit_feedback.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request";
    header("Location: ../views/feedback/submit_feedback.php");
    exit();
}
?>