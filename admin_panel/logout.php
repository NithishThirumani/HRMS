<?php
require_once dirname(__DIR__) . '/includes/hrms_session.php';
hrms_destroy_session('admin');
header("Location: /emps/login.php");
exit();