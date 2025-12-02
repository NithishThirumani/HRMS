<?php
require_once '../includes/db_connect.php';
session_start();

class FeedbackController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function submitFeedback() {
        $department = $_POST['department'];
        $message = $_POST['message'];
        
        $stmt = $this->conn->prepare("INSERT INTO anonymous_feedback (department, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $department, $message);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Feedback submitted successfully!";
        } else {
            $_SESSION['error'] = "Error submitting feedback.";
        }
        
        header("Location: ../views/feedback/submit_feedback.php");
        exit();
    }

    public function getFeedbacks($department) {
        $stmt = $this->conn->prepare("SELECT * FROM anonymous_feedback WHERE department = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $department);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateFeedbackStatus($feedback_id, $status) {
        $stmt = $this->conn->prepare("UPDATE anonymous_feedback SET status = ? WHERE feedback_id = ?");
        $stmt->bind_param("si", $status, $feedback_id);
        return $stmt->execute();
    }
}

if(isset($_POST['action'])) {
    $controller = new FeedbackController($conn);
    
    switch($_POST['action']) {
        case 'submit_feedback':
            $controller->submitFeedback();
            break;
        case 'update_status':
            $controller->updateFeedbackStatus($_POST['feedback_id'], $_POST['status']);
            break;
    }
}