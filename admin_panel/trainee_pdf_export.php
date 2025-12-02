<?php
include('connection.php');
include('session.php');
require_once('tcpdf/tcpdf.php');

// Create new PDF document
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 15, 'Trainees Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Trainees Report');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set margins
$pdf->SetMargins(15, 25, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Add a page
$pdf->AddPage('L', 'A4');

// Set font
$pdf->SetFont('helvetica', '', 10);

// Fetch all trainees with department names
$query = "SELECT t.*, d.name as department_name 
          FROM trainees t 
          LEFT JOIN departments d ON t.department_id = d.id 
          ORDER BY t.trainee_id";
$result = mysqli_query($mysqli, $query);

// Table headers
$headers = array(
    'Trainee ID', 'Full Name', 'Email', 'Contact', 'Department', 
    'Designation', 'Join Date', 'Status', 'Location'
);

// Calculate column widths (total width = 267 for A4 Landscape)
$widths = array(20, 40, 45, 25, 30, 35, 25, 25, 22);

// Colors for header row
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0);
$pdf->SetFont('', 'B');

// Print header row
for($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Reset font
$pdf->SetFont('', '');
$pdf->SetFillColor(255, 255, 255);

// Data rows
while($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell($widths[0], 6, $row['trainee_id'], 1, 0, 'C');
    $pdf->Cell($widths[1], 6, $row['full_name'], 1);
    $pdf->Cell($widths[2], 6, $row['email'], 1);
    $pdf->Cell($widths[3], 6, $row['contact'], 1);
    $pdf->Cell($widths[4], 6, $row['department_name'], 1);
    $pdf->Cell($widths[5], 6, $row['designation'], 1);
    $pdf->Cell($widths[6], 6, $row['date_of_joining'], 1, 0, 'C');
    $pdf->Cell($widths[7], 6, $row['status'], 1, 0, 'C');
    $pdf->Cell($widths[8], 6, $row['location'], 1);
    $pdf->Ln();
}

// Close and output PDF document
$pdf->Output('Trainees_Report_' . date('Y-m-d') . '.pdf', 'D');
?> 