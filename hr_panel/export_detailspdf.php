<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';
// ... rest of your code ...

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';

// Initialize mPDF
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);

// Add some CSS styling
$stylesheet = '
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: left;
    }
    th {
        background-color: #f4f4f4;
    }
';
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

// Get data based on active tab
if ($activeTab == 'personal') {
    $headers = ['EID', 'Full Name', 'Email', 'Birthday', 'Marital Status', 'Contact', 'Address', 'Country'];
    $query = "SELECT eid, full_name, email, birthday, maritalsts, contact, address, country FROM employees";
} elseif ($activeTab == 'education') {
    $headers = ['EID', 'Full Name', 'Degree', 'Institute', 'Start From', 'End To'];
    $query = "SELECT eid, full_name, degree, Institute, start_from, end_to FROM employees";
} else {
    $headers = ['EID', 'Full Name', 'Department', 'Location', 'Division', 'Grade', 'Cost Center', 'DOJ', 'Field Staff', 'Status'];
    $query = "SELECT eid, full_name, department, EmpLoc, EmpDiv, EmpGrade, EmpCostcenter, doj, is_field_staff, status FROM employees";
}

$result = mysqli_query($con, $query);

// Start building HTML content
$html = '<h2>Employee List</h2>';
$html .= '<table>';
$html .= '<tr>';
foreach ($headers as $header) {
    $html .= '<th>' . $header . '</th>';
}
$html .= '</tr>';

while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr>';
    foreach ($row as $value) {
        $html .= '<td>' . htmlspecialchars($value) . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</table>';

$mpdf->WriteHTML($html);
$mpdf->Output('employee_list.pdf', 'D');