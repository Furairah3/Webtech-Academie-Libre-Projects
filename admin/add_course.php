<?php require_once 'auth.php'; ?>
<?php 
require_once '../includes/functions.php';
if(!is_admin()) redirect('../login.php');
require_once '../config/db.php';

if($_POST)
{
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    
    $thumbnail = '';
    if($_FILES['thumbnail']['name']){
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $thumbnail = "course_" . time() . ".$ext";
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "../assets/uploads/$thumbnail");
    }

    $pdo->prepare("INSERT INTO courses (title, description, thumbnail) VALUES (?,?,?)")
        ->execute([$title, $desc, $thumbnail]);
    echo "<p style='color:#10b981;'>Course added!</p>";
}
include 'includes/admin-header.php'; 
?>

<h2 style="color:#f59e0b;">Add New Course</h2>
<form method="POST" enctype="multipart/form-data" class="card">
    <input type="text" name="title" placeholder="Course Title (e.g. Mathematics Grade 10)" required>
    <textarea name="description" placeholder="40" placeholder="Short description (optional)"></textarea>
    <p>Course Thumbnail (optional):</p>
    <input type="file" name="thumbnail" accept="image/*">
    <button type="submit" class="btn" style="background:#f59e0b;width:100%;margin-top:15px;">Create Course</button>
</form>

<?php include '../includes/footer.php'; ?>