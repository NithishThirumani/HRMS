<?php
if (!isset($con)) {
    include_once(__DIR__ . '/../../connection.php');
}

/**
 * Get employee's leave balances for all leave types
 */
function getLeaveBalances($emp_id, $limit = 5) {
    global $con;
    
    $query = "SELECT 
                lp.leave_type,
                lp.max_days as total_days,
                lp.monthly_accrual,
                COALESCE(SUM(CASE WHEN l.status = 'Approved' THEN l.total_days ELSE 0 END), 0) as used_days,
                lp.max_days - COALESCE(SUM(CASE WHEN l.status = 'Approved' THEN l.total_days ELSE 0 END), 0) as available_days
              FROM leave_policies lp
              LEFT JOIN leaves l ON lp.leave_type = l.type_of_leave 
                AND l.emp_id = ? 
                AND YEAR(l.start_date) = YEAR(CURRENT_DATE)
              GROUP BY lp.leave_type, lp.max_days, lp.monthly_accrual";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $balances = array();
    while ($row = $result->fetch_assoc()) {
        $balances[] = $row;
    }
    
    return $balances;
}

/**
 * Get pending leaves for an employee
 */
function getPendingLeaves($emp_id) {
    global $con;
    
    $query = "SELECT l.*, lp.requires_certificate
              FROM leaves l
              JOIN leave_policies lp ON l.type_of_leave = lp.leave_type
              WHERE l.emp_id = ? AND l.status = 'Pending'
              ORDER BY l.start_date DESC";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaves = array();
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    
    return $leaves;
}

/**
 * Get recent leave applications
 */
