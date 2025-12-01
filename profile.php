<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';
require_once 'config/base.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user) { session_destroy(); redirect('index.php'); }
$is_admin_user = ($user['role'] === 'admin');

// CHOOSE HEADER
if($is_admin_user) {
    include 'admin/includes/admin-header.php';
} else {
    include 'includes/header.php';
}

// ====================== HANDLE FORM SUBMISSION (ONE FORM TO RULE THEM ALL) ======================
$success = $error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Picture upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if(in_array($ext, $allowed)) {
            // Delete old custom picture
            if($user['profile_pic'] && $user['profile_pic'] !== 'default.jpg') {
                $old = "assets/uploads/" . $user['profile_pic'];
                if(file_exists($old)) unlink($old);
            }
            
            $new_name = "profile_{$user_id}_" . time() . ".{$ext}";
            $dest = "assets/uploads/" . $new_name;
            
            if(move_uploaded_file($file['tmp_name'], $dest)) {
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")
                    ->execute([$new_name, $user_id]);
                $_SESSION['profile_pic'] = $new_name;
                $user['profile_pic'] = $new_name;
                $success = "Profile picture updated!";
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Only JPG, PNG, GIF, WebP allowed!";
        }
    }
    
    // Text fields update
    else if(isset($_POST['fname'])) {
        $fname    = trim($_POST['fname']);
        $lname    = trim($_POST['lname']);
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $phone    = trim($_POST['phone'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $new_pass = $_POST['new_password'] ?? '';

        // Check username/email uniqueness
        if($user['username'] !== $username) {
            $check = $pdo->prepare("SELECT 1 FROM users WHERE username = ? AND id != ?");
            $check->execute([$username, $user_id]);
            if($check->fetch()) $error = "Username already taken!";
        }
        if($user['email'] !== $email) {
            $check = $pdo->prepare("SELECT 1 FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if($check->fetch()) $error = "Email already taken!";
        }

        if(!$error) {
            $sql = "UPDATE users SET fname=?, lname=?, username=?, email=?, phone=?, bio=?";
            $params = [$fname, $lname, $username, $email, $phone, $bio];

            if(!empty($new_pass) && strlen($new_pass) >= 6) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_pass, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $pdo->prepare($sql)->execute($params);
            
            $_SESSION['user_name'] = "$fname $lname";
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
}
?>

<!-- ADD THIS CSS FOR CACHE BUSTING -->
<style>
    .profile-img { border-radius: 50%; width: 80px; height: 80px; object-fit: cover; border: 3px solid #06b6d4; }
</style>

<div class="profile-container" style="max-width:1200px; margin:40px auto; padding:20px;">
    <?php if($success): ?>
        <div style="background:#10b981;color:white;padding:15px;border-radius:8px;margin:20px 0;text-align:center;font-weight:bold;">
            ✓ <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div style="background:#ef4444;color:white;padding:15px;border-radius:8px;margin:20px 0;text-align:center;font-weight:bold;">
            ✗ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="learning-overview" style="background:white;border-radius:15px;padding:30px;margin-bottom:30px;box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h1 style="color:#1e293b;font-size:2rem;margin-bottom:25px;">My Profile</h1>
        
        <div style="display:flex;align-items:center;gap:25px;margin-bottom:30px;">
            <!-- ONE FORM FOR EVERYTHING -->
            <form method="POST" enctype="multipart/form-data" id="mainProfileForm">
                <div style="position:relative;">
                    <img src="<?= ASSETS_URL ?>/uploads/<?= $user['profile_pic'] ?? 'default.jpg' ?>?v=<?= time() ?>"
                         alt="Profile" class="profile-img">
                    <label style="position:absolute;bottom:-8px;right:-8px;background:#06b6d4;color:white;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">
                        📷
                        <input type="file" name="profile_pic" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </label>
                </div>
            </form>

            <div>
                <h2 style="margin:0;font-size:1.8rem;color:#1e293b;">
                    <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>
                </h2>
                <p style="margin:5px 0 0;color:#64748b;">
                    <?= $is_admin_user ? 'Administrator' : 'Student' ?>
                    <?php if($is_admin_user): ?>
                        <span style="background:#f59e0b;color:black;padding:4px 12px;border-radius:50px;font-size:0.8rem;margin-left:10px;font-weight:bold;">ADMIN</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Edit Profile Button -->
        <button onclick="document.getElementById('editSection').style.display = document.getElementById('editSection').style.display === 'block' ? 'none' : 'block'"
                style="background:#06b6d4;color:white;border:none;padding:14px 32px;border-radius:12px;font-weight:bold;cursor:pointer;font-size:1.1rem;">
            Edit Profile Information
        </button>
    </div>

    <!-- EDIT PROFILE SECTION -->
    <div id="editSection" style="display:none;background:white;border-radius:15px;padding:35px;box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="margin-top:0;color:#1e293b;">Update Your Information</h2>
        <form method="POST" action="">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" placeholder="First Name" required style="padding:14px;border:1px solid #ddd;border-radius:8px;">
                <input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" placeholder="Last Name" required style="padding:14px;border:1px solid #ddd;border-radius:8px;">
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" placeholder="Username" required style="padding:14px;border:1px solid #ddd;border-radius:8px;">
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required style="padding:14px;border:1px solid #ddd;border-radius:8px;">
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Phone" style="padding:14px;border:1px solid #ddd;border-radius:8px;">
                <input type="password" name="new_password" placeholder="New Password (leave blank to keep)" style="padding:14px;border:1px solid #ddd;border-radius:8px;grid-column:span 2;">
            </div>
            <div style="margin-top:20px;">
                <textarea name="bio" placeholder="Short bio" style="width:100%;height:100px;padding:14px;border:1px solid #ddd;border-radius:8px;resize:vertical;"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            <div style="margin-top:25px;display:flex;gap:15px;">
                <button type="button" onclick="document.getElementById('editSection').style.display='none'" 
                        style="background:#6b7280;color:white;padding:14px 30px;border:none;border-radius:8px;cursor:pointer;">Cancel</button>
                <button type="submit" 
                        style="background:#06b6d4;color:white;padding:14px 30px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
