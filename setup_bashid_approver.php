<?php
// Setup Bashid Khan as default approver
// This script will update the database to set Bashid Khan as the default approver for all leave applications

include('connection.php');

echo "<h2>Setting up Bashid Khan as Default Approver</h2>";

// Check if Bashid Khan exists
$bashid_query = "SELECT id, eid, full_name, email, role FROM employees WHERE eid = 'CME0001' OR email = 'bashid@communikmarketing.com'";
$bashid_result = $con->query($bashid_query);

if ($bashid_result->num_rows > 0) {
    $bashid = $bashid_result->fetch_assoc();
    echo "<p><strong>✅ Bashid Khan found:</strong> " . $bashid['full_name'] . " (ID: " . $bashid['id'] . ", EID: " . $bashid['eid'] . ")</p>";
} else {
    echo "<p><strong>❌ Error:</strong> Bashid Khan not found in employees table!</p>";
    exit();
}

// Add approver columns to leaves table if they don't exist
echo "<h3>1. Adding approver columns to leaves table...</h3>";

$alter_queries = [
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_id VARCHAR(20) AFTER recommender_id",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_name VARCHAR(100) AFTER approver_id",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS recommender_id VARCHAR(20) AFTER hod_name",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS recommender_name VARCHAR(100) AFTER recommender_id",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS recommender_status ENUM('Pending', 'Recommended', 'Not Recommended') DEFAULT 'Pending' AFTER approver_name",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS recommender_remarks TEXT AFTER recommender_status",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS recommender_action_date TIMESTAMP NULL AFTER recommender_remarks",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending' AFTER recommender_action_date",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_remarks TEXT AFTER approver_status",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_action_date TIMESTAMP NULL AFTER approver_remarks",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS approver_by VARCHAR(20) AFTER approver_action_date",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS hod_status ENUM('Pending', 'Recommended', 'Not Recommended') DEFAULT 'Pending' AFTER approver_by",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS hod_remarks TEXT AFTER hod_status",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS hod_action_date TIMESTAMP NULL AFTER hod_remarks",
    "ALTER TABLE leaves ADD COLUMN IF NOT EXISTS hod_by VARCHAR(20) AFTER hod_action_date"
];

foreach ($alter_queries as $query) {
    if ($con->query($query)) {
        echo "<p>✅ " . $query . "</p>";
    } else {
        echo "<p>❌ Error: " . $con->error . "</p>";
    }
}

// Update existing leaves to have Bashid Khan as approver
echo "<h3>2. Updating existing leaves...</h3>";
$update_leaves = "UPDATE leaves SET approver_id = 'CME0001', approver_name = 'Bashid Khan' WHERE approver_id IS NULL OR approver_id = ''";
if ($con->query($update_leaves)) {
    $affected_rows = $con->affected_rows;
    echo "<p>✅ Updated $affected_rows existing leave applications</p>";
} else {
    echo "<p>❌ Error updating leaves: " . $con->error . "</p>";
}

// Setup leave hierarchy
echo "<h3>3. Setting up leave hierarchy...</h3>";

// Clear existing hierarchy
$con->query("DELETE FROM leave_hierarchy");

// Insert new hierarchy records
$hierarchy_query = "INSERT INTO leave_hierarchy (employee_id, recommender_id, approver_id, department_id, type)
SELECT 
    e.id as employee_id,
    CASE 
        WHEN e.role = 'HOD' THEN NULL
        ELSE (
            SELECT h.id FROM employees h 
            WHERE h.role = 'HOD' AND h.department_id = e.department_id 
            LIMIT 1
        )
    END as recommender_id,
    " . $bashid['id'] . " as approver_id,
    e.department_id,
    'approver'
FROM employees e
WHERE e.eid != 'CME0001' AND e.status = 'active'";

if ($con->query($hierarchy_query)) {
    $affected_rows = $con->affected_rows;
    echo "<p>✅ Created $affected_rows hierarchy records</p>";
} else {
    echo "<p>❌ Error creating hierarchy: " . $con->error . "</p>";
}

// Show summary
echo "<h3>4. Summary</h3>";

$total_leaves = $con->query("SELECT COUNT(*) as count FROM leaves WHERE approver_id = 'CME0001'")->fetch_assoc()['count'];
$total_hierarchy = $con->query("SELECT COUNT(*) as count FROM leave_hierarchy WHERE approver_id = " . $bashid['id'])->fetch_assoc()['count'];

echo "<p><strong>Total leaves with Bashid Khan as approver:</strong> $total_leaves</p>";
echo "<p><strong>Total employees in hierarchy with Bashid Khan as approver:</strong> $total_hierarchy</p>";

echo "<h3>5. Test Links</h3>";
echo "<p><a href='user_panel/leave_management/apply.php'>Apply for Leave</a></p>";
echo "<p><a href='hod_panel/leave_management/recommend_leave.php'>HOD Recommend Leave</a></p>";
echo "<p><a href='admin_panel/leave_management/approve_leaves.php'>Admin Approve Leaves</a></p>";

echo "<h3>6. Database Structure</h3>";
$structure = $con->query("DESCRIBE leaves");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>✅ Setup complete! Bashid Khan is now the default approver for all leave applications.</strong></p>";
?> 