function getRecentLeaves($emp_id, $limit = 5) {
    global $con;
    $query = "SELECT l.* 
              FROM leaves l
              WHERE l.emp_id = ?
              ORDER BY l.applied_at DESC
              LIMIT ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("si", $emp_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $leaves = array();
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    return $leaves;
}

/**
 * Calculate working days between two dates (excluding weekends)
 */
function calculateWorkingDays($start_date, $end_date) {
    try {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        if ($start > $end) {
            throw new Exception("Start date cannot be after end date");
        }
        
        $days = 0;
        while ($start <= $end) {
            if ($start->format('N') < 6) { // Weekdays only
                $days++;
            }
            $start->modify('+1 day');
        }
        return $days;
    } catch (Exception $e) {
        error_log("Date calculation error: " . $e->getMessage());
        return 0; // Or handle differently
    }
}

/**
 * Check if employee has sufficient leave balance
 */
function hasEnoughLeaveBalance($emp_id, $leave_type_id, $requested_days) {
    global $con;
    
    $query = "SELECT 
                lp.max_days - COALESCE(SUM(CASE WHEN l.status = 'Approved' THEN l.total_days ELSE 0 END), 0) as available_days
              FROM leave_policies lp
              LEFT JOIN leaves l ON lp.leave_type_id = l.leave_type_id 
                AND l.emp_id = ? 
                AND YEAR(l.start_date) = YEAR(CURRENT_DATE)
              WHERE lp.leave_type_id = ?
              GROUP BY lp.max_days";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("si", $emp_id, $leave_type_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result && $result['available_days'] >= $requested_days;
}

/**
 * Get leave request details
 */
function getLeaveDetails($leave_id) {
    global $con;
    
    $query = "SELECT l.*, lt.leave_type, e.full_name as employee_name,
              d.name as department_name, 
              r1.full_name as recommender_name,
              r2.full_name as approver_name
              FROM leaves l
              JOIN leave_types lt ON l.leave_type_id = lt.id
              JOIN employees e ON l.emp_id = e.eid
              JOIN departments d ON e.department_id = d.id
              LEFT JOIN employees r1 ON l.recommender_id = r1.eid
              LEFT JOIN employees r2 ON l.approver_id = r2.eid
              WHERE l.id = ?";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get leave approval history
 */
function getLeaveHistory($leave_id) {
    global $con;
    
    $query = "SELECT lh.*, e.full_name as actor_name
              FROM leave_history lh
              JOIN employees e ON lh.actor_id = e.eid
              WHERE lh.leave_id = ?
              ORDER BY lh.created_at ASC";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get badge class based on leave status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Approved':
            return 'success';
        case 'Pending':
            return 'warning';
        case 'Rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Calculate available leave days considering service months and gender restrictions
 */
function getAvailableLeaveBalance($con, $emp_id, $leave_type) { // Added $con as a parameter
    global $con;
    
    // Get employee details
    // Corrected join_date to doj
    $emp_query = "SELECT gender, doj, TIMESTAMPDIFF(MONTH, doj, CURRENT_DATE) as service_months 
                 FROM employees 
                 WHERE eid = ?";
    $stmt = $con->prepare($emp_query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $emp_result = $stmt->get_result();
    $emp_data = $emp_result->fetch_assoc();
    
    // Get leave policy
    $policy_query = "SELECT * FROM leave_policies WHERE leave_type = ?";
    $stmt = $con->prepare($policy_query);
    $stmt->bind_param("s", $leave_type);
    $stmt->execute();
    $policy_result = $stmt->get_result();
    $policy = $policy_result->fetch_assoc();
    
    // Check eligibility
    if ($emp_data['service_months'] < $policy['min_service_months']) {
        return 0;
    }
    
    if ($policy['gender_restriction'] != 'all' && $emp_data['gender'] != $policy['gender_restriction']) {
        return 0;
    }
    
    // Calculate used days
    $used_query = "SELECT COALESCE(SUM(total_days), 0) as used_days
                   FROM leaves 
                   WHERE emp_id = ? 
                   AND type_of_leave = ? 
                   AND status = 'Approved'
                   AND YEAR(start_date) = YEAR(CURRENT_DATE)";
    $stmt = $con->prepare($used_query);
    $stmt->bind_param("ss", $emp_id, $leave_type);
    $stmt->execute();
    $used_result = $stmt->get_result();
    $used_data = $used_result->fetch_assoc();
    
    // Calculate accrued days if applicable
    $max_days = $policy['max_days'];
    if ($policy['monthly_accrual'] > 0) {
        $months_passed = min(12, $emp_data['service_months']);
        $accrued_days = $months_passed * $policy['monthly_accrual'];
        $max_days = min($max_days, $accrued_days);
    }
    
    return max(0, $max_days - $used_data['used_days']);
}

/**
 * Get pending leave recommendations for a recommender
 */
function getPendingRecommendations($recommender_id) {
    global $con;
    
    $query = "SELECT l.*, e.full_name as employee_name, d.name as department_name
              FROM leaves l
              JOIN employees e ON l.emp_id = e.eid
              JOIN departments d ON e.department_id = d.id
              JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
              WHERE lh.recommender_id = ? AND l.status = 'Pending'
              ORDER BY l.applied_at DESC";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $recommender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaves = [];
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    
    return $leaves;
}

/**
 * Get recent leave recommendations for a recommender
 */
function getRecentRecommendations($recommender_id) {
    global $con;
    
    $query = "SELECT l.*, e.full_name as employee_name, d.name as department_name
              FROM leaves l
              JOIN employees e ON l.emp_id = e.eid
              JOIN departments d ON e.department_id = d.id
              JOIN leave_hierarchy lh ON lh.employee_id = e.id AND lh.type = 'recommender'
              WHERE lh.recommender_id = ? AND l.status IN ('Recommended', 'Rejected')
              ORDER BY l.applied_at DESC
              LIMIT 10";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $recommender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaves = [];
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    
    return $leaves;
}

/**
 * Ensure roles table exists with correct structure
 */
function ensureRolesTable() {
    global $con;
    
    // Create roles table if it doesn't exist
    $query = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_code VARCHAR(50) NOT NULL UNIQUE,
        role_name VARCHAR(100) NOT NULL,
        can_recommend TINYINT(1) DEFAULT 0,
        can_approve TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $con->query($query);
    
    // Insert default roles if they don't exist
    $roles = [
        ['HOD', 'Head of Department', 1, 1],
        ['MANAGER', 'Manager', 1, 0],
        ['EMPLOYEE', 'Employee', 0, 0]
    ];
    
    $stmt = $con->prepare("INSERT IGNORE INTO roles (role_code, role_name, can_recommend, can_approve) VALUES (?, ?, ?, ?)");
    
    foreach ($roles as $role) {
        $stmt->bind_param("ssii", $role[0], $role[1], $role[2], $role[3]);
        $stmt->execute();
    }
}

/**
 * Get leave balance for an employee
 */
function getLeaveBalance($eid) {
    global $con;
    
    // Get employee's department
    $query = "SELECT e.id as emp_id, e.department_id 
              FROM employees e 
              WHERE e.eid = ?";
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $eid);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    
    if (!$employee) {
        return [
            'total' => 0,
            'used' => 0,
            'remaining' => 0
        ];
    }
    
    // Get leave policy for the employee's department
    $query = "SELECT lp.*, lt.leave_type 
              FROM leave_policies lp 
              JOIN leave_types lt ON lp.leave_type = lt.id 
              WHERE lp.leave_type = 1"; // Assuming 1 is annual leave type
    
    $stmt = $con->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $policy = $result->fetch_assoc();
    
    if (!$policy) {
        return [
            'total' => 0,
            'used' => 0,
            'remaining' => 0
        ];
    }
    
    // Get used leaves for current year
    $current_year = date('Y');
    $query = "SELECT SUM(total_days) as used_days 
              FROM leaves 
              WHERE emp_id = ? 
              AND YEAR(start_date) = ? 
              AND type_of_leave = ? 
              AND status IN ('Approved', 'Recommended')";
              
    $stmt = $con->prepare($query);
    $leave_type = $policy['leave_type'];
    $stmt->bind_param("sii", $eid, $current_year, $leave_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $used = $result->fetch_assoc();
    
    $used_days = $used['used_days'] ?? 0;
    $total_days = $policy['max_days'] ?? 0;
    $remaining = max(0, $total_days - $used_days);
    
    return [
        'total' => $total_days,
        'used' => $used_days,
        'remaining' => $remaining,
        'type' => $policy['leave_type']
    ];
}

/**
 * Ensure departments table has required columns
 */
function ensureDepartmentsTable() {
    global $con;
    
    // Check if annual_leave_days column exists
    $result = $con->query("SHOW COLUMNS FROM departments LIKE 'annual_leave_days'");
    if ($result->num_rows == 0) {
        // Add annual_leave_days column if it doesn't exist
        $con->query("ALTER TABLE departments ADD COLUMN annual_leave_days INT DEFAULT 30");
        
        // Update existing departments with default value
        $con->query("UPDATE departments SET annual_leave_days = 30 WHERE annual_leave_days IS NULL");
    }
}

/**
 * Ensure leave types and policies exist
 */
function ensureLeaveTypesAndPolicies() {
    global $con;
    
    // Check if annual leave type exists
    $result = $con->query("SELECT id FROM leave_types WHERE leave_type = 'Annual Leave'");
    if ($result->num_rows == 0) {
        // Insert annual leave type
        $con->query("INSERT INTO leave_types (leave_type, description) VALUES ('Annual Leave', 'Regular annual leave')");
    }
    
    // Get annual leave type ID
    $result = $con->query("SELECT id FROM leave_types WHERE leave_type = 'Annual Leave'");
    $annual_leave = $result->fetch_assoc();
    
    if ($annual_leave) {
        // Check if policy exists for annual leave
        $result = $con->query("SELECT id FROM leave_policies WHERE leave_type = " . $annual_leave['id']);
        if ($result->num_rows == 0) {
            // Insert default policy
            $con->query("INSERT INTO leave_policies (leave_type, max_days, min_service_months, requires_certificate, gender_restriction, is_paid, monthly_accrual) 
                        VALUES (" . $annual_leave['id'] . ", 30, 0, 0, NULL, 1, 0)");
        }
    }
}

/**
 * Get next approver for a leave request
 */
function getNextApprover($emp_numeric_id, $department_id) {
    // TODO: Implement actual logic
    return null;
}

// Call this function when the file is included
ensureRolesTable();
ensureDepartmentsTable();
ensureLeaveTypesAndPolicies();
