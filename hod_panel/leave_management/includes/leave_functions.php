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
 * Check if user has approver rights
 */
function hasApproverRights($role) {
    $approver_roles = ['HOD', 'HR', 'ADMIN', 'MANAGER'];
    return in_array(strtoupper($role), $approver_roles);
}

/**
 * Get next approver for a leave request
 */
function getNextApprover($emp_numeric_id, $department_id) {
    global $con;
    
    // Get the employee's leave hierarchy
    $query = "SELECT lh.*, e.full_name, e.email 
              FROM leave_hierarchy lh
              JOIN employees e ON lh.approver_id = e.id
              WHERE lh.employee_id = ? AND lh.type = 'approver'
              ORDER BY lh.id ASC";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $emp_numeric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $approvers = [];
    while ($row = $result->fetch_assoc()) {
        $approvers[] = $row;
    }
    
    // Return the first approver (next in line)
    return !empty($approvers) ? $approvers[0] : null;
}

/**
 * Update leave balance for an employee
 */
function updateLeaveBalance($emp_id, $leave_type, $total_days) {
    global $con;
    
    // This function can be implemented to update leave balances
    // For now, we'll just log the action
    error_log("Leave balance update: Employee $emp_id, Type: $leave_type, Days: $total_days");
}

/**
 * Send leave notifications
 */
function sendLeaveNotifications($approver_role, $emp_id, $leave_id) {
    // This function can be implemented to send notifications
    // For now, we'll just log the action
    error_log("Leave notification: Role $approver_role, Employee $emp_id, Leave $leave_id");
}

/**
 * Send status notification
 */
function sendStatusNotification($emp_id, $leave_id, $status) {
    // This function can be implemented to send status notifications
    // For now, we'll just log the action
    error_log("Status notification: Employee $emp_id, Leave $leave_id, Status $status");
}

/**
 * Get leave history for HOD - shows leaves that HOD has recommended or approved
 */
