<?php
class AppraisalPeriod {
    private $db;

    public function __construct() {
        global $con;
        $this->db = $con;
    }

    public function getActivePeriod() {
        $query = "SELECT * FROM appraisal_periods WHERE status = 'active' ORDER BY created_at DESC LIMIT 1";
        $result = $this->db->query($query);
        
        return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
    }
}