// Add at the beginning after session check
    if($_POST['attendance_date'] != date('Y-m-d')) {
        echo json_encode([
            'success' => false,
            'message' => 'Attendance can only be marked for current date'
        ]);
        exit;
    }