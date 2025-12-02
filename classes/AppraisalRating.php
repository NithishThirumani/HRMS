<?php
require_once 'connection.php';

class AppraisalRating
{
    private $con;
    private $rating_id;
    private $appraisal_id;
    private $criteria_id;
    private $self_rating;
    private $hod_rating;
    private $hr_rating;
    private $comments;

    public function __construct()
    {
        global $con;
        $this->con = $con;
    }

    public function submitSelfRating($appraisal_id, $criteria_id, $rating, $comments)
    {
        $sql = "INSERT INTO appraisal_ratings (appraisal_id, criteria_id, self_rating, comments) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE self_rating = ?, comments = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("iiisss", $appraisal_id, $criteria_id, $rating, $comments, $rating, $comments);
        return $stmt->execute();
    }

    public function submitHodRating($appraisal_id, $criteria_id, $rating, $comments)
    {
        $sql = "UPDATE appraisal_ratings 
                SET hod_rating = ?, comments = CONCAT(comments, '\nHOD: ', ?) 
                WHERE appraisal_id = ? AND criteria_id = ?";
        $params = [$rating, $comments, $appraisal_id, $criteria_id];
        return $this->con->execute($sql, $params);
    }

    public function submitHrRating($appraisal_id, $criteria_id, $rating, $comments)
    {
        $sql = "UPDATE appraisal_ratings 
                SET hr_rating = ?, comments = CONCAT(comments, '\nHR: ', ?) 
                WHERE appraisal_id = ? AND criteria_id = ?";
        $params = [$rating, $comments, $appraisal_id, $criteria_id];
        return $this->con->execute($sql, $params);
    }

    public function getAppraisalRatings($appraisal_id)
    {
        $sql = "SELECT ar.*, ac.criteria_name, ac.description, ac.weightage 
                FROM appraisal_ratings ar 
                JOIN appraisal_criteria ac ON ar.criteria_id = ac.criteria_id 
                WHERE ar.appraisal_id = ?";
        return $this->con->queryAll($sql, [$appraisal_id]);
    }

    public function getRatingsByCriteria($criteria_id, $period_id)
    {
        $sql = "SELECT ar.*, ea.employee_id, e.first_name, e.last_name 
                FROM appraisal_ratings ar 
                JOIN employee_appraisals ea ON ar.appraisal_id = ea.appraisal_id 
                JOIN employees e ON ea.employee_id = e.id 
                WHERE ar.criteria_id = ? AND ea.period_id = ?";
        $params = [$criteria_id, $period_id];
        return $this->con->queryAll($sql, $params);
    }

    public function getRatingById($rating_id)
    {
        $sql = "SELECT ar.*, ac.criteria_name 
                FROM appraisal_ratings ar 
                JOIN appraisal_criteria ac ON ar.criteria_id = ac.criteria_id 
                WHERE ar.rating_id = ?";
        return $this->con->queryOne($sql, [$rating_id]);
    }

    public function deleteRating($rating_id)
    {
        $sql = "DELETE FROM appraisal_ratings WHERE rating_id = ?";
        return $this->con->execute($sql, [$rating_id]);
    }

    public function getAverageRatings($period_id, $department_id = null)
    {
        $sql = "SELECT ac.criteria_name, 
                       AVG(ar.self_rating) as avg_self, 
                       AVG(ar.hod_rating) as avg_hod, 
                       AVG(ar.hr_rating) as avg_hr 
                FROM appraisal_ratings ar 
                JOIN employee_appraisals ea ON ar.appraisal_id = ea.appraisal_id 
                JOIN appraisal_criteria ac ON ar.criteria_id = ac.criteria_id 
                JOIN employees e ON ea.employee_id = e.id 
                WHERE ea.period_id = ?";

        $params = [$period_id];

        if ($department_id) {
            $sql .= " AND e.department_id = ?";
            $params[] = $department_id;
        }

        $sql .= " GROUP BY ac.criteria_id";
        return $this->con->queryAll($sql, $params);
    }
}
?>