<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure temporary directory
$tempDir = $_SERVER['DOCUMENT_ROOT'] . '/emps/admin_panel/tmp';

// Create directory if it doesn't exist
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/emps/vendor/autoload.php';
    
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'tempDir' => $tempDir,
        'default_font' => 'dejavusans'
    ]);
    
    $mpdf->WriteHTML('<h1>Working PDF Test</h1>');
    $mpdf->Output();
} catch (\Exception $e) {
    echo "PDF Error: " . $e->getMessage();
    echo "<br>PHP Version: " . phpversion();
    echo "<br>Temp Directory: " . (is_writable($tempDir) ? 'Writable' : 'Not Writable');
}