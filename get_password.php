<?php
// Include database connection
include('connection.php');

// Set the username you want to check
$username_to_check = "hr"; // Change this to the username you want to check

// Prepare and execute query
$sql = "SELECT user_name, password FROM emp_login WHERE user_name = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $username_to_check);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h3>User: " . $row['user_name'] . "</h3>";
    echo "<p>Hashed Password: " . $row['password'] . "</p>";
    
    // For testing password verification
    echo "<h3>Test Password Verification</h3>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='hash' value='" . htmlspecialchars($row['password']) . "'>";
    echo "<input type='text' name='password' placeholder='Enter password to test'>";
    echo "<input type='submit' value='Verify'>";
    echo "</form>";
    
    // Check if form was submitted
    if (isset($_POST['password']) && isset($_POST['hash'])) {
        $test_password = $_POST['password'];
        $stored_hash = $_POST['hash'];
        
        if (password_verify($test_password, $stored_hash)) {
            echo "<p style='color:green'>Password verification SUCCESSFUL!</p>";
        } else {
            echo "<p style='color:red'>Password verification FAILED!</p>";
        }
    }
} else {
    echo "<p>User not found</p>";
}

// Close connection
$stmt->close();
$con->close();
?>