// Get submission statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted
          FROM appraisal_forms 
          WHERE period_id = ?";