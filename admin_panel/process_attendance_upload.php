<?php
include('session.php');
require 'vendor/autoload.php'; // Make sure this path is correct

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($_FILES['attendance_file'])) {
            throw new Exception('No file uploaded');
        }

        $file = $_FILES['attendance_file'];
        $fileName = $file['name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($fileType, ['xlsx', 'xls', 'csv'])) {
            throw new Exception('Invalid file type. Please upload Excel or CSV file.');
        }

        // Load the spreadsheet
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Remove header row
        $header = array_shift($rows);

        // Begin transaction
        mysqli_begin_transaction($con);

        foreach ($rows as $row) {
            // Map columns to variables
            $employee_code = mysqli_real_escape_string($con, $row[1]); // Employee Code
            $full_name = mysqli_real_escape_string($con, $row[0]);
            $visa_under = mysqli_real_escape_string($con, $row[2]);
            $manager = mysqli_real_escape_string($con, $row[3]);
            $status = mysqli_real_escape_string($con, $row[4]);
            $designation = mysqli_real_escape_string($con, $row[5]);
            $client_team = mysqli_real_escape_string($con, $row[6]);
            $mobile = mysqli_real_escape_string($con, $row[7]);
            $email = mysqli_real_escape_string($con, $row[8]);
            $joining_date = mysqli_real_escape_string($con, $row[9]);
            $offer_letter = mysqli_real_escape_string($con, $row[10]);
            $payroll_start = mysqli_real_escape_string($con, $row[11]);
            $absent_days = (int)$row[12];
            $late_entries = (int)$row[13];
            $sick_leave = (int)$row[14];
            $approved_leave = (int)$row[15];
            $half_days = (int)$row[16];
            $annual_leave = (int)$row[17];
            $ontime_entries = (int)$row[18];
            $payable_days = (float)$row[19];

            // Update or insert employee data
            $query = "INSERT INTO employee_attendance 
                    (employee_code, full_name, visa_under, manager, status, designation, 
                    client_team, mobile, email, joining_date, offer_letter, payroll_start,
                    absent_days, late_entries, sick_leave, approved_leave, half_days,
                    annual_leave, ontime_entries, payable_days, upload_date)
                    VALUES 
                    ('$employee_code', '$full_name', '$visa_under', '$manager', '$status',
                    '$designation', '$client_team', '$mobile', '$email', '$joining_date',
                    '$offer_letter', '$payroll_start', $absent_days, $late_entries,
                    $sick_leave, $approved_leave, $half_days, $annual_leave,
                    $ontime_entries, $payable_days, NOW())
                    ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    visa_under = VALUES(visa_under),
                    manager = VALUES(manager),
                    status = VALUES(status),
                    designation = VALUES(designation),
                    client_team = VALUES(client_team),
                    mobile = VALUES(mobile),
                    email = VALUES(email),
                    joining_date = VALUES(joining_date),
                    offer_letter = VALUES(offer_letter),
                    payroll_start = VALUES(payroll_start),
                    absent_days = VALUES(absent_days),
                    late_entries = VALUES(late_entries),
                    sick_leave = VALUES(sick_leave),
                    approved_leave = VALUES(approved_leave),
                    half_days = VALUES(half_days),
                    annual_leave = VALUES(annual_leave),
                    ontime_entries = VALUES(ontime_entries),
                    payable_days = VALUES(payable_days),
                    upload_date = NOW()";

            if (!mysqli_query($con, $query)) {
                throw new Exception("Error inserting data for employee: $employee_code");
            }
        }

        // Commit transaction
        mysqli_commit($con);
        $_SESSION['success'] = "Attendance data uploaded successfully!";
        header("Location: upload_attendance.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: upload_attendance.php");
        exit();
    }
}
?>