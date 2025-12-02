<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once('connection.php');

class Feedback_model extends CI_Model
{

    public function get_all_feedback()
    {
        global $con;
        $query = $con->query("SELECT * FROM anonymous_feedback ORDER BY created_at DESC");
        return $query->fetch_all(MYSQLI_ASSOC);
    }
}