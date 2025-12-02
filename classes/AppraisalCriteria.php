<?php
require_once(__DIR__ . '/../connection.php');

class AppraisalCriteria
{
    private $con;
    private $criteria_id;
    private $criteria_name;
    private $description;
    private $weightage;
    private $is_active;

    public function __construct()
    {
        global $con;
        $this->con = $con;
    }

    public function createCriteria($criteria_name, $description, $weightage)
    {
        $sql = "INSERT INTO appraisal_criteria (criteria_name, description, weightage) 
                VALUES (?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ssd", $criteria_name, $description, $weightage);
        return $stmt->execute();
    }

    public function updateCriteria($criteria_id, $criteria_name, $description, $weightage)
    {
        $sql = "UPDATE appraisal_criteria 
                SET criteria_name = ?, description = ?, weightage = ? 
                WHERE criteria_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ssdi", $criteria_name, $description, $weightage, $criteria_id);
        return $stmt->execute();
    }

    public function toggleStatus($criteria_id)
    {
        $sql = "UPDATE appraisal_criteria 
                SET is_active = NOT is_active 
                WHERE criteria_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $criteria_id);
        return $stmt->execute();
    }

    public function getAllCriteria($activeOnly = true)
    {
        $sql = "SELECT * FROM appraisal_criteria";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY criteria_name";
        $result = $this->con->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCriteriaById($criteria_id)
    {
        $sql = "SELECT * FROM appraisal_criteria WHERE criteria_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $criteria_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getTotalWeightage()
    {
        $sql = "SELECT SUM(weightage) as total_weightage 
                FROM appraisal_criteria 
                WHERE is_active = TRUE";
        $result = $this->con->query($sql);
        $row = $result->fetch_assoc();
        return $row['total_weightage'] ?? 0;
    }

    public function deleteCriteria($criteria_id)
    {
        $sql = "DELETE FROM appraisal_criteria WHERE criteria_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $criteria_id);
        return $stmt->execute();
    }

    public function addCriteria($name, $description, $weightage)
    {
        try {
            $sql = "INSERT INTO appraisal_criteria (criteria_name, description, weightage, is_active) 
                    VALUES (?, ?, ?, 1)";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("ssi", $name, $description, $weightage);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding criteria: " . $e->getMessage());
            return false;
        }
    }
}
?>