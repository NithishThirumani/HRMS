<?php
require_once('../session.php');
require_once('../../connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: current_appraisal.php');
    exit();
}

$appraisalId = (int) ($_POST['appraisal_id'] ?? 0);
$empId = (int) ($_SESSION['user_id'] ?? 0);
$ratings = $_POST['ratings'] ?? [];
$comments = $_POST['comments'] ?? [];

if ($appraisalId <= 0 || $empId <= 0) {
    $_SESSION['error'] = 'Invalid appraisal submission.';
    header('Location: current_appraisal.php');
    exit();
}

$check = $con->prepare('SELECT appraisal_id FROM employee_appraisals WHERE appraisal_id = ? AND employee_id = ?');
$check->bind_param('ii', $appraisalId, $empId);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $_SESSION['error'] = 'Appraisal record not found.';
    header('Location: current_appraisal.php');
    exit();
}

try {
    $con->begin_transaction();

    foreach ($ratings as $criteriaId => $rating) {
        $criteriaId = (int) $criteriaId;
        $rating = (int) $rating;
        $comment = $comments[$criteriaId] ?? '';

        $exists = $con->prepare('SELECT rating_id FROM appraisal_ratings WHERE appraisal_id = ? AND criteria_id = ? LIMIT 1');
        $exists->bind_param('ii', $appraisalId, $criteriaId);
        $exists->execute();
        if ($exists->get_result()->num_rows > 0) {
            $upd = $con->prepare(
                'UPDATE appraisal_ratings SET self_rating = ?, comments = ? WHERE appraisal_id = ? AND criteria_id = ?'
            );
            $upd->bind_param('isii', $rating, $comment, $appraisalId, $criteriaId);
            $upd->execute();
        } else {
            $ins = $con->prepare(
                'INSERT INTO appraisal_ratings (appraisal_id, criteria_id, self_rating, comments) VALUES (?, ?, ?, ?)'
            );
            $ins->bind_param('iiis', $appraisalId, $criteriaId, $rating, $comment);
            $ins->execute();
        }
    }

    $statusUpd = $con->prepare("UPDATE employee_appraisals SET status = 'Self_Submitted' WHERE appraisal_id = ?");
    $statusUpd->bind_param('i', $appraisalId);
    $statusUpd->execute();

    $con->commit();
    $_SESSION['success'] = 'Self appraisal submitted successfully.';
} catch (Exception $e) {
    $con->rollback();
    $_SESSION['error'] = 'Could not save appraisal: ' . $e->getMessage();
}

header('Location: current_appraisal.php');
exit();
