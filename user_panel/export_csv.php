<?php
include('connection.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=family_details.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Employee Name', 'Family Member Name', 'DOB', 'Nationality', 'Blood Group', 'Gender', 'Profession', 'Relation'));

$sql = "SELECT e.full_name, f.fm_name, f.fm_dob, f.fm_nationality, f.fm_blood_group, f.fm_gender, f.fm_profession, f.fm_relation
        FROM employee_family f
        JOIN employees e ON f.eid = e.eid
        ORDER BY e.full_name";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}
fclose($output);
?>
