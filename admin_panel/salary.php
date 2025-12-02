<?php  
include('session.php');
include('connection.php');

// Fetch all registered employees
$employees_query = $con->query("SELECT * FROM employees");

// Initialize an empty array to hold employee data
$employee_data = [];

while ($employee = $employees_query->fetch_assoc()) {
    $employee_id = $employee['eid'];

    // Fetch salary for each employee
    $salary_query = $con->query("SELECT * FROM salary WHERE emp_id = '$employee_id'");
    $salary = $salary_query->fetch_assoc() ?: ['base_salary' => 0, 'bonus' => 0, 'total_salary' => 0];
    $base_salary = $salary['base_salary'] ?? 0;

    // Fetch salary_id
    $salary_id_query = $con->query("SELECT id FROM salary WHERE emp_id = '$employee_id'");
    $salary_id = $salary_id_query->fetch_assoc()['id'] ?? null;
    if (!$salary_id) {
        echo "Error: Salary ID not found for employee $employee_id";
        continue;
    }

    // Calculate allowances
    $hra_allowance = $base_salary * 0.20;
    $transport_allowance = $base_salary * 0.08;
    $food_allowance = $base_salary * 0.05;
    $overtime_allowance = $base_salary * 0.10;

    // Insert or update allowances
    $allowances = [
        'HRA' => $hra_allowance,
        'Transport' => $transport_allowance,
        'Food' => $food_allowance,
        'Overtime' => $overtime_allowance
    ];

    foreach ($allowances as $type => $amount) {
    $query = "
        INSERT INTO salary_allowances (emp_id, salary_id, allowance_type, allowance_amount)
        VALUES ('$employee_id', '$salary_id', '$type', '$amount')
        ON DUPLICATE KEY UPDATE allowance_amount = '$amount'
    ";

    if (!$con->query($query)) {
        echo "Error updating allowance $type for employee $employee_id: " . $con->error;
    }
}



// Define the get_absent_days function
    if (!function_exists('get_absent_days')) {
        function get_absent_days($employee_id) {
        global $con;
        $current_month = date('m');
        $current_year = date('Y');
        
        // Query to get the total absent days for the employee in the current month
        $absent_query = $con->query("
            SELECT COUNT(*) as absent_days 
            FROM attendance 
            WHERE emp_eid = '$employee_id' 
              AND MONTH(date) = '$current_month' 
              AND YEAR(date) = '$current_year' 
              AND status = 'absent'
        ");
        
        $absent_result = $absent_query->fetch_assoc();
        return $absent_result['absent_days'] ?: 0;
        }
    }

    // Calculate deductions
    $pf_deduction = 0.10 * $base_salary;
    $absent_days = get_absent_days($employee_id);
    $total_days_in_month = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
    $absent_deduction = ($base_salary / $total_days_in_month) * $absent_days;
    $insurance_deduction = 0.05 * $base_salary;
    $tax_deduction = 0.05 * $salary['total_salary'];

    // Insert or update deductions
    $deductions = [
        'PF' => $pf_deduction,
        'Absent' => $absent_deduction,
        'Insurance' => $insurance_deduction,
        'Tax' => $tax_deduction
    ];

    foreach ($deductions as $type => $amount) {
        $con->query("
            INSERT INTO salary_deductions (emp_id, salary_id, deduction_type, deduction_amount)
            VALUES ('$employee_id', '$salary_id', '$type', '$amount')
            ON DUPLICATE KEY UPDATE deduction_amount = '$amount'
        ");
    }

    // Calculate total allowances and deductions
    $total_allowances = array_sum($allowances);
    $total_deductions = array_sum($deductions);

    // Calculate final salary
    $final_salary = $base_salary + $total_allowances - $total_deductions;

    // Update salary table
    $con->query("UPDATE salary SET total_salary = '$final_salary' WHERE emp_id = '$employee_id'");

    // Store employee data for payslip
    $employee_data[] = [
        'eid' => $employee['eid'],
        'name' => $employee['full_name'],
        'base_salary' => $base_salary,
        'total_allowances' => $total_allowances,
        'total_deductions' => $total_deductions,
        'total_salary' => $final_salary
    ];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Salary</title>
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
</head>
<body>
 <?php include('sidebar.php'); ?>
    <div class="container mt-5">
	<?php include('header.php'); ?>
	<div class="container-fluid">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
        <h3 class="m-0 font-weight-bold text-success">Employee Salary</h3>
		<div class="card-body">
                        <div class="table-responsive">
						<div class="export-buttons">
    <a href="export_excel.php" class="btn btn-success">Excel</a>
    <a href="export_csv.php" class="btn btn-primary">CSV</a>
    <a href="export_pdf.php" class="btn btn-danger">PDF</a>
    <button onclick="printTable()" class="btn btn-info">Print</button>
</div>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Total Salary</th>
					  <th>Click to View</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employee_data as $employee): ?>
                <tr>
                    <td><?php echo $employee['eid']; ?></td>
                    <td><?php echo $employee['name']; ?></td>
                    <td>
                        <?php echo number_format($employee['total_salary'], 2); ?>
                        
                    </td>
					<td>
                        <a href="employee_salary_details.php?emp_id=<?php echo $employee['eid']; ?>">
                            Click
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
	</div>
	</div>
	</div>
	</div>
	</div>

    <!-- Include footer and Bootstrap JS -->
    <?php include 'footer.php'; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
