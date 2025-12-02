<?php
// Start output buffering
ob_start();

include('connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

$format = $_GET['format'] ?? 'excel';
// Replace the existing query with this comprehensive one
$query = "SELECT 
    e.eid, 
    e.full_name, 
    e.email, 
    e.birthday, 
    e.maritalsts,
    e.contact, 
    e.address, 
    e.country, 
    d.name as department_name,
    e.designation,
    e.EmpLoc, 
    e.EmpDiv, 
    e.EmpGrade, 
    e.doj,
    e.reporting_manager,
    e.EmpCostcenter,
    e.MOLID,
    e.status,
    e.blood_group,
    CASE WHEN e.is_field_staff = 1 THEN 'Yes' ELSE 'No' END as is_field_staff,
    CASE WHEN e.is_trainee = 1 THEN 'Yes' ELSE 'No' END as is_trainee
FROM employees e
LEFT JOIN departments d ON e.department_id = d.id
WHERE e.emp_left_org = 0";

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
    'Field Staff',
    'Is Trainee'
];
$result = mysqli_query($con, $query);

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Company header with styling
    $sheet->mergeCells('A1:U1');
    $sheet->mergeCells('A2:U2');
    $sheet->mergeCells('A3:U3');
    $sheet->setCellValue('A1', 'Communik Marketing Management LLC');
    $sheet->setCellValue('A2', 'M-13, ACICO Business Park, Al Khabaisi, Port Saeed, Dubai, UAE | Tel: +971 4 123 4567 | Email: hrms@communikmarketing.com');
    $sheet->setCellValue('A3', 'Employee Directory Report');
    
    // Style the company header
    $sheet->getStyle('A1:U1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 16,
            'color' => ['rgb' => '000000']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E2EFDA']
        ]
    ]);
    
    $sheet->getStyle('A2')->applyFromArray([
        'font' => [
            'size' => 10,
            'color' => ['rgb' => '666666']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ]
    ]);
    
    $sheet->getStyle('A3')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => '000000']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ]
    ]);

    // Export information
    $sheet->mergeCells('A4:U4');
    $sheet->setCellValue('A4', 'Exported on: ' . date('d-m-Y H:i:s') . ' | Exported by: ' . ($_SESSION['email'] ?? 'Admin'));
    $sheet->getStyle('A4')->applyFromArray([
        'font' => ['italic' => true, 'size' => 10],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
    ]);

    // Add a blank row for spacing
    $sheet->getRowDimension(5)->setRowHeight(10);
    
    // Set headers with full column names starting from row 6
    $col = 'A';
    $headerRow = 6;
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headerRow, $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }
    
    // Add data starting from row 7
    $row = 7;
    while ($employee = mysqli_fetch_assoc($result)) {
        $col = 'A';
        foreach ($employee as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        
        // Apply conditional formatting based on employee status and type
        if ($employee['status'] === 'inactive') {
            $sheet->getStyle('A' . $row . ':U' . $row)->applyFromArray([
                'font' => ['color' => ['rgb' => 'FF0000']] // Red color for inactive employees
            ]);
        }
        if ($employee['is_trainee'] === 'Yes') {
            $sheet->getStyle('A' . $row . ':U' . $row)->applyFromArray([
                'font' => ['color' => ['rgb' => 'FF8C00']] // Orange color for trainees
            ]);
        }
        
        $row++;
    }
    
    // Enhanced styling for header row
    $lastCol = 'U';
    $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
    ]);
    
    // Set row height for header
    $sheet->getRowDimension($headerRow)->setRowHeight(30);
    
    // Add borders to all data cells
    $sheet->getStyle('A' . ($headerRow + 1) . ':' . $lastCol . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);

    // Freeze the header row
    $sheet->freezePane('A' . ($headerRow + 1));
    
    // Fix the file extension and add proper headers
    $filename = 'employees_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Create writer with proper settings
    $writer = new Xlsx($spreadsheet);
    $writer->setOffice2003Compatibility(false);
    $writer->setPreCalculateFormulas(false);
    
    // Save to PHP output
    ob_end_clean(); // Clear output buffer
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