<?php
require_once __DIR__ . '/../config/database.php';
session_destroy();
header("Location: /j17-fitnes/auth/login.php");
exit;
