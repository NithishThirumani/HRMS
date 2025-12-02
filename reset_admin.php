<?php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Special reset key to prevent unauthorized access
$RESET_KEY = "reset_super_admin_2024"; // Change this to a secure value

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reset_key = $_POST['reset_key'] ?? '';
    
    if ($reset_key === $RESET_KEY) {
        try {
            // Start transaction
            mysqli_begin_transaction($con);

            // Remove super admin from admin table
            $sql1 = "DELETE FROM admin WHERE role = 'super_admin'";
            if (!mysqli_query($con, $sql1)) {
                throw new Exception("Error removing from admin table: " . mysqli_error($con));
            }

            // Get employee IDs with super_admin role
            $sql2 = "SELECT eid FROM employees WHERE role = 'super_admin'";
            $result = mysqli_query($con, $sql2);
            $eids = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $eids[] = $row['eid'];
            }

            // Remove from emp_login table
            if (!empty($eids)) {
                $eids_str = "'" . implode("','", $eids) . "'";
                $sql3 = "DELETE FROM emp_login WHERE emp_id IN ($eids_str)";
                if (!mysqli_query($con, $sql3)) {
                    throw new Exception("Error removing from emp_login table: " . mysqli_error($con));
                }
            }

            // Remove from employees table
            $sql4 = "DELETE FROM employees WHERE role = 'super_admin'";
            if (!mysqli_query($con, $sql4)) {
                throw new Exception("Error removing from employees table: " . mysqli_error($con));
            }

            // Commit transaction
            mysqli_commit($con);
            
            echo "<script>
                alert('Super admin reset successful. You can now use setup_admin.php to create a new super admin.');
                window.location.href = 'setup_admin.php';
            </script>";
            exit();

        } catch (Exception $e) {
            mysqli_rollback($con);
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid reset key!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Super Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .reset-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
            color: #dc3545;
        }
        .form-group label {
            font-weight: 600;
            color: #34495e;
        }
        .btn-reset {
            background-color: #dc3545;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        .btn-reset:hover {
            background-color: #c82333;
            color: white;
        }
        .warning-text {
            color: #dc3545;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="reset-header">
                <h2>⚠️ Reset Super Admin</h2>
                <p class="text-muted">This will remove the existing super admin account</p>
            </div>

            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to reset the super admin? This action cannot be undone!');">
                <div class="form-group">
                    <label for="reset_key">Reset Key</label>
                    <input type="password" class="form-control" id="reset_key" name="reset_key" required>
                    <small class="form-text text-muted">
                        Enter the reset key to proceed with the super admin reset
                    </small>
                </div>

                <button type="submit" class="btn btn-reset">Reset Super Admin</button>
            </form>

            <div class="warning-text">
                <p>⚠️ Warning: This action will remove the existing super admin account.</p>
                <p>After reset, you will need to create a new super admin using setup_admin.php</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 