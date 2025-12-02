<?php
include('session.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_type = $_POST['report_type'];
    $year = $_POST['year'];
    $period = $_POST['period'];

    // Calculate date range
    switch($report_type) {
        case 'monthly':
            $start_date = "$year-$period-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            $period_name = date('F', strtotime($start_date));
            break;
        case 'quarterly':
            $start_month = ($period - 1) * 3 + 1;
            $start_date = "$year-$start_month-01";
            $end_date = date('Y-m-t', strtotime("$year-".($start_month + 2)."-01"));
            $period_name = "Q$period";
            break;
        case 'halfyearly':
            $start_month = ($period - 1) * 6 + 1;
            $start_date = "$year-$start_month-01";
            $end_date = date('Y-m-t', strtotime("$year-".($start_month + 5)."-01"));
            $period_name = $period == 1 ? "H1" : "H2";
            break;
        case 'annually':
            $start_date = "$year-01-01";
            $end_date = "$year-12-31";
            $period_name = "Annual";
            break;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $headers = ['Employee ID', 'Employee Name', 'Department', 'Leave Type', 'Start Date', 'End Date', 
                'Total Days', 'Status', 'Applied Date', 'HR Status', 'Admin Status'];
    foreach (range('A', 'K') as $index => $column) {
        $sheet->setCellValue($column.'1', $headers[$index]);
        $sheet->getStyle($column.'1')->getFont()->setBold(true);
    }

    // Fetch data
    $query = "SELECT l.*, e.id as emp_id, e.eid, e.full_name, d.name as department
              FROM leaves l
              LEFT JOIN employees e ON (l.emp_id = e.id OR l.emp_id = e.eid)
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE l.start_date BETWEEN ? AND ?
              ORDER BY d.name, e.full_name, l.start_date";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = 2;
    while($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A'.$row, $data['emp_id']);
        $sheet->setCellValue('B'.$row, $data['full_name']);
        $sheet->setCellValue('C'.$row, $data['department']);
        $sheet->setCellValue('D'.$row, $data['type_of_leave']);
        $sheet->setCellValue('E'.$row, date('d-m-Y', strtotime($data['start_date'])));
        $sheet->setCellValue('F'.$row, date('d-m-Y', strtotime($data['end_date'])));
        $sheet->setCellValue('G'.$row, $data['total_days']);
        $sheet->setCellValue('H'.$row, $data['status']);
        $sheet->setCellValue('I'.$row, date('d-m-Y', strtotime($data['applied_at'])));
        $sheet->setCellValue('J'.$row, $data['hr_status']);
        $sheet->setCellValue('K'.$row, $data['admin_status']);
        $row++;
    }

    // Auto-size columns
    foreach(range('A', 'K') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Set filename
    $filename = "Leave_Report_{$period_name}_{$year}.xlsx";

    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>