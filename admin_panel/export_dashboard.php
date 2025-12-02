<?php
require('session.php');
require('connection.php');
require_once __DIR__ . '/vendor/autoload.php';

// Fetch all required data first
// Total employees
$query = "SELECT COUNT(*) AS total_employees FROM employees";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_employees = $row['total_employees'];

// Employees on leave today
$query = "SELECT COUNT(*) as total_on_leave FROM leaves 
          WHERE status='approved' AND DATE(start_date) <= CURDATE() 
          AND DATE(end_date) >= CURDATE()";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_on_leave = $row['total_on_leave'];

// Birthdays this month
$query = "SELECT COUNT(*) as birthdays_count 
          FROM employees 
          WHERE MONTH(birthday) = MONTH(CURRENT_DATE())";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$birthdays_count = $row['birthdays_count'];

// Work anniversaries
$query = "SELECT COUNT(*) as anniversaries_count 
          FROM employees 
          WHERE MONTH(doj) = MONTH(CURRENT_DATE())";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$anniversaries_count = $row['anniversaries_count'];

// Visa expiring
$query = "SELECT COUNT(*) as visa_expiring 
          FROM employees 
          WHERE visa_expiry_date BETWEEN CURDATE() 
          AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$visa_expiring = $row['visa_expiring'];

// Labor cards expiring
$query = "SELECT COUNT(*) as labor_cards_expiring 
          FROM employees 
          WHERE labour_card_end_date BETWEEN CURDATE() 
          AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$labor_cards_expiring = $row['labor_cards_expiring'];

// Department distribution
$deptQuery = "SELECT d.name, COUNT(e.id) as count 
              FROM departments d 
              LEFT JOIN employees e ON d.id = e.department_id 
              GROUP BY d.id";
$deptResult = mysqli_query($con, $deptQuery);

// Create new mPDF instance
$mpdf = new \Mpdf\Mpdf();

// Add custom styling
// Update the stylesheet with modern colors and design
$stylesheet = '
<style>
    body { 
        font-family: "Helvetica", sans-serif;
        line-height: 1.4;
        margin: 0;
        padding: 0;
    }
    .page-container {
        padding: 20px;
    }
    .company-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        border-bottom: 2px solid #1a237e;
        padding-bottom: 15px;
    }
    .company-logo {
        font-size: 28px;
        font-weight: bold;
        color: #1a237e;
    }
    .company-details {
        text-align: right;
        font-size: 11px;
        color: #555;
    }
    .report-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        color: #1a237e;
        margin: 20px 0;
        padding: 10px;
        background: #e8eaf6;
        border-radius: 5px;
    }
    .content-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    .stats-box {
        flex: 1;
        min-width: 48%;
        padding: 12px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .box-title {
        font-size: 14px;
        font-weight: bold;
        color: #1a237e;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e0e0e0;
    }
    .stats-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .stat-label {
        font-size: 12px;
        color: #555;
    }
    .stat-value {
        font-size: 14px;
        font-weight: bold;
        color: #1a237e;
    }
    .alert-value {
        color: #e65100;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th {
        background: #1a237e;
        color: white;
        font-size: 12px;
        text-align: left;
        padding: 8px;
    }
    td {
        padding: 8px;
        font-size: 12px;
        border-bottom: 1px solid #e0e0e0;
    }
    tr:nth-child(even) {
        background: #f5f5f5;
    }
    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 10px;
        color: #777;
        border-top: 1px solid #e0e0e0;
        padding-top: 10px;
    }
</style>';

$mpdf->WriteHTML($stylesheet);

$html = '<div class="page-container">
    <div class="company-header">
        <div class="company-logo">HR Matrix</div>
        <div class="company-details">
            123 Business District, Dubai, UAE<br>
            Tel: +971 4 123 4567 | Email: info@san-solutions.in
        </div>
    </div>
    
    <div class="report-title">Executive Dashboard Report - ' . date('F d, Y') . '</div>
    
    <div class="content-grid">
        <div class="stats-box">
            <div class="box-title">Employee Statistics</div>
            <div class="stats-row">
                <span class="stat-label">Total Employees:</span>
                <span class="stat-value">' . $total_employees . '</span>
            </div>
            <div class="stats-row">
                <span class="stat-label">On Leave Today:</span>
                <span class="stat-value">' . $total_on_leave . '</span>
            </div>
            <div class="stats-row">
                <span class="stat-label">Birthdays This Month:</span>
                <span class="stat-value">' . $birthdays_count . '</span>
            </div>
            <div class="stats-row">
                <span class="stat-label">Work Anniversaries:</span>
                <span class="stat-value">' . $anniversaries_count . '</span>
            </div>
        </div>
        
        <div class="stats-box">
            <div class="box-title">Critical Alerts</div>
            <div class="stats-row">
                <span class="stat-label">Visa Expiring Soon:</span>
                <span class="stat-value alert-value">' . $visa_expiring . '</span>
            </div>
            <div class="stats-row">
                <span class="stat-label">Labor Cards Expiring:</span>
                <span class="stat-value alert-value">' . $labor_cards_expiring . '</span>
            </div>
        </div>
    </div>
    
    <div class="stats-box" style="width: 100%;">
        <div class="box-title">Department Distribution</div>
        <table>
            <tr>
                <th>Department</th>
                <th>Employee Count</th>
                <th>Percentage</th>
            </tr>';

mysqli_data_seek($deptResult, 0);
while ($row = mysqli_fetch_assoc($deptResult)) {
    $percentage = ($total_employees > 0) ? round(($row['count'] / $total_employees) * 100, 1) : 0;
    $html .= '<tr>
        <td>' . $row['name'] . '</td>
        <td>' . $row['count'] . '</td>
        <td>' . $percentage . '%</td>
    </tr>';
}

$html .= '</table>
    </div>
    
    <div class="footer">
        This report is generated automatically by HR Matrix System | Confidential
    </div>
</div>';

// Write HTML to PDF
$mpdf->WriteHTML($html);

// Output PDF
$mpdf->Output('Executive_Dashboard_' . date('Y-m-d') . '.pdf', 'D');
?>