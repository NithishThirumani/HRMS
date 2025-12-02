<?php
include('session.php');

$department = $_POST['department'] ?? '';

$where = "status = 'Active'";
if (!empty($department)) {
    $where .= " AND department = '" . mysqli_real_escape_string($con, $department) . "'";
}

$query = "SELECT id, full_name FROM employees WHERE $where ORDER BY full_name";
$result = mysqli_query($con, $query);

echo "<option value=''>All Employees</option>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
}
?>