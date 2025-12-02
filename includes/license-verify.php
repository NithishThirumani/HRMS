<?php
class LicenseVerifier {
    private $license_server = 'http://localhost/license-server/api/verify.php';
    private $license_key;
    
    public function __construct($license_key) {
        $this->license_key = $license_key;
    }
    
    public function verify() {
        $domain = $_SERVER['HTTP_HOST'];
        
        $ch = curl_init($this->license_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'license_key' => $this->license_key,
            'domain' => $domain
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);
        
        return $result;
    }
}