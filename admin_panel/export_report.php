<?php
require('session.php');
require('connection.php');
require('mpdf/mpdf.php'); // You'll need to install FPDF library

class DashboardPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Dashboard Report - ' . date('Y-m-d'), 0, 1, 'C');
        $this->Ln(10);
    }
}

// Create PDF object
$pdf = new DashboardPDF();
$pdf->AddPage();

// Add employee statistics
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Employee Statistics', 0, 1);
$pdf->SetFont('Arial', '', 11);

// Fetch data from database
$query = "SELECT COUNT(*) as total FROM employees";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$pdf->Cell(0, 8, 'Total Employees: ' . $row['total'], 0, 1);

// Add leave statistics
$query = "SELECT COUNT(*) as total FROM leaves WHERE status='approved' AND DATE(start_date) <= CURDATE() AND DATE(end_date) >= CURDATE()";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$pdf->Cell(0, 8, 'Employees on Leave Today: ' . $row['total'], 0, 1);

// Add department statistics
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Department Distribution', 0, 1);
$pdf->SetFont('Arial', '', 11);

$query = "SELECT d.name, COUNT(e.id) as count 
          FROM departments d 
          LEFT JOIN employees e ON d.id = e.department_id 
          GROUP BY d.id";
$result = mysqli_query($con, $query);
while($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(0, 8, $row['name'] . ': ' . $row['count'] . ' employees', 0, 1);
}

// Output PDF
$pdf->Output('Dashboard_Report_' . date('Y-m-d') . '.pdf', 'D');
?>