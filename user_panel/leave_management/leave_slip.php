<?php
include('../connection.php');

// mPDF setup
require_once '../../vendor/autoload.php';

if (!isset($_GET['id'])) die('No leave ID provided.');
$leave_id = intval($_GET['id']);

// Enhanced query to include recommender and approver information
$stmt = $con->prepare("SELECT l.*, e.full_name, e.eid, d.name as department,
                              r.full_name as recommender_name, r.email as recommender_email, 
                              l.recommender_remarks, l.recommender_action_date, l.recommender_status,
                              a.full_name as approver_name, a.email as approver_email, 
                              l.approver_remarks, l.approver_action_date, l.approver_status
                       FROM leaves l
                       JOIN employees e ON l.emp_id = e.eid
                       JOIN departments d ON e.department_id = d.id
                       LEFT JOIN employees r ON l.recommender_id = r.id
                       LEFT JOIN employees a ON l.approver_id = a.id
                       WHERE l.id = ?");
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$leave = $stmt->get_result()->fetch_assoc();
if (!$leave) die('Leave not found.');

$html = ' 
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .company-logo { width: 150px; margin-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin: 5px 0; color: #333; }
        .company-address { font-size: 14px; margin: 5px 0; color: #666; }
        .document-title { font-size: 22px; font-weight: bold; margin-top: 20px; color: #333; }
        .section { margin-bottom: 25px; }
        .label { font-weight: bold; width: 200px; display: inline-block; color: #555; }
        .value { display: inline-block; color: #333; }
        .status { font-weight: bold; font-size: 16px; padding: 4px 8px; border-radius: 4px; }
        .approved { color: #155724; background: #d4edda; }
        .rejected { color: #721c24; background: #f8d7da; }
        .pending { color: #856404; background: #fff3cd; }
        .footer { margin-top: 40px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 20px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background: #f9f9f9; }
        .two-column { display: inline-block; width: 48%; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../img/clogo.png" class="company-logo" alt="Company Logo">
        <div class="company-name">Communik Marketing Management Est</div>
        <div class="company-address">M-13, ACICO Business Park, Al Khabaisi, Port Saeed, Dubai, UAE</div>
        <div class="company-address">Email: info@communikmarketing.com</div>
        <div class="document-title">Leave Application Slip</div>
        <div style="font-size: 14px; color: #666;">Leave ID: ' . $leave['id'] . '</div>
    </div>
    <div class="section">
        <div class="section-title">Employee Information</div>
        <div class="info-box">
            <span class="label">Employee Name:</span> <span class="value">' . htmlspecialchars($leave['full_name'] ?? 'N/A') . '</span><br>
            <span class="label">Employee ID:</span> <span class="value">' . htmlspecialchars($leave['eid'] ?? 'N/A') . '</span><br>
            <span class="label">Department:</span> <span class="value">' . htmlspecialchars($leave['department'] ?? 'N/A') . '</span><br>
        </div>
    </div>
    <div class="section">
        <div class="section-title">Leave Details</div>
        <div class="info-box">
            <span class="label">Leave Type:</span> <span class="value">' . htmlspecialchars($leave['type_of_leave'] ?? 'N/A') . '</span><br>
            <span class="label">Start Date:</span> <span class="value">' . htmlspecialchars($leave['start_date'] ?? 'N/A') . '</span><br>
            <span class="label">End Date:</span> <span class="value">' . htmlspecialchars($leave['end_date'] ?? 'N/A') . '</span><br>
            <span class="label">Total Days:</span> <span class="value">' . htmlspecialchars($leave['total_days'] ?? 'N/A') . '</span><br>
            <span class="label">Reason:</span> <span class="value">' . nl2br(htmlspecialchars($leave['reason'] ?? 'N/A')) . '</span><br>
            <span class="label">Applied On:</span> <span class="value">' . htmlspecialchars($leave['applied_at'] ?? 'N/A') . '</span><br>
        </div>
    </div>
    <div class="section">
        <div class="section-title">Application Status</div>
        <div class="info-box">
            <span class="label">Status:</span> <span class="status ' . strtolower($leave['status'] ?? 'pending') . '">' . htmlspecialchars($leave['status'] ?? 'Pending') . '</span><br>
        </div>
    </div>';

// Add approval workflow section if available
if (!empty($leave['recommender_name']) || !empty($leave['approver_name']) || !empty($leave['recommender_status']) || !empty($leave['approver_status'])) {
    $html .= '
    <div class="section">
        <div class="section-title">Approval Workflow</div>
        <div class="info-box">
            <div class="two-column">
                <span class="label">Recommender:</span> <span class="value">' . htmlspecialchars($leave['recommender_name'] ?? 'N/A') . '</span><br>
                <span class="label">Status:</span> <span class="value">' . htmlspecialchars($leave['recommender_status'] ?? 'N/A') . '</span><br>
                <span class="label">Date:</span> <span class="value">' . htmlspecialchars($leave['recommender_action_date'] ?? 'N/A') . '</span><br>
            </div>
            <div class="two-column">
                <span class="label">Approver:</span> <span class="value">' . htmlspecialchars($leave['approver_name'] ?? 'N/A') . '</span><br>
                <span class="label">Status:</span> <span class="value">' . htmlspecialchars($leave['approver_status'] ?? 'N/A') . '</span><br>
                <span class="label">Date:</span> <span class="value">' . htmlspecialchars($leave['approver_action_date'] ?? 'N/A') . '</span><br>
            </div>
        </div>';
    
    // Add remarks if available
    if (!empty($leave['recommender_remarks']) || !empty($leave['approver_remarks'])) {
        $html .= '
        <div class="info-box">
            <span class="label">Recommender Remarks:</span> <span class="value">' . htmlspecialchars($leave['recommender_remarks'] ?? 'N/A') . '</span><br>
            <span class="label">Approver Remarks:</span> <span class="value">' . htmlspecialchars($leave['approver_remarks'] ?? 'N/A') . '</span><br>
        </div>';
    }
    
    $html .= '</div>';
}

$html .= '
    <div class="footer">
        This is a system-generated leave slip.<br>
        Generated on ' . date('d M Y H:i') . '<br>
        Leave ID: ' . $leave['id'] . '
    </div>
</body>
</html>';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15
]);
$mpdf->WriteHTML($html);
$filename = 'Leave_Slip_' . ($leave['eid'] ?? 'EMP') . '_' . $leave['id'] . '.pdf';
$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
exit;