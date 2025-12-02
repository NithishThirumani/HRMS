<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('feedback_model');
        $this->load->helper('url');
    }

    public function index() {
        redirect('feedback/history');
    }

    public function history() {
        $data['feedbacks'] = $this->feedback_model->get_all_feedback();
        $data['title'] = 'Feedback History';
        
        $this->load->view('includes/header', $data);
        $this->load->view('feedback/history', $data);
        $this->load->view('includes/footer');
    }
}