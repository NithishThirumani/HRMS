<?php
require_once __DIR__ . '/../includes/hrms_session.php';
hrms_destroy_session('employee');
header('Location: /emps/login.php');
exit();

