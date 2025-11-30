<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $name = $fname . ' ' . $lname;

    if($password !== $confirm) 
        {
        die("Passwords do not match!");
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        die("Password must be at least 8 characters long and include:
        - One uppercase letter<br>
        - One lowercase letter<br>
        - One number<br>
        - One special character (e.g. @, #, $, %, *)");
    }
    
    // if(strlen($password) < 8) 
    // {
    //     die("Password must be at least 6 characters!");
    // }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0) {
        die("Email already registered! <a href='login.php'>Login here</a>");
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (fname, lname, username, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$fname, $lname, $username, $email, $hashed]);

    echo "<h2 style='text-align:center;color:#06b6d4;margin-top:100px;'>
          Registration Successful!<br>
          <a href='login.php' style='color:#06b6d4;'>Click here to login</a>
          </h2>";
}
?>