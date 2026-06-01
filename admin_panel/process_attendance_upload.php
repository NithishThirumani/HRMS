<?php
include('session.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload_attendance.php');
    exit();
}

/**
 * Parse dates from Excel/CSV (d/m/Y, m/d/Y, Y-m-d, Excel serial, "27 Sep 2025", etc.)
 */
function parse_attendance_date($raw): ?string
{
    $raw = trim((string)$raw);
    if ($raw === '') {
        return null;
    }

    if (is_numeric($raw) && (float)$raw > 30000) {
        $unix = ((float)$raw - 25569) * 86400;
        return gmdate('Y-m-d', (int)$unix);
    }

    $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'd/m/y', 'j M Y', 'd M Y', 'M j, Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $raw);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($raw);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }

    return null;
}

function normalize_header(string $h): string
{
    return strtolower(trim(preg_replace('/\s+/', ' ', $h)));
}

function find_column_index(array $header, array $candidates)
{
    foreach ($candidates as $name) {
        $idx = array_search($name, $header, true);
        if ($idx !== false) {
            return $idx;
        }
    }
    return false;
}

try {
    if (!isset($_FILES['attendance_file']) || $_FILES['attendance_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload failed.');
    }

    $file = $_FILES['attendance_file'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileType, ['csv', 'xlsx', 'xls'], true)) {
        throw new Exception('Invalid file type. Please upload a CSV or Excel file.');
    }

    $rows = [];

    if ($fileType === 'csv') {
        if (($handle = fopen($file['tmp_name'], 'r')) === false) {
            throw new Exception('Could not read CSV file.');
        }
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
    } else {
        if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
            throw new Exception('Excel support requires Composer packages. Save the file as CSV and upload again.');
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray();
    }

    if (count($rows) < 2) {
        throw new Exception('File is empty or has no data rows.');
    }

    $header = array_map('normalize_header', $rows[0]);
    array_shift($rows);

    $eidIdx = find_column_index($header, ['eid', 'employee id', 'employee code', 'staff code', 'emp id']);
    if ($eidIdx === false) {
        $eidIdx = 0;
    }

    $dateIdx = find_column_index($header, ['attendance_date', 'attendance date', 'date', 'work date']);
    $statusIdx = find_column_index($header, ['attendance status', 'attendance', 'punch status']);
    if ($statusIdx === false) {
        $statusIdx = find_column_index($header, ['status']);
    }

    $hasPerRowDates = ($dateIdx !== false);

    $bulkDate = null;
    if (!empty($_POST['import_date'])) {
        $bulkDate = parse_attendance_date($_POST['import_date']);
    }

    $isEmployeeList = !$hasPerRowDates && (
        find_column_index($header, ['employee id', 'employee', 'employee name']) !== false
        || $eidIdx === 0
    );

    if ($isEmployeeList && $bulkDate === null) {
        throw new Exception(
            'Your file looks like an employee list (no attendance date column). ' .
            'Choose an "Attendance date" on the upload form, or use a file with columns: eid, attendance_date, status.'
        );
    }

    if (!$isEmployeeList && !$hasPerRowDates) {
        throw new Exception('Could not find a date column. Add attendance_date (or pick a bulk date for employee lists).');
    }

    $checkStmt = mysqli_prepare($con, 'SELECT id FROM attendance WHERE eid = ? AND attendance_date = ? LIMIT 1');
    $updateStmt = mysqli_prepare($con, 'UPDATE attendance SET status = ? WHERE eid = ? AND attendance_date = ?');
    $insertStmt = mysqli_prepare($con, 'INSERT INTO attendance (eid, attendance_date, status, created_at) VALUES (?, ?, ?, NOW())');
    $empStmt = mysqli_prepare($con, 'SELECT eid FROM employees WHERE eid = ? LIMIT 1');

    if (!$checkStmt || !$updateStmt || !$insertStmt || !$empStmt) {
        throw new Exception('Database prepare failed: ' . mysqli_error($con));
    }

    mysqli_begin_transaction($con);

    $imported = 0;
    $skipped = 0;
    $errors = [];

    foreach ($rows as $rowNum => $row) {
        if (!is_array($row) || count($row) === 0) {
            continue;
        }

        $eid = trim((string)($row[$eidIdx] ?? ''));
        if ($eid === '' || stripos($eid, 'employee') !== false) {
            continue;
        }

        if ($hasPerRowDates) {
            $attendanceDate = parse_attendance_date($row[$dateIdx] ?? '');
        } else {
            $attendanceDate = $bulkDate;
        }

        if ($attendanceDate === null) {
            $skipped++;
            continue;
        }

        mysqli_stmt_bind_param($empStmt, 's', $eid);
        mysqli_stmt_execute($empStmt);
        $empResult = mysqli_stmt_get_result($empStmt);
        if (!$empResult || mysqli_num_rows($empResult) === 0) {
            $skipped++;
            $errors[] = "Unknown employee ID: {$eid}";
            continue;
        }

        if ($statusIdx !== false && isset($row[$statusIdx])) {
            $rawStatus = trim((string)$row[$statusIdx]);
            $status = in_array(strtolower($rawStatus), ['active', 'present', 'p'], true) ? 'Present'
                : (in_array(strtolower($rawStatus), ['inactive', 'absent', 'a'], true) ? 'Absent' : $rawStatus);
        } else {
            $status = 'Present';
        }

        if ($status === '') {
            $status = 'Present';
        }

        mysqli_stmt_bind_param($checkStmt, 'ss', $eid, $attendanceDate);
        mysqli_stmt_execute($checkStmt);
        $existing = mysqli_stmt_get_result($checkStmt);

        if ($existing && mysqli_fetch_assoc($existing)) {
            mysqli_stmt_bind_param($updateStmt, 'sss', $status, $eid, $attendanceDate);
            $ok = mysqli_stmt_execute($updateStmt);
        } else {
            mysqli_stmt_bind_param($insertStmt, 'sss', $eid, $attendanceDate, $status);
            $ok = mysqli_stmt_execute($insertStmt);
        }

        if (!empty($ok)) {
            $imported++;
        } else {
            $skipped++;
        }
    }

    if ($imported === 0) {
        mysqli_rollback($con);
        $hint = $isEmployeeList
            ? ' No rows imported. Use employee IDs like CME0144 and pick the correct attendance date.'
            : ' No rows imported. Check column headers: eid, attendance_date, status.';
        throw new Exception('Import failed.' . $hint . ($errors ? ' ' . implode(' ', array_slice($errors, 0, 3)) : ''));
    }

    mysqli_commit($con);

    $dateNote = $bulkDate ? " for {$bulkDate}" : '';
    $_SESSION['success'] = "Attendance import complete: {$imported} record(s) saved{$dateNote}" . ($skipped ? " ({$skipped} skipped)." : '.');
} catch (Exception $e) {
    if (isset($con)) {
        mysqli_rollback($con);
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: upload_attendance.php');
exit();
