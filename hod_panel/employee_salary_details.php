<?php  
include('session.php');
include('connection.php');

// Get employee ID from URL
$emp_id = $_GET['emp_id'];

// Fetch employee details
$employee_query = $con->query("
    SELECT employees.id, 
           employees.eid, 
           employees.full_name, 
           employees.birthday, 
           employees.doj, 
           employees.EmpGrade, 
           employees.department, 
           employees.MOLID
    FROM employees
    WHERE employees.eid = '$emp_id'");


$employee = $employee_query->fetch_assoc();

// Fetch salary details
$salary_query = $con->query("SELECT * FROM salary WHERE emp_id = '$emp_id'");
$salary = $salary_query->fetch_assoc() ?: ['base_salary' => 0, 'bonus' => 0, 'total_salary' => 0];

// Fetch deductions and allowances
$deductions_query = $con->query("SELECT * FROM salary_deductions WHERE salary_id = (SELECT id FROM salary WHERE emp_id = '$emp_id')");
$deductions = $deductions_query->fetch_assoc() ?: [];

$allowances_query = $con->query("SELECT * FROM salary_allowances WHERE salary_id = (SELECT id FROM salary WHERE emp_id = '$emp_id')");
$allowances = $allowances_query->fetch_assoc() ?: [];

// Initialize default values for allowances and deductions if empty
$allowances = array_merge([
    'transport' => 0,
    'hra' => 0,
    'food' => 0,
    'overtime' => 0,
    'misc_allowance' => 0,
], $allowances);

$deductions = array_merge([
    'pf' => 0,
    'insurance' => 0,
    'late_penalty' => 0,
    'unpaid_leave' => 0,
    'absent' => 0,
    'tax' => 0,
    'misc_deduction' => 0,
], $deductions);

// Calculate total deductions, allowances, and final salary
$total_deductions = array_sum($deductions);
$total_allowances = array_sum($allowances);
$final_salary = $salary['base_salary'] + $total_allowances - $total_deductions;

// Get the current payslip month
$payslip_month = date("F Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Salary Details</title>
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
	
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<style>
    th {
        color: #333; /* Dark grey */
    }
</style>
   
</head>
<body id="page-top">
<?php include('sidebar.php'); ?>
<div class="container">
<?php include('header.php'); ?>
<div class="container-fluid">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
<div class="export-buttons">
    <a href="export_excel.php" class="btn btn-success">Excel</a>
    <a href="export_csv.php" class="btn btn-primary">CSV</a>
    <a href="export_pdf.php" class="btn btn-danger">PDF</a>
    <button onclick="printTable()" class="btn btn-info">Print</button>
</div>
    <h1 class="text-center" style="font-size: 1.8rem; color: #4a90e2;">ORGANISATION NAME</h1>

    <!-- Employee Information Section -->
    <div class="card p-2 mb-4" style="font-size: 0.9rem;">
        <h4 style="color: #333;">Employee Information</h4>
        <table class="table table-sm">
            <!-- Hiding the Employee ID -->
            <!-- <tr><th>Employee ID</th><td><?php //echo $employee['id']; ?></td></tr> -->
            <tr>
                <th>Employee EID</th>
                <td><?php echo $employee['eid']; ?></td>
                <th>Full Name</th>
                <td><?php echo $employee['full_name']; ?></td>
            </tr>
            <tr>
                <th>Department</th>
                <td><?php echo $employee['department']; ?></td>
                <th>Employee Grade</th>
                <td><?php echo $employee['EmpGrade']; ?></td>
            </tr>
            <tr>
                <th>Birthday</th>
                <td><?php echo $employee['birthday']; ?></td>
                <th>Date of Joining (DOJ)</th>
                <td><?php echo $employee['doj']; ?></td>
            </tr>
            <tr>
                <th>MOL ID</th>
                <td><?php echo $employee['MOLID']; ?></td>
                <th>Payslip Month</th>
                <td><?php echo $payslip_month; ?></td>
            </tr>
        </table>
    </div>

    <!-- Salary Breakdown and Charts Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card p-2 mb-4">
                <h5 style="color: #333;">Salary Breakdown</h5>
                <table class="table table-bordered table-sm">
                    <tr><th>Base Salary</th><td><?php echo number_format($salary['base_salary'], 2); ?></td>
                    <th>Bonus</th><td><?php echo number_format($salary['bonus'], 2); ?></td>
                    <th>Total Salary</th><td><?php echo number_format($final_salary, 2); ?></td></tr>
                </table>
            </div>

           <!-- Side by side Deductions and Allowances -->
            <div class="row">
                <!-- Allowances Section -->
                <div class="col-md-6">
                    <div class="card p-2 mb-4">
                        <h5 style="color: #333;">Allowances</h5>
                        <table class="table table-bordered table-sm">
                            <tr><th>Transport</th><td><?php echo number_format($allowances['transport'], 2); ?></td></tr>
                            <tr><th>HRA</th><td><?php echo number_format($allowances['hra'], 2); ?></td></tr>
                            <tr><th>Food</th><td><?php echo number_format($allowances['food'], 2); ?></td></tr>
                            <tr><th>Overtime</th><td><?php echo number_format($allowances['overtime'], 2); ?></td></tr>
                            <tr><th>Misc Allowance</th><td><?php echo number_format($allowances['misc_allowance'], 2); ?></td></tr>
                        </table>
                    </div>
                </div>

                <!-- Deductions Section -->
                <div class="col-md-6">
                    <div class="card p-2 mb-4">
                        <h5 style="color: #333;">Deductions</h5>
                        <table class="table table-bordered table-sm">
                            <tr><th>PF</th><td><?php echo number_format($deductions['pf'], 2); ?></td></tr>
                            <tr><th>Insurance</th><td><?php echo number_format($deductions['insurance'], 2); ?></td></tr>
                            <tr><th>Late Penalty</th><td><?php echo number_format($deductions['late_penalty'], 2); ?></td></tr>
                            <tr><th>Unpaid Leave</th><td><?php echo number_format($deductions['unpaid_leave'], 2); ?></td></tr>
                            <tr><th>Absent</th><td><?php echo number_format($deductions['absent'], 2); ?></td></tr>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
<div class="col-md-6">
    <div class="card p-3 mb-4">
        <h5 class="text-center" style="color: #333;">Salary Breakdown Chart <?php echo $payslip_month; ?> </h5>
        <canvas id="salaryChart" style="max-height: 200px; width: 100%;"></canvas> <!-- Set a maximum height -->
    </div>
</div>

      
    </div>
</div>


<!-- Custom Styling -->



    <script>
        // Prepare data for the chart
        const data = {
            labels: ['Base Salary', 'Allowances', 'Deductions'],
            datasets: [{
                label: 'Salary Breakdown',
                data: [<?php echo $salary['base_salary']; ?>, <?php echo $total_allowances; ?>, <?php echo $total_deductions; ?>],
                backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b'],
                hoverOffset: 4
            }]
        };

        // Configuring the chart
        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        };

        // Render the chart
        const salaryChart = new Chart(
            document.getElementById('salaryChart'),
            config
        );
    </script>
</div>
</div>
</div> <?php include_once('footer.php'); ?>
    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
                        href="http://localhost/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


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
</body>
</html>
