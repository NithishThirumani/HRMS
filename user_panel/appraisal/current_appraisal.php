<?php
include('../session.php');

$emp_id = (int) ($_SESSION['user_id'] ?? 0);
if ($emp_id <= 0) {
    header('Location: /emps/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Self Appraisal</title>
    <!-- Fix asset paths to point to parent directory -->
    <link href="../img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <script src="../js/jquery-3.6.4.min.js"></script>
</head>

<body id="page-top">
    <?php include('../sidebar.php'); ?>
    <?php include('../topbar.php'); ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Self Appraisal</h1>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Active Appraisal Period</h6>
            </div>
            <div class="card-body">
                <?php
                // 1. Get the current active period
                $period_query = "SELECT * FROM appraisal_periods WHERE status = 'Active' ORDER BY created_at DESC LIMIT 1";
                $period_result = $con->query($period_query);
                if ($period_result->num_rows > 0) {
                    $period = $period_result->fetch_assoc();
                    $ea_query = "SELECT * FROM employee_appraisals WHERE period_id = ? AND employee_id = ? LIMIT 1";
                    $stmt = $con->prepare($ea_query);
                    $stmt->bind_param('ii', $period['period_id'], $emp_id);
                    $stmt->execute();
                    $appraisal = $stmt->get_result()->fetch_assoc();
                    if (!$appraisal) {
                        $create = $con->prepare(
                            "INSERT INTO employee_appraisals (employee_id, period_id, status) VALUES (?, ?, 'Pending')"
                        );
                        $create->bind_param('ii', $emp_id, $period['period_id']);
                        $create->execute();
                        $appraisal_id = (int) $con->insert_id;
                    } else {
                        $appraisal_id = (int) $appraisal['appraisal_id'];
                    }
                    $criteria_query = "SELECT c.*, r.self_rating, r.comments as self_comments
                                       FROM appraisal_criteria c
                                       LEFT JOIN appraisal_ratings r ON c.criteria_id = r.criteria_id AND r.appraisal_id = ?
                                       WHERE c.is_active = 1";
                    $stmt = $con->prepare($criteria_query);
                    $stmt->bind_param('i', $appraisal_id);
                    $stmt->execute();
                    $criteria_result = $stmt->get_result();
                    if ($criteria_result->num_rows > 0) {
                        ?>
                        <div class="mb-4">
                            <p><strong>Period:</strong> <?php echo htmlspecialchars($period['start_date']); ?> to
                                <?php echo htmlspecialchars($period['end_date']); ?>
                            </p>
                        </div>
                        <form action="save_self_appraisal.php" method="POST">
                            <input type="hidden" name="appraisal_id" value="<?php echo $appraisal_id; ?>">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Criteria</th>
                                            <th>Description</th>
                                            <th>Self Rating</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($criteria = $criteria_result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($criteria['criteria_name']); ?></td>
                                                <td><?php echo htmlspecialchars($criteria['description']); ?></td>
                                                <td>
                                                    <select name="ratings[<?php echo $criteria['criteria_id']; ?>]" class="form-control" required>
                                                        <option value="">Select Rating</option>
                                                        <option value="1" <?php echo ($criteria['self_rating'] == 1) ? 'selected' : ''; ?>>1 - Poor</option>
                                                        <option value="2" <?php echo ($criteria['self_rating'] == 2) ? 'selected' : ''; ?>>2 - Below Average</option>
                                                        <option value="3" <?php echo ($criteria['self_rating'] == 3) ? 'selected' : ''; ?>>3 - Average</option>
                                                        <option value="4" <?php echo ($criteria['self_rating'] == 4) ? 'selected' : ''; ?>>4 - Above Average</option>
                                                        <option value="5" <?php echo ($criteria['self_rating'] == 5) ? 'selected' : ''; ?>>5 - Excellent</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <textarea name="comments[<?php echo $criteria['criteria_id']; ?>]" class="form-control" rows="3" required><?php echo htmlspecialchars($criteria['self_comments'] ?? ''); ?></textarea>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Submit Self Appraisal</button>
                                <button type="button" class="btn btn-secondary" onclick="saveDraft()">Save as Draft</button>
                            </div>
                        </form>
                        <?php
                    } else {
                        echo '<div class="alert alert-info">No active criteria found for the current appraisal period.</div>';
                    }
                } else {
                    echo '<div class="alert alert-info">No active appraisal period found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->

    <?php include('../footer.php'); ?>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <script>
        function saveDraft() {
            // Add the draft status to the form
            const form = document.querySelector('form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'status';
            input.value = 'draft';
            form.appendChild(input);
            form.submit();
        }
    </script>

</body>

</html>