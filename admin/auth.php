<?php
session_start(); 
require_once '../includes/functions.php';

if(!is_logged_in()) {
    redirect('../login.php');
}

if(!is_admin()) {
    // Not an admin → kick out immediately
    echo '<h2 style="text-align:center;color:#ef4444;margin-top:100px;">
          Access Denied!<br><br>
          <a href="../dashboard.php" style="color:#06b6d4;">← Back to Dashboard</a>
          </h2>';
    exit();
}
?>