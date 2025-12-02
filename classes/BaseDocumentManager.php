class BaseDocumentManager {
    protected $con;
    
    public function __construct($connection) {
        $this->con = $connection;
    }
    
    // Common methods used by both panels
    public function getDocumentDetails($assignmentId) {
        // ... existing getDocumentDetails logic ...
    }
}