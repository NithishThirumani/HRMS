<?php
class EmployeeAppraisal {
    private $db;

    public function __construct() {
        global $con;
        $this->db = $con;
    }

    public function getActiveAppraisal($employee_id) {
        $query = "SELECT af.*, ap.start_date, ap.end_date
                 FROM appraisal_forms af
                 JOIN appraisal_periods ap ON af.period_id = ap.period_id
                 WHERE af.employee_id = ? AND ap.status = 'active'
                 ORDER BY ap.created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }

        $appraisal = $result->fetch_assoc();
        $appraisal['criteria'] = $this->getAppraisalCriteria($appraisal['form_id']);
        
        return $appraisal;
    }

    private function getAppraisalCriteria($form_id) {
        $query = "SELECT c.*, ac.self_rating, ac.self_comments
                 FROM appraisal_criteria c
                 LEFT JOIN appraisal_ratings ac ON c.criteria_id = ac.criteria_id 
                 AND ac.form_id = ?
                 WHERE c.status = 'active'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}