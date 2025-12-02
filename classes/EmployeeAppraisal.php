<?php
require_once(__DIR__ . '/../connection.php');

class EmployeeAppraisal
{
    private $con;
    private $appraisal_id;
    private $employee_id;
    private $period_id;
    private $status;
    private $final_rating;

    public function __construct()
    {
        global $con;
        $this->con = $con;
    }

    public function createAppraisal($employee_id, $period_id)
    {
        $sql = "INSERT INTO employee_appraisals (employee_id, period_id) 
                VALUES (?, ?)";
        $params = [$employee_id, $period_id];
        return $this->con->execute($sql, $params);
    }

    public function updateStatus($appraisal_id, $status)
    {
        $sql = "UPDATE employee_appraisals 
                SET status = ? 
                WHERE appraisal_id = ?";
        $params = [$status, $appraisal_id];
        return $this->con->execute($sql, $params);
    }

    public function updateFinalRating($appraisal_id, $final_rating)
    {
        $sql = "UPDATE employee_appraisals 
                SET final_rating = ? 
                WHERE appraisal_id = ?";
        $params = [$final_rating, $appraisal_id];
        return $this->con->execute($sql, $params);
    }

    public function getEmployeeAppraisals($employee_id)
    {
        $sql = "SELECT ea.*, ap.start_date, ap.end_date 
                FROM employee_appraisals ea 
                JOIN appraisal_periods ap ON ea.period_id = ap.period_id 
                WHERE ea.employee_id = ? 
                ORDER BY ap.start_date DESC";
        return $this->con->queryAll($sql, [$employee_id]);
    }

    public function getDepartmentAppraisals($department_id, $period_id)
    {
        $sql = "SELECT ea.*, e.first_name, e.last_name, e.employee_code 
                FROM employee_appraisals ea 
                JOIN employees e ON ea.employee_id = e.id 
                WHERE e.department_id = ? AND ea.period_id = ?";
        $params = [$department_id, $period_id];
        return $this->con->queryAll($sql, $params);
    }

    public function getAppraisalById($appraisal_id)
    {
        $sql = "SELECT ea.*, e.first_name, e.last_name, e.employee_code,
                       ap.start_date, ap.end_date 
                FROM employee_appraisals ea 
                JOIN employees e ON ea.employee_id = e.id 
                JOIN appraisal_periods ap ON ea.period_id = ap.period_id 
                WHERE ea.appraisal_id = ?";
        return $this->con->queryOne($sql, [$appraisal_id]);
    }

    public function getPendingAppraisals($role, $department_id = null)
    {
        $sql = "SELECT ea.*, e.first_name, e.last_name, e.employee_code 
                FROM employee_appraisals ea 
                JOIN employees e ON ea.employee_id = e.id 
                WHERE 1=1";

        $params = [];

        switch ($role) {
            case 'HOD':
                $sql .= " AND e.department_id = ? AND ea.status = 'Self_Submitted'";
                $params[] = $department_id;
                break;
            case 'HR':
                $sql .= " AND ea.status = 'HOD_Reviewed'";
                break;
            case 'Admin':
                $sql .= " AND ea.status = 'HR_Reviewed'";
                break;
        }

        return $this->con->queryAll($sql, $params);
    }

    public function calculateFinalRating($appraisal_id)
    {
        $sql = "SELECT ar.*, ac.weightage 
                FROM appraisal_ratings ar 
                JOIN appraisal_criteria ac ON ar.criteria_id = ac.criteria_id 
                WHERE ar.appraisal_id = ?";

        $ratings = $this->con->queryAll($sql, [$appraisal_id]);
        $final_rating = 0;
        $total_weightage = 0;

        foreach ($ratings as $rating) {
            $avg_rating = ($rating['self_rating'] + $rating['hod_rating'] + $rating['hr_rating']) / 3;
            $weighted_rating = ($avg_rating * $rating['weightage']) / 100;
            $final_rating += $weighted_rating;
            $total_weightage += $rating['weightage'];
        }

        $final_rating = ($total_weightage > 0) ? ($final_rating * 100) / $total_weightage : 0;
        $this->updateFinalRating($appraisal_id, $final_rating);

        return $final_rating;
    }
}
?>