<?php
include('session.php');
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['leave_id'])) {
    die("Leave ID not provided");
}

$leave_id = $_GET['leave_id'];

// Add security check to ensure the user has permission to view this leave slip
// Fix session variable
$user_id = $_SESSION['login_user']; // Changed from $_SESSION['id']
$user_role = $_SESSION['role'];

// Update query to match the table structure
$query = "SELECT l.*, e.full_name, e.department 
          FROM leaves l 
          JOIN employees e ON l.emp_id = e.id 
          WHERE l.id = ?";

if ($user_role == 'employee') {
    // Employees can only view their own leave slips
    $query .= " AND l.emp_id = " . $_SESSION['login_user'];
}

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

// Build HTML content
$html = '
<html>
<head>
<style>
    body { font-family: arial; }
    .header { text-align: center; margin-bottom: 20px; }
    .hrmatrix-title { 
        font-size: 24px; 
        font-weight: bold; 
        color: #1a237e;
        margin-bottom: 5px;
    }
    .subtitle { 
        font-size: 18px; 
        color: #303f9f; 
        margin-bottom: 20px;
    }
    .title { 
        font-size: 16px; 
        font-weight: bold; 
        text-align: center; 
        margin: 20px 0;
        background-color: #e8eaf6;
        padding: 10px;
        border-radius: 5px;
    }
    .ref-number { 
        text-align: right; 
        font-size: 12px;
        color: #555;
    }
    .section-title { 
        font-size: 14px; 
        font-weight: bold; 
        margin-top: 15px;
        background-color: #c5cae9;
        padding: 5px 10px;
        border-radius: 3px;
    }
    .details-table { 
        width: 100%; 
        margin: 10px 0;
        border-collapse: collapse;
        border: 1px solid #9fa8da;
    }
    .details-table td { 
        padding: 8px;
        border: 1px solid #9fa8da;
    }
    .label { 
        font-weight: bold; 
        width: 150px;
        background-color: #e8eaf6;
    }
    .leave-summary {
        margin: 20px 0;
        padding: 10px;
        background-color: #f5f5f5;
        border-radius: 5px;
    }
    .leave-summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .leave-summary-table th, .leave-summary-table td {
        border: 1px solid #9fa8da;
        padding: 8px;
        text-align: center;
    }
    .leave-summary-table th {
        background-color: #c5cae9;
    }
    .approval-section { margin-top: 30px; text-align: center; }
    .signatures { 
        margin-top: 50px;
        display: table;
        width: 100%;
    }
    .signature-box { 
        width: 33%; 
        float: left; 
        text-align: center;
    }
    .signature-line { 
        border-top: 2px solid #7986cb; 
        width: 80%; 
        margin: 40px auto 5px auto;
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
                <td>' . $leave['type_of_leave'] . '</td>
                <td>12</td>
                <td>' . $leave['total_days'] . '</td>
                <td>' . (12 - $leave['total_days']) . '</td>
            </tr>
        </table>
    </div>

    <div class="section-title">EMPLOYEE DETAILS</div>
    <table class="details-table">
        <tr>
            <td class="label">Employee Name:</td>
            <td>' . $leave['full_name'] . '</td>
        </tr>
        <tr>
            <td class="label">Department:</td>
            <td>' . $leave['department'] . '</td>
        </tr>
    </table>

    <div class="section-title">LEAVE DETAILS</div>
    <table class="details-table">
        <tr>
            <td class="label">Leave Type:</td>
            <td>' . $leave['type_of_leave'] . '</td>
        </tr>
        <tr>
            <td class="label">From Date:</td>
            <td>' . date('d/m/Y', strtotime($leave['start_date'])) . '</td>
            <td class="label">To Date:</td>
            <td>' . date('d/m/Y', strtotime($leave['end_date'])) . '</td>
        </tr>
        <tr>
            <td class="label">Total Days:</td>
            <td>' . $leave['total_days'] . ' days</td>
        </tr>
        <tr>
            <td class="label">Reason:</td>
            <td colspan="3">' . $leave['reason'] . '</td>
        </tr>
    </table>

    <div class="approval-section">
        <div class="section-title">APPROVAL STATUS</div>
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                HOD Status<br>
                <span class="status-' . strtolower($leave['status']) . '">' . ucfirst($leave['status']) . '</span><br>
                ' . ($leave['action_date'] ? date('d/m/Y', strtotime($leave['action_date'])) : '') . '
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                HR Status<br>
                <span class="status-' . strtolower($leave['hr_status']) . '">' . ucfirst($leave['hr_status']) . '</span><br>
                ' . ($leave['hr_action_date'] ? date('d/m/Y', strtotime($leave['hr_action_date'])) : '') . '
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                Admin Status<br>
                <span class="status-' . strtolower($leave['admin_status']) . '">' . ucfirst($leave['admin_status']) . '</span><br>
                ' . ($leave['admin_action_date'] ? date('d/m/Y', strtotime($leave['admin_action_date'])) : '') . '
            </div>
        </div>
    </div>
</body>
</html>';

// Write PDF
$mpdf->WriteHTML($html);

// Output PDF
$mpdf->Output('Leave_Slip_' . $leave_slip_number . '.pdf', 'I');
?>