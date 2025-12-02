<?php
session_start();
require_once('../../connection.php');
require_once '../../vendor/autoload.php';
require_once('../../classes/EmployeeAppraisal.php');
require_once('../../classes/Employee.php');

if (!isset($_GET['appraisal_id']) || !isset($_SESSION['user_id'])) {
    header('Location: view_appraisals.php');
    exit();
}

$appraisal_id = $_GET['appraisal_id'];
$appraisalObj = new EmployeeAppraisal();
$employeeObj = new Employee();

$appraisal = $appraisalObj->getAppraisalById($appraisal_id);
$employee = $employeeObj->getEmployeeById($appraisal['employee_id']);

$mpdf = new \Mpdf\Mpdf();

// Add Header
$mpdf->WriteHTML('<h2 style="text-align: center;">Employee Appraisal Report</h2><br>');

// Employee Details
$html = '
<h3>Employee Information</h3>
<table style="width: 100%;">
    <tr><td style="width: 150px;"><strong>Name:</strong></td><td>' . $employee['full_name'] . '</td></tr>
    <tr><td><strong>Department:</strong></td><td>' . $employee['department'] . '</td></tr>
    <tr><td><strong>Position:</strong></td><td>' . $employee['role'] . '</td></tr>
</table><br>

<h3>Appraisal Period</h3>
<table style="width: 100%;">
    <tr><td style="width: 150px;"><strong>Start Date:</strong></td><td>' . date('d/m/Y', strtotime($appraisal['start_date'])) . '</td></tr>
    <tr><td><strong>End Date:</strong></td><td>' . date('d/m/Y', strtotime($appraisal['end_date'])) . '</td></tr>
</table><br>

<h3>Performance Evaluation</h3>';

// Performance Criteria
if ($appraisal['criteria']) {
    $criteria = json_decode($appraisal['criteria'], true);
    foreach ($criteria as $category => $items) {
        $html .= '<h4>' . ucfirst($category) . '</h4>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Criteria</th>
                <th style="border: 1px solid #ddd; padding: 8px; width: 100px;">Rating</th>
            </tr>';
        
        foreach ($items as $item => $rating) {
            $html .= '<tr>
                <td style="border: 1px solid #ddd; padding: 8px;">' . $item . '</td>
                <td style="border: 1px solid #ddd; padding: 8px;">' . $rating . '/5</td>
            </tr>';
        }
        $html .= '</table>';
    }
}

// Comments
$html .= '<h3>Comments</h3>
<div style="margin-bottom: 10px;">
    <strong>Self Assessment:</strong><br>
    <p style="padding: 10px; background-color: #f9f9f9;">' . ($appraisal['self_assessment'] ?? 'Not provided') . '</p>
</div>

<div style="margin-bottom: 10px;">
    <strong>Manager Comments:</strong><br>
    <p style="padding: 10px; background-color: #f9f9f9;">' . ($appraisal['manager_comments'] ?? 'Not provided') . '</p>
</div>

<div style="margin-bottom: 10px;">
    <strong>HR Comments:</strong><br>
    <p style="padding: 10px; background-color: #f9f9f9;">' . ($appraisal['hr_comments'] ?? 'Not provided') . '</p>
</div>

<h3>Final Rating: ' . $appraisal['final_rating'] . '/5</h3>
<h3>Status: ' . $appraisal['status'] . '</h3>

<div style="margin-top: 50px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 33%; text-align: center; border-top: 1px solid #000;">Employee Signature</td>
            <td style="width: 33%; text-align: center; border-top: 1px solid #000;">Manager Signature</td>
            <td style="width: 33%; text-align: center; border-top: 1px solid #000;">HR Signature</td>
        </tr>
    </table>
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Appraisal_Report_' . $employee['full_name'] . '.pdf', 'D');
?>