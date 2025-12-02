<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once('../../connection.php');

// Debug: Log all POST data
file_put_contents('debug.log', date('Y-m-d H:i:s') . ' POST Data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' Error: Invalid request method' . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$appraisal_id = $_POST['assignment_id'] ?? null;
$ratings = $_POST['ratings'] ?? [];
$comments = $_POST['comments'] ?? [];

// Debug: Log processed data
file_put_contents('debug.log', date('Y-m-d H:i:s') . ' Processed Data: ' . 
    "Appraisal ID: $appraisal_id, " . 
    "Ratings: " . print_r($ratings, true) . 
    "Comments: " . print_r($comments, true) . "\n", 
FILE_APPEND);

if (!$appraisal_id || empty($ratings) || empty($comments)) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ' Error: Missing required data' . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    exit;
}

try {
    $con->begin_transaction();

    // Get correct employee ID (using id, not eid)
    $username = $_SESSION['username'];
    $empQuery = "SELECT id FROM employees WHERE user_name = ?";
    $empStmt = $con->prepare($empQuery);
    $empStmt->bind_param("s", $username);
    $empStmt->execute();
    $empResult = $empStmt->get_result();
    $employee = $empResult->fetch_assoc();
    $employee_id = $employee['id'];

    // Get period_id from assignment
    $stmt = $con->prepare("SELECT period_id FROM appraisal_assignments WHERE id = ?");
    $stmt->bind_param("i", $appraisal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignment = $result->fetch_assoc();
    
    if (!$assignment) {
        throw new Exception('Assignment not found');
    }
    
    $period_id = $assignment['period_id'];

    // Create or get employee_appraisal using correct employee id
    $stmt = $con->prepare("SELECT appraisal_id FROM employee_appraisals WHERE period_id = ? AND employee_id = ?");
    $stmt->bind_param("ii", $period_id, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        $stmt = $con->prepare("INSERT INTO employee_appraisals (period_id, employee_id, status) VALUES (?, ?, 'In Progress')");
        $stmt->bind_param("ii", $period_id, $employee_id);
        $stmt->execute();
        $employee_appraisal_id = $con->insert_id;
    } else {
        $employee_appraisal_id = $row['appraisal_id'];
    }

    // Get the correct appraisal_id using period_id and employee_id
    $stmt = $con->prepare("SELECT ea.appraisal_id 
                          FROM employee_appraisals ea 
                          JOIN appraisal_assignments aa ON ea.period_id = aa.period_id 
                          WHERE aa.id = ? AND aa.employee_id = ?");
    
    // Get employee_id from session
    $username = $_SESSION['username'];
    $empQuery = "SELECT eid FROM employees WHERE user_name = ?";
    $empStmt = $con->prepare($empQuery);
    $empStmt->bind_param("s", $username);
    $empStmt->execute();
    $empResult = $empStmt->get_result();
    $employee = $empResult->fetch_assoc();
    $employee_id = $employee['eid'];

    // Now get the appraisal_id
    $stmt->bind_param("is", $appraisal_id, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception('Invalid appraisal assignment');
    }
    
    $employee_appraisal_id = $row['appraisal_id'];

    // Update assignment status
    $stmt = $con->prepare("UPDATE appraisal_assignments SET status = 'Submitted' WHERE id = ?");
    $stmt->bind_param("i", $appraisal_id);
    $stmt->execute();

    // Insert ratings using the correct appraisal_id
    $stmt = $con->prepare("INSERT INTO appraisal_ratings (appraisal_id, criteria_id, self_rating, hod_rating, hr_rating, comments) 
                          VALUES (?, ?, ?, NULL, NULL, ?)");
    
    foreach ($ratings as $criteria_id => $rating) {
        $comment = $comments[$criteria_id] ?? '';
        $stmt->bind_param("iids", $employee_appraisal_id, $criteria_id, $rating, $comment);
        $stmt->execute();
    }

    $con->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    $con->rollback();
    error_log("Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$con->close();