<?php 
require_once 'config/base.php';
require_once 'includes/functions.php';
if(is_logged_in()) {
    if($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('dashboard.php');
    }
}
?>
<link rel="stylesheet" href="assets/css/singin.css">

<div class="card" style="max-width: 500px; margin: 80px auto; padding: 40px;">
    
    <h2 class="text-center" style="color:#06b6d4; font-size:2rem;">Welcome to Academie Libre</h2>
    <p class="text-center" style="margin:20px 0; color:#94a3b8;">Log in to continue learning</p>

    
    <?php if(isset($_SESSION['error'])): ?>
      <div class="error-box">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form action="login-process.php" method="POST">
        <input type="email" name="email" placeholder="Email" required style="margin-bottom:15px;">
        <input type="password" name="password" placeholder="Password" required style="margin-bottom:20px;">
        <button type="submit" class="btn" style="width:100%; padding:15px; font-size:1.1rem;">Login</button>
    </form>

    <p class="text-center" style="margin-top:30px;">
        No account? <a href="register.php" style="color:#06b6d4; font-weight:bold;">Register here</a>
        
    </p>
    <p><a href="index.php" style="color:#06b6d4; font-weight:bold;">Want to go back home? </a></p>
</div>

