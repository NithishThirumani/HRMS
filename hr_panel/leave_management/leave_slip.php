<?php
// Clear any output buffers first
while (ob_get_level()) {
    ob_end_clean();
}

// Use the same connection as other HR panel files
include('../connection.php');

// Check if mPDF is available
$mpdf_available = false;
try {
    require_once '../../vendor/autoload.php';
    $mpdf_available = true;
} catch (Exception $e) {
    error_log("mPDF not available: " . $e->getMessage());
}

if (!isset($_GET['leave_id'])) {
    die('Leave ID not specified.');
}
$leave_id = intval($_GET['leave_id']);

// Fetch leave details
$query = "SELECT l.*, e.full_name as employee_name, e.eid, e.department_id, e.email as employee_email, d.name as dept_name,
                 r.full_name as recommender_name, r.email as recommender_email, l.recommender_remarks, l.recommender_action_date,
                 a.full_name as approver_name, a.email as approver_email, l.hr_remarks, l.hr_action_date,
                 lh_recommender.recommender_id as hierarchy_recommender_id,
                 lh_approver.approver_id as hierarchy_approver_id
          FROM leaves l
          LEFT JOIN employees e ON l.emp_id = e.id
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN employees r ON l.recommender_id = r.id
          LEFT JOIN employees a ON l.approver_id = a.id
          LEFT JOIN leave_hierarchy lh_recommender ON l.emp_id = lh_recommender.employee_id AND lh_recommender.type = 'recommender'
          LEFT JOIN leave_hierarchy lh_approver ON l.emp_id = lh_approver.employee_id AND lh_approver.type = 'approver'
          WHERE l.id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $leave_id);
$stmt->execute();
$result = $stmt->get_result();
$leave = $result->fetch_assoc();

// Get recommender and approver names from hierarchy if not available in leaves table
if ($leave) {
    // Get recommender name from hierarchy if not available
    if (empty($leave['recommender_name']) && !empty($leave['hierarchy_recommender_id'])) {
        $rec_query = "SELECT full_name, email FROM employees WHERE id = ?";
        $rec_stmt = $con->prepare($rec_query);
        $rec_stmt->bind_param("i", $leave['hierarchy_recommender_id']);
        $rec_stmt->execute();
        $rec_result = $rec_stmt->get_result();
        if ($rec_row = $rec_result->fetch_assoc()) {
            $leave['recommender_name'] = $rec_row['full_name'];
            $leave['recommender_email'] = $rec_row['email'];
        }
        $rec_stmt->close();
    }
    
    // Get approver name from hierarchy if not available
    if (empty($leave['approver_name']) && !empty($leave['hierarchy_approver_id'])) {
        $app_query = "SELECT full_name, email FROM employees WHERE id = ?";
        $app_stmt = $con->prepare($app_query);
        $app_stmt->bind_param("i", $leave['hierarchy_approver_id']);
        $app_stmt->execute();
        $app_result = $app_stmt->get_result();
        if ($app_row = $app_result->fetch_assoc()) {
            $leave['approver_name'] = $app_row['full_name'];
            $leave['approver_email'] = $app_row['email'];
        }
        $app_stmt->close();
    }
}

if (!$leave) {
    die('Leave application not found.');
}

