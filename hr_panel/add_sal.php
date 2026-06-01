<?php
include('session.php'); 
include('connection.php');
// Initialize variables
$employees = [];
$error = '';
$success = '';

// Fetch employees for dropdown
$stmt = $con->prepare("SELECT eid, full_name, user_name FROM employees");
$stmt->execute();
$employees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required = ['emp_id', 'salary_month', 'base_salary', 'calculated_days', 'present_days','leaves'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                throw new Exception("$field is required");
            }
        }
        
        // Get form data
        $emp_id = $_POST['emp_id'];
        $salary_date = $_POST['salary_month'] . '-01';
        $base_salary = (float)$_POST['base_salary'];
        $calculated_days = (int)$_POST['calculated_days'];
        $present_days = (int)$_POST['present_days'];
        $leaves = (int)$_POST['leaves'];
        $lto = (int)$_POST['lto'];
        $deduction_remarks = $_POST['deduction_remarks'] ?? '';
        $performance_points = (int)$_POST['performance_points'];
        $incentive = (float)($_POST['incentive'] ?? 0);
        $visa_expense = (float)($_POST['visa_expense'] ?? 0);

        // Calculate salary components
        $one_day_salary = $base_salary / $calculated_days;
        $total_present_days = $present_days;
        $deductions = ($leaves + $lto) * $one_day_salary;
        $payable_salary = ($total_present_days * $one_day_salary) + $incentive;
        $total_salary = $payable_salary - $visa_expense - $deductions;


// Check if salary already exists for the same month and year
$check_stmt = $con->prepare("SELECT id FROM sal WHERE emp_id = ? AND YEAR(salary_date) = ? AND MONTH(salary_date) = ?");
$salary_year = date('Y', strtotime($salary_date));
$salary_month = date('m', strtotime($salary_date));

$check_stmt->bind_param("sss", $emp_id, $salary_year, $salary_month); // Change "iss" to "sss"
$check_stmt->execute();

$result = $check_stmt->get_result();

if ($result === false) {
    throw new Exception("Query failed: " . $con->error);
}


if ($result->num_rows > 0) {
    throw new Exception("Salary already exists for this employee and month.");
} 





 

        // there’s a default value for leaves:        
    $leaves = isset($_POST['leaves']) ? $_POST['leaves'] : 0; // Defaults to 0 if not set
        // Insert salary record
        $insert_stmt = $con->prepare("INSERT INTO sal (
            emp_id, base_salary, calculated_days, present_days, leaves, lto, 
            deductions, deduction_remarks, performance_points, incentive, 
            payable_salary, visa_expense, total_salary, salary_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert_stmt->bind_param(
            "sddiiidssiddds",
            $emp_id,
            $base_salary,
            $calculated_days,
            $present_days,
            $leaves,
            $lto,
            $deductions,
            $deduction_remarks,
            $performance_points,
            $incentive,
            $payable_salary,
            $visa_expense,
            $total_salary,
            $salary_date
        );

        if ($insert_stmt->execute()) {
            $success = "Salary added successfully!";
        } else {
            throw new Exception("Error saving salary: " . $con->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Salary</title>
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
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container.mt-5 {
            margin-top: 1rem !important;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            max-width: 1000px;
            margin: 0 auto;
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 0.5rem !important;
        }
        .py-4 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
        .card-body {
            padding: 1rem !important;
        }
        .form-label {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .btn-primary {
            background-color: #6a11cb;
            border: none;
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background-color: #2575fc;
        }
        .alert {
            border-radius: 6px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.75rem;
        }
        h4.mb-0 {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .table {
            margin-bottom: 0.5rem;
        }
        .table td {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .form-control, .form-select {
            padding: 0.375rem 0.5rem;
            font-size: 0.9rem;
        }
        textarea.form-control {
            min-height: 60px;
        }
        .form-group {
            margin-bottom: 0.5rem;
        }
    </style>


</head>
<body>
    <?php include('sidebar.php'); ?>   
    <?php include('header.php'); ?>
    
    <div class="container mt-5">
        <div class="card shadow">
        <div class="card-header text-center py-4">
                <h4 class="mb-0">Add Employee Salary</h4>
            </div>            
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="post">
                <table class="table">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Salary Month</label>
                                        <input type="month" name="salary_month" class="form-control" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Employee</label>
                                        <select name="emp_id" class="form-select" required>
                                            <option value="">Select Employee</option>
                                            <?php foreach ($employees as $emp): ?>
                                                <option value="<?= $emp['eid'] ?>" data-username="<?= htmlspecialchars($emp['user_name']) ?>">
                                                    <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_name']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Base Salary(AED)</label>
                                        <input type="number" step="0.01" name="base_salary" class="form-control" required>
                                    </div>
                                </td>
                            </tr>
                            <!-- Rest of the table rows remain the same -->
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Days in Month</label>
                                        <input type="number" name="calculated_days" class="form-control" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Present Days</label>
                                        <input type="number" name="present_days" class="form-control" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Leaves Taken</label>
                                        <input type="number" name="leaves" class="form-control">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Late to Office (LTO)</label>
                                        <input type="number" name="lto" class="form-control">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Incentive Amount</label>
                                        <input type="number" step="0.01" name="incentive" class="form-control">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Visa Expenses</label>
                                        <input type="number" step="0.01" name="visa_expense" class="form-control">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Performance Points</label>
                                        <input type="number" name="performance_points" class="form-control">
                                    </div>
                                </td>
                                <td colspan="2">
                                    <div class="form-group">
                                        <label class="form-label">Deduction Remarks</label>
                                        <textarea name="deduction_remarks" class="form-control" rows="3"></textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-center">
                                    <button type="submit" class="btn btn-primary">Calculate & Save Salary</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <?php
          include_once('footer.php');
          ?>
    </div>


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

<script>
    // Auto-fill calculated_days
document.querySelector('input[name="salary_month"]').addEventListener('change', function() {
    const date = new Date(this.value + '-01');
    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    document.querySelector('input[name="calculated_days"]').value = daysInMonth;

    // Reset leaves field to avoid empty submission
    document.querySelector('input[name="leaves"]').value = '0'; 
});

// Optional: auto-calculate leaves if present_days is updated
document.querySelector('input[name="present_days"]').addEventListener('input', function() {
    const totalDays = parseInt(document.querySelector('input[name="calculated_days"]').value) || 0;
    const presentDays = parseInt(this.value) || 0;
    const leaves = Math.max(totalDays - presentDays, 0); // Ensuring it doesn't go negative
    document.querySelector('input[name="leaves"]').value = leaves;
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