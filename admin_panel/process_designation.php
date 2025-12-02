<?php
include ('session.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header('Location: designations.php');
    exit;
}

$action = $_POST['action'];

if ($action === 'edit') {
    $old_name = $_POST['old_name'] ?? '';
    $new_name = $_POST['new_name'] ?? '';

    if (empty($old_name) || empty($new_name)) {
        echo 'Error: Both old and new names are required.';
        exit;
    }

    $query = "UPDATE employees SET designation = ? WHERE designation = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ss', $new_name, $old_name);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Database error: ' . $stmt->error;
    }
    $stmt->close();

} elseif ($action === 'delete') {
    $name = $_POST['name'] ?? '';

    if (empty($name)) {
        echo 'Error: Designation name is required.';
        exit;
    }
    
    // Sets the designation to NULL. Change to '' if your column doesn't allow NULLs.
    $query = "UPDATE employees SET designation = NULL WHERE designation = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('s', $name);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Database error: ' . $stmt->error;
    }
    $stmt->close();

} else {
    echo 'Invalid action specified.';
}
?> 