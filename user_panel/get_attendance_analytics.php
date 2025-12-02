<?php
include('session.php');
header('Content-Type: application/json');

try {
    // Get date range from request or default to current month
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
    
    // Get employee ID
    $emp_query = "SELECT e.eid, e.role FROM employees e 
                  JOIN emp_login el ON e.eid = el.emp_id 
                  WHERE el.user_name = ?";
    $stmt = mysqli_prepare($con, $emp_query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $emp_data = mysqli_fetch_assoc($stmt->get_result());

    // Build query based on role
    if($emp_data['role'] == 'hr' || $emp_data['role'] == 'admin') {
        $query = "SELECT 
                    COUNT(DISTINCT CASE WHEN status = 'Present' THEN attendance_date END) as present_days,
                    AVG(total_hours) as avg_hours,
                    COUNT(DISTINCT attendance_date) as total_days
                  FROM attendance 
                  WHERE attendance_date BETWEEN ? AND ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
    } else {
        $query = "SELECT 
                    COUNT(DISTINCT CASE WHEN status = 'Present' THEN attendance_date END) as present_days,
                    AVG(total_hours) as avg_hours,
                    COUNT(DISTINCT attendance_date) as total_days
                  FROM attendance 
                  WHERE eid = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sss", $emp_data['eid'], $startDate, $endDate);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $analytics = mysqli_fetch_assoc($result);

    // Calculate metrics
    $presentDays = $analytics['present_days'] ?? 0;
    $averageHours = round($analytics['avg_hours'] ?? 0, 1);
    $totalDays = $analytics['total_days'] ?? 0;
    $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'presentDays' => $presentDays,
        'averageHours' => $averageHours,
        'attendanceRate' => $attendanceRate
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>