<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../connection.php';

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/esign_templates.sql');

if ($con->multi_query($sql)) {
    echo "Table esign_templates created successfully";
} else {
    echo "Error creating table: " . $con->error;
}

$con->close();
?> 