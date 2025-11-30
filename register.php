<?php 
require_once 'includes/functions.php';
if(is_logged_in()) redirect('dashboard.php');
?>

<link rel="stylesheet" href="assets/css/singin.css">
<div class="card" style="max-width: 500px;margin: 50px auto;">
    <h2 class="text-center" style="color:#06b6d4;">Create Your Account</h2>

    <form action="register-process.php" method="POST">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>
        <input type="text" name="username" placeholder="User Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password (min 8 chars)" minlength="8" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" class="btn" style="width:100%;margin-top:10px;">Register</button>
    </form>

    <p class="text-center mt-20">
        Already have an account? <a href="login.php" style="color:#06b6d4;">Login here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>