<?php
// Script to fix old leave records where reason = '0'
include('config/config.php');

echo "<h2>Fixing Old Leave Records</h2>";

// First, let's see how many records have reason = '0'
$count_query = "SELECT COUNT(*) as count FROM leaves WHERE reason = '0' OR reason = '' OR reason IS NULL";
$result = $con->query($count_query);
$count_data = $result->fetch_assoc();
$count = $count_data['count'];

echo "<p>Found {$count} leave records with empty or '0' reason.</p>";

if ($count > 0) {
    // Update the records with a default reason
    $update_query = "UPDATE leaves SET reason = 'Leave application' WHERE reason = '0' OR reason = '' OR reason IS NULL";
    
    if ($con->query($update_query)) {
        $affected_rows = $con->affected_rows;
        echo "<p style='color: green;'>Successfully updated {$affected_rows} records.</p>";
        
        // Show some examples of the updated records
        $show_query = "SELECT id, emp_id, user_name, type_of_leave, reason, start_date, end_date FROM leaves WHERE reason = 'Leave application' ORDER BY id DESC LIMIT 5";
        $show_result = $con->query($show_query);
        
        echo "<h3>Updated Records (showing last 5):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Employee ID</th><th>Employee Name</th><th>Leave Type</th><th>Reason</th><th>Start Date</th><th>End Date</th></tr>";
        
        while ($row = $show_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['emp_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['type_of_leave']) . "</td>";
            echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
            echo "<td>" . htmlspecialchars($row['start_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['end_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>Error updating records: " . $con->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>No records found with empty or '0' reason.</p>";
}

echo "<br><p><a href='user_panel/leave_management/dashboard.php'>Back to Dashboard</a></p>";
?> 