function getHODLeaveHistory($hod_numeric_id, $limit = 100) {
    global $con;
    
    // First check if approver_remarks column exists
    $check_column = $con->query("SHOW COLUMNS FROM leaves LIKE 'approver_remarks'");
    $has_approver_remarks = $check_column->num_rows > 0;
    
    if ($has_approver_remarks) {
        // Use separate columns for approver remarks and dates
        $query = "SELECT l.*, 
                         e.full_name as employee_name,
                         e.email as employee_email,
                         d.name as department_name,
                         CASE 
                             WHEN l.recommender_id = ? AND l.approver_id = ? THEN 'Both Recommender & Approver'
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN 'Recommended'
                             WHEN l.approver_id = ? AND l.approver_id != 0 THEN 'Approved'
                             ELSE 'Unknown'
                         END as hod_role,
                         CASE 
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN l.recommender_remarks
                             WHEN l.approver_id = ? AND l.approver_id != 0 THEN l.approver_remarks
                             ELSE NULL
                         END as hod_remarks,
                         CASE 
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN l.recommender_action_date
                             WHEN l.approver_id = ? AND l.approver_id != 0 THEN l.approver_action_date
                             ELSE NULL
                         END as hod_action_date
                  FROM leaves l
                  LEFT JOIN employees e ON l.emp_id = e.eid
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE (l.recommender_id = ? OR l.approver_id = ?)
                  AND (l.recommender_id != 0 OR l.approver_id != 0)
                  ORDER BY l.applied_at DESC
                  LIMIT ?";
    } else {
        // Use recommender columns for both roles (fallback)
        $query = "SELECT l.*, 
                         e.full_name as employee_name,
                         e.email as employee_email,
                         d.name as department_name,
                         CASE 
                             WHEN l.recommender_id = ? AND l.approver_id = ? THEN 'Both Recommender & Approver'
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN 'Recommended'
                             WHEN l.approver_id = ? AND l.approver_id != 0 THEN 'Approved'
                             ELSE 'Unknown'
                         END as hod_role,
                         CASE 
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN l.recommender_remarks
                             WHEN l.approver_id = ? AND l.recommender_id != 0 THEN l.recommender_remarks
                             ELSE NULL
                         END as hod_remarks,
                         CASE 
                             WHEN l.recommender_id = ? AND l.recommender_id != 0 THEN l.recommender_action_date
                             WHEN l.approver_id = ? AND l.recommender_id != 0 THEN l.recommender_action_date
                             ELSE NULL
                         END as hod_action_date
                  FROM leaves l
                  LEFT JOIN employees e ON l.emp_id = e.eid
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE (l.recommender_id = ? OR l.approver_id = ?)
                  AND (l.recommender_id != 0 OR l.approver_id != 0)
                  ORDER BY l.applied_at DESC
                  LIMIT ?";
    }
              
    $stmt = $con->prepare($query);
    $stmt->bind_param("iiiiiiiiiii", 
        $hod_numeric_id, $hod_numeric_id,  // For both recommender & approver (2)
        $hod_numeric_id, $hod_numeric_id,  // For individual roles (2)
        $hod_numeric_id, $hod_numeric_id,  // For remarks (2)
        $hod_numeric_id, $hod_numeric_id,  // For action dates (2)
        $hod_numeric_id, $hod_numeric_id,  // For WHERE clause (2)
        $limit                              // For LIMIT (1)
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $leaves = array();
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    return $leaves;
}

/**
 * Get pending leaves that HOD needs to act on
 */
function getHODPendingLeaves($hod_numeric_id) {
    global $con;
    
    $query = "SELECT DISTINCT l.*, e.full_name as employee_name, e.email as employee_email,
                     d.name as dept_name, e.department_id,
                     CASE 
                       WHEN (l.recommender_id IS NULL OR l.recommender_id = 0) THEN 'Recommend'
                       WHEN l.recommender_id = ? AND (l.approver_id IS NULL OR l.approver_id = 0) THEN 'Approve'
                       ELSE 'Review'
                     END as action_required
                     FROM leaves l 
                     LEFT JOIN employees e ON l.emp_id = e.eid 
                     LEFT JOIN departments d ON e.department_id = d.id
                     WHERE l.status = 'Pending' 
                     AND (
                         l.hod_id = ? 
                         OR EXISTS (
                             SELECT 1 FROM leave_hierarchy lh 
                             WHERE lh.employee_id = e.id 
                             AND (lh.recommender_id = ? OR lh.approver_id = ?)
                         )
                     )
                     AND (
                         -- For recommendation: HOD hasn't recommended yet
                         ((l.recommender_id IS NULL OR l.recommender_id = 0) AND EXISTS (
                             SELECT 1 FROM leave_hierarchy lh 
                             WHERE lh.employee_id = e.id 
                             AND lh.recommender_id = ? 
                             AND lh.type = 'recommender'
                         ))
                         OR
                         -- For approval: HOD has recommended but hasn't approved yet
                         (l.recommender_id = ? AND (l.approver_id IS NULL OR l.approver_id = 0) AND EXISTS (
                             SELECT 1 FROM leave_hierarchy lh 
                             WHERE lh.employee_id = e.id 
                             AND lh.approver_id = ? 
                             AND lh.type = 'approver'
                         ))
                         OR
                         -- For direct HOD assignment
                         (l.hod_id = ? AND (l.recommender_id IS NULL OR l.recommender_id = 0))
                     )
                     ORDER BY l.applied_at DESC";
                     
    $stmt = $con->prepare($query);
    $stmt->bind_param("iiiiiiii", 
        $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id,
        $hod_numeric_id, $hod_numeric_id, $hod_numeric_id, $hod_numeric_id
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $leaves = array();
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    return $leaves;
}

// Call this function when the file is included
ensureRolesTable();
ensureDepartmentsTable();
ensureLeaveTypesAndPolicies();
