<?php
require_once "db.php";
require_once "auth.php";
logout_user($conn);
header('Location: login.php');
exit;
