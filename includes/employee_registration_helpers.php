<?php
/**
 * Shared validation and helpers for Add Employee (admin / HR / HOD panels).
 */
require_once __DIR__ . '/email_functions.php';

if (!function_exists('hrms_normalize_email')) {
    function hrms_normalize_email(string $email): string
    {
        return strtolower(trim($email));
    }
}

if (!function_exists('hrms_registration_fail')) {
    function hrms_registration_fail(string $message): void
    {
        $safe = json_encode($message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        echo '<script>alert(' . $safe . '); window.history.back();</script>';
        exit();
    }
}

if (!function_exists('hrms_db_connection_label')) {
    /** Shown in admin UI so staff know which database receives new rows. */
    function hrms_db_connection_label(): string
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'EMPS';
        $ssl = filter_var(getenv('DB_SSL') ?: '0', FILTER_VALIDATE_BOOLEAN) ? ' (SSL)' : '';

        return $host . ':' . $port . ' / ' . $name . $ssl;
    }
}

if (!function_exists('hrms_employee_email_exists')) {
    function hrms_employee_email_exists(mysqli $con, string $email): bool
    {
        $email = hrms_normalize_email($email);
        if ($email === '') {
            return false;
        }

        $tables = [
            'SELECT 1 FROM employees WHERE LOWER(TRIM(email)) = ? LIMIT 1',
            'SELECT 1 FROM emp_login WHERE LOWER(TRIM(email)) = ? LIMIT 1',
        ];

        foreach ($tables as $sql) {
            $stmt = $con->prepare($sql);
            if (!$stmt) {
                continue;
            }
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            $found = $stmt->num_rows > 0;
            $stmt->close();
            if ($found) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('hrms_employee_visa_exists')) {
    function hrms_employee_visa_exists(mysqli $con, string $visa_number): bool
    {
        $visa_number = trim($visa_number);
        if ($visa_number === '') {
            return false;
        }

        $sql = 'SELECT id FROM employees WHERE visa_number = ? LIMIT 1';
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $visa_number);
        $stmt->execute();
        $stmt->store_result();
        $found = $stmt->num_rows > 0;
        $stmt->close();

        return $found;
    }
}

if (!function_exists('hrms_employee_passport_exists')) {
    function hrms_employee_passport_exists(mysqli $con, string $passport_number): bool
    {
        $passport_number = trim($passport_number);
        if ($passport_number === '') {
            return false;
        }

        $sql = 'SELECT id FROM employees WHERE passport_number = ? LIMIT 1';
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $passport_number);
        $stmt->execute();
        $stmt->store_result();
        $found = $stmt->num_rows > 0;
        $stmt->close();

        return $found;
    }
}

if (!function_exists('hrms_registration_precheck')) {
    /**
     * Run all duplicate checks before uploads / password_hash / SMTP.
     *
     * @return string|null Error message, or null if OK.
     */
    function hrms_registration_precheck(mysqli $con, string $email, string $visa_number, string $passport_number): ?string
    {
        if ($email === '') {
            return 'Email is required.';
        }

        if (hrms_employee_email_exists($con, $email)) {
            return 'This email is already registered (employee or login account). Use a different email or update the existing employee.';
        }

        if ($visa_number === '') {
            return 'Visa number is required.';
        }

        if (hrms_employee_visa_exists($con, $visa_number)) {
            return 'This visa number is already assigned to another employee.';
        }

        if ($passport_number !== '' && hrms_employee_passport_exists($con, $passport_number)) {
            return 'This passport number is already assigned to another employee.';
        }

        return null;
    }
}

if (!function_exists('hrms_generate_next_eid')) {
    function hrms_generate_next_eid(mysqli $con): string
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(eid, 4) AS UNSIGNED)) AS max_eid FROM employees WHERE eid REGEXP '^CME0?[0-9]+$'";
        $result = mysqli_query($con, $sql);
        $max_eid = 0;
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $max_eid = (int) ($row['max_eid'] ?? 0);
        }

        $next = $max_eid + 1;
        $new_eid = 'CME0' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        $check = $con->prepare('SELECT eid FROM employees WHERE eid = ? LIMIT 1');
        while ($check) {
            $check->bind_param('s', $new_eid);
            $check->execute();
            $check->store_result();
            if ($check->num_rows === 0) {
                break;
            }
            $next++;
            $new_eid = 'CME0' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $check->free_result();
        }
        if ($check) {
            $check->close();
        }

        return $new_eid;
    }
}

if (!function_exists('hrms_send_registration_welcome_email')) {
    /**
     * Optional welcome email using email_config. Never blocks registration (short timeout).
     */
    function hrms_send_registration_welcome_email(
        mysqli $con,
        string $email,
        string $first_name,
        string $username,
        string $token
    ): void {
        if (!filter_var(getenv('HRMS_SEND_REGISTRATION_EMAIL') ?: '0', FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $config = getEmailConfig($con);
        if (!$config || empty($config['host']) || empty($config['username'])) {
            return;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->Timeout = 10;
            $mail->SMTPKeepAlive = false;

            if (isset($config['secure']) && $config['secure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = (int) ($config['port'] ?? 587);
            $mail->setFrom($config['from_email'], $config['from_name'] ?? 'HRMS');
            $mail->addAddress($email, $first_name);
            $mail->isHTML(true);
            $mail->Subject = 'Account created';
            $mail->Body = 'Hello ' . htmlspecialchars($first_name) . ', your HRMS account was created.<br>Username: '
                . htmlspecialchars($username);
            $mail->send();
        } catch (Throwable $e) {
            error_log('Registration welcome email failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('hrms_registration_duplicate_message')) {
    function hrms_registration_duplicate_message(mysqli $con, int $errno, string $error): string
    {
        if ($errno === 1062) {
            if (stripos($error, 'email') !== false) {
                return 'This email is already registered. The employee may have been created on a previous attempt — refresh the employee list before trying again.';
            }
            if (stripos($error, 'visa') !== false) {
                return 'This visa number already exists in the database.';
            }
            if (stripos($error, 'passport') !== false) {
                return 'This passport number already exists in the database.';
            }
            return 'A duplicate value was detected. Please check email, visa, and passport numbers.';
        }

        return 'Could not save employee: ' . $error;
    }
}
