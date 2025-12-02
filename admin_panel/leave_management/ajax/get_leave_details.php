<?php
include('../../session.php');
include('../../connection.php');

if (!isset($_GET['id'])) {
    echo "Leave ID is required";
    exit;
}

$leave_id = $_GET['id'];

$query = "SELECT * FROM leaves WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$result = $stmt->get_result();
$leave = $result->fetch_assoc();

if (!$leave) {
    echo "Leave request not found";
    exit;
}
?>

<div class="table-responsive">
    <table class="table table-bordered">
        <?php foreach ($leave as $key => $value): ?>
        <tr>
            <th><?php echo htmlspecialchars($key); ?></th>
            <td><?php echo htmlspecialchars($value ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if (!empty($leave['doctor_cert'])): ?>
<div class="mt-3">
    <h6>Doctor's Certificate</h6>
    <a href="../../uploads/<?php echo $leave['doctor_cert']; ?>" target="_blank" class="btn btn-info btn-sm">
        <i class="fas fa-file-medical"></i> View Certificate
    </a>
</div>
<?php endif; ?>