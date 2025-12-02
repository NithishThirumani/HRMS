<?php
require_once('../connection.php');

// Read and execute SQL file
$sql = file_get_contents('sql/create_tables.sql');

// Split SQL file into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$errors = [];

// Execute each query
foreach ($queries as $query) {
    if (!empty($query)) {
        try {
            if (!$con->query($query)) {
                $success = false;
                $errors[] = "Error executing query: " . $con->error;
            }
        } catch (Exception $e) {
            $success = false;
            $errors[] = "Exception: " . $e->getMessage();
        }
    }
}

// Output results
if ($success) {
    echo "Database setup completed successfully!";
} else {
    echo "Errors occurred during database setup:<br>";
    foreach ($errors as $error) {
        echo "- " . htmlspecialchars($error) . "<br>";
    }
}
?> 