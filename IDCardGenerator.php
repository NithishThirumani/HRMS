<?php
class IDCardGenerator
{
    private $con;

    public function __construct($db)
    {
        $this->con = $db;
    }

    public function generateIDCard($employeeId)
    {
        // Get employee details
        $query = "SELECT * FROM employees WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $cardNo = 'IDC' . str_pad($employee['id'], 5, '0', STR_PAD_LEFT);
        if (!$employee) {
            return false;
        }

        // Add QR code to the front HTML
        $qrData = $employee['eid']; // or whatever data you want in QR
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);

        // Generate HTML for ID card
        $frontHTML = '<div class="card-container">
            <div class="id-card card-front">
                <div class="header">
                    <img src="/emps/img/logo.png" alt="HR Matrix Logo" class="logo">
                    <h1>EMPLOYEE IDENTITY CARD</h1>
                </div>
                <div class="card-content">
                    <div class="profile-section">
                        <div class="profile-pic">
                            <img src="/emps/admin_panel/uploads/profile_pics/' . ($employee['profile_pic'] ?? 'default.jpg') . '" alt="Profile Picture">
                        </div>
                        <div class="employee-name">' . $employee['full_name'] . '</div>
                        <div class="designation">' . $employee['designation'] . '</div>
                                       
                        <div class="info-row">
                            <span class="info-label">Employee ID</span>
                            <span class="info-value">' . $employee['eid'] . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Department</span>
                            <span class="info-value">' . $employee['department'] . '</span>
                        </div>
                    
                    <div class="qr-section">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($employee['eid']) . '" alt="QR Code">
                    </div> 
                </div>
                </div>
            </div>
            <div class="id-card card-back">
                <div class="back-header">
                    <h2>Additional Information</h2>
                </div>
                <div class="card-content">
                    <div class="info-group">
                        <div class="info-row">
                            <span>Blood Group:</span>
                            <span>' . $employee['blood_group'] . '</span>
                        </div>
                        <div class="info-row">
                            <span>Contact:</span>
                            <span>' . $employee['contact'] . '</span>
                        </div>
                        <div class="info-row">
                            <span>Date of Joining:</span>
                            <span>' . date('d/m/Y', strtotime($employee['doj'])) . '</span>
                        </div>
                    
                    <div class="company-info">
                        <h3>HR Matrix</h3>
                        <p>123 Business Avenue, Tech Park</p>
                        <p>City, State - PIN</p>
                        <p>Phone: +1234567890</p>
                        <p>Email: info@hrmatrix.com</p>
                    </div>
                    <div class="signature-section">
                        <div class="sign-line"></div>
                        <div class="sign-text">Authorized Signature</div>
                    </div>
                     </div>
                </div>
            </div>
        </div>';
        return $frontHTML;
    }
}
?>