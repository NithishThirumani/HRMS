<?php
class AppraisalPeriod {
    private $con;

    public function __construct() {
        global $con;
        $this->con = $con;
    }

    public function getActivePeriods() {
        $query = "SELECT period_id, start_date, end_date, status, created_by, created_at, updated_at 
                 FROM appraisal_periods 
                 WHERE status = 'Active' 
                 ORDER BY start_date DESC";
        
        $result = $this->con->query($query);
        if (!$result) {
            error_log("SQL Error in getActivePeriods: " . $this->con->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllPeriods() {
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM employees) as total_employees,
                  (SELECT COUNT(*) FROM appraisal_forms WHERE period_id = p.period_id AND status = 'Completed') as completed_count
                  FROM appraisal_periods p
                  ORDER BY p.start_date DESC";
        
        $result = $this->con->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCurrentPeriod() {
        $query = "SELECT * FROM appraisal_periods 
                 WHERE start_date <= CURDATE() 
                 AND end_date >= CURDATE() 
                 AND status = 1 
                 LIMIT 1";
        
        $result = $this->con->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function addPeriod($startDate, $endDate) {
        global $con;
        
        $status = 'Draft';
        $query = "INSERT INTO appraisal_periods (start_date, end_date, status) VALUES (?, ?, ?)";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("sss", $startDate, $endDate, $status);
        
        return $stmt->execute();
    }

    public function initiateAppraisal($startDate, $endDate, $departments, $createdBy) {
        global $con;
        
        try {
            $con->begin_transaction();
            
            // Insert the appraisal period
            $status = 'Draft';
            $query = "INSERT INTO appraisal_periods (start_date, end_date, status, created_by) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("sssi", $startDate, $endDate, $status, $createdBy);
            $stmt->execute();
            $periodId = $con->insert_id;
            
            // Get employees from selected departments
            $placeholders = str_repeat('?,', count($departments) - 1) . '?';
            $employeeQuery = "SELECT eid FROM employees WHERE department IN ($placeholders)";
            $stmt = $con->prepare($employeeQuery);
            $stmt->bind_param(str_repeat('s', count($departments)), ...$departments);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create appraisal assignments
            while ($employee = $result->fetch_assoc()) {
                $assignQuery = "INSERT INTO appraisal_assignments 
                              (period_id, employee_id, status) VALUES (?, ?, 'Pending')";
                $stmt = $con->prepare($assignQuery);
                $stmt->bind_param("ii", $periodId, $employee['eid']);
                $stmt->execute();
            }
            
            $con->commit();
            return $periodId;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }
}
?>