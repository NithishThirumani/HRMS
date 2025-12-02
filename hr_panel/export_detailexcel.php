<?php
include('session.php');
include('connection.php');
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
foreach ($headers as $key => $header) {
    $column = chr(65 + $key); // Convert number to letter (A, B, C, etc.)
    $sheet->setCellValue($column . '1', $header);
}

// Add data
$row = 2;
while ($data = mysqli_fetch_assoc($result)) {
    $col = 0;
    foreach ($data as $value) {
        $column = chr(65 + $col); // Convert number to letter (A, B, C, etc.)
        $sheet->setCellValue($column . $row, $value);
        $col++;
    }
    $row++;
}

// Auto-size columns
foreach (range('A', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="employee_' . $activeTab . '_data.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;