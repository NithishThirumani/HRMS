<?php
session_start();
require_once('../../connection.php');
include('../header.php');

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$assignment_id = $_GET['id'];
$emp_id = $_SESSION['user_id'];

// Fetch appraisal details with responses
$query = "SELECT a.*, ap.start_date, ap.end_date, 
          af.achievements, af.improvements, af.training_needs, af.career_goals,
          af.manager_comments, af.rating
          FROM appraisal_assignments a 
          JOIN appraisal_periods ap ON a.period_id = ap.period_id
          LEFT JOIN appraisal_forms af ON a.id = af.assignment_id
          WHERE a.id = ? AND a.employee_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("is", $assignment_id, $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$appraisal = $result->fetch_assoc();

if (!$appraisal) {
    header('Location: ../index.php');
    exit;
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Appraisal Details</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Appraisal Period: <?php echo date('d M Y', strtotime($appraisal['start_date'])) . ' - ' . 
                                      date('d M Y', strtotime($appraisal['end_date'])); ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5 class="font-weight-bold">Key Achievements</h5>
                <p><?php echo nl2br(htmlspecialchars($appraisal['achievements'] ?? '')); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="font-weight-bold">Areas for Improvement</h5>
                <p><?php echo nl2br(htmlspecialchars($appraisal['improvements'] ?? '')); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="font-weight-bold">Training Needs</h5>
                <p><?php echo nl2br(htmlspecialchars($appraisal['training_needs'] ?? '')); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="font-weight-bold">Career Goals</h5>
                <p><?php echo nl2br(htmlspecialchars($appraisal['career_goals'] ?? '')); ?></p>
            </div>

            <?php if ($appraisal['manager_comments']): ?>
            <div class="mb-4">
                <h5 class="font-weight-bold">Manager's Comments</h5>
                <p><?php echo nl2br(htmlspecialchars($appraisal['manager_comments'])); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="font-weight-bold">Rating</h5>
                <p><?php echo $appraisal['rating']; ?>/5</p>
            </div>
            <?php endif; ?>

            <a href="../index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include('../footer.php'); ?>