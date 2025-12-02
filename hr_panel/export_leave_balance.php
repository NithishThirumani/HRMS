<?php
include('session.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$department = $_GET['department'] ?? '';
$employee = $_GET['employee'] ?? '';
$year = $_GET['year'] ?? date('Y');

$where = "e.status = 'Active'";
if (!empty($department)) {
    $where .= " AND e.department = '" . mysqli_real_escape_string($con, $department) . "'";
}
if (!empty($employee)) {
    $where .= " AND e.id = '" . mysqli_real_escape_string($con, $employee) . "'";
}

$query = "SELECT 
            e.full_name,
            e.department,
            lp.leave_type,
            lp.max_days as total_allocation,
            COALESCE((
                SELECT SUM(total_days) 
                FROM leaves 
                WHERE emp_id = e.id 
                AND type_of_leave = lp.leave_type 
                AND YEAR(start_date) = $year
                AND status = 'Approved'
            ), 0) as used_leaves,
            lp.max_days - COALESCE((
                SELECT SUM(total_days) 
                FROM leaves 
                WHERE emp_id = e.id 
                AND type_of_leave = lp.leave_type 
                AND YEAR(start_date) = $year
                AND status = 'Approved'
            ), 0) as balance,
            CURRENT_DATE as report_date
          FROM employees e
          CROSS JOIN leave_policies lp
          WHERE $where
          ORDER BY e.department, e.full_name, lp.leave_type";

$result = mysqli_query($con, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Employee Leave Balance Report');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// Set report parameters
$sheet->setCellValue('A3', 'Department:');
$sheet->setCellValue('B3', !empty($department) ? $department : 'All Departments');
$sheet->setCellValue('A4', 'Year:');
$sheet->setCellValue('B4', $year);
$sheet->setCellValue('A5', 'Generated On:');
$sheet->setCellValue('B5', date('d M Y H:i'));

// Set column headers
$headers = [
    'Employee Name',
    'Department',
    'Leave Type',
    'Total Allocation',
    'Used Leaves',
    'Balance',
    'Last Updated'
];
foreach (range('A', 'G') as $index => $column) {
    $sheet->setCellValue($column . '7', $headers[$index]);
    $sheet->getStyle($column . '7')->getFont()->setBold(true);
}

// Add data
$row = 8;
while ($data = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $row, $data['full_name']);
    $sheet->setCellValue('B' . $row, $data['department']);
    $sheet->setCellValue('C' . $row, $data['leave_type']);
    $sheet->setCellValue('D' . $row, $data['total_allocation']);
    $sheet->setCellValue('E' . $row, $data['used_leaves']);
    $sheet->setCellValue('F' . $row, $data['balance']);
    $sheet->setCellValue('G' . $row, date('d M Y'));
    $row++;
}

// Auto-size columns
foreach (range('A', 'G') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Add borders
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A7:G' . ($row - 1))->applyFromArray($styleArray);

// Set filename
$filename = "Leave_Balance_Report_" . ($department ? $department . "_" : "") . $year . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>