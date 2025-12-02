<?php
include('connection.php');
include('session.php');
require_once __DIR__ . '/../vendor/autoload.php';

// Create new mPDF document
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L', 'format' => 'A4']);

// Set document information
$mpdf->SetTitle('Trainees Report');

// Header HTML
$header = '<div style="text-align: center; font-weight: bold; font-size: 18px;">Trainees Report</div><hr style="margin-bottom:10px;">';
$mpdf->SetHTMLHeader($header);

// Fetch all trainees with department names
$query = "SELECT t.*, d.name as department_name 
          FROM trainees t 
          LEFT JOIN departments d ON t.department_id = d.id 
          ORDER BY t.trainee_id";
$result = mysqli_query($con, $query);

// Table headers
$headers = array(
    'Trainee ID', 'Full Name', 'Email', 'Contact', 'Department', 
    'Designation', 'Join Date', 'Status', 'Location'
);

// Start HTML table
$html = '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
$html .= '<thead><tr style="background:#f0f0f0; font-weight:bold;">';
foreach ($headers as $head) {
    $html .= '<th>' . htmlspecialchars($head) . '</th>';
}
$html .= '</tr></thead><tbody>';

// Data rows
while($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr>';
    $html .= '<td align="center">' . htmlspecialchars($row['trainee_id']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['full_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['email']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['contact']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['designation']) . '</td>';
    $html .= '<td align="center">' . htmlspecialchars($row['date_of_joining']) . '</td>';
    $html .= '<td align="center">' . htmlspecialchars($row['status']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['location']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// Write HTML to PDF
$mpdf->WriteHTML($html);

// Output PDF
$mpdf->Output('Trainees_Report_' . date('Y-m-d') . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
?> 