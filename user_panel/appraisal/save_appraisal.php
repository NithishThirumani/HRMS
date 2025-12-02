<?php
session_start();
require_once('../../connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$assignment_id = $_POST['assignment_id'];
$emp_id = $_SESSION['user_id'];

try {
    $con->begin_transaction();

    // Insert appraisal form data
    $query = "INSERT INTO appraisal_forms (assignment_id, achievements, improvements, 
              training_needs, career_goals, submission_date) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($query);
    $stmt->bind_param(
        "issss",
        $assignment_id,
        $_POST['achievements'],
        $_POST['improvements'],
        $_POST['training_needs'],
        $_POST['career_goals']
    );
    $stmt->execute();

    // Update assignment status
    $query = "UPDATE appraisal_assignments SET status = 'Submitted' WHERE id = ? AND employee_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $assignment_id, $emp_id);
    $stmt->execute();

    $con->commit();
    header('Location: ../index.php?success=1');
} catch (Exception $e) {
    $con->rollback();
    header('Location: fill_form.php?id=' . $assignment_id . '&error=1');
}
?>