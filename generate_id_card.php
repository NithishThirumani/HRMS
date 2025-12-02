<?php
require_once 'connection.php';
require_once 'classes/IDCardGenerator.php';

// Update the redirect location
if (!isset($_GET['id'])) {
    header('Location: admin_panel/view_emp1.php');
    exit();
}

$idCard = new IDCardGenerator($con);
$cardHTML = $idCard->generateIDCard($_GET['id']);

if (!$cardHTML) {
    header('Location: admin_panel/view_emp1.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee ID Card</title>
    <style>
        .card-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px;
        }

        .id-card {
            width: 350px;
            height: 520px;
            background: linear-gradient(135deg, #B2EBF2 0%, #E0F7FA 100%);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid #80DEEA;
        }

        .header {
            background: #008B8B;
            padding: 15px;
            text-align: center;
        }

        .card-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }


        .info-section {
            margin-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .qr-section {
            text-align: center;
            margin-top: 10px;
        }

        .qr-section img {
            width: 90px;
            height: 90px;
            padding: 5px;
            background: white;
            border-radius: 8px;
            margin: 0 auto;
        }


        .signature-section {
            margin-top: 20px;
            text-align: center;
            padding: 10px;
            background: transparent;
            border: none;
        }

        .sign-line {
            width: 150px;
            height: 1px;
            background: #333;
            margin: 0 auto 5px;
        }

        .sign-text {
            font-size: 12px;
            color: #333;
        }


        .id-card:hover {
            transform: translateY(-5px);
        }

        .card-back {
            background: linear-gradient(135deg, #B2EBF2 0%, #E0F7FA 100%);
        }

        .header {
            background: linear-gradient(135deg, #00838F 0%, #006064 100%);
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #80DEEA;
        }


        .logo {
            height: 35px;
            margin-bottom: 8px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .back-header {
            padding: 15px;
            background: rgba(153, 219, 227);
            border-bottom: 2px solid #B2EBF2;
        }

        .back-header h2 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .header h1 {
            color: white;
            font-size: 16px;
            margin: 5px 0;
        }

        .card-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: space-between;
            /* Distribute space evenly */
            height: calc(100% - 60px);
            /* Adjusted height calculation */
        }

        .profile-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            margin: 0 auto 10px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #fff;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-name {
            font-size: 24px;
            font-weight: 700;
            color: rgb(2, 61, 63);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }

        .designation {
            color: rgb(2, 61, 63);
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .additional-info {
            background: rgba(153, 219, 227);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .additional-info h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .info-section,
        .info-group {
            background: solidrgb(178, 235, 242);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solidrgb(178, 235, 242);
        }


        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .company-info {
            background: rgba(165, 243, 239, 0.9);
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #B2EBF2;
        }


        .company-info h3 {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .company-info p {
            margin: 4px 0;
            color: #666;
        }

        .card-no {
            font-size: 12px;
            color: rgb(2, 61, 63);
            margin-top: 5px;
            font-weight: 500;
        }


        .sign-text {
            font-size: 12px;
            color: #666;
        }

        .print-btn {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 12px;
            background: linear-gradient(135deg, #00838F 0%, #006064 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-align: center;
            font-weight: 500;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        @media print {
            .card-container {
                gap: 0;
            }

            .print-btn {
                display: none;
            }

            .id-card {
                width: 350px;
                height: 550px;
                /* Increased height */
                background: linear-gradient(135deg, rgb(153, 219, 227) 0%, #B2EBF2 100%);
                border-radius: 25px;
                /* Rounded corners like in image */
                overflow: hidden;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                border: 1px solid #80DEEA;
                transition: transform 0.3s ease;
            }
        }
    </style>
</head>

<body>



    <button class="print-btn" onclick="window.print()">Print ID Card</button>
    <?php echo $cardHTML; ?>
</body>

</html>