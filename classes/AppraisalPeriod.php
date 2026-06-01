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
                  (SELECT COUNT(*) FROM employees WHERE LOWER(TRIM(status)) = 'active') as total_employees,
                  (SELECT COUNT(*) FROM employee_appraisals ea 
                   WHERE ea.period_id = p.period_id AND ea.status = 'Completed') as completed_count
                  FROM appraisal_periods p
                  ORDER BY p.start_date DESC";
        
        $result = $this->con->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCurrentPeriod() {
        $query = "SELECT * FROM appraisal_periods 
                 WHERE start_date <= CURDATE() 
                 AND end_date >= CURDATE() 
                 AND status = 'Active' 
                 LIMIT 1";
        
        $result = $this->con->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function addPeriod($startDate, $endDate) {
        $status = 'Draft';
        $query = "INSERT INTO appraisal_periods (start_date, end_date, status) VALUES (?, ?, ?)";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("sss", $startDate, $endDate, $status);
        
        return $stmt->execute();
    }

    public function updatePeriod($periodId, $startDate, $endDate) {
        $stmt = $this->con->prepare(
            'UPDATE appraisal_periods SET start_date = ?, end_date = ? WHERE period_id = ?'
        );
        $stmt->bind_param('ssi', $startDate, $endDate, $periodId);
        return $stmt->execute();
    }

    public function deletePeriod($periodId) {
        $periodId = (int) $periodId;
        $this->con->begin_transaction();
        try {
            $ids = [];
            $stmt = $this->con->prepare('SELECT appraisal_id FROM employee_appraisals WHERE period_id = ?');
            $stmt->bind_param('i', $periodId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $ids[] = (int) $row['appraisal_id'];
            }

            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $types = str_repeat('i', count($ids));
                $delRatings = $this->con->prepare("DELETE FROM appraisal_ratings WHERE appraisal_id IN ($placeholders)");
                $delRatings->bind_param($types, ...$ids);
                $delRatings->execute();
            }

            $stmt = $this->con->prepare('DELETE FROM employee_appraisals WHERE period_id = ?');
            $stmt->bind_param('i', $periodId);
            $stmt->execute();

            $stmt = $this->con->prepare('DELETE FROM appraisal_assignments WHERE period_id = ?');
            $stmt->bind_param('i', $periodId);
            $stmt->execute();

            $stmt = $this->con->prepare('DELETE FROM appraisal_periods WHERE period_id = ?');
            $stmt->bind_param('i', $periodId);
            $stmt->execute();

            $this->con->commit();
            return true;
        } catch (Exception $e) {
            $this->con->rollback();
            error_log('deletePeriod: ' . $e->getMessage());
            return false;
        }
    }
}
