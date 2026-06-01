<?php
include('session.php');

$leader_id = (int) ($user_data['id'] ?? 0);
if ($leader_id <= 0 && !empty($_SESSION['eid'])) {
    $stmt = $con->prepare('SELECT id FROM employees WHERE eid = ? LIMIT 1');
    $stmt->bind_param('s', $_SESSION['eid']);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();
    if ($emp) {
        $leader_id = (int) $emp['id'];
    }
}

if ($leader_id <= 0) {
    header('Location: /emps/login.php');
    exit();
}

$_SESSION['user_id'] = $leader_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Project Status</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Project Status</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Leader Name</th>
                                <th>Due Date</th>
                                <th>Submission Date</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Submit</th>
                                <th>Submission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $con->prepare('SELECT * FROM projects WHERE leader_id = ? ORDER BY due_date DESC');
                            $stmt->bind_param('i', $leader_id);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $projectCount = 0;
                            while ($row = $res->fetch_assoc()):
                                $projectCount++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['leader_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['sub_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['points']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <a href="submit.php?submit=<?php echo (int) $row['p_id']; ?>" class="btn btn-success btn-circle btn-sm">
                                        <i class="fas fa-check"></i>
                                    </a>
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-circle btn-sm" href="download.php?project_id=<?php echo (int) $row['p_id']; ?>">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($projectCount === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No projects assigned to you yet. Ask your admin to assign a project from Assign Project.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
</body>
</html>
