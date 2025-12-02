<?php
class Claim {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getClaimsByEmployee($employee_id) {
        $this->db->query('SELECT c.*, e.name as employee_name 
                         FROM claims c 
                         JOIN employees e ON c.employee_id = e.id 
                         WHERE c.employee_id = :employee_id 
                         ORDER BY c.created_at DESC');
        $this->db->bind(':employee_id', $employee_id);
        return $this->db->resultSet();
    }

    public function addClaim($data) {
        $this->db->query('INSERT INTO claims (employee_id, claim_type, claim_category, 
                         claim_amount, claim_date, description, status) 
                         VALUES (:employee_id, :claim_type, :claim_category, 
                         :claim_amount, :claim_date, :description, :status)');
        
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':claim_type', $data['claim_type']);
        $this->db->bind(':claim_category', $data['claim_category']);
        $this->db->bind(':claim_amount', $data['claim_amount']);
        $this->db->bind(':claim_date', $data['claim_date']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function getClaimById($id) {
        $this->db->query('SELECT c.*, e.name as employee_name 
                         FROM claims c 
                         JOIN employees e ON c.employee_id = e.id 
                         WHERE c.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getAllClaims() {
        $this->db->query('SELECT c.*, e.name as employee_name 
                         FROM claims c 
                         JOIN employees e ON c.employee_id = e.id 
                         ORDER BY c.created_at DESC');
        return $this->db->resultSet();
    }

    public function updateClaimStatus($data) {
        $this->db->query('UPDATE claims SET status = :status 
                         WHERE id = :claim_id');
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':claim_id', $data['claim_id']);
        return $this->db->execute();
    }

    public function addClaimWithDocuments($claimData, $documents) {
        $this->db->beginTransaction();
        
        try {
            // Insert main claim
            $this->db->query('INSERT INTO claims (employee_id, claim_type, claim_category, 
                             claim_amount, claim_date, description, status) 
                             VALUES (:employee_id, :claim_type, :claim_category, 
                             :claim_amount, :claim_date, :description, :status)');
            
            $this->db->bind(':employee_id', $claimData['employee_id']);
            $this->db->bind(':claim_type', $claimData['claim_type']);
            $this->db->bind(':claim_category', $claimData['claim_category']);
            $this->db->bind(':claim_amount', $claimData['claim_amount']);
            $this->db->bind(':claim_date', $claimData['claim_date']);
            $this->db->bind(':description', $claimData['description']);
            $this->db->bind(':status', 'Pending');
            
            $this->db->execute();
            $claim_id = $this->db->lastInsertId();
            
            // Insert claim details with documents
            foreach($documents as $doc) {
                $this->db->query('INSERT INTO claim_details (claim_id, expense_type, 
                                 amount, receipt_path) 
                                 VALUES (:claim_id, :expense_type, :amount, :receipt_path)');
                
                $this->db->bind(':claim_id', $claim_id);
                $this->db->bind(':expense_type', $doc['type']);
                $this->db->bind(':amount', $doc['amount']);
                $this->db->bind(':receipt_path', $doc['file_path']);
                
                $this->db->execute();
            }
            
            $this->db->commit();
            return true;
        } catch(Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}