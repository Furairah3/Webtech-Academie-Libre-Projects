<?php require_once 'auth.php'; ?>
<?php 
require_once '../config/db.php';
require_once '../config/base.php';
$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();

if($_POST && isset($_POST['module_id'])) {
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description'] ?? '');
    $file_path = null;

    if(isset($_FILES['assignment_file']) && $_FILES['assignment_file']['size'] > 0) {
        $file = $_FILES['assignment_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "assign_posted_{$module_id}_" . time() . ".$ext";
        $path = "../assets/uploads/$filename";
        if(move_uploaded_file($file['tmp_name'], $path)) {
            $file_path = "assets/uploads/$filename";
        }
    }

    $check = $pdo->prepare("SELECT id FROM module_assignments WHERE module_id = ?");
    $check->execute([$module_id]);
    if($check->fetch()) {
        $sql = "UPDATE module_assignments SET title=?, description=?, file_path=? WHERE module_id=?";
        $pdo->prepare($sql)->execute([$title, $desc, $file_path, $module_id]);
    } else {
        $sql = "INSERT INTO module_assignments (module_id, title, description, file_path) VALUES (?,?,?,?)";
        $pdo->prepare($sql)->execute([$module_id, $title, $desc, $file_path]);
    }
    $success = "Assignment posted successfully!";
}

include 'includes/admin-header.php'; 
?>

<h2 style="color:#f59e0b;text-align:center;margin:40px 0;">Post Assignment</h2>

<?php if(isset($success)): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:800px;margin:0 auto;padding:40px;">
    <form method="POST" enctype="multipart/form-data">
        <label>Select Course</label>
        <select name="course_id" id="course_id" required onchange="loadModules()" style="width:100%;padding:14px;margin:10px 0;">
            <option value="">Choose Course</option>
            <?php foreach($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Select Module</label>
        <select name="module_id" id="module_id" required style="width:100%;padding:14px;margin:10px 0;">
            <option value="">First select a course</option>
        </select>

        <input type="text" name="title" placeholder="Assignment Title" required style="margin:15px 0;">
        <textarea name="description" rows="5" placeholder="Instructions (optional)" style="width:100%;padding:16px;margin:15px 0;"></textarea>

        <p><strong>Attach File (PDF, DOCX, TXT)</strong></p>
        <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.txt" style="margin:15px 0;">

        <button type="submit" class="btn" style="width:100%;background:#f59e0b;padding:18px;font-size:1.4rem;">
            Post Assignment
        </button>
    </form>
</div>

<script>
function loadModules() {
    const courseId = document.getElementById('course_id').value;
    const moduleSelect = document.getElementById('module_id');
    if(!courseId) {
        moduleSelect.innerHTML = '<option value="">First select a course</option>';
        return;
    }
    fetch('get-modules.php?course_id=' + courseId)
        .then(r => r.text())
        .then(data => {
            moduleSelect.innerHTML = '<option value="">Select Module</option>' + data;
        });
}
</script>

<?php include '../includes/footer.php'; ?>
