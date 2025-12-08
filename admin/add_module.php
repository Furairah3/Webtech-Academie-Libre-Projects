<?php 
require_once 'auth.php'; 
require_once '../config/base.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check admin
if(!is_admin()) redirect(BASE_URL . '/login.php');

$success = $error = '';

// === DELETE MODULE ===
if(isset($_GET['delete_module'])) {
    $id = (int)$_GET['delete_module'];
    $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
    $success = "Module deleted successfully!";
}

// === EDIT MODULE (save changes) ===
if(isset($_POST['save_edit'])) {
    $id = (int)$_POST['module_id'];
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $order = (int)$_POST['module_order'];

    if($course_id && $title) {
        $pdo->prepare("UPDATE modules SET course_id=?, title=?, module_order=?, updated_at=NOW() WHERE id=?")
            ->execute([$course_id, $title, $order, $id]);
        $success = "Module updated successfully!";
    } else {
        $error = "All fields required!";
    }
}

// === CREATE NEW MODULE ===
if($_POST && isset($_POST['course_id']) && !isset($_POST['save_edit'])) {
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $order = (int)$_POST['module_order'];

    if($course_id && $title) {
        $pdo->prepare("INSERT INTO modules (course_id, title, module_order) VALUES (?,?,?)")
            ->execute([$course_id, $title, $order]);
        $success = "Module created successfully!";
    } else {
        $error = "All fields required!";
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();

// Fetch all modules with course info
$modules = $pdo->query("
    SELECT m.*, c.title as course_title, 
           (SELECT COUNT(*) FROM lectures WHERE module_id = m.id) as lecture_count,
           (SELECT COUNT(*) FROM module_assignments WHERE module_id = m.id) as assignment_count
    FROM modules m
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.title, m.module_order
")->fetchAll();

include 'includes/admin-header.php'; 
?>

<h2 style="color:#7c3aed;text-align:center;margin:40px 0;">Manage Modules</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?= $success ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div style="background:#ef4444;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?= $error ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>

<!-- Create New Module Form -->
<div class="card" style="max-width:700px;margin:0 auto 40px;padding:40px;">
    <h3 style="color:#1e293b;text-align:center;margin-bottom:30px;font-size:1.5rem;">Add New Module</h3>
    <form method="POST">
        <select name="course_id" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
            <option value="">Choose a course</option>
            <?php foreach($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Module Title (e.g. Introduction to PHP)" required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

        <input type="number" name="module_order" value="1" min="1" required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

        <button type="submit" class="btn" style="width:100%;background:#7c3aed;padding:18px;font-size:1.4rem;">
            Add Module
        </button>
    </form>
</div>

<!-- List of All Modules -->
<div class="card" style="max-width:1000px;margin:40px auto;padding:40px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
        <h3 style="color:#1e293b;margin:0;font-size:1.5rem;">
            All Modules <span style="background:#7c3aed;color:white;padding:5px 15px;border-radius:20px;"><?= count($modules) ?></span>
        </h3>
        <div style="color:#64748b;font-size:0.9rem;">
            Showing <?= count($modules) ?> module<?= count($modules) !== 1 ? 's' : '' ?>
        </div>
    </div>
    
    <?php if(empty($modules)): ?>
        <div style="text-align:center;padding:40px;color:#64748b;">
            No modules created yet. Create your first module above!
        </div>
    <?php endif; ?>
    
    <?php 
    $currentCourse = '';
    foreach($modules as $m): 
        if($currentCourse != $m['course_title']):
            $currentCourse = $m['course_title'];
    ?>
        <div style="background:#f5f3ff;padding:12px 20px;margin:25px 0 15px;border-radius:8px;color:#5b21b6;font-weight:bold;font-size:1.1rem;">
            📚 <?= htmlspecialchars($currentCourse) ?>
        </div>
    <?php endif; ?>
    
    <div style="background:#f8fafc;padding:25px;margin:15px 0;border-radius:12px;border:1px solid #e2e8f0;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;">
            <div style="flex:1;min-width:300px;">
                <div style="display:flex;align-items:flex-start;gap:15px;">
                    <div style="flex-shrink:0;">
                        <div style="width:60px;height:60px;background:#7c3aed;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.8rem;font-weight:bold;">
                            M<?= $m['module_order'] ?>
                        </div>
                    </div>
                    <div>
                        <strong style="font-size:1.2rem;color:#1e293b;"><?= htmlspecialchars($m['title']) ?></strong><br>
                        <div style="color:#64748b;margin-top:8px;">
                            <div style="display:flex;gap:15px;margin-top:5px;">
                                <span style="background:#e0e7ff;color:#3730a3;padding:4px 10px;border-radius:4px;font-size:0.9rem;">
                                    📚 <?= htmlspecialchars($m['course_title']) ?>
                                </span>
                                <span style="background:#dcfce7;color:#166534;padding:4px 10px;border-radius:4px;font-size:0.9rem;">
                                    🎓 <?= $m['lecture_count'] ?> lecture<?= $m['lecture_count'] != 1 ? 's' : '' ?>
                                </span>
                                <span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:4px;font-size:0.9rem;">
                                    📝 <?= $m['assignment_count'] ?> assignment<?= $m['assignment_count'] != 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <small style="color:#94a3b8;display:block;margin-top:8px;">
                                ID: <?= $m['id'] ?> • 
                                <?php if($m['created_at']): ?>
                                    Created: <?= date('M d, Y', strtotime($m['created_at'])) ?>
                                <?php endif; ?>
                                <?php if($m['updated_at'] && $m['updated_at'] != $m['created_at']): ?>
                                    • Updated: <?= date('M d, Y', strtotime($m['updated_at'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="?edit_module=<?= $m['id'] ?>" class="btn" 
                   style="background:#7c3aed;padding:10px 20px;border-radius:8px;color:white;text-decoration:none;">
                    Edit
                </a>
                <a href="?delete_module=<?= $m['id'] ?>" class="btn" 
                   style="background:#ef4444;padding:10px 20px;border-radius:8px;color:white;text-decoration:none;"
                   onclick="return confirm('Delete this module? All lectures and assignments in this module will also be deleted.')">
                    Delete
                </a>
            </div>
        </div>
        
        <!-- Edit Form (shown when ?edit_module=id) -->
        <?php if(isset($_GET['edit_module']) && $_GET['edit_module'] == $m['id']): ?>
            <div style="background:#f5f3ff;padding:25px;margin-top:20px;border-radius:12px;border:2px solid #7c3aed;">
                <h4 style="color:#5b21b6;margin-bottom:15px;">Edit Module</h4>
                <form method="POST">
                    <input type="hidden" name="module_id" value="<?= $m['id'] ?>">
                    
                    <select name="course_id" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
                        <option value="">Choose a course</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $m['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="title" value="<?= htmlspecialchars($m['title']) ?>" 
                           placeholder="Module Title" required 
                           style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

                    <input type="number" name="module_order" value="<?= $m['module_order'] ?>" min="1" required 
                           style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">

                    <div style="display:flex;gap:15px;margin-top:20px;">
                        <button type="submit" name="save_edit" class="btn" 
                                style="background:#10b981;padding:15px 30px;font-size:1.1rem;border-radius:8px;color:white;">
                            Save Changes
                        </button>
                        <a href="?" class="btn" 
                           style="background:#6b7280;padding:15px 30px;font-size:1.1rem;border-radius:8px;color:white;text-decoration:none;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
