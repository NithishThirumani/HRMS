<?php
require_once __DIR__ . '/../vendor/autoload.php';
include('connection.php');

$mpdf = new \Mpdf\Mpdf();
$html = '<h2>Family Details Report</h2>';
$html .= '<table border="1" style="width:100%; border-collapse: collapse;">';
$html .= '<tr><th>Employee Name</th><th>Family Member Name</th><th>DOB</th><th>Nationality</th><th>Blood Group</th><th>Gender</th><th>Profession</th><th>Relation</th></tr>';

$sql = "SELECT e.full_name, f.fm_name, f.fm_dob, f.fm_nationality, f.fm_blood_group, f.fm_gender, f.fm_profession, f.fm_relation
        FROM employee_family f
        JOIN employees e ON f.eid = e.eid
        ORDER BY e.full_name";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr>';
    $html .= '<td>' . $row['full_name'] . '</td>';
    $html .= '<td>' . $row['fm_name'] . '</td>';
    $html .= '<td>' . $row['fm_dob'] . '</td>';
    $html .= '<td>' . $row['fm_nationality'] . '</td>';
    $html .= '<td>' . $row['fm_blood_group'] . '</td>';
    $html .= '<td>' . $row['fm_gender'] . '</td>';
    $html .= '<td>' . $row['fm_profession'] . '</td>';
    $html .= '<td>' . $row['fm_relation'] . '</td>';
    $html .= '</tr>';
}

$html .= '</table>';
$mpdf->WriteHTML($html);
$mpdf->Output('family_details.pdf', 'D');
?>
 