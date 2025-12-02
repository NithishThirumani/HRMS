<?php
include('../connection.php');

$query = "SELECT e.id, e.join_date, lp.* 
          FROM employees e 
          CROSS JOIN leave_policies lp 
          WHERE lp.monthly_accrual > 0";
$result = mysqli_query($con, $query);

while($row = mysqli_fetch_assoc($result)) {
    $months_employed = floor((strtotime('now') - strtotime($row['join_date'])) / (30 * 24 * 60 * 60));
    
    if($months_employed >= $row['min_service_months']) {
        $year = date('Y');
        
        // Check if balance entry exists
        $check_query = "SELECT * FROM employee_leave_balance 
                       WHERE emp_id = '{$row['id']}' 
                       AND leave_type = '{$row['leave_type']}'
                       AND year = $year";
        $check_result = mysqli_query($con, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            // Update existing balance
            $update = "UPDATE employee_leave_balance 
                      SET balance = LEAST(balance + {$row['monthly_accrual']}, {$row['max_days']}),
                          last_updated = CURRENT_DATE
                      WHERE emp_id = '{$row['id']}'
                      AND leave_type = '{$row['leave_type']}'
                      AND year = $year";
            mysqli_query($con, $update);
        } else {
            // Create new balance entry
            $insert = "INSERT INTO employee_leave_balance 
                      (emp_id, leave_type, balance, year, last_updated)
                      VALUES ('{$row['id']}', '{$row['leave_type']}', 
                      {$row['monthly_accrual']}, $year, CURRENT_DATE)";
            mysqli_query($con, $insert);
        }
    }
}
?>