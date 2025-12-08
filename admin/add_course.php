<?php 
require_once 'auth.php';
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) redirect(BASE_URL . '/login.php');
require_once '../config/db.php';

$success = $error = '';

// === DELETE COURSE ===
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);
    $success = "Course deleted successfully!";
}

// === EDIT COURSE (save changes) ===
if(isset($_POST['save_edit'])) {
    $id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $thumbnail = $_POST['old_thumbnail'] ?? '';

    if(empty($title)) {
        $error = "Title is required!";
    } else {
        if(!empty($_FILES['thumbnail']['name'])) {
            $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumbnail = "course_" . time() . ".$ext";
            $target = "../assets/uploads/courses/$thumbnail";
            if(!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target);
        }

        $pdo->prepare("UPDATE courses SET title = ?, description = ?, thumbnail = ? WHERE id = ?")
            ->execute([$title, $desc, $thumbnail, $id]);
        $success = "Course updated successfully!";
    }
}

// === CREATE NEW COURSE ===
if(isset($_POST['create'])) {
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
        $success = "Course created successfully!";
    }
}

$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
include 'includes/admin-header.php'; 
?>

<h2 style="color:#f59e0b;">Manage Courses</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:15px;border-radius:12px;margin:20px 0;text-align:center;">
        <?= $success ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div style="background:#ef4444;color:white;padding:15px;border-radius:12px;margin:20px 0;text-align:center;">
        <?= $error ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>

<!-- Create New Course Form -->
<form method="POST" enctype="multipart/form-data" class="card" style="max-width:700px;margin:0 auto 40px;padding:30px;">
    <h3 style="color:#1e293b;margin-bottom:20px;text-align:center;">Create New Course</h3>
    <input type="text" name="title" placeholder="Course Title (e.g. Mathematics Grade 10)" required 
           style="width:100%;padding:15px;margin:15px 0;border-radius:12px;border:1px solid #cbd5e1;">
    <textarea name="description" placeholder="Short description (optional)" 
              style="width:100%;padding:15px;margin:15px 0;border-radius:12px;height:120px;border:1px solid #cbd5e1;"></textarea>
    <p style="margin:15px 0 10px;color:#475569;">Course Thumbnail (optional):</p>
    <input type="file" name="thumbnail" accept="image/*" style="margin:10px 0;">
    <button type="submit" name="create" class="btn" style="background:#f59e0b;width:100%;margin-top:20px;padding:15px;font-size:1.1rem;border-radius:12px;">
        Create Course
    </button>
</form>

<!-- List of All Courses -->
<div class="card" style="margin-top:30px;">
    <h3 style="color:#1e293b;margin-bottom:25px;border-bottom:2px solid #f1f5f9;padding-bottom:10px;">
        All Courses <span style="background:#f59e0b;color:white;padding:5px 12px;border-radius:20px;font-size:0.9rem;"><?= count($courses) ?></span>
    </h3>
    
    <?php if(empty($courses)): ?>
        <div style="text-align:center;padding:40px;color:#64748b;">
            No courses found. Create your first course above!
        </div>
    <?php endif; ?>
    
    <?php foreach($courses as $c): ?>
        <div style="background:#f8fafc;padding:20px;margin:15px 0;border-radius:12px;display:flex;justify-content:space-between;align-items:center;border:1px solid #e2e8f0;">
            <div style="flex:1;display:flex;align-items:center;">
                <?php if($c['thumbnail']): ?>
                    <img src="../assets/uploads/courses/<?= htmlspecialchars($c['thumbnail']) ?>" 
                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;margin-right:20px;">
                <?php else: ?>
                    <div style="width:80px;height:80px;background:#f1f5f9;border-radius:8px;margin-right:20px;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                        No Image
                    </div>
                <?php endif; ?>
                <div>
                    <strong style="font-size:1.2rem;color:#1e293b;"><?= htmlspecialchars($c['title']) ?></strong><br>
                    <small style="color:#64748b;"><?= htmlspecialchars($c['description'] ?: 'No description') ?></small><br>
                    <small style="color:#94a3b8;">ID: <?= $c['id'] ?> • Created: <?= date('M d, Y', strtotime($c['created_at'] ?? 'now')) ?></small>
                </div>
            </div>
            <div>
                <a href="?edit=<?= $c['id'] ?>" class="btn" 
                   style="background:#06b6d4;padding:10px 20px;margin:5px;border-radius:8px;color:white;text-decoration:none;">
                    Edit
                </a>
                <a href="?delete=<?= $c['id'] ?>" class="btn" 
                   style="background:#ef4444;padding:10px 20px;margin:5px;border-radius:8px;color:white;text-decoration:none;"
                   onclick="return confirm('Delete this course forever? This action cannot be undone.')">
                    Delete
                </a>
            </div>
        </div>

        <!-- Edit Form (shown when ?edit=id) -->
        <?php if(isset($_GET['edit']) && $_GET['edit'] == $c['id']): ?>
            <div style="background:#e0f2fe;padding:25px;border-radius:12px;margin-top:15px;border:2px solid #06b6d4;">
                <h4 style="color:#0e7490;margin-bottom:15px;">Edit Course: <?= htmlspecialchars($c['title']) ?></h4>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                    <input type="hidden" name="old_thumbnail" value="<?= htmlspecialchars($c['thumbnail']) ?>">
                    
                    <input type="text" name="title" value="<?= htmlspecialchars($c['title']) ?>" required 
                           style="width:100%;padding:12px;margin:10px 0;border-radius:8px;border:1px solid #cbd5e1;">
                    
                    <textarea name="description" 
                              style="width:100%;padding:12px;margin:10px 0;border-radius:8px;height:100px;border:1px solid #cbd5e1;"><?= htmlspecialchars($c['description']) ?></textarea>
                    
                    <?php if($c['thumbnail']): ?>
                        <p style="margin:10px 0 5px;color:#475569;">Current thumbnail:</p>
                        <img src="../assets/uploads/courses/<?= htmlspecialchars($c['thumbnail']) ?>" 
                             style="width:120px;height:120px;object-fit:cover;border-radius:8px;margin-bottom:15px;border:2px solid #94a3b8;">
                    <?php endif; ?>
                    
                    <p style="margin:15px 0 5px;color:#475569;"><?= $c['thumbnail'] ? 'Change thumbnail (optional):' : 'Upload thumbnail (optional):' ?></p>
                    <input type="file" name="thumbnail" accept="image/*" style="margin:10px 0;">
                    
                    <div style="display:flex;gap:10px;margin-top:20px;">
                        <button type="submit" name="save_edit" class="btn" 
                                style="background:#10b981;padding:12px 25px;border-radius:8px;color:white;border:none;cursor:pointer;">
                            Save Changes
                        </button>
                        <a href="?" class="btn" 
                           style="background:#6b7280;padding:12px 25px;border-radius:8px;color:white;text-decoration:none;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
