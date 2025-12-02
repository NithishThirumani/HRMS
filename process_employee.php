// ... existing code where employee is successfully created ...
$employeeId = $con->insert_id; // Get the ID of the newly created employee
header("Location: generate_id_card.php?id=" . $employeeId);
exit();