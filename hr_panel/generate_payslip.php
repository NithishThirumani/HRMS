<?php
include('session.php');
include('connection.php');
require_once __DIR__ . '/../vendor/autoload.php';


use Mpdf\Mpdf;

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

    // Add decimal part
    if ($decnum > 0) {
        $words .= " and " . $decnum . "/100";
    }

    return trim($words) . " Dirhams Only";
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
           employees.department,
           employees.bank_name,
           employees.account_no,
           employees.iban,
           employees.nominee,
           employees.EmpLoc
    FROM sal 
    INNER JOIN employees ON sal.emp_id = employees.eid 
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

        // Adjust watermark settings for better visibility
        $mpdf->SetWatermarkImage(
            'img/translogo.png',    // image path
            0.30,                   // opacity increased slightly
            array(200, 200),        // size adjusted
            array(5, 60)          // position adjusted
        );

        // Set watermark image  
        $mpdf->showWatermarkImage = true;


        // Calculate leave balance
        $leave_balance = $salary['calculated_days'] - $salary['present_days'] - $salary['leaves'];




        $html = '
      
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
        padding: 10px;
        font-family: Arial, sans-serif;
    }
    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding: 5px;
        margin-bottom: 10px;
    }
    .company-address {
        text-align: center;
        margin-bottom: 10px;
        font-size: 14px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
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
        margin-bottom: 15px;
    }
    .employee-summary td {
        padding: 4px;
        border: 1px solid #000;
    }
    .salary-details {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
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
        padding: 10px;
        margin: 15px 0;
        text-align: center;
        font-weight: bold;
    }
    .signature {
        margin-top: 20px;
        text-align: center;
    }
    .signature td {
        border-top: 1px solid #000;
        padding-top: 5px;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        margin-top: 15px;
        border-top: 1px solid #000;
        padding-top: 10px;
    }
    strong {
        color: #000;
    }
</style>



<div class="slip-number">
    Slip No: HRMS-' . str_pad($slip_no, 6, "0", STR_PAD_LEFT) . '<br>
    Company Reg: 123456789
</div>



        <div class="payslip">
            <div class="header">
                <img src="img/logo.jpg" class="company-logo">
                <h4>Enhancing Productivity, Streamlining Processes</h4>
                 <div class="company-address">
        M03 Sarah Building Al Garhoud Dubai.
    </div>
                <div class="pay-period">
        Payslip for the Month of ' . date('F, Y', strtotime($salary['salary_date'])) . '
    </div>
            
           <table class="employee-summary">
        <tr>
            <td><strong>Employee Name:</strong> ' . $salary['full_name'] . '</td>
            <td><strong>Bank Name:</strong> ' . $salary['bank_name'] . '</td>
        </tr>
        <tr>
            <td><strong>Employee Number:</strong> ' . $salary['eid'] . '</td>
            <td><strong>Days Paid:</strong> ' . $salary['present_days'] . '</td>
        </tr>
        <tr>
            <td><strong>Designation:</strong> ' . $salary['department'] . '</td>
            <td><strong>LOP Days:</strong> ' . $salary['leaves'] . '</td>
        </tr>
        <tr>
            <td><strong>Date of Joining:</strong> ' . date('d-m-Y', strtotime($salary['doj'])) . '</td>
            <td><strong>Account No:</strong> ' . $salary['account_no'] . '</td>
        </tr>
        <tr>
            <td><strong>Pay Period:</strong> ' . date('M-y', strtotime($salary['salary_date'])) . '</td>
            <td><strong>IBAN:</strong> ' . $salary['iban'] . '</td>
        </tr>
        <tr>
            <td><strong>Location:</strong> ' . $salary['EmpLoc'] . '</td>
            <td><strong>Salary Type:</strong> WPS-Bank Transfer</td>
        </tr>
        <tr>
    <td colspan="2"><strong>Nominee:</strong> ' . $salary['nominee'] . '</td>
</tr>
    </table>

    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th width="50%" style="background: #4CAF50; color: white;">EARNINGS</th>
            <th width="50%" style="background: #f44336; color: white;">DEDUCTIONS</th>
        </tr>
        <tr>
            <td>
                <table width="100%" cellspacing="0" cellpadding="3" class="earnings">

                <tr>
                    <th>Sl.No</th>
                    <th>Description</th>
                    <th align="right">Amount</th>
                </tr>
                   <tr>
                    <td>1</td>
                    <td>Basic Salary</td>
                    <td align="right">AED ' . number_format($salary['base_salary'], 2) . '</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Housing Allowance</td>
                    <td align="right">AED ' . number_format($salary['base_salary'] * 0.25, 2) . '</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Transportation Allowance</td>
                    <td align="right">AED ' . number_format($salary['base_salary'] * 0.15, 2) . '</td>
                </tr>
                <tr style="border-top: 2px solid #000;">
                    <td colspan="2"><strong>Total Earnings</strong></td>
                    <td align="right"><strong>AED ' . number_format(($salary['base_salary'] + ($salary['base_salary'] * 0.25) + ($salary['base_salary'] * 0.15)), 2) . '</strong></td>
                </tr>
                </table>
            </td>
            <td>
                <table width="100%" cellspacing="0" cellpadding="3" class="deductions">
                <tr>
                    <th>Sl.No</th>
                    <th>Description</th>
                    <th align="right">Amount</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Leave Deductions</td>
                    <td align="right">AED ' . number_format($salary['deductions'], 2) . '</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Leaves</td>
                    <td align="right">' . $salary['leaves'] . ' Days</td>
                </tr>
                <tr style="border-top: 2px solid #000;">
                    <td colspan="2"><strong>Total Deductions</strong></td>
                    <td align="right"><strong>AED ' . number_format($salary['deductions'], 2) . '</strong></td>
                </tr>
                </table>
            </td>
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
                <td width="34%"><img src="img/signa.jpg" width="100"></td>
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