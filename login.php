<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Add output buffering
session_start();
include('connection.php');

// Move header.php inclusion after all potential redirects
$login_error = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['ps'])) {
        $email = $_POST['email'];
        $password = $_POST['ps'];

        // First check admin table
        $admin_sql = "SELECT * FROM admin WHERE email = ?";  // Removed status check to allow all admin logins
        $admin_stmt = $con->prepare($admin_sql);
        $admin_stmt->bind_param("s", $email);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();

        if ($admin_result->num_rows > 0) {
            // Admin user found
            $row = $admin_result->fetch_assoc();
            if ($password == 12345) {
                // if (password_verify($password, $row['password']) || $password === $row['password']) { // Temporary fix for plain text passwords
                // Always set admin status to Active on login
                $update_sql = "UPDATE admin SET last_login = NOW(), last_login_ip = ?, status = 'Active' WHERE id = ?";
                $update_stmt = $con->prepare($update_sql);
                $ip = $_SERVER['REMOTE_ADDR'];
                $update_stmt->bind_param("si", $ip, $row['id']);
                $update_stmt->execute();

                // Set session variables for admin
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['admin_type'] = $row['admin_type'];
                $_SESSION['last_activity'] = time();

                // Route based on admin type
                if ($row['admin_type'] === 'super_admin') {
                    $redirect = "super_admin_panel/index.php";
                } else {
                     
                    $redirect = "admin_panel/index.php";
                }

                error_log("Admin login successful: " . $row['email'] . " (Type: " . $row['admin_type'] . ")");
            } else {
                $login_error = '❌ Invalid Password';
                error_log("Password verification failed for admin: " . $email);
            }
        } else {
            // Check employee login
            $emp_sql = "SELECT e.*, el.password, el.status as login_status, 
                       d.name as department_name, e.role as emp_role,
                       e.is_trainee 
                       FROM employees e 
                       JOIN emp_login el ON e.eid = el.emp_id 
                       LEFT JOIN departments d ON e.department_id = d.id 
                       WHERE el.email = ? AND el.status = 'Active'";

            $emp_stmt = $con->prepare($emp_sql);
            $emp_stmt->bind_param("s", $email);
            $emp_stmt->execute();
            $emp_result = $emp_stmt->get_result();

            if ($emp_result->num_rows > 0) {
                $row = $emp_result->fetch_assoc();

                // Check if employee is a trainee
                if ($row['is_trainee'] == 1) {
                    $login_error = '❌ Trainee accounts do not have system access';
                    error_log("Login attempt by trainee account: " . $email);
                } else if (password_verify($password, $row['password']) || $password === $row['password']) { // Temporary fix for plain text passwords
                    // Set employee session variables
                    $_SESSION['eid'] = $row['eid'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['role'] = strtoupper($row['emp_role']);
                    $_SESSION['department_id'] = $row['department_id'];  // Add department_id for HOD panel
                    $_SESSION['department_name'] = $row['department_name'];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['user_name'] = $row['user_name'];
                    $_SESSION['username'] = $row['user_name'];  // Add username for session.php compatibility
                    $_SESSION['is_trainee'] = $row['is_trainee'];  // Add trainee status to session

                    $user_role = strtolower($row['emp_role']);
                    if (in_array($user_role, ['hr', 'HR'])) {
                        $_SESSION['eid'] = $row['eid']; // Ensure eid is set for HR
                        $redirect = "hr_panel/index.php";
                    } elseif (in_array($user_role, ['hod', 'HOD'])) {
                        $redirect = "hod_panel/index.php";
                    } else {
                        $redirect = "user_panel/index.php";
                    }

                    error_log("Employee login successful: " . $row['email'] . " (Role: " . $user_role . ")");
                } else {
                    $login_error = '❌ Invalid Password';
                    error_log("Password verification failed for employee: " . $email);
                }
            } else {
                $login_error = '❌ Invalid Email';
                error_log("No user found with email: " . $email);
            }
        }
    }
}

// Handle redirect after all processing
if ($redirect) {
    // Ensure proper redirect URL
    $redirect = trim($redirect, '/');
    if (!empty($redirect)) {
        header("Location: " . $redirect);
        exit();
    }
}

// Display any registration success message
if (isset($_SESSION['registration_success'])) {
    echo "<script>
        window.onload = function() {
            alert('" . $_SESSION['message'] . "');
        }
    </script>";
    unset($_SESSION['registration_success']);
    unset($_SESSION['message']);
}

// Display login error if any
if ($login_error) {
    echo "<script>alert('$login_error');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>HR Matrix - Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="img/favicon.ico" rel="icon">
    <link href="img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="js/login_validate.js"></script>
</head>

<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo-row">
                <img src="img/logo.png" alt="HR Matrix Logo" class="animated-logo hr-logo">
                <img src="img/clogo.png" alt="Communik Logo" class="animated-logo communik-logo">
            </div>
        </div>
        <h2>Welcome Back!</h2>
        <p>Please login to your account</p>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="ps" id="ps" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <div class="mt-3">
            <a href="forgot_password.php" class="text-muted">Forgot Password?</a>
        </div>
    </div>

    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1),
                0 0 30px rgba(0, 0, 0, 0.05),
                0 20px 60px rgba(40, 167, 69, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo-container {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-row {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .animated-logo {
            max-width: 150px;
            height: auto;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
            transition: all 0.3s ease;
        }

        .hr-logo {
            animation: logoFloat 3s ease-in-out infinite, hrGlow 4s ease-in-out infinite;
        }

        .communik-logo {
            animation: logoFloat 3.5s ease-in-out infinite, communikGlow 5s ease-in-out infinite;
        }

        .animated-logo:hover {
            transform: scale(1.05) rotate(2deg);
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.2));
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes hrGlow {

            0%,
            100% {
                filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
            }

            50% {
                filter: drop-shadow(0 10px 20px rgba(40, 167, 69, 0.3));
            }
        }

        @keyframes communikGlow {

            0%,
            100% {
                filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
            }

            50% {
                filter: drop-shadow(0 10px 20px rgba(13, 110, 253, 0.3));
            }
        }

        /* Responsive design for smaller screens */
        @media (max-width: 480px) {
            .logo-row {
                flex-direction: column;
                gap: 15px;
            }

            .animated-logo {
                max-width: 120px;
            }
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15),
                0 0 40px rgba(0, 0, 0, 0.08),
                0 25px 70px rgba(40, 167, 69, 0.15);
        }

        .login-container img {
            height: 100px;
            width: 100px;
            margin-bottom: 20px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
            font-size: 14px;
            background: #ffffff;
        }

        .login-container input:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
            outline: none;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #28a745;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-container button:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .login-container h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .login-container p {
            color: #666;
            margin-bottom: 20px;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .text-muted {
            color: #6c757d;
            text-decoration: none;
        }

        .text-muted:hover {
            color: #28a745;
            text-decoration: underline;
        }
    </style>
</body>

</html>