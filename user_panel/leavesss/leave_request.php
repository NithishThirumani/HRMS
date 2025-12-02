<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="stylesheet" href="styles.css"> <!-- Using CSS from your uploaded file -->
</head>
<body>
    <div class="container">
        <h2>Submit Leave Request</h2>
        <?php
        include('session.php');
        include('connection.php');

        $emp_id = $_SESSION['user_id'];
        
        // Fetch employee details
        $query = "SELECT department, department_name, full_name FROM employees WHERE id = '$emp_id'";
        $result = mysqli_query($con, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $department = $row['department'];
            $department_name = $row['department_name'];
            $user_name = $row['full_name'];

            // Fetch department head details
            $query2 = "SELECT head_id, head_name FROM department_heads WHERE dept_name = '$department'";
            $result2 = mysqli_query($con, $query2);
            
            if (mysqli_num_rows($result2) > 0) {
                $row2 = mysqli_fetch_assoc($result2);
                $head_id = $row2['head_id'];
                $head_name = $row2['head_name'];
            } else {
                $head_name = "Not Assigned";
            }
        } else {
            $user_name = "Unknown";
            $department_name = "Unknown";
            $head_name = "Unknown";
        }
        ?>
        
        <form method="POST" action="">
            <label for="user_name">Employee Name:</label>
            <input type="text" name="user_name" value="<?php echo $user_name; ?>" readonly><br>

            <label for="department_name">Department:</label>
            <input type="text" name="department_name" value="<?php echo $department_name; ?>" readonly><br>

            <label for="head_name">Department Head:</label>
            <input type="text" name="head_name" value="<?php echo $head_name; ?>" readonly><br>
            
            <label for="reason">Reason:</label>
            <input type="text" name="reason" required><br>
            
            <label for="type_of_leave">Type of Leave:</label>
            <select name="type_of_leave" required>                
            </select><br>
            
            <label for="sd">Start Date:</label>
            <input type="date" name="sd" required><br>
            
            <label for="ed">End Date:</label>
            <input type="date" name="ed" required><br>
            
            <button type="submit" name="submit">Submit Leave Request</button>
        </form>
    </div>
    
    <?php
    if (isset($_POST['submit'])) {
        $reason = $_POST['reason'];
        $type_of_leave = $_POST['type_of_leave'];
        $sd = $_POST['sd'];
        $ed = $_POST['ed'];
        $start_date = new DateTime($sd);
        $end_date = new DateTime($ed);
        $interval = $start_date->diff($end_date);
        $total_days = $interval->days;
        $status = "Pending Head Approval";
        $applied_at = date('Y-m-d H:i:s');

        // Insert leave request assigned to department head
        $q = "INSERT INTO leaves (emp_id, user_name, reason, type_of_leave, start_date, end_date, total_days, applied_at, status, head_approval, hr_approval) 
              VALUES ('$emp_id', '$user_name', '$reason', '$type_of_leave', '$sd', '$ed', '$total_days', '$applied_at', '$status', NULL, NULL)";
        
        if (mysqli_query($con, $q)) {
            echo "<p class='success-msg'>Leave request submitted to Department Head ($head_name) for approval.</p>";
        } else {
            echo "<p class='error-msg'>Error: " . mysqli_error($con) . "</p>";
        }
    }
    ?>
</body>
</html>
