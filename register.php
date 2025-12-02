<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include('header.php');
include('connection.php');

// Add this rate limiting function at the top
function checkRateLimit($con, $ip_address, $max_attempts = 3, $time_window = 300)
{
    // Clean up old attempts (older than time window)
    $cleanup = "DELETE FROM registration_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)";
    $stmt = mysqli_prepare($con, $cleanup);
    mysqli_stmt_bind_param($stmt, "i", $time_window);
    mysqli_stmt_execute($stmt);

    // Count recent attempts
    $check = "SELECT COUNT(*) FROM registration_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
    $stmt = mysqli_prepare($con, $check);
    mysqli_stmt_bind_param($stmt, "si", $ip_address, $time_window);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_row($result)[0];

    // Log this attempt
    $log = "INSERT INTO registration_attempts (ip_address) VALUES (?)";
    $stmt = mysqli_prepare($con, $log);
    mysqli_stmt_bind_param($stmt, "s", $ip_address);
    mysqli_stmt_execute($stmt);

    return $count < $max_attempts;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check rate limit
    if (!checkRateLimit($con, $ip_address)) {
        echo "<script>alert('Too many registration attempts. Please try again later.'); window.location.href='register.php';</script>";
        exit();
    }

    if ($_POST['ps'] !== $_POST['cp']) {
        echo "<script>alert('Error: Passwords do not match'); window.location.href='register.php';</script>";
        exit();
    }

    try {
        // Prepare the statement for email check
        $stmt_check_email = mysqli_prepare($con, "SELECT email FROM employees WHERE email = ?");
        mysqli_stmt_bind_param($stmt_check_email, "s", $_POST['em']);
        mysqli_stmt_execute($stmt_check_email);
        mysqli_stmt_store_result($stmt_check_email);

        if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
            echo "<script>alert('Error: User with this email already exists'); window.location.href='register.php';</script>";
            exit();
        }
        mysqli_stmt_close($stmt_check_email);

        // Generate EID
        $sql_eid = "SELECT MAX(CAST(SUBSTRING(eid, 2) AS UNSIGNED)) AS max_eid FROM employees";
        $result_eid = mysqli_query($con, $sql_eid);
        $row_eid = mysqli_fetch_assoc($result_eid);
        $max_eid = $row_eid['max_eid'] ?? 0;
        $new_eid = 'E' . str_pad(($max_eid + 1), 2, "0", STR_PAD_LEFT);

        // Get reporting manager based on department and role
        $department_id = $_POST['department'];
        $role = 'USER'; // Default role for new registrations

        // Find the HOD for the department
        $sql_hod = "SELECT e.eid FROM employees e 
                    WHERE e.department_id = ? AND e.role = 'HOD'";
        $stmt_hod = mysqli_prepare($con, $sql_hod);
        mysqli_stmt_bind_param($stmt_hod, "i", $department_id);
        mysqli_stmt_execute($stmt_hod);
        $result_hod = mysqli_stmt_get_result($stmt_hod);
        $hod = mysqli_fetch_assoc($result_hod);
        
        // Find HR manager
        $sql_hr = "SELECT e.eid FROM employees e WHERE e.role = 'HR' LIMIT 1";
        $result_hr = mysqli_query($con, $sql_hr);
        $hr = mysqli_fetch_assoc($result_hr);

        // Set reporting manager based on hierarchy
        $reporting_manager = $hod['eid'] ?? ($hr['eid'] ?? null);

        // Prepare user data
        $full_name = $_POST['fn'] . " " . $_POST['ln'];
        $hashed_password = password_hash($_POST['ps'], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));

        // Prepare the statement for employee insertion
        $stmt_insert = mysqli_prepare($con, "INSERT INTO employees (
            eid, first_name, last_name, full_name, email, password, 
            contact, department_id, birthday, gender, address, 
            profile_pic, status, role, reporting_manager, token
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            'profile.jpg', 'pending', ?, ?, ?
        )");

        mysqli_stmt_bind_param(
            $stmt_insert,
            "sssssssssssss",
            $new_eid,
            $_POST['fn'],
            $_POST['ln'],
            $full_name,
            $_POST['em'],
            $hashed_password,
            $_POST['pn'],
            $department_id,
            $_POST['birthday'],
            $_POST['gender'],
            $_POST['address'],
            $role,
            $reporting_manager,
            $verification_token
        );

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt_insert)) {
            mysqli_stmt_close($stmt_insert);
            
            // Insert into emp_login table
            $stmt_login = mysqli_prepare($con, "INSERT INTO emp_login (
                emp_id, email, password, status
            ) VALUES (?, ?, ?, 'inactive')");
            mysqli_stmt_bind_param(
                $stmt_login,
                "sss",
                $new_eid,
                $_POST['em'],
                $hashed_password
            );
            mysqli_stmt_execute($stmt_login);

            // Send verification email
            require 'PHPMailer/PHPMailerAutoload.php';
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'your-smtp-host';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email';
            $mail->Password = 'your-password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your-email', 'Your Company');
            $mail->addAddress($_POST['em']);
            $mail->isHTML(true);

            $mail->Subject = 'Verify Your Account';
            $mail->Body = "Welcome to our system! Please click the link below to verify your account:<br><br>
                          <a href='http://your-domain/verify_account.php?email=" . $_POST['em'] . "&token=" . $verification_token . "'>
                          Verify Account</a>";

            if($mail->send()) {
                $_SESSION['registration_success'] = true;
                $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
                header("Location: login.php");
                exit();
            } else {
                echo "<script>alert('Registration successful but failed to send verification email. Please contact support.');</script>";
            }
        } else {
            echo "<script>alert('Error in registration. Please try again.');</script>";
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo "<script>alert('An error occurred during registration. Please try again later.');</script>";
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="css/register.css">
<script src="js/register.js"></script>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="img/logo.png" alt="Company Logo">
            <h3>Create Your Account</h3>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="form3">
            <div class="auth-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fn1">First Name</label>
                        <input type="text" name="fn" id="fn1" class="form-control" placeholder="Enter First Name"
                            required>
                        <span id="fn_err" class="error-msg"></span>
                    </div>

                    <div class="form-group">
                        <label for="ln1">Last Name</label>
                        <input type="text" name="ln" id="ln1" class="form-control" placeholder="Enter Last Name"
                            required>
                        <span id="ln_err" class="error-msg"></span>
                    </div>

                    <div class="form-group">
                        <label for="em3">Email Address</label>
                        <input type="email" name="em" id="em3" class="form-control"
                            placeholder="Enter your Email Address" required>
                        <span id="em1_err" class="error-msg"></span>
                    </div>
                    <!-- Replace the existing password fields with these -->
                    <div class="form-group">
                        <label for="ps1">Password</label>
                        <div class="password-field">
                            <input type="password" name="ps" id="ps1" class="form-control"
                                placeholder="Create a Password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('ps1')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span id="ps_err" class="error-msg"></span>
                    </div>

                    <div class="form-group">
                        <label for="cp1">Confirm Password</label>
                        <div class="password-field">
                            <input type="password" name="cp" id="cp1" class="form-control"
                                placeholder="Confirm Password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('cp1')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span id="cp_err" class="error-msg"></span>
                    </div>

                    <!-- Remove the existing show password checkbox -->

                    <!-- Update the JavaScript function -->
                    <script>
                        function togglePassword(inputId) {
                            const input = document.getElementById(inputId);
                            const icon = event.currentTarget.querySelector('i');

                            if (input.type === "password") {
                                input.type = "text";
                                icon.classList.remove("fa-eye");
                                icon.classList.add("fa-eye-slash");
                            } else {
                                input.type = "password";
                                icon.classList.remove("fa-eye-slash");
                                icon.classList.add("fa-eye");
                            }
                        }
                    </script>
                    <div class="form-group">
                        <label for="pn1">Phone Number</label>
                        <input type="tel" name="pn" id="pn1" class="form-control" placeholder="Enter Your Phone Number"
                            required>
                        <span id="pn_err" class="error-msg"></span>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Date of Birth</label>
                        <input type="date" name="birthday" id="birthday" class="form-control" required>
                        <span id="birthday_err" class="error-msg"></span>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="gender-group">
                            <label><input type="radio" name="gender" value="Male" checked> Male</label>
                            <label><input type="radio" name="gender" value="Female"> Female</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select name="department" id="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="IT">IT</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Operations">Operations</option>
                        </select>
                    </div>



                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2"
                            placeholder="Enter your Address" required></textarea>
                        <span id="address_err" class="error-msg"></span>
                    </div>



                    <div class="form-group full-width">
                        <button class="btn-register" name="register" type="submit">Create Account</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="auth-footer">
            <p>Already have an account?</p>
            <a href="login.php">
                <button class="btn-login" type="button">Sign in</button>
            </a>
        </div>
    </div>
</div>

<script>
    function myFunction() {
        var x = document.getElementById("ps1");
        var y = document.getElementById("cp1");
        if (x.type === "password") {
            x.type = "text";
            y.type = "text";
        } else {
            x.type = "password";
            y.type = "password";
        }
    }
</script>

<?php

include('footer.php');
?>