<?php
// Add these at the VERY TOP
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
include('download_session.php');

// Get salary ID from URL parameter
$id = $_GET['id'] ?? null;

include('connection.php');
use Mpdf\Mpdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Update the numberToWords function at the top of the file
function numberToWords($number)
{
    $ones = array(
        0 => "",
        1 => "One",
        2 => "Two",
        3 => "Three",
        4 => "Four",
        5 => "Five",
        6 => "Six",
        7 => "Seven",
        8 => "Eight",
        9 => "Nine",
        10 => "Ten",
        11 => "Eleven",
        12 => "Twelve",
        13 => "Thirteen",
        14 => "Fourteen",
        15 => "Fifteen",
        16 => "Sixteen",
        17 => "Seventeen",
        18 => "Eighteen",
        19 => "Nineteen"
    );
    $tens = array(
        2 => "Twenty",
        3 => "Thirty",
        4 => "Forty",
        5 => "Fifty",
        6 => "Sixty",
        7 => "Seventy",
        8 => "Eighty",
        9 => "Ninety"
    );

    $number = number_format($number, 2, ".", "");
    $num_arr = explode(".", $number);
    $wholenum = $num_arr[0];
    $decnum = $num_arr[1];
    $words = "";

    // Handle thousands
    if ($wholenum >= 1000) {
        $thousands = floor($wholenum / 1000);
        if ($thousands < 20) {
            $words .= $ones[$thousands] . " Thousand ";
        } else {
            $words .= $tens[floor($thousands / 10)];
            if ($thousands % 10 > 0) {
                $words .= " " . $ones[$thousands % 10];
            }
            $words .= " Thousand ";
        }
        $wholenum = $wholenum % 1000;
    }

    // Handle hundreds
    if ($wholenum >= 100) {
        $hundreds = floor($wholenum / 100);
        $words .= $ones[$hundreds] . " Hundred ";
        $wholenum = $wholenum % 100;
    }

    // Handle tens and ones
    if ($wholenum >= 20) {
        $words .= $tens[floor($wholenum / 10)];
        if ($wholenum % 10 > 0) {
            $words .= " " . $ones[$wholenum % 10];
        }
    } elseif ($wholenum > 0) {
        $words .= $ones[$wholenum];
    }

    // Add Dirhams
    $words = trim($words) . " Dirhams";

    // Handle Fils
    if ($decnum > 0) {
        $words .= " and ";
        if ($decnum < 20) {
            $words .= $ones[$decnum];
        } else {
            $words .= $tens[floor($decnum / 10)];
            if ($decnum % 10 > 0) {
                $words .= " " . $ones[$decnum % 10];
            }
        }
        $words .= " Fils";
    }

    return $words . " Only";
}




if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $con->prepare("SELECT MAX(slip_no) as last_no FROM salary_slips");
    $stmt->execute();
    $result = $stmt->get_result();
    $slip_data = $result->fetch_assoc();
    $slip_no = ($slip_data['last_no'] ?? 0) + 1;

    // Insert new slip number
    $stmt = $con->prepare("INSERT INTO salary_slips (slip_no, salary_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $slip_no, $id);
    $stmt->execute();



    $stmt = $con->prepare("
    SELECT sal.*, 
          employees.full_name, 
           employees.eid,
           employees.doj,
           employees.birthday,
           departments.name as department,
           employees.designation,  
           employees.bank_name,
           employees.account_no,
           employees.iban,
           employees.nominee,
           employees.EmpLoc,          
           sal.pay_mode
    FROM sal 
    INNER JOIN employees ON sal.emp_id = employees.eid 
    LEFT JOIN departments ON employees.department_id = departments.id
    WHERE sal.id = ?
");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $salary = $result->fetch_assoc();

    // First, configure mPDF with watermark settings
    if ($salary) {
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 5
        ]);

        // Generate QR code - corrected method
        $qrData = 'EMPID-' . $salary['eid'] .
            '|DEPT-' . $salary['department'] .
            '|COMP-Communik Marketing Management Est' .
            '|MONTH-' . date('F-Y', strtotime($salary['salary_date'])) .
            '|SAL-' . $salary['total_salary'];
        $qrCode = new QrCode($qrData);  // Changed from ::create() to direct instantiation
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeImage = base64_encode($result->getString());

        // Adjust watermark settings for better visibility
        $mpdf->SetWatermarkImage(
            'img/translogo.png',    // image path
            0.30,                   // opacity increased slightly
            array(200, 200),        // size adjusted
            array(5, 60)          // position adjusted
        );

        // Set watermark image  
        $mpdf->showWatermarkImage = false;


        // Calculate leave balance
        $leave_balance = $salary['calculated_days'] - $salary['present_days'] - $salary['leaves'];




        $html = '
      <div class="qr-code">
            <img src="data:image/png;base64,' . $qrCodeImage . '" style="width: 100%">
        </div>
<style>
     .slip-number {
        text-align: right;
        padding: 5px;
        font-size: 12px;
    }
    table tr td {
        border-bottom: 1px solid #ddd;
        padding: 4px;      // reduced padding
    }
    .earnings td, .deductions td {
        border-bottom: 1px solid #eee;
        padding: 5px 0;
    }
    .download-info {
        font-size: 10px;
        text-align: right;
        color: #666;
        margin-top: 5px;
    }


    .payslip {
        padding: 5px;  
        font-family: Arial, sans-serif;
    }
    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding: 3px;
        margin-bottom: 5px;
    }
    .company-address {
        text-align: center;
        margin-bottom: 5px;
        font-size: 11px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
    }
    .pay-period {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        margin: 10px 0;
    }
    .employee-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .employee-summary td {
        padding: 3px;
        border: 1px solid #000;
        font-size: 10px; 
    }
    .salary-details {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
    }
    .salary-details th, .salary-details td {
        border: 1px solid #000;
        padding: 8px;
    }
    .salary-details th {
        border-bottom: 2px solid #000;
    }
    .net-pay {
        border: 2px solid #000;
        padding: 8px;
        margin: 12px 0;
        text-align: center;
        font-weight: bold;
    }
    .signature {
        margin-top: 10px;
        text-align: center;
    }
    .signature td {
        border-top: 1px solid #000;
        padding-top: 5px;
    }
    .footer {
        text-align: center;
        font-size: 9px;
        margin-top: 8px;
        border-top: 1px solid #000;
        padding-top: 5px;
    }
        table tr td {
        font-size: 10px;  
    }
    strong {
        color: #000;
    }
        .qr-code {
        position: absolute;
        left: 10px;
        top: 10px;
        width: 60px;
    }
