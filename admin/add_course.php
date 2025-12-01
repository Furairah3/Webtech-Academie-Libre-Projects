<?php 
require_once 'auth.php'; 
require_once '../includes/functions.php';
require_once '../config/base.php';  // ← ADDED
if(!is_admin()) redirect(BASE_URL . '/login.php');
require_once '../config/db.php';

$success = $error = '';

if($_POST) {
    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    
    if(empty($title)) {
        $error = "Title is required!";
    } else {
        $thumbnail = '';
        if(!empty($_FILES['thumbnail']['name'])) {
            $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumbnail = "course_" . time() . ".$ext";
            $target = "../assets/uploads/courses/$thumbnail";
            if(!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target);
        }

        $pdo->prepare("INSERT INTO courses (title, description, thumbnail) VALUES (?,?,?)")
            ->execute([$title, $desc, $thumbnail]);
        $success = "Course added successfully!";
    }
}

include 'includes/admin-header.php'; 
?>

<h2 style="color:#f59e0b;">Add New Course</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;margin:20px 0;text-align:center;">
        <?= $success ?>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div style="background:#ef4444;color:white;padding:20px;border-radius:12px;margin:20px 0;text-align:center;">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card" style="max-width:700px;margin:0 auto;padding:40px;">
    <input type="text" name="title" placeholder="Course Title (e.g. Mathematics Grade 10)" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
    <textarea name="description" placeholder="Short description (optional)" style="width:100%;padding:15px;margin:15px 0;border-radius:12px;height:120px;"></textarea>
    <p style="margin:20px 0 10px;color:#e2e8f0;">Course Thumbnail (optional):</p>
    <input type="file" name="thumbnail" accept="image/*" style="margin:10px 0;">
    <button type="submit" class="btn" style="background:#f59e0b;width:100%;margin-top:20px;padding:18px;font-size:1.3rem;">
        Create Course
    </button>
</form>

<?php include '../includes/footer.php'; ?>
