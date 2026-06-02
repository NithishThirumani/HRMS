<?php
require_once dirname(__DIR__) . '/includes/hrms_session.php';
require_once dirname(__DIR__) . '/includes/hrms_paths.php';
hrms_destroy_session('employee');
hrms_redirect('login.php');
