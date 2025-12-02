<?php
include('connection.php');

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=family_details.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo '<table border="1">';
echo '<tr><th>Employee Name</th><th>Family Member Name</th><th>DOB</th><th>Nationality</th><th>Blood Group</th><th>Gender</th><th>Profession</th><th>Relation</th></tr>';

$sql = "SELECT e.full_name, f.fm_name, f.fm_dob, f.fm_nationality, f.fm_blood_group, f.fm_gender, f.fm_profession, f.fm_relation
        FROM employee_family f
        JOIN employees e ON f.eid = e.eid
        ORDER BY e.full_name";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['full_name'] . '</td>';
    echo '<td>' . $row['fm_name'] . '</td>';
    echo '<td>' . $row['fm_dob'] . '</td>';
    echo '<td>' . $row['fm_nationality'] . '</td>';
    echo '<td>' . $row['fm_blood_group'] . '</td>';
    echo '<td>' . $row['fm_gender'] . '</td>';
    echo '<td>' . $row['fm_profession'] . '</td>';
    echo '<td>' . $row['fm_relation'] . '</td>';
    echo '</tr>';
}

echo '</table>';
?>
