<?php
include('connection.php');

$where_conditions = array();

if (!empty($_POST['department'])) {
    $department = mysqli_real_escape_string($con, $_POST['department']);
    $where_conditions[] = "department = '$department'";
}

if (!empty($_POST['status'])) {
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $where_conditions[] = "status = '$status'";
}

if (!empty($_POST['location'])) {
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $where_conditions[] = "EmpLoc = '$location'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
$query = "SELECT * FROM employees $where_clause";
$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Output filtered employee cards
    include('employee_card_template.php');
}
?>