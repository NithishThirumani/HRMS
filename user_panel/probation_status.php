<?php
// Include database connection
header('Content-Type: application/json');

include('connection.php');

// SQL query to get probation status
$query = "
    SELECT e.eid, e.user_name, e.doj,
    CASE 
        WHEN e.doj = '0000-00-00' THEN 'Invalid Date'
        WHEN DATE_ADD(e.doj, INTERVAL 6 MONTH) <= CURDATE() THEN 
            IF(e.probation_passed = 1, 'Completed', 'Pending')
        ELSE 'Pending'
    END AS probation_status,
    e.probation_passed_date
    FROM employees e
";

$result = $con->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($data);

$con->close();
?>