// Format leave slip HTML
$html = ' 
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; font-size: 10px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .company-logo { width: 80px; margin-bottom: 5px; }
        .company-name { font-size: 16px; font-weight: bold; margin: 2px 0; color: #333; }
        .company-address { font-size: 9px; margin: 1px 0; color: #666; }
        .document-title { font-size: 14px; font-weight: bold; margin-top: 10px; color: #333; }
        .section { margin-bottom: 12px; }
        .label { font-weight: bold; width: 120px; display: inline-block; color: #555; font-size: 9px; }
        .value { display: inline-block; color: #333; font-size: 9px; }
        .status { font-weight: bold; font-size: 10px; padding: 2px 6px; border-radius: 3px; }
        .approved { color: #155724; background: #d4edda; }
        .rejected { color: #721c24; background: #f8d7da; }
        .pending { color: #856404; background: #fff3cd; }
        .footer { margin-top: 15px; font-size: 8px; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
        .section-title { font-size: 11px; font-weight: bold; margin-bottom: 8px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 3px; }
        .info-box { border: 1px solid #ddd; padding: 8px; margin-bottom: 10px; background: #f9f9f9; }
        .compact-row { margin-bottom: 3px; }
        .two-column { display: inline-block; width: 48%; vertical-align: top; }
        .three-column { display: inline-block; width: 32%; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../img/clogo.png" class="company-logo" alt="Company Logo">
        <div class="company-name">Communik Marketing Management Est</div>
        <div class="company-address">M-13, ACICO Business Park, Al Khabaisi, Port Saeed, Dubai, UAE</div>
        <div class="company-address">Email: info@communikmarketing.com</div>
        <div class="document-title">Leave Application Slip</div>
        <div style="font-size: 9px; color: #666;">Leave ID: ' . $leave['id'] . '</div>
    </div>
    
    <div class="section">
        <div class="section-title">Employee & Leave Information</div>
        <div class="info-box">
            <div class="two-column">
                <div class="compact-row"><span class="label">Employee:</span> <span class="value">' . htmlspecialchars($leave['employee_name'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Employee ID:</span> <span class="value">' . htmlspecialchars($leave['eid'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Department:</span> <span class="value">' . htmlspecialchars($leave['dept_name'] ?? 'N/A') . '</span></div>
            </div>
            <div class="two-column">
                <div class="compact-row"><span class="label">Leave Type:</span> <span class="value">' . htmlspecialchars($leave['type_of_leave'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Total Days:</span> <span class="value">' . htmlspecialchars($leave['total_days'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Status:</span> <span class="status ' . strtolower($leave['status'] ?? 'pending') . '">' . htmlspecialchars($leave['status'] ?? 'Pending') . '</span></div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Leave Period & Reason</div>
        <div class="info-box">
            <div class="three-column">
                <div class="compact-row"><span class="label">Start Date:</span></div>
                <div class="compact-row"><span class="value">' . htmlspecialchars($leave['start_date'] ?? 'N/A') . '</span></div>
            </div>
            <div class="three-column">
                <div class="compact-row"><span class="label">End Date:</span></div>
                <div class="compact-row"><span class="value">' . htmlspecialchars($leave['end_date'] ?? 'N/A') . '</span></div>
            </div>
            <div class="three-column">
                <div class="compact-row"><span class="label">Applied:</span></div>
                <div class="compact-row"><span class="value">' . htmlspecialchars($leave['applied_at'] ?? 'N/A') . '</span></div>
            </div>
            <div style="clear: both; margin-top: 5px;">
                <div class="compact-row"><span class="label">Reason:</span> <span class="value">' . htmlspecialchars($leave['reason'] ?? 'N/A') . '</span></div>
            </div>
        </div>
    </div>';

// Add approval workflow section if available
if (!empty($leave['recommender_name']) || !empty($leave['approver_name']) || !empty($leave['recommender_status']) || !empty($leave['hr_status'])) {
    $html .= '
    <div class="section">
        <div class="section-title">Approval Workflow</div>
        <div class="info-box">
            <div class="two-column">
                <div class="compact-row"><span class="label">Recommender:</span> <span class="value">' . htmlspecialchars($leave['recommender_name'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Status:</span> <span class="value">' . htmlspecialchars($leave['recommender_status'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Date:</span> <span class="value">' . htmlspecialchars($leave['recommender_action_date'] ?? 'N/A') . '</span></div>
            </div>
            <div class="two-column">
                <div class="compact-row"><span class="label">Approver:</span> <span class="value">' . htmlspecialchars($leave['approver_name'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Status:</span> <span class="value">' . htmlspecialchars($leave['approver_status'] ?? 'N/A') . '</span></div>
                <div class="compact-row"><span class="label">Date:</span> <span class="value">' . htmlspecialchars($leave['approver_action_date'] ?? 'N/A') . '</span></div>
            </div>
        </div>';
    
    // Add remarks if available
    if (!empty($leave['recommender_remarks']) || !empty($leave['approver_remarks'])) {
        $html .= '
        <div class="info-box" style="margin-top: 5px;">
            <div class="compact-row"><span class="label">Recommender Remarks:</span> <span class="value">' . htmlspecialchars($leave['recommender_remarks'] ?? 'N/A') . '</span></div>
            <div class="compact-row"><span class="label">Approver Remarks:</span> <span class="value">' . htmlspecialchars($leave['approver_remarks'] ?? 'N/A') . '</span></div>
        </div>';
    }
    
    $html .= '</div>';
}

$html .= '
    <div class="footer">
        System-generated leave slip | Generated: ' . date('d M Y H:i') . ' | Leave ID: ' . $leave['id'] . '
    </div>
</body>
</html>';

if ($mpdf_available) {
    try {
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Leave_Slip_' . ($leave['eid'] ?? 'EMP') . '_' . $leave['id'] . '.pdf"');
        
        // Generate PDF
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
    } catch (Exception $e) {
        error_log("PDF generation failed: " . $e->getMessage());
        // Fallback to HTML display
        header('Content-Type: text/html; charset=utf-8');
        echo "<h1>PDF Generation Error</h1>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<hr>";
        echo $html;
        exit;
    }
} else {
    // Fallback to HTML display if mPDF is not available
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}