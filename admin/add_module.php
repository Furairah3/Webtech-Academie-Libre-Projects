<?php require_once 'auth.php'; ?>
<?php 
require_once '../config/db.php';

$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();

if($_POST) {
    $course_id = (int)$_POST['course_id'];
    $title     = trim($_POST['title']);
    $order     = (int)$_POST['module_order'];

    // Insert without assignment_required
    $pdo->prepare("INSERT INTO modules (course_id, title, module_order) VALUES (?,?,?)")
        ->execute([$course_id, $title, $order]);

    $success = "Module added successfully!";
}

include 'includes/admin-header.php'; 
?>

<h2 style="color:#7c3aed;text-align:center;margin:40px 0;">Add New Module</h2>

<?php if(isset($success)): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:700px;margin:0 auto;padding:40px;">
    <form method="POST">
        <label style="color:#e2e8f0;font-size:1.2rem;">Select Course</label>
        <select name="course_id" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
            <option value="">Choose a course</option>
            <?php foreach($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Module Title (e.g. Introduction to PHP)" required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

        <input type="number" name="module_order" value="1" min="1" required 
               placeholder="Order number" style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

        <button type="submit" class="btn" style="width:100%;background:#7c3aed;padding:18px;font-size:1.4rem;">
            Add Module
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>