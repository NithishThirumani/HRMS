<?php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Check if super admin already exists - check both tables
$check_admin = "SELECT 
    (SELECT COUNT(*) FROM employees WHERE role = 'super_admin') +
    (SELECT COUNT(*) FROM admin WHERE role = 'super_admin') as total_count";
$result = mysqli_query($con, $check_admin);
$row = mysqli_fetch_assoc($result);

if ($row['total_count'] > 0) {
    die("Super admin already exists. This setup page is no longer accessible.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);
    $setup_key = mysqli_real_escape_string($con, $_POST['setup_key']);

    // Verify setup key (you should change this to a secure value)
    $correct_setup_key = "initial_setup_2024"; // Change this to a secure key
    
    if ($setup_key !== $correct_setup_key) {
        echo "<script>alert('Invalid setup key!');</script>";
        exit();
    }

    // Validate password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    // Validate password strength
    if (strlen($password) < 8 || !preg_match("#[0-9]+#", $password) || !preg_match("#[A-Z]+#", $password) || !preg_match("#[a-z]+#", $password)) {
        echo "<script>alert('Password must be at least 8 characters and include uppercase, lowercase, and numbers!');</script>";
        exit();
    }

    // Check if email already exists in any table
    $check_email = "SELECT 
        (SELECT COUNT(*) FROM employees WHERE email = ?) +
        (SELECT COUNT(*) FROM admin WHERE email = ?) +
        (SELECT COUNT(*) FROM emp_login WHERE email = ?) as email_count";
    $stmt_check = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt_check, "sss", $email, $email, $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $row_check = mysqli_fetch_assoc($result_check);
    
    if ($row_check['email_count'] > 0) {
        echo "<script>alert('Email already exists in the system!');</script>";
        exit();
    }

    $full_name = $first_name . " " . $last_name;
    $username = explode("@", $email)[0];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        // Start transaction
        mysqli_begin_transaction($con);

        // Generate employee ID
        $sql_eid = "SELECT COALESCE(MAX(CAST(SUBSTRING(eid, 4) AS UNSIGNED)), 0) AS max_eid FROM employees";
        $result_eid = mysqli_query($con, $sql_eid);
        $row_eid = mysqli_fetch_assoc($result_eid);
        $max_eid = $row_eid['max_eid'];
        $new_eid = 'CME' . str_pad(($max_eid + 1), 3, "0", STR_PAD_LEFT);

        // Insert into admin table first
        $sql_admin = "INSERT INTO admin (user_name, email, password, role, admin_type, status) 
                     VALUES (?, ?, ?, 'super_admin', 'super_admin', 'Active')";
        
        $stmt_admin = mysqli_prepare($con, $sql_admin);
        mysqli_stmt_bind_param($stmt_admin, "sss", $username, $email, $hashed_password);
        
        if (!mysqli_stmt_execute($stmt_admin)) {
            throw new Exception("Error creating admin account: " . mysqli_error($con));
        }

        // Insert into employees table for hierarchy
        $sql_insert = "INSERT INTO employees (eid, first_name, last_name, full_name, user_name, email, password, role, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'super_admin', 'active')";
        
        $stmt = mysqli_prepare($con, $sql_insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $new_eid, $first_name, $last_name, $full_name, $username, $email, $hashed_password);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating employee record: " . mysqli_error($con));
        }

        // Insert into emp_login table
        $sql_login = "INSERT INTO emp_login (emp_id, user_name, email, password, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt_login = mysqli_prepare($con, $sql_login);
        mysqli_stmt_bind_param($stmt_login, "ssss", $new_eid, $username, $email, $hashed_password);
        
        if (!mysqli_stmt_execute($stmt_login)) {
            throw new Exception("Error creating login credentials: " . mysqli_error($con));
        }

        // Commit transaction
        mysqli_commit($con);
        
        echo "<script>
            alert('Super Admin account created successfully! Please login with your credentials.');
            window.location.href = 'login.php';
        </script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Super Admin Setup</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .form-group label {
            font-weight: 600;
            color: #34495e;
        }
        .btn-setup {
            background-color: #2c3e50;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        .btn-setup:hover {
            background-color: #34495e;
            color: white;
        }
        .setup-warning {
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="setup-header">
                <h2>Initial Super Admin Setup</h2>
                <p class="text-muted">Create the first super administrator account</p>
            </div>

            <form method="POST" action="" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text text-muted">
                        Password must be at least 8 characters long and include uppercase, lowercase, and numbers
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group">
                    <label for="setup_key">Setup Key</label>
                    <input type="password" class="form-control" id="setup_key" name="setup_key" required>
                    <small class="form-text text-muted">
                        Enter the provided setup key to create the super admin account
                    </small>
                </div>

                <button type="submit" name="create_admin" class="btn btn-setup">Create Super Admin Account</button>
            </form>

            <div class="setup-warning">
                <p>⚠️ This page will be accessible only once for initial setup.</p>
                <p>After creating the super admin account, this page will be disabled.</p>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check password match
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            // Check password strength
            if (password.length < 8) {
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            if (!/[A-Z]/.test(password)) {
                alert('Password must include at least one uppercase letter!');
                return false;
            }
            
            if (!/[a-z]/.test(password)) {
                alert('Password must include at least one lowercase letter!');
                return false;
            }
            
            if (!/[0-9]/.test(password)) {
                alert('Password must include at least one number!');
                return false;
            }
            
            return true;
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 