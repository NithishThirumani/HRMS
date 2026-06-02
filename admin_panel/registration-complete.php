<?php session_start();
if (!isset($_SESSION['registration_details'])) {
    header("Location: add_emp.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Confirmation</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <style>
        .confirmation-box {
            background: #ffffff;
            border: none;
            border-radius: 15px;
            padding: 30px;
            max-width: 700px;
            margin: 50px auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .print-header {
            color: #2E8B57;
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .print-header h2 {
            font-weight: 600;
            color: #1a746a;
        }

        .detail-row {
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fc;
            display: flex;
            align-items: center;
        }

        .detail-label {
            font-weight: 600;
            color: #4e73df;
            width: 180px;
        }

        .detail-value {
            color: #5a5c69;
            flex: 1;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: #1cc88a;
            border: none;
        }

        .btn-success:hover {
            background: #169e6c;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #858796;
            border: none;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #717384;
            transform: translateY(-2px);
        }

        @media print {
            body {
                visibility: hidden;
            }

            .confirmation-box {
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                box-shadow: none;
            }

            .btn {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container-fluid">
        <?php if (isset($_SESSION['registration_details'])): ?>
            <div class="confirmation-box">
                <div class="print-header">
                    <h2><i class="fas fa-check-circle mr-2"></i>Employee Registration Confirmation</h2>
                    <p class="text-muted">Generated on: <?= date('Y-m-d H:i:s') ?></p>
                </div>

                <div class="mt-4">
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-id-badge mr-2"></i>Employee ID:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['employee_id'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-user mr-2"></i>Full Name:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['full_name'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-user-circle mr-2"></i>Username:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['username'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-key mr-2"></i>Temporary Password:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['password'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-envelope mr-2"></i>Email:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['email'] ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-calendar mr-2"></i>Registration Date:</span>
                        <span class="detail-value"><?= $_SESSION['registration_details']['registration_date'] ?></span>
                    </div>
                </div>

                <div class="mt-5 text-center">
                    <button onclick="window.print()" class="btn btn-success">
                        <i class="fas fa-print mr-2"></i>Print Receipt
                    </button>
                    <a href="../login.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>New Registration
                    </a>
                </div>
            </div>
            <?php
            session_unset();
            session_destroy();
        else:
            header("Location: add_emp.php");
            exit();
        endif;
        ?>
    </div>
</body>

</html>