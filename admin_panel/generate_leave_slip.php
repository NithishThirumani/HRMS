<?php
include('session.php');
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['leave_id'])) {
    die("Leave ID not provided");
}

$leave_id = $_GET['leave_id'];

// Add security check to ensure the user has permission to view this leave slip
// Use correct admin panel session variables
$user_email = $_SESSION['email'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Query to get leave data with employee and department information
$query = "SELECT l.*, e.full_name, d.name as department 
          FROM leaves l 
          JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id 
          WHERE l.id = ?";

// Admin can view all leave slips, so no additional restriction needed
// The session.php already ensures only admin users can access this file

try {
    // Initialize mPDF
    $mpdf = new \Mpdf\Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
    ]);

    // Get the data from database
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Leave not found");
    }

    $leave = $result->fetch_assoc();

    // Generate unique leave slip number
    $leave_slip_number = 'LS-' . date('Ymd') . '-' . str_pad($leave_id, 4, '0', STR_PAD_LEFT);

    // Build HTML content with simplified structure
    $html = '
    <html>
    <head>
    <style>
        body { font-family: arial; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .hrmatrix-title { 
            font-size: 20px; 
            font-weight: bold; 
            color: #1a237e;
            margin-bottom: 5px;
        }
        .subtitle { 
            font-size: 14px; 
            color: #303f9f; 
            margin-bottom: 20px;
        }
        .title { 
            font-size: 14px; 
            font-weight: bold; 
            text-align: center; 
            margin: 20px 0;
            background-color: #e8eaf6;
            padding: 10px;
        }
        .ref-number { 
            text-align: right; 
            font-size: 10px;
            color: #555;
        }
        .section-title { 
            font-size: 12px; 
            font-weight: bold; 
            margin-top: 15px;
            background-color: #c5cae9;
            padding: 5px 10px;
        }
        .details-table { 
            width: 100%; 
            margin: 10px 0;
            border-collapse: collapse;
            border: 1px solid #9fa8da;
        }
        .details-table td { 
            padding: 6px;
            border: 1px solid #9fa8da;
        }
        .label { 
            font-weight: bold; 
            width: 120px;
            background-color: #e8eaf6;
        }
        .leave-summary {
            margin: 20px 0;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .leave-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .leave-summary-table th, .leave-summary-table td {
            border: 1px solid #9fa8da;
            padding: 6px;
            text-align: center;
        }
        .leave-summary-table th {
            background-color: #c5cae9;
        }
        .approval-section { margin-top: 30px; text-align: center; }
        .signatures { 
            margin-top: 30px;
            width: 100%;
        }
        .signature-box { 
            width: 30%; 
            display: inline-block; 
            text-align: center;
            margin: 0 1%;
        }
        .signature-line { 
            border-top: 1px solid #7986cb; 
            width: 80%; 
            margin: 20px auto 5px auto;
        }
        .status-approved { color: #2e7d32; }
        .status-pending { color: #f57c00; }
        .status-rejected { color: #c62828; }
    </style>
    </head>
    <body>
        <div class="header">
            <div class="hrmatrix-title">HRMatrix</div>
            <div class="subtitle">Leave Management System</div>
        </div>
        
        <div class="title">LEAVE APPLICATION SLIP</div>
        
        <div class="ref-number">
            Reference No: ' . $leave_slip_number . '<br>
            Date: ' . date('d/m/Y') . '
        </div>

        <div class="leave-summary">
            <div class="section-title">LEAVE BALANCE SUMMARY</div>
            <table class="leave-summary-table">
                <tr>
                    <th>Leave Type</th>
                    <th>Total</th>
                    <th>Used</th>
                    <th>Balance</th>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($leave['type_of_leave']) . '</td>
                    <td>12</td>
                    <td>' . htmlspecialchars($leave['total_days']) . '</td>
                    <td>' . (12 - intval($leave['total_days'])) . '</td>
                </tr>
            </table>
        </div>

        <div class="section-title">EMPLOYEE DETAILS</div>
        <table class="details-table">
            <tr>
                <td class="label">Employee Name:</td>
                <td>' . htmlspecialchars($leave['full_name']) . '</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>' . htmlspecialchars($leave['department'] ?? 'N/A') . '</td>
            </tr>
        </table>

        <div class="section-title">LEAVE DETAILS</div>
        <table class="details-table">
            <tr>
                <td class="label">Leave Type:</td>
                <td>' . htmlspecialchars($leave['type_of_leave']) . '</td>
            </tr>
            <tr>
                <td class="label">From Date:</td>
                <td>' . date('d/m/Y', strtotime($leave['start_date'])) . '</td>
                <td class="label">To Date:</td>
                <td>' . date('d/m/Y', strtotime($leave['end_date'])) . '</td>
            </tr>
            <tr>
                <td class="label">Total Days:</td>
                <td>' . htmlspecialchars($leave['total_days']) . ' days</td>
            </tr>
            <tr>
                <td class="label">Reason:</td>
                <td colspan="3">' . htmlspecialchars($leave['reason']) . '</td>
            </tr>
        </table>

        <div class="approval-section">
            <div class="section-title">APPROVAL STATUS</div>
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    HOD Status<br>
                    <span class="status-' . strtolower($leave['status'] ?? 'pending') . '">' . ucfirst($leave['status'] ?? 'Pending') . '</span><br>
                    ' . (isset($leave['hod_action_date']) && $leave['hod_action_date'] ? date('d/m/Y', strtotime($leave['hod_action_date'])) : '') . '
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    HR Status<br>
                    <span class="status-' . strtolower($leave['hr_status'] ?? 'pending') . '">' . ucfirst($leave['hr_status'] ?? 'Pending') . '</span><br>
                    ' . (isset($leave['hr_action_date']) && $leave['hr_action_date'] ? date('d/m/Y', strtotime($leave['hr_action_date'])) : '') . '
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    Admin Status<br>
                    <span class="status-' . strtolower($leave['status'] ?? 'pending') . '">' . ucfirst($leave['status'] ?? 'Pending') . '</span><br>
                    ' . (isset($leave['applied_at']) ? date('d/m/Y', strtotime($leave['applied_at'])) : '') . '
                </div>
            </div>
        </div>
    </body>
    </html>';

    // Write PDF
    $mpdf->WriteHTML($html);

    // Output PDF
    $mpdf->Output('Leave_Slip_' . $leave_slip_number . '.pdf', 'I');
    
} catch (Exception $e) {
    // Log the error
    error_log("PDF Generation Error: " . $e->getMessage());
    
    // Display a user-friendly error message
    echo "Error generating PDF. Please try again or contact support.";
    echo "<br>Error details: " . $e->getMessage();
}
?>