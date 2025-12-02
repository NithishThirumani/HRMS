function logActivity($con, $employee, $activity, $eid, $performed_by, $type) {
$sql = "INSERT INTO activity_log (employee, activity, eid, performed_by, type)
VALUES (?, ?, ?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("sssss", $employee, $activity, $eid, $performed_by, $type);
$stmt->execute();
}