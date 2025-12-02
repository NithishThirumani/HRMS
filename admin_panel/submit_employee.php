<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Personal Details
    $personal = $_SESSION['personal_data'];
    $education = $_SESSION['education_data'];
    $employment = $_SESSION['employment_data'];
    $documents = $_SESSION['documents_data'];
    $labor = $_SESSION['labor_card_data'];

    try {
        $conn->beginTransaction();

        // Insert Personal Details
        $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, email, password, birthday, gender, phone, address, country, marital_status, blood_group) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$personal['fn'], $personal['ln'], $personal['em'], 
                       password_hash($personal['ps'], PASSWORD_DEFAULT),
                       $personal['birthday'], $personal['gender'], $personal['pn'],
                       $personal['address'], $personal['country'], 
                       $personal['maritalsts'], $personal['blood_group']]);
        
        $emp_id = $conn->lastInsertId();

        // Insert Education Details
        $stmt = $conn->prepare("INSERT INTO education (emp_id, degree, start_date, end_date, institute) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $education['degree'], $education['start_from'],
                       $education['end_to'], $education['institute']]);

        // Insert Employment Details
        $stmt = $conn->prepare("INSERT INTO employment (emp_id, doj, department, reporting_manager, location, salary) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $employment['doj'], $employment['department'],
                       $employment['reporting_manager'], $employment['EmpLoc'], 
                       $employment['sal']]);

        // Insert Documents Details
        $stmt = $conn->prepare("INSERT INTO documents (emp_id, visa_number, visa_type, visa_issue_date, visa_expiry_date,
                               passport_number, passport_type, passport_issue_date, passport_expiry_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $documents['visa_number'], $documents['visa_type'],
                       $documents['visa_issue_date'], $documents['visa_expiry_date'],
                       $documents['passport_number'], $documents['passport_type'],
                       $documents['passport_issue_date'], $documents['passport_expiry_date']]);

        // Insert Labor Card Details
        $stmt = $conn->prepare("INSERT INTO labor_cards (emp_id, card_number, start_date, end_date) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$emp_id, $labor['labor_card_no'], $labor['labor_card_start_date'],
                       $labor['labor_card_end_date']]);

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Employee registered successfully']);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>