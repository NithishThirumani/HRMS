<?php
session_start();
include('connection.php');


$emp_id = mysqli_real_escape_string($con, $_GET['id']);
$query = "SELECT * FROM employees WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    header("Location: pending_registrations.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Employee Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Employee Registration Details</h2>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>NID:</strong> <?php echo htmlspecialchars($employee['nid']); ?></p>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($employee['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($employee['contact']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Birthday:</strong> <?php echo htmlspecialchars($employee['birthday']); ?></p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($employee['gender']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($employee['address']); ?></p>
                        <p><strong>Degree:</strong> <?php echo htmlspecialchars($employee['degree']); ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <form method="POST" action="pending_registrations.php" style="display: inline;">
                        <input type="hidden" name="emp_id" value="<?php echo $employee['id']; ?>">
                        <button type="submit" name="approve" class="btn btn-success">Approve Registration</button>
                    </form>
                    <a href="pending_registrations.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>