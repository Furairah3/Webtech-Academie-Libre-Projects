<?php
require_once 'config/db.php';

$email = 'boss@learnstep.com';
$pass  = 'MyStrongPassword@2025';
$username  = 'Head Admin';
$fname = 'Head';
$lname = 'Admin';

$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo->prepare("INSERT INTO users (fname, lname, username, email, password, role) VALUES (?,?,?,?,?,?)")
    ->execute([$fname, $lname, $username, $email, $hash, 'admin']);

echo "Admin created!";
?>