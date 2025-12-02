<?php
include('../connection.php');
if (!isset($_GET['id'])) die('No leave ID provided.');
$leave_id = intval($_GET['id']);

$stmt = $con->prepare("SELECT l.*, e.full_name, e.eid, d.name as department
                       FROM leaves l
                       JOIN employees e ON l.emp_id = e.eid
                       JOIN departments d ON e.department_id = d.id
                       WHERE l.id = ?");
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$leave = $stmt->get_result()->fetch_assoc();
if (!$leave) die('Leave not found.');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Leave Slip - #<?php echo htmlspecialchars($leave_id); ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f8fb; margin: 0; }
        .slip {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 40px;
            max-width: 600px;
            margin: 40px auto;
            border-top: 8px solid #4e73df;
            position: relative;
        }
        .logo {
            width: 120px;
            display: block;
            margin: 0 auto 16px auto;
        }
        h2 {
            text-align: center;
            color: #4e73df;
            margin-bottom: 8px;
        }
        .leave-id {
            text-align: center;
            color: #888;
            font-size: 1.1em;
            margin-bottom: 24px;
        }
        .row { margin-bottom: 14px; }
        .label {
            font-weight: 600;
            color: #2e59d9;
            width: 160px;
            display: inline-block;
        }
        .value { display: inline-block; color: #222; }
        .status {
            font-weight: bold;
            padding: 4px 16px;
            border-radius: 12px;
            color: #fff;
            display: inline-block;
        }
        .status.Approved { background: #1cc88a; }
        .status.Pending { background: #f6c23e; color: #222; }
        .status.Rejected { background: #e74a3b; }
        .print-btn {
            margin: 32px auto 0 auto;
            display: block;
            background: #4e73df;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 32px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .print-btn:hover { background: #224abe; }
        @media print {
            .print-btn { display: none; }
            .slip { box-shadow: none; border-top: 4px solid #4e73df; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <div class="slip">
        <img src="../../img/clogo.png" alt="Company Logo" class="logo">
        <h2>Leave Slip</h2>
        <div class="leave-id">Leave ID: <strong>#<?php echo htmlspecialchars($leave_id); ?></strong></div>
        <div class="row"><span class="label">Name:</span> <span class="value"><?php echo htmlspecialchars($leave['full_name']); ?></span></div>
        <div class="row"><span class="label">EID:</span> <span class="value"><?php echo htmlspecialchars($leave['eid']); ?></span></div>
        <div class="row"><span class="label">Department:</span> <span class="value"><?php echo htmlspecialchars($leave['department']); ?></span></div>
        <div class="row"><span class="label">Leave Type:</span> <span class="value"><?php echo htmlspecialchars($leave['type_of_leave']); ?></span></div>
        <div class="row"><span class="label">Start Date:</span> <span class="value"><?php echo htmlspecialchars($leave['start_date']); ?></span></div>
        <div class="row"><span class="label">End Date:</span> <span class="value"><?php echo htmlspecialchars($leave['end_date']); ?></span></div>
        <div class="row"><span class="label">Total Days:</span> <span class="value"><?php echo htmlspecialchars($leave['total_days']); ?></span></div>
        <div class="row"><span class="label">Reason:</span> <span class="value"><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></span></div>
        <div class="row"><span class="label">Status:</span> <span class="value status <?php echo htmlspecialchars($leave['status']); ?>"><?php echo htmlspecialchars($leave['status']); ?></span></div>
        <button class="print-btn" onclick="window.print()">Print</button>
    </div>
</body>
</html>