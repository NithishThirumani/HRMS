<?php
include('session.php'); 
include('connection.php');
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

// Get distinct months for tabbed view
$months_result = $con->query("SELECT DISTINCT DATE_FORMAT(salary_date, '%Y-%m') AS salary_month FROM sal ORDER BY salary_month DESC");
$months = [];
while ($row = $months_result->fetch_assoc()) {
    $months[] = $row['salary_month'];
}

// Fetch salary data grouped by month
$salary_data = [];
foreach ($months as $month) {
    $stmt = $con->prepare("SELECT sal.*, employees.eid, employees.full_name, DATE_FORMAT(salary_date, '%Y-%m') AS salary_month FROM sal INNER JOIN employees ON sal.emp_id = employees.eid WHERE DATE_FORMAT(salary_date, '%Y-%m') = ? ORDER BY salary_date DESC");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $salary_data[$month][] = $row;
    }
}
// Sample months array
$months = ['2025-03', '2025-02', '2025-01']; // Example data from database

// Convert 'YYYY-MM' to 'Mon' format

function formatMonth($date) {
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
    fputcsv($output, ['ID', 'Employee Name', 'Employee ID', 'Base Salary', 'Calculated Days', 'Present Days', 'Leaves', 'LTO', 'Deductions', 'Deduction Remarks', 'Performance Points', 'Incentive', 'Payable Salary', 'Visa Expense', 'Total Salary', 'Salary Date', 'Leave Balance', 'Bonus']);
    if (isset($salary_data[$selected_month])) {
        foreach ($salary_data[$selected_month] as $row) {
            $leave_balance = $row['calculated_days'] - $row['present_days'] - $row['leaves'];
            fputcsv($output, [$row['id'], $row['full_name'], $row['emp_id'], $row['base_salary'], $row['calculated_days'], $row['present_days'], $row['leaves'], $row['lto'], $row['deductions'], $row['deduction_remarks'], $row['performance_points'], $row['incentive'], $row['payable_salary'], $row['visa_expense'], $row['total_salary'], $row['salary_date'], $leave_balance, $row['bonus'] ?? '']);
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

    if (isset($salary_data[$selected_month])) {
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th>ID</th><th>Employee Name</th><th>Employee ID</th><th>Base Salary</th><th>Calculated Days</th><th>Present Days</th><th>Leaves</th><th>LTO</th><th>Deductions</th><th>Deduction Remarks</th><th>Performance Points</th><th>Incentive</th><th>Payable Salary</th><th>Visa Expense</th><th>Total Salary</th><th>Salary Date</th><th>Leave Balance</th><th>Bonus</th></tr>';

        foreach ($salary_data[$selected_month] as $row) {
            $leave_balance = $row['calculated_days'] - $row['present_days'] - $row['leaves'];
            $html .= '<tr><td>' . $row['id'] . '</td><td>' . $row['full_name'] . '</td><td>' . $row['emp_id'] . '</td><td>' . $row['base_salary'] . '</td><td>' . $row['calculated_days'] . '</td><td>' . $row['present_days'] . '</td><td>' . $row['leaves'] . '</td><td>' . $row['lto'] . '</td><td>' . $row['deductions'] . '</td><td>' . $row['deduction_remarks'] . '</td><td>' . $row['performance_points'] . '</td><td>' . $row['incentive'] . '</td><td>' . $row['payable_salary'] . '</td><td>' . $row['visa_expense'] . '</td><td>' . $row['total_salary'] . '</td><td>' . $row['salary_date'] . '</td><td>' . $leave_balance . '</td><td>' . ($row['bonus'] ?? '') . '</td></tr>';
        }
        $html .= '</table>';
    }

    $mpdf->WriteHTML($html);
    $mpdf->Output('salary_data_' . $selected_month . '.pdf', 'D');
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
            font-family: Arial, sans-serif;
        }
        .tabs {
            display: flex;
            cursor: pointer;
        }
        .tab {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-bottom: none;
        }
        .tab.active {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .tab-content {
            border: 1px solid #ccc;
            padding: 20px;
        }

.salary-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.salary-table th {
    padding: 4px 6px;
    text-align: left;
    white-space: nowrap;
    background-color: #1cc88a; /* Bootstrap success color (green) */
    /* Or use #4e73df for blue */
    color: #fff; /* White text for better contrast */
}
.salary-table td {
    padding: 4px 6px;
    text-align: left;
    white-space: nowrap;
    background-color: transparent; /* Reset background for table cells */
}

.salary-table-container {
    margin: 10px 0;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
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
<body>
<?php include('sidebar.php'); ?>   
<?php include('header.php'); ?>
<div class="container-fluid">
        <div class="card shadow">
        <div class="card-header text-center py-4">
            <h1 class="mb-0">View and Export Salary Data</h1>
        </div>

 <!-- Tab buttons -->
    <div class="tabs">
               <?php foreach ($months as $index => $month): ?>
                    <button class="tab-link <?= $index === 0 ? 'active' : '' ?>" onclick="openTab(event, 'tab-<?php echo $index; ?>')">
                        <?php echo formatMonth($month); ?>
                    </button>
                <?php endforeach; ?>
    </div>

    
    <?php foreach ($salary_data as $month => $salaries): ?>
        <div id="tab-<?php echo array_search($month, $months); ?>" class="tab-content">
         <form method="POST">
                <input type="hidden" name="export_month" value="<?php echo $month; ?>">
                <button type="submit" name="export_csv">Export CSV</button>
                <button type="submit" name="export_pdf">Export PDF</button>
            </form>   
        
            <div class="salary-table-container">
    <table class="salary-table table table-bordered table-striped" id="salaryTable-<?php echo array_search($month, $months); ?>">
        <thead>
            <tr>
            <th>EID</th>
                <th>Name</th>
               
                <th>Base(AED)</th>
                <th>Cal Days</th>
                <th>Present</th>
                <th>Leaves</th>
                <th>LTO</th>
                <th>Deduct</th>
                <th>Remarks</th>
                <th>Points</th>
                <th>Incentive</th>
                <th>Payable</th>
                <th>Visa</th>
                <th>Total</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salaries as $row): ?>
                <tr>
                <td><?php echo $row['emp_id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                   
                    <td><?php echo $row['base_salary']; ?></td>
                    <td><?php echo $row['calculated_days']; ?></td>
                    <td><?php echo $row['present_days']; ?></td>
                    <td><?php echo $row['leaves']; ?></td>
                    <td><?php echo $row['lto']; ?></td>
                    <td><?php echo $row['deductions']; ?></td>
                    <td><?php echo $row['deduction_remarks']; ?></td>
                    <td><?php echo $row['performance_points']; ?></td>
                    <td><?php echo $row['incentive']; ?></td>
                    <td><?php echo $row['payable_salary']; ?></td>
                    <td><?php echo $row['visa_expense']; ?></td>
                    <td><?php echo $row['total_salary']; ?></td>
                    <td><?php echo $row['salary_date']; ?></td>
                    <td>
                        <a href="edit_salary.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="generate_payslip.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm ml-1" target="_blank">
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
                    <a class="btn btn-success"
                        href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>




<!--  JavaScript functions -->


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


// Add this JavaScript at the bottom of your file, before </body>
<script>
$(document).ready(function() {
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