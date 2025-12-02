<?php
include('session.php');
include('connection.php');

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Onboarding_Status_Report.xls"');
header('Cache-Control: max-age=0');

// Create the header row
$header = "Full Name\tEmployee Code\tVisa Under\tManager/TL Name\tStatus\tDesignation\tClient Team\tDate of Joining\t";
$header .= "Biometric Login\tProcess Evaluation\tOffer Letter\tResume\tKYC\tPolicy Docs\tStaff ID\tDeem ID Request\tRemarks\n";

echo $header;

// Fetch employee data with document status
$query = "SELECT e.*, d.name as department_name, ds.* 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN document_status ds ON e.id = ds.employee_id 
          ORDER BY e.doj DESC";

$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Get document upload status from document_uploads table
    $upload_query = "SELECT document_type, status FROM document_uploads WHERE id = '{$row['id']}'";
    $upload_result = mysqli_query($con, $upload_query);
    $uploads = [];
    while ($upload = mysqli_fetch_assoc($upload_result)) {
        $uploads[$upload['document_type']] = $upload['status'];
    }

    $line = array(
        $row['full_name'],
        $row['eid'],
        $row['visa_type'] ?? 'N/A',
        $row['reporting_manager'],
        ucfirst($row['status']),
        $row['designation'],
        $row['department_name'],
        date('d M Y', strtotime($row['doj'])),
        $row['biometric_login'] ?? 'No',
        $row['process_evaluation'] ?? 'No',
        $row['offer_letter'] ?? 'No',
        $row['resume'] ?? 'No',
        $row['kyc'] ?? 'No',
        $row['policy_docs'] ?? 'No',
        $row['staff_id'] ?? 'No',
        $row['deem_id_request'] ?? 'No',
        $row['remarks'] ?? ''
    );
    
    // Clean the data and escape any special characters
    foreach ($line as &$field) {
        $field = str_replace(array("\r", "\n", "\t"), ' ', $field);
        $field = preg_replace('/\s+/', ' ', $field);
    }
    
    echo implode("\t", $line) . "\n";
}

exit;
?>