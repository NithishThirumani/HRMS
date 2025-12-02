<?php
include('session.php');
include('connection.php');
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;
// Add after the existing use Mpdf\Mpdf statement
use PhpOffice\PhpSpreadsheet\Spreadsheet as XLSSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


// Add this after including connection.php
$dept_query = "SELECT name FROM departments WHERE id = ?";
$stmt = $con->prepare($dept_query);
$stmt->bind_param("i", $_SESSION['department_id']);
$stmt->execute();
$dept_result = $stmt->get_result();
$department_name = $dept_result->fetch_assoc()['name'];


// Get distinct months for tabbed view
$months_result = $con->query("SELECT DISTINCT DATE_FORMAT(salary_date, '%Y-%m') AS salary_month FROM sal ORDER BY salary_month DESC");
$months = [];
while ($row = $months_result->fetch_assoc()) {
    $months[] = $row['salary_month'];
}

// Fetch salary data grouped by month
$salary_data = [];
foreach ($months as $month) {
    $stmt = $con->prepare("SELECT sal.*, employees.eid, employees.full_name, employees.department_id, 
                          DATE_FORMAT(salary_date, '%Y-%m') AS salary_month 
                          FROM sal 
                          INNER JOIN employees ON sal.emp_id = employees.eid 
                          WHERE DATE_FORMAT(salary_date, '%Y-%m') = ? 
                          AND employees.department_id = ? 
                          ORDER BY salary_date DESC");
    $stmt->bind_param("si", $month, $_SESSION['department_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $salary_data[$month][] = $row;
    }
}

// Also update the months query to only show months with data for the department
$months_query = "SELECT DISTINCT DATE_FORMAT(s.salary_date, '%Y-%m') AS salary_month 
                FROM sal s 
                INNER JOIN employees e ON s.emp_id = e.eid 
                WHERE e.department_id = ? 
                ORDER BY salary_month DESC";
$stmt = $con->prepare($months_query);
$stmt->bind_param("i", $_SESSION['department_id']);
$stmt->execute();
$months_result = $stmt->get_result();
$months = [];
while ($row = $months_result->fetch_assoc()) {
    $months[] = $row['salary_month'];
}

// Sample months array
$months = ['2025-03', '2025-02', '2025-01']; // Example data from database

// Convert 'YYYY-MM' to 'Mon' format

function formatMonth($date)
{
    return date('M', strtotime($date));
}


// Add this near the top of the file after fetching salary data
$selected_month = isset($_POST['export_month']) ? $_POST['export_month'] : $months[0];


// Export to CSV

if (isset($_POST['export_csv'])) {
    $selected_month = $_POST['export_month'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="salary_data_' . $selected_month . '.csv"');
    $output = fopen('php://output', 'w');



    // Add CSV header comments
    fputcsv($output, ['COMPANY NAME', 'San Solutions']);
    fputcsv($output, ['ADDRESS', '123 Business Street, Dubai, UAE']);
    fputcsv($output, ['CONTACT', 'Tel: +971 4 123 4567 | Email: info@company.com']);
    fputcsv($output, ['']);  // Empty row
    fputcsv($output, ['SALARY MONTH', date('F Y', strtotime($selected_month . '-01'))]);
    fputcsv($output, ['EXPORT DATE', date('d-M-Y H:i')]);
    fputcsv($output, ['']);  // Empty row

    // Column headers with separation
    fputcsv($output, [
        'Employee ID',
        'Employee Name',
        'Base Salary',
        'Housing Allowance',
        'Transport Allowance',
        'Performance Bonus',
        'Incentive',
        'Calculated Days',
        'Present Days',
        'Leaves',
        'LTO',
        'Leaves Amount',    // Added
        'LTO Amount',       // Added
        'Hold',
        'Advance Paid',
        'Other Deductions',
        'Deduction Remarks',
        'Performance Points',
        'Visa Expense',
        'Payable Salary',
        'Deductions',
        'Total Salary',
        'Pay Mode'         // Added
    ]);

    if (isset($salary_data[$selected_month])) {
        foreach ($salary_data[$selected_month] as $row) {
            $leave_balance = $row['calculated_days'] - $row['present_days'] - $row['leaves'];
            fputcsv($output, [
                $row['emp_id'],
                $row['full_name'],

                $row['base_salary'],
                $row['housing_allowance'],
                $row['transportation_allowance'],
                $row['performance_bonus'],
                $row['calculated_days'],
                $row['present_days'],
                $row['leaves'],
                $row['lto'],
                $row['hold'],
                $row['advance_paid'],
                $row['others_deduction'],
                $row['deductions'],
                $row['deduction_remarks'],
                $row['performance_points'],
                $row['incentive'],
                $row['payable_salary'],
                $row['visa_expense'],
                $row['total_salary'],
                $row['bonus'] ?? ''
            ]);
        }
    }
    fclose($output);
    exit;
}

// Export to PDF using mPDF

if (isset($_POST['export_pdf'])) {
    $selected_month = $_POST['export_month'];
    $mpdf = new Mpdf();
    $html = '<h1>Salary Data for ' . $selected_month . '</h1>';

    // Add header with logo and organization info
    $html = '
    <div style="display: flex; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #ccc; padding-bottom: 15px;">
        <img src="img/logo.png" style="height: 60px; margin-right: 25px;">
        <div>
            <h2 style="margin:0; color: #0ea5e9;">Company Name</h2>
            <p style="margin:0; font-size: 14px; color: #666;">
                123 Business Street, Dubai, UAE<br>
                Tel: +971 4 123 4567 | Email: info@company.com
            </p>
        </div>
    </div>
    <h3 style="color: #0ea5e9; margin-bottom: 15px;">Salary Report for ' . formatMonth($selected_month) . ' ' . date('Y', strtotime($selected_month)) . '</h3>';

    if (isset($salary_data[$selected_month])) {
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr>
    <th>EID</th>
    <th>Name</th>
    <th>Base Salary(AED)</th>
    <th>Housing Allow</th>
    <th>Tpt Allow</th>
    <th>Performance Bonus</th>
     <th>Incentive</th>
    <th>Days in Month</th>
    <th>Present</th>
    <th>Unpaid Leaves</th>
    <th>LTO</th>
    <th>Hold</th>
    <th>Advances</th>
    <th>Other Deductions</th>
     <th>Deduction Remarks</th> 
    <th>KPI Points</th>
       <th>Visa</th>
           <th>Payable</th>
       <th>Deduct</th>   
    <th>Total</th>
</tr>';

        foreach ($salary_data[$selected_month] as $row) {
            $leave_balance = $row['calculated_days'] - $row['present_days'] - $row['leaves'];
            $html .= '<tr><td>' . $row['emp_id'] . '</td><td>' . $row['full_name'] . '</td><td>' . $row['base_salary'] . '</td> <td>' . $row['housing_allowance'] . '</td>
    <td>' . $row['transportation_allowance'] . '</td>
    <td>' . $row['performance_bonus'] . '</td> <td>' . $row['incentive'] . '</td> <td>' . $row['calculated_days'] . '</td><td>' . $row['present_days'] . '</td><td>' . $row['leaves'] . '</td><td>' . $row['lto'] . '</td><td>' . $row['hold'] . '</td>
    <td>' . $row['advance_paid'] . '</td>
    <td>' . $row['others_deduction'] . '</td><td>' . $row['deduction_remarks'] . '</td><td>' . $row['performance_points'] . '</td><td>' . $row['visa_expense'] . '</td><td>' . $row['payable_salary'] . '</td><td>' . $row['deductions'] . '</td><td>' . $row['total_salary'] . '</td><td>' . ($row['bonus'] ?? '') . '</td></tr>';
        }
        $html .= '</table>';
    }

    $mpdf->WriteHTML($html);
    $mpdf->Output('salary_data_' . $selected_month . '.pdf', 'D');
    exit;
}

//  Excel export handler 

if (isset($_POST['export_excel'])) {
    $selected_month = $_POST['export_month'];
    $spreadsheet = new XLSSpreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Company header with better styling
    $sheet->mergeCells('A1:W1');
    $sheet->mergeCells('A2:W2');
    $sheet->setCellValue('A1', 'San Solutions');
    $sheet->setCellValue('A2', 'Salary Report - ' . date('F Y', strtotime($selected_month . '-01')));

    // Style for company header
    $sheet->getStyle('A1:W2')->applyFromArray([
        'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0EA5E9']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FFFFFF']]]
    ]);

    // Contact information
    $sheet->mergeCells('A3:W3');
    $sheet->setCellValue('A3', '123 Business Street, Dubai, UAE | Tel: +971 4 123 4567 | Email: info@company.com');
    $sheet->getStyle('A3')->applyFromArray([
        'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']]
    ]);

    // Add spacing
    $sheet->getRowDimension(4)->setRowHeight(10);

    // Column headers with grouped sections
    $headers = [
        ['Employee Details', 2],
        ['Salary Components', 5],
        ['Attendance', 4],
        ['Deductions', 6],
        ['Performance', 2],
        ['Final Amounts', 4]
    ];

    // Create grouped headers
    $col = 'A';
    $row = 5;
    foreach ($headers as $header) {
        $endCol = chr(ord($col) + $header[1] - 1);
        $sheet->mergeCells($col . $row . ':' . $endCol . $row);
        $sheet->setCellValue($col . $row, $header[0]);
        $sheet->getStyle($col . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $col = chr(ord($endCol) + 1);
    }

    // Subheaders
    $row = 6;
    $headers = [
        'EID',
        'Name',
        'Base Salary',
        'Housing',
        'Transport',
        'Bonus',
        'Incentive',
        'Cal Days',
        'Present',
        'Leaves',
        'LTO',
        'Leaves Amt',
        'LTO Amt',
        'Hold',
        'Advance',
        'Others',
        'Remarks',
        'Points',
        'Visa',
        'Payable',
        'Deduct',
        'Total',
        'Pay Mode'
    ];

    $sheet->fromArray([$headers], null, 'A' . $row);
    $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '64748B']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // Data rows with alternating colors
    $row = 7;
    if (isset($salary_data[$selected_month])) {
        foreach ($salary_data[$selected_month] as $data) {
            $rowData = [
                $data['emp_id'],
                $data['full_name'],
                $data['base_salary'],
                $data['housing_allowance'],
                $data['transportation_allowance'],
                $data['performance_bonus'],
                $data['incentive'],
                $data['calculated_days'],
                $data['present_days'],
                $data['leaves'],
                $data['lto'],
                $data['leaves_amt'],
                $data['lto_amt'],
                $data['hold'],
                $data['advance_paid'],
                $data['others_deduction'],
                $data['deduction_remarks'],
                $data['performance_points'],
                $data['visa_expense'],
                $data['payable_salary'],
                $data['deductions'],
                $data['total_salary'],
                $data['pay_mode']
            ];

            $sheet->fromArray([$rowData], null, 'A' . $row);

            // Style for amount columns
            $amountColumns = ['C', 'D', 'E', 'F', 'G', 'L', 'M', 'N', 'O', 'P', 'S', 'T', 'U', 'V'];
            foreach ($amountColumns as $col) {
                $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00 "AED"');
            }

            // Alternate row colors and apply cell styles
            $fillColor = ($row % 2 == 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $row++;
        }
    }

    // Auto-size and minimum width
    foreach (range('A', 'W') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
        // Set width if column is too narrow (instead of setMinWidth)
        if ($sheet->getColumnDimension($col)->getWidth() < 12) {
            $sheet->getColumnDimension($col)->setWidth(12);
        }
    }

    // Rest of the export code remains the same
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="salary_data_' . $selected_month . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
<!-- HTML Code starts -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary View</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <script src="js/search.js"></script>
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
        }

        .card {
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 4px 4px 0 0;
            padding: 0.5rem;
        }

        .card-header h1 {
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .tabs {
            padding: 0.25rem 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            gap: 0.15rem;
        }

        .tab-link {
            padding: 0.50rem 1rem;
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
            border: none;
            border-radius: 4px 4px 0 0;
            transition: all 0.2s;
        }

        .tab-link:hover {
            color: #0ea5e9;
            background: #f1f5f9;
        }

        .tab-link.active {
            color: #0ea5e9;
            background: white;
            font-weight: 600;
            box-shadow: 0 2px 0 #0ea5e9;
        }

        .tab-content {
            padding: 0.50rem;
            background: white;
        }

        .salary-table {
            width: 90%;
            font-size: 0.8rem;
            border-radius: 4px;
            overflow-x: auto;
            margin: 0.5rem 0;
        }

        .salary-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            padding: 0.5rem;
            font-size: 0.75rem;
            white-space: nowrap;
            border-bottom: 2px solid #e2e8f0;
        }

        .salary-table td {
            padding: 0.4rem 0.5rem;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .salary-table tr:hover td {
            background: #f8fafc;
        }

        button[name="export_csv"],
        button[name="export_pdf"] {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: white;
            border: none;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            font-size: 0.8rem;
            margin: 0 0.25rem 0.5rem 0;
        }

        button[name="export_csv"]:hover,
        button[name="export_pdf"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15);
        }

        .btn-primary {
            background: #0ea5e9;
            border: none;
        }

        .btn-info {
            background: #06b6d4;
            border: none;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 0.75rem;
        }

        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.4rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        /* Adjust table cell widths */
        .salary-table th:nth-child(1),
        .salary-table td:nth-child(1) {
            width: 5%;
        }

        /* EID */
        .salary-table th:nth-child(2),
        .salary-table td:nth-child(2) {
            width: 12%;
        }

        /* Name */
        .salary-table th:nth-child(3),
        .salary-table td:nth-child(3) {
            width: 8%;
        }

        /* Base */
        .salary-table th:nth-child(4),
        .salary-table td:nth-child(4) {
            width: 6%;
        }

        /* Cal Days */
        .salary-table th:nth-child(5),
        .salary-table td:nth-child(5) {
            width: 6%;
        }

        /* Present */
        .salary-table th:nth-child(6),
        .salary-table td:nth-child(6) {
            width: 5%;
        }

        /* Leaves */
    </style>
    <script>
        function openTab(event, tabId) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            // Remove active class from all tabs
            document.querySelectorAll('.tab-link').forEach(tab => tab.classList.remove('active'));
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            // Highlight active tab
            event.currentTarget.classList.add('active');
        }
    </script>
