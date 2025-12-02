<?php
include('connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

$format = $_GET['format'] ?? 'excel';
// Replace the existing query with this comprehensive one
$query = "SELECT 
    eid, 
    full_name, 
    email, 
    birthday, 
    maritalsts,
    contact, 
    address, 
    country, 
    department, 
    designation,
    EmpLoc, 
    EmpDiv, 
    EmpGrade, 
    doj,
    reporting_manager,
    EmpCostcenter,
    MOLID,
    status,
    blood_group,
    is_field_staff
FROM employees";

// Update the headers array
$headers = [
    'EID', 
    'Full Name', 
    'Email', 
    'Birthday', 
    'Marital Status',
    'Contact', 
    'Address', 
    'Country', 
    'Department', 
    'Designation',
    'Location', 
    'Division', 
    'Grade', 
    'Date of Joining',
    'Reporting Manager',
    'Cost Center',
    'MOL ID',
    'Status',
    'Blood Group',
    'Field Staff'
];
$result = mysqli_query($con, $query);

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers with full column names
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }
    
    // Add data
    $row = 2;
    while ($employee = mysqli_fetch_assoc($result)) {
        $col = 'A';
        foreach ($employee as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }
    
    // Enhanced styling for header row
    $lastCol = 'T'; // Adjusted for 20 columns
    $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '000000'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F0F2F4']
        ],
        'borders' => [
            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
            'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
    ]);
    
    // Set row height for header
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    // Add light borders to all cells
    $sheet->getStyle('A1:' . $lastCol . $row)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC'],
            ]
        ]
    ]);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="employees_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} elseif ($format === 'pdf') {
    $mpdf = new Mpdf([
        'orientation' => 'L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'format' => 'A3'
    ]);

    $stylesheet = '
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 11px;
            background-color: #ffffff;
        }
        th { 
            background-color: #F0F2F4;
            color: #000000;
            font-weight: bold;
            padding: 12px 8px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        td { 
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px;
        }
        .header h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 12px;
        }
    ';
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

    $html = '<div class="header">
                <h2>Employee Directory</h2>
                <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
            </div>';
    
    $html .= '<table><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . htmlspecialchars($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    mysqli_data_seek($result, 0);
    while ($employee = mysqli_fetch_assoc($result)) {
        $html .= '<tr>';
        foreach ($employee as $value) {
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output('employees_' . date('Y-m-d') . '.pdf', 'D');
    exit;
}
?>