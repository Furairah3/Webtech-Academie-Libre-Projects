<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        // SUCCESSFUL LOGIN
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];  // ← Fixed: was 'username'
        $_SESSION['role']      = $user['role'];
        $_SESSION['profile_pic'] = $user['profile_pic'] ?? 'default.jpg';

        // REDIRECT BASED ON ROLE
        if($user['role'] === 'admin') {
            redirect('admin/dashboard.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        redirect('login.php');
    }
}
?>