</head>

<body #page-top>
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <div class="card shadow">


            <div class="card-header text-center py-4">
                <h1 class="mb-0">View and Export Salary Data</h1>
                <p class="text-white mb-0">Department: <?php echo $department_name; ?></p>
            </div>

            <!-- Tab buttons -->
            <div class="tabs">
                <?php foreach ($months as $index => $month): ?>
                    <button class="tab-link <?= $index === 0 ? 'active' : '' ?>"
                        onclick="openTab(event, 'tab-<?php echo $index; ?>')">
                        <?php echo formatMonth($month); ?>
                    </button>
                <?php endforeach; ?>
            </div>


            <?php foreach ($salary_data as $month => $salaries): ?>
                <div id="tab-<?php echo array_search($month, $months); ?>" class="tab-content">
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="export_month" value="<?php echo $month; ?>">
                        <button type="submit" name="export_csv" class="btn btn-primary btn-sm">Export CSV</button>
                        <button type="submit" name="export_pdf" class="btn btn-info btn-sm">Export PDF</button>
                        <button type="submit" name="export_excel" class="btn btn-success btn-sm">Export Excel</button>
                    </form>

                    <div class="salary-table-container">
                        <table class="salary-table table table-bordered table-striped"
                            id="salaryTable-<?php echo array_search($month, $months); ?>">
                            <thead>
                                <tr>
                                    <th>EID</th>
                                    <th>Name</th>
                                    <th>Base(AED)</th>
                                    <th>Housing</th>
                                    <th>Transport</th>
                                    <th>Bonus</th>
                                    <th>Incentive</th>
                                    <th>Cal Days</th>
                                    <th>Present</th>
                                    <th>Leaves</th>
                                    <th>LTO</th>
                                    <th>Leaves Amt</th> <!-- Added -->
                                    <th>LTO Amt</th> <!-- Added -->
                                    <th>Hold</th>
                                    <th>Advance</th>
                                    <th>Others Deduct</th>
                                    <th>Deduct Remarks</th>
                                    <th>Points</th>

                                    <th>Visa</th>
                                    <th>Payable</th>
                                    <th>Deduct</th>
                                    <th>Total</th>
                                    <th>Pay Mode</th> <!-- Added -->
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salaries as $row): ?>
                                    <tr>
                                        <td><?php echo $row['emp_id']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>

                                        <td><?php echo $row['base_salary']; ?></td>
                                        <td><?php echo $row['housing_allowance']; ?></td>
                                        <td><?php echo $row['transportation_allowance']; ?></td>
                                        <td><?php echo $row['performance_bonus']; ?></td>
                                        <td><?php echo $row['incentive']; ?></td>
                                        <td><?php echo $row['calculated_days']; ?></td>
                                        <td><?php echo $row['present_days']; ?></td>
                                        <td><?php echo $row['leaves']; ?></td>
                                        <td><?php echo $row['lto']; ?></td>
                                        <td><?php echo $row['leaves_amt']; ?></td> <!-- Added -->
                                        <td><?php echo $row['lto_amt']; ?></td> <!-- Added -->
                                        <td><?php echo $row['hold']; ?></td>
                                        <td><?php echo $row['advance_paid']; ?></td>
                                        <td><?php echo $row['others_deduction']; ?></td>

                                        <td><?php echo $row['deduction_remarks']; ?></td>
                                        <td><?php echo $row['performance_points']; ?></td>
                                        <td><?php echo $row['visa_expense']; ?></td>
                                        <td><?php echo $row['payable_salary']; ?></td>
                                        <td><?php echo $row['deductions']; ?></td>
                                        <td><?php echo $row['total_salary']; ?></td>
                                        <td><?php echo $row['pay_mode']; ?></td> <!-- Added -->
                                        <td>
                                            <a href="edit_salary.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="generate_payslip.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-info btn-sm ml-1" target="_blank">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>



    <?php
    include_once('footer.php');
    ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <script>
        function openTab(evt, tabId) {
            let i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabId).style.display = "block";
            evt.currentTarget.className += " active";
        }
        document.getElementsByClassName("tab-link")[0].click(); // Open first tab by default
    </script>


    <script>
        $(document).ready(function () {
            <?php foreach ($months as $index => $month): ?>
                $('#salaryTable-<?php echo $index; ?>').DataTable({
                    scrollX: true,
                    scrollY: '50vh',
                    scrollCollapse: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    pageLength: 25,
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'excel', 'pdf'
                    ]
                });
            <?php endforeach; ?>
        });
    </script>


    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript -->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages -->
    <script src="js/sb-admin-2.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>