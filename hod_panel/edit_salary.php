<?php
include('session.php');
include('connection.php');

// Get salary record by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $con->prepare("SELECT sal.*, employees.full_name 
                          FROM sal 
                          INNER JOIN employees ON sal.emp_id = employees.eid 
                          WHERE sal.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $salary = $result->fetch_assoc();

    if (!$salary) {
        header("Location: view_salary.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_salary = $_POST['base_salary'];
    $calculated_days = $_POST['calculated_days'];
    $present_days = $_POST['present_days'];
    $leaves = $_POST['leaves'];
    $lto = $_POST['lto'];
    $deductions = $_POST['deductions'];
    $deduction_remarks = $_POST['deduction_remarks'];
    $performance_points = $_POST['performance_points'];
    $incentive = $_POST['incentive'];
    $visa_expense = $_POST['visa_expense'];
    $salary_date = $_POST['salary_date'];
    $housing_allowance = (float) ($_POST['housing_allowance'] ?? 0);
    $transportation_allowance = (float) ($_POST['transportation_allowance'] ?? 0);
    $performance_bonus = (float) ($_POST['performance_bonus'] ?? 0);
    $hold = (float) ($_POST['hold'] ?? 0);
    $advance_paid = (float) ($_POST['advance_paid'] ?? 0);
    $others_deduction = (float) ($_POST['others_deduction'] ?? 0);

    //-------------------------------------------------------------------------------

    // Calculate salary components
    $one_day_salary = $base_salary / $calculated_days;

    // Calculate deductions components
    $unpaid_leave_deduction = ($leaves + $lto) * $one_day_salary;
    $total_deductions = $unpaid_leave_deduction + $advance_paid + $others_deduction + $hold + $visa_expense;

    // Calculate total allowances and gross earnings
    $total_allowances = $housing_allowance + $transportation_allowance + $performance_bonus + $incentive;
    $gross_earnings = $base_salary + $total_allowances;

    // Calculate net payable salary
    $net_payable = $gross_earnings - $total_deductions;

    // Update database values
    $payable_salary = $gross_earnings;
    $total_salary = $net_payable;
    //-----------------------------------------------------------------------------------------
    $stmt = $con->prepare("UPDATE sal SET 
    base_salary = ?, calculated_days = ?, present_days = ?, 
    leaves = ?, lto = ?, deductions = ?, deduction_remarks = ?,
    performance_points = ?, incentive = ?, payable_salary = ?,
    visa_expense = ?, total_salary = ?, salary_date = ?,
    housing_allowance = ?, transportation_allowance = ?,
    performance_bonus = ?, hold = ?, advance_paid = ?, others_deduction = ?
    WHERE id = ?");

    $stmt->bind_param(
        "ddddddsdddddsddddddi",
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
        $salary_date,
        $housing_allowance,
        $transportation_allowance,
        $performance_bonus,
        $hold,
        $advance_paid,
        $others_deduction,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view_salary.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Salary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4e73df;
            border: none;
        }

        .btn-primary:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        label {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 8px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>




</head>

<body>
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Salary Record</h6>
            </div>
            <div class="card-body">
                <form method="POST" id="editSalaryForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" value="<?php echo $salary['full_name']; ?>"
                                readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Base Salary (AED)</label>
                            <input type="number" step="0.01" name="base_salary" class="form-control"
                                value="<?php echo $salary['base_salary']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Calculated Days</label>
                            <div class="form-control bg-light"><?= $salary['calculated_days'] ?></div>
                            <input type="hidden" name="calculated_days" value="<?= $salary['calculated_days'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Present Days</label>
                            <input type="number" name="present_days" class="form-control"
                                value="<?php echo $salary['present_days']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Leaves</label>
                            <input type="number" name="leaves" class="form-control"
                                value="<?php echo $salary['leaves']; ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Housing Allowance</label>
                            <input type="number" step="0.01" name="housing_allowance" class="form-control"
                                value="<?= $salary['housing_allowance'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Transportation Allowance</label>
                            <input type="number" step="0.01" name="transportation_allowance" class="form-control"
                                value="<?= $salary['transportation_allowance'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Performance Bonus</label>
                            <input type="number" step="0.01" name="performance_bonus" class="form-control"
                                value="<?= $salary['performance_bonus'] ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Hold</label>
                            <input type="number" step="0.01" name="hold" class="form-control"
                                value="<?= $salary['hold'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Advance Paid</label>
                            <input type="number" step="0.01" name="advance_paid" class="form-control"
                                value="<?= $salary['advance_paid'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Other Deductions</label>
                            <input type="number" step="0.01" name="others_deduction" class="form-control"
                                value="<?= $salary['others_deduction'] ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>LTO</label>
                            <input type="number" name="lto" class="form-control" value="<?php echo $salary['lto']; ?>"
                                required>
                        </div>
                        
                   
 
    <div class="col-md-4">
    <label>Deductions</label>
    <div class="form-control bg-light"><?= number_format($salary['deductions'] ?? 0, 2) ?></div>
    <input type="hidden" name="deductions" value="<?= $salary['deductions'] ?? 0 ?>">
</div>
                       
                       
                        <div class="col-md-4">
                            <label>Performance Points</label>
                            <input type="number" step="0.01" name="performance_points" class="form-control"
                                value="<?php echo $salary['performance_points']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Deduction Remarks</label>
                            <textarea name="deduction_remarks"
                                class="form-control"><?php echo $salary['deduction_remarks']; ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label>Incentive</label>
                            <input type="number" step="0.01" name="incentive" class="form-control"
                                value="<?php echo $salary['incentive']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Visa Expense</label>
                            <input type="number" step="0.01" name="visa_expense" class="form-control"
                                value="<?php echo $salary['visa_expense']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Salary Date</label>
                            <input type="date" name="salary_date" class="form-control"
                                value="<?php echo $salary['salary_date']; ?>" required>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Salary</button>
                        <a href="view_salary.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>


    <!-- JavaScript -->
    <script>
        $(document).ready(function () {
            // Auto-calculate payable salary and total salary
            // Update the calculation in the JavaScript
            function calculateSalaries() {
                // ... existing variables ...

                // New: Calculate deductions properly
                const leaves = parseFloat($('[name="leaves"]').val()) || 0;
                const lto = parseFloat($('[name="lto"]').val()) || 0;
                const oneDaySalary = baseSalary / calculatedDays;

                // Calculate total deductions including new components
                const deductions = (leaves + lto) * oneDaySalary +
                    hold + advancePaid + otherDeductions;

                // Update hidden deduction field
                $('[name="deductions"]').val(deductions.toFixed(2));

                const incentive = parseFloat($('[name="incentive"]').val()) || 0;
                const visaExpense = parseFloat($('[name="visa_expense"]').val()) || 0;

                const payableSalary = (baseSalary / calculatedDays) * presentDays;
                const totalSalary = payableSalary + incentive - deductions + visaExpense;

                $('#payable_salary').text(payableSalary.toFixed(2));
                $('#total_salary').text(totalSalary.toFixed(2));
            }

            // Add calculation display after visa expense row
            $('.row:last').before(`
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Payable Salary (AED)</label>
                <div class="form-control bg-light" id="payable_salary">0.00</div>
            </div>
            <div class="col-md-6">
                <label>Total Salary (AED)</label>
                <div class="form-control bg-light" id="total_salary">0.00</div>
            </div>
        </div>
    `);

            // Calculate on input change
            $('input[type="number"]').on('input', calculateSalaries);
            calculateSalaries(); // Initial calculation

            // Form submission confirmation
            $('#editSalaryForm').on('submit', function (e) {
                if (!confirm('Are you sure you want to update this salary record?')) {
                    e.preventDefault();
                }
            });
        });
    </script>








    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>