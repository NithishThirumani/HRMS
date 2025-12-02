<?php
include('connection.php');

$company_name = 'Communik Marketing Management LLC';
$company_address = 'M-13, ACICO Business Park, Al Khabaisi, Port Saeed, Dubai, UAE.'; // Update as needed

$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'excel';

if ($format === 'pdf') {
    require_once __DIR__ . '/../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $html = '<div style="text-align:center;">'
        . '<div style="font-size:28px; font-weight:bold; color:purple;">' . $company_name . '</div>'
        . '<div style="font-size:18px; font-weight:bold; color:purple; margin-bottom:20px;">' . $company_address . '</div>'
        . '</div>';
    $html .= '<table border="1" style="width:100%; border-collapse: collapse;">';
    $html .= '<tr><th>EID</th><th>Full Name</th><th>Email</th><th>Contact</th><th>Department</th><th>Status</th><th>Designation</th><th>Date of Joining</th></tr>';

    $sql = "SELECT e.eid, e.full_name, e.email, e.contact, d.name as department, e.status, e.designation, e.doj FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.full_name";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $row_style = '';
        if (strtolower($row['status'] ?? '') === 'inactive') {
            $row_style = ' style="color:red;"';
        }
        $html .= '<tr' . $row_style . '>';
        $html .= '<td>' . htmlspecialchars($row['eid'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['full_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['contact'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['department'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['status'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['designation'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['doj'] ?? '') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('employee_list.pdf', 'D');
    exit;
}

// Default: Excel export
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=employee_list.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo '<table border="1">';
echo '<tr><th colspan="8" style="font-size:28px; font-weight:bold; color:purple; text-align:center;">' . $company_name . '</th></tr>';
echo '<tr><th colspan="8" style="font-size:18px; font-weight:bold; color:purple; text-align:center;">' . $company_address . '</th></tr>';
echo '<tr><td colspan="8"></td></tr>';
echo '<tr><th>EID</th><th>Full Name</th><th>Email</th><th>Contact</th><th>Department</th><th>Status</th><th>Designation</th><th>Date of Joining</th></tr>';

$sql = "SELECT e.eid, e.full_name, e.email, e.contact, d.name as department, e.status, e.designation, e.doj FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.full_name";
$result = mysqli_query($con, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $row_style = '';
    if (strtolower($row['status'] ?? '') === 'inactive') {
        $row_style = ' style="color:red;"';
    }
    echo '<tr' . $row_style . '>';
    echo '<td>' . htmlspecialchars($row['eid'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['full_name'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['contact'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['department'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['status'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['designation'] ?? '') . '</td>';
    echo '<td>' . htmlspecialchars($row['doj'] ?? '') . '</td>';
    echo '</tr>';
}
echo '</table>';
?> 