<?php
/**
 * Database Connection Handler
 * 
 * Manages database connections for biometric data
 */

require_once 'config.php';

class Database {
    private $conn;
    
    /**
     * Constructor - establishes database connection
     */
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
    }
    
    /**
     * Get database connection
     * 
     * @return mysqli Database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Execute query and return result
     * 
     * @param string $query SQL query
     * @param array $params Parameters for prepared statement
     * @return mysqli_result|bool Query result
     */
    public function query($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        if (!empty($params)) {
            $types = '';
            $bindParams = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                
                $bindParams[] = $param;
            }
            
            array_unshift($bindParams, $types);
            call_user_func_array([$stmt, 'bind_param'], $this->refValues($bindParams));
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Helper function for reference parameters
     * 
     * @param array $arr Array to convert to references
     * @return array Array of references
     */
    private function refValues($arr) {
        $refs = [];
        
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        
        return $refs;
    }
}