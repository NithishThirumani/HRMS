<?php
class Claims extends Controller {
    private $claimModel;
    
    public function __construct() {
        if(!isLoggedIn()) {
            redirect('users/login');
        }
        $this->claimModel = $this->model('Claim');
    }

    public function index() {
        // Check if user is HR
        if(isHR()) {
            $data = [
                'title' => 'Claims Management',
                'claims' => $this->claimModel->getAllClaims()
            ];
            $this->view('claims/hr_dashboard', $data);
        } else {
            $data = [
                'title' => 'My Claims',
                'claims' => $this->claimModel->getClaimsByEmployee($_SESSION['user_id'])
            ];
            $this->view('claims/index', $data);
        }
    }

    public function review($id) {
        // Only HR can access this
        if(!isHR()) {
            redirect('claims');
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'claim_id' => $id,
                'status' => trim($_POST['status']),
                'comments' => trim($_POST['comments'])
            ];

            if($this->claimModel->updateClaimStatus($data)) {
                flash('claim_message', 'Claim Status Updated Successfully');
                redirect('claims');
            }
        } else {
            $claim = $this->claimModel->getClaimById($id);
            $data = [
                'title' => 'Review Claim',
                'claim' => $claim
            ];
            $this->view('claims/review', $data);
        }
    }

    public function create() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle file uploads
            $documents = [];
            $uploadDir = 'uploads/claims/';
            
            // Create upload directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Process each uploaded document
            $fileCount = count($_FILES['documents']['name']);
            for($i = 0; $i < $fileCount; $i++) {
                if($_FILES['documents']['error'][$i] == 0) {
                    $fileName = uniqid() . '_' . $_FILES['documents']['name'][$i];
                    $filePath = $uploadDir . $fileName;
                    
                    if(move_uploaded_file($_FILES['documents']['tmp_name'][$i], $filePath)) {
                        $documents[] = [
                            'type' => $_POST['doc_type'][$i],
                            'amount' => $_POST['doc_amount'][$i],
                            'file_path' => $filePath
                        ];
                    }
                }
            }

            $claimData = [
                'employee_id' => $_SESSION['user_id'],
                'claim_type' => trim($_POST['claim_type']),
                'claim_category' => trim($_POST['claim_category']),
                'claim_amount' => trim($_POST['claim_amount']),
                'claim_date' => trim($_POST['claim_date']),
                'description' => trim($_POST['description'])
            ];

            if($this->claimModel->addClaimWithDocuments($claimData, $documents)) {
                flash('claim_message', 'Claim Submitted Successfully');
                redirect('claims');
            } else {
                die('Something went wrong');
            }
        } else {
            $this->view('claims/create');
        }
    }
}