</style>



<div class="qr-code">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=EMPID-' . $salary['eid'] . '-SAL-' . $salary['total_salary'] . '" style="width: 100%">
</div>


<div class="slip-number">
    Slip No: HRMS-' . str_pad($slip_no, 6, "0", STR_PAD_LEFT) . '<br>
    Company Reg: 1090403
</div>



        <div class="payslip">
            <div class="header">
                <img src="img/logo.jpg" class="company-logo" style="height:50px;">
                <h5>Communik Marketing Management Est</h5>
                 <div class="company-address">
        M-13, ACICO Business Park, Al Khabaisi, Port Saeed, Dubai, UAE.
    </div>
                <div class="pay-period">
        Payslip for the Month of ' . date('F, Y', strtotime($salary['salary_date'])) . '
    </div>
            
            <!-- Employee Particulars Table -->
    <table class="employee-summary">
        <tr>
            <th colspan="2" style="background:rgb(95, 34, 175); color: white; padding: 8px;">Name & Particulars of Employee</th>
        </tr>
        <tr>
            <td width="50%"><strong>EID:</strong> ' . $salary['eid'] . '</td>
            <td width="50%"><strong>Designation:</strong> ' . $salary['designation'] . '</td>
        </tr>
        <tr>
            <td width="50%"><strong>Full Name:</strong> ' . $salary['full_name'] . '</td>
            <td width="50%"><strong>Deaprtment:</strong> ' . $salary['department'] . '</td>
        </tr>
    </table>

    <!-- Two Column Details Table -->
    <table class="employee-summary">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <table width="100%">
                    <tr><td><strong>Date of Birth:</strong> ' . date('d-m-Y', strtotime($salary['birthday'])) . '</td></tr>
                    <tr><td><strong>Labour ID No.:</strong> MOL-' . $salary['eid'] . '</td></tr>
                    <tr><td><strong>Date of Joining:</strong> ' . date('d-m-Y', strtotime($salary['doj'])) . '</td></tr>
                    <tr><td><strong>Bank Name:</strong> ' . $salary['bank_name'] . '</td></tr>
                    <tr><td><strong>IBAN Number:</strong> ' . $salary['iban'] . '</td></tr>
                </table>
            </td>
            <td width="50%" style="vertical-align: top;">
                <table width="100%">
                    <tr><td><strong>Nominee:</strong> ' . $salary['nominee'] . '</td></tr>
                    <tr><td><strong>MOL ID:</strong> ' . $salary['eid'] . '-UAE</td></tr>
                    <tr><td><strong>Location:</strong> ' . $salary['EmpLoc'] . '</td></tr>
                    <tr><td><strong>Account No:</strong> ' . $salary['account_no'] . '</td></tr>
                    <tr><td><strong>Payment Mode:</strong> ' . $salary['pay_mode'] . '</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Blank Row -->
    <div style="height: 15px;"></div>

    <!-- Earnings & Deductions Table -->
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th width="50%" style="background:rgb(95, 34, 175); color: white;">EARNINGS</th>
            <th width="50%" style="background:rgb(95, 34, 175); color: white;">DEDUCTIONS</th>
        </tr>
        <tr>
            <td>
                <table width="100%" cellspacing="0" cellpadding="3" class="earnings">
                    <tr><th>Sl.No</th><th>Description</th><th align="right">Amount</th></tr>
                    <tr><td>1</td><td>Basic Salary</td><td align="right">AED ' . number_format($salary['base_salary'], 2) . '</td></tr>
                    <tr><td>2</td><td>Housing Allowance</td><td align="right">AED ' . number_format($salary['housing_allowance'], 2) . '</td></tr>
                    <tr><td>3</td><td>Transport Allowance</td><td align="right">AED ' . number_format($salary['transportation_allowance'], 2) . '</td></tr>
                    <tr><td>4</td><td>Performance Bonus</td><td align="right">AED ' . number_format($salary['performance_bonus'], 2) . '</td></tr>
                    <tr><td>5</td><td>Incentive</td><td align="right">AED ' . number_format($salary['incentive'], 2) . '</td></tr>
                    <tr style="border-top: 2px solid #000;"><td colspan="2"><strong>Gross Earnings</strong></td><td align="right"><strong>AED ' . number_format(($salary['base_salary'] + $salary['housing_allowance'] + $salary['transportation_allowance'] + $salary['performance_bonus'] + $salary['incentive']), 2) . '</strong></td></tr>
                </table>
            </td>
            <td>
                <table width="100%" cellspacing="0" cellpadding="3" class="deductions">
                    <tr><th>Sl.No</th><th>Description</th><th align="right">Amount</th></tr>
                    <tr><td>1</td><td>Leave Amount</td><td align="right">AED ' . number_format($salary['leaves_amt'], 2) . '</td></tr>
                    <tr><td>2</td><td>LTO Amount</td><td align="right">AED ' . number_format($salary['lto_amt'], 2) . '</td></tr>
                    <tr><td>3</td><td>Hold</td><td align="right">AED ' . number_format($salary['hold'], 2) . '</td></tr>
                    <tr><td>4</td><td>Advances</td><td align="right">AED ' . number_format($salary['advance_paid'], 2) . '</td></tr>
                    <tr><td>5</td><td>Other Deductions</td><td align="right">AED ' . number_format($salary['others_deduction'], 2) . '</td></tr>
                    <tr><td>6</td><td>Visa Expenses</td><td align="right">AED ' . number_format($salary['visa_expense'], 2) . '</td></tr>
                    <tr style="border-top: 2px solid #000;"><td colspan="2"><strong>Total Deductions</strong></td><td align="right"><strong>AED ' . number_format(($salary['leaves_amt'] + $salary['lto_amt'] + $salary['hold'] + $salary['advance_paid'] + $salary['others_deduction'] + $salary['visa_expense']), 2) . '</strong></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Attendance Details -->
    <table width="100%" cellspacing="0" cellpadding="3" style="margin-top: 5px; font-size: 9px; border: 1px solid #ddd;">
        <tr style="background: #f8f9fa;">
            <td width="50%">Unpaid Leaves: ' . $salary['leaves'] . ' days</td>
            <td width="50%">LTO (Late/Time Off): ' . $salary['lto'] . ' days</td>
        </tr>
        <tr>
            <td colspan="2">Deduction Remarks: ' . $salary['deduction_remarks'] . ' | Payment Mode: ' . $salary['pay_mode'] . '</td>
        </tr>
    </table>

    <div class="net-pay">
        <p>Total Net Payable: AED ' . number_format($salary['total_salary'], 2) . '</p>
      <p>' . numberToWords($salary['total_salary']) . '</p>
    </div>

    <div class="signature">
        <table width="100%">
            <tr>
                <td width="33%">Authorised Signatory</td>
                
                <td width="33%">Managing Director</td>
            </tr>
        </table>
    </div>


    <div class="download-info">
    Downloaded from: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '<br>
    Generated on: ' . date('d-m-Y H:i:s') . '
</div>


    <div class="footer">
        *** This payslip is computer-generated, and the stamps and signatures are also required, possessing legal validity.
    </div>
</div>';

        $mpdf->WriteHTML($html);
        $mpdf->Output('Payslip_' . $salary['eid'] . '_' . date('F_Y', strtotime($salary['salary_date'])) . '.pdf', 'D');
        exit;
    }
}


header("Location: view_salary.php");
exit;
?>