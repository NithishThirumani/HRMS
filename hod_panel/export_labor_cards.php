<?php
include('connection.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

$format = $_GET['format'] ?? 'excel';
$query = "SELECT eid, full_name, department, designation, MOLID, labour_card_no, 
          labour_card_start_date, labour_card_end_date, status 
          FROM employees 
          WHERE MOLID IS NOT NULL";
$result = mysqli_query($con, $query);

if ($format === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = [
        'EID', 
        'Full Name', 
        'Department', 
        'Designation', 
        'MOL ID', 
        'Labor Card No', 
        'Start Date', 
        'End Date', 
        'Status'
    ];
    
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
            if (strpos($value, '_date') !== false) {
                $value = date('d-m-Y', strtotime($value));
            }
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }
    
    // Style the header row
    $lastCol = 'I';
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
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
    ]);
    
    // Set row height for header
    $sheet->getRowDimension(1)->setRowHeight(30);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="labor_cards_' . date('Y-m-d') . '.xlsx"');
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
        'margin_bottom' => 15
    ]);

    $stylesheet = '
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            font-size: 11px;
        }
        th { 
            background-color: #F0F2F4;
            color: #000000;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
        }
        td { 
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
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
    ';
    
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

    $html = '<div class="header">
                <h2>Labor Card Directory</h2>
                <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
            </div>';
    
    $html .= '<table border="1"><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . htmlspecialchars($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    mysqli_data_seek($result, 0);
    while ($employee = mysqli_fetch_assoc($result)) {
        $html .= '<tr>';
        foreach ($employee as $key => $value) {
            if (strpos($key, '_date') !== false) {
                $value = date('d-m-Y', strtotime($value));
            }
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output('labor_cards_' . date('Y-m-d') . '.pdf', 'D');
    exit;
}
?>