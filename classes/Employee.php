<?php
class Employee {
    private $con;

    public function __construct() {
        global $con;
        $this->con = $con;
    }

    public function getAllEmployees() {
        $query = "SELECT * FROM employees WHERE status = 1";
        $result = $this->con->query($query);
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return $employees;
    }

    public function getEmployeeById($id) {
        $query = "SELECT * FROM employees WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEmployeesByDepartment($department) {
        $query = "SELECT * FROM employees WHERE department = ? AND status = 1";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return $employees;
    }

    public function getReportingEmployees($manager_id) {
        $query = "SELECT * FROM employees WHERE reporting_manager = ? AND status = 1";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $manager_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return $employees;
    }

    public function getEmployeeAppraisals($eid) {
        global $con;
        
        $query = "SELECT a.*, ap.start_date, ap.end_date 
                  FROM appraisal_assignments a 
                  JOIN appraisal_periods ap ON a.period_id = ap.period_id 
                  WHERE a.employee_id = ? AND a.status != 'Completed'
                  ORDER BY ap.start_date DESC";
                  
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $eid);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>