<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/base.php';

if(!is_logged_in()) {
    redirect(BASE_URL . '/login.php');
}
if(!is_admin()) {
    die("Access denied");
}
?>
