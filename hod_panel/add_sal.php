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
        $required = ['emp_id', 'salary_month', 'base_salary', 'calculated_days', 'present_days', 'leaves'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                throw new Exception("$field is required");
            }
        }

        // Get form data
        $emp_id = $_POST['emp_id'];
        $salary_date = $_POST['salary_month'] . '-01';
        $base_salary = (float) $_POST['base_salary'];
        $calculated_days = (int) $_POST['calculated_days'];
        $present_days = (int) $_POST['present_days'];
        $leaves = (int) $_POST['leaves'];
        $lto = (int) $_POST['lto'];
        $deduction_remarks = $_POST['deduction_remarks'] ?? '';
        $performance_points = (int) $_POST['performance_points'];
        $incentive = (float) ($_POST['incentive'] ?? 0);
        $visa_expense = (float) ($_POST['visa_expense'] ?? 0);

        // Add new allowance fields
        $housing_allowance = (float) ($_POST['housing_allowance'] ?? 0);
        $transportation_allowance = (float) ($_POST['transportation_allowance'] ?? 0);
        $performance_bonus = (float) ($_POST['performance_bonus'] ?? 0);
        // Add new deduction fields
        $hold = (float) ($_POST['hold'] ?? 0);
        $advance_paid = (float) ($_POST['advance_paid'] ?? 0);
        $others_deduction = (float) ($_POST['others_deduction'] ?? 0);
        //------------------------------------------------------------------------

        // Calculate salary components
        $one_day_salary = $base_salary / $calculated_days;


        // Calculate leaves and LTO amounts separately
        $leaves_amt = $leaves * $one_day_salary;
        $lto_amt = $lto * $one_day_salary;



        // Calculate deductions components
        $unpaid_leave_deduction = $leaves_amt + $lto_amt;
        $total_deductions = $unpaid_leave_deduction + $advance_paid + $others_deduction + $hold + $visa_expense;
        $deductions = $total_deductions;
        // Calculate total allowances and gross earnings
        $total_allowances = $housing_allowance + $transportation_allowance + $performance_bonus + $incentive;
        $gross_earnings = $base_salary + $total_allowances;

        // Calculate net payable salary
        $net_payable = $gross_earnings - $total_deductions;

        // Update database values
        $payable_salary = $gross_earnings;
        $total_salary = $net_payable;
        $pay_mode = 'Cash Memo'; // Default pay mode

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
        emp_id, base_salary, housing_allowance, transportation_allowance, 
        performance_bonus, incentive, calculated_days, present_days, 
        leaves, lto, leaves_amt, lto_amt, hold, advance_paid, 
        visa_expense, others_deduction, deduction_remarks, performance_points,
        payable_salary, deductions, total_salary, salary_date, pay_mode
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


        $insert_stmt->bind_param(
            "sddddddiiiddddddssiddss",  // 23 parameters
            $emp_id,
            $base_salary,
            $housing_allowance,
            $transportation_allowance,
            $performance_bonus,
            $incentive,
            $calculated_days,
            $present_days,
            $leaves,
            $lto,
            $leaves_amt,
            $lto_amt,
            $hold,
            $advance_paid,
            $visa_expense,
            $others_deduction,
            $deduction_remarks,
            $performance_points,
            $payable_salary,
            $deductions,
            $total_salary,
            $salary_date,
            $pay_mode
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
            background-color: #f8f9fc;
            font-family: 'Poppins', sans-serif;
        }

        .container.mt-5 {
            margin-top: 2rem !important;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-header {
            background: linear-gradient(135deg, #00325c, #005eb8);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem !important;
            border-bottom: none;
        }

        .card-header h4 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 2rem !important;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e6ed;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #005eb8;
            box-shadow: 0 0 0 0.2rem rgba(0, 94, 184, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #00325c, #005eb8);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #005eb8, #00325c);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 94, 184, 0.2);
        }

        .table td {
            padding: 1rem;
            border: 1px solid #e0e6ed;
            vertical-align: middle;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background-color: #e3f7ec;
            color: #1d6f42;
        }

        .alert-danger {
            background-color: #fee7e7;
            color: #c53030;
        }

        textarea.form-control {
            min-height: 100px;
        }

        /* Add golden accent for Dubai theme */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f4e08d, #d4af37);
            border-radius: 0 0 4px 4px;
        }

        /* Amount fields styling */
        input[type="number"] {
            text-align: right;
            font-family: 'Arial', sans-serif;
            font-weight: 500;
        }

        /* Add currency symbol */
        .currency-field {
            position: relative;
        }

        .currency-field::before {
            content: 'AED';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 1;
        }

        .currency-field input {
            padding-left: 50px;
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

        .form-control,
        .form-select {
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
                                                <option value="<?= $emp['eid'] ?>"
                                                    data-username="<?= htmlspecialchars($emp['user_name']) ?>">
                                                    <?= htmlspecialchars($emp['full_name']) ?>
                                                    (<?= htmlspecialchars($emp['user_name']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Base Salary(AED)</label>
                                        <input type="number" step="0.01" name="base_salary" class="form-control"
                                            required>
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
                                        <label class="form-label">Unpaid Leaves</label>
                                        <input type="number" name="leaves" class="form-control">
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Housing Allowance</label>
                                        <input type="number" step="0.01" name="housing_allowance" class="form-control"
                                            value="0">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Transportation Allowance</label>
                                        <input type="number" step="0.01" name="transportation_allowance"
                                            class="form-control" value="0">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Performance Bonus</label>
                                        <input type="number" step="0.01" name="performance_bonus" class="form-control"
                                            value="0">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>

                                    <div class="form-group">
                                        <label class="form-label">Incentive Amount</label>
                                        <input type="number" step="0.01" name="incentive" class="form-control">
                                    </div>




                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Advance Paid</label>
                                        <input type="number" step="0.01" name="advance_paid" class="form-control"
                                            value="0">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Other Deductions</label>
                                        <input type="number" step="0.01" name="others_deduction" class="form-control"
                                            value="0">
                                    </div>
                                </td>
                            </tr>


                            <tr>
                                <td>

                                    <div class="form-group">
                                        <label class="form-label">Hold</label>
                                        <input type="number" step="0.01" name="hold" class="form-control" value="0">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <label class="form-label">Late to Office (LTO)</label>
                                        <input type="number" name="lto" class="form-control">
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
                    <a class="btn btn-success" href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill calculated_days
        document.querySelector('input[name="salary_month"]').addEventListener('change', function () {
            const date = new Date(this.value + '-01');
            const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            document.querySelector('input[name="calculated_days"]').value = daysInMonth;

            // Reset leaves field to avoid empty submission
            document.querySelector('input[name="leaves"]').value = '0';
        });

        // Optional: auto-calculate leaves if present_days is updated
        document.querySelector('input[name="present_days"]').addEventListener('input', function () {
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