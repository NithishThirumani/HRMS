<?php
include('connection.php');
include('session.php');

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Trainees_Report_' . date('Y-m-d') . '.xls"');

// Fetch all trainees with department names
$query = "SELECT t.*, d.name as department_name 
          FROM trainees t 
          LEFT JOIN departments d ON t.department_id = d.id 
          ORDER BY t.trainee_id";
$result = mysqli_query($mysqli, $query);

// Start the Excel content
echo '<table border="1">';

// Headers
echo '<tr style="background-color: #f0f0f0; font-weight: bold;">
        <th>Trainee ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Contact</th>
        <th>Gender</th>
        <th>Date of Birth</th>
        <th>Marital Status</th>
        <th>Blood Group</th>
        <th>Address</th>
        <th>Country</th>
        <th>Department</th>
        <th>Designation</th>
        <th>Date of Joining</th>
        <th>Location</th>
        <th>Reporting Manager</th>
        <th>Training Duration (Months)</th>
        <th>Status</th>
        <th>Degree</th>
        <th>Institute</th>
        <th>Education Start</th>
        <th>Education End</th>
      </tr>';

// Data rows
while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['trainee_id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . htmlspecialchars($row['contact']) . '</td>';
    echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
    echo '<td>' . htmlspecialchars($row['birthday']) . '</td>';
    echo '<td>' . htmlspecialchars($row['marital_status']) . '</td>';
    echo '<td>' . htmlspecialchars($row['blood_group']) . '</td>';
    echo '<td>' . htmlspecialchars($row['address']) . '</td>';
    echo '<td>' . htmlspecialchars($row['country']) . '</td>';
    echo '<td>' . htmlspecialchars($row['department_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['designation']) . '</td>';
    echo '<td>' . htmlspecialchars($row['date_of_joining']) . '</td>';
    echo '<td>' . htmlspecialchars($row['location']) . '</td>';
    echo '<td>' . htmlspecialchars($row['reporting_manager']) . '</td>';
    echo '<td>' . htmlspecialchars($row['training_duration']) . '</td>';
    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
    echo '<td>' . htmlspecialchars($row['degree']) . '</td>';
    echo '<td>' . htmlspecialchars($row['institute']) . '</td>';
    echo '<td>' . htmlspecialchars($row['education_start']) . '</td>';
    echo '<td>' . htmlspecialchars($row['education_end']) . '</td>';
    echo '</tr>';
}

echo '</table>';
?> 