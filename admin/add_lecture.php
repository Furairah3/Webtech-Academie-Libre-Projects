<?php 
require_once 'auth.php'; 
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) redirect(BASE_URL . '/login.php');
require_once '../config/db.php';

$success = $error = '';

// === DELETE LECTURE ===
if(isset($_GET['delete_lecture'])) {
    $id = (int)$_GET['delete_lecture'];
    $pdo->prepare("DELETE FROM lectures WHERE id = ?")->execute([$id]);
    $success = "Lecture deleted successfully!";
}

// === EDIT LECTURE (save changes) ===
if(isset($_POST['save_edit'])) {
    $id = (int)$_POST['lecture_id'];
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['youtube_url']);
    $order = (int)$_POST['lecture_order'];

    if($module_id && $title && $url) {
        $pdo->prepare("UPDATE lectures SET module_id = ?, title = ?, youtube_url = ?, lecture_order = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$module_id, $title, $url, $order, $id]);
        $success = "Lecture updated successfully!";
    } else {
        $error = "All fields are required!";
    }
}

// === CREATE NEW LECTURE ===
if(isset($_POST['create_lecture'])) {
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['youtube_url']);
    $order = (int)$_POST['lecture_order'];

    if($module_id && $title && $url) {
        $pdo->prepare("INSERT INTO lectures (module_id, title, youtube_url, lecture_order) VALUES (?,?,?,?)")
            ->execute([$module_id, $title, $url, $order]);
        $success = "Lecture created successfully!";
    } else {
        $error = "All fields are required!";
    }
}

// Fetch all modules for dropdowns
$modules = $pdo->query("
    SELECT m.id, m.title, c.title as course, c.id as course_id
    FROM modules m 
    JOIN courses c ON m.course_id = c.id 
    ORDER BY c.title, m.module_order
")->fetchAll();

// Fetch all lectures with module and course info
$lectures = $pdo->query("
    SELECT l.*, m.title as module_title, c.title as course_title
    FROM lectures l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.title, m.module_order, l.lecture_order
")->fetchAll();

include 'includes/admin-header.php'; 
?>

<h2 style="color:#06b6d4;border-bottom:2px solid #06b6d4;padding-bottom:10px;">Manage YouTube Lectures</h2>

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

<!-- Create New Lecture Form -->
<div class="card" style="max-width:800px;margin:0 auto 40px;padding:30px;">
    <h3 style="color:#1e293b;margin-bottom:20px;text-align:center;">Add New YouTube Lecture</h3>
    <form method="POST">
        <select name="module_id" required 
                style="width:100%;padding:15px;margin:15px 0;border-radius:12px;border:1px solid #cbd5e1;background:white;">
            <option value="">Select Module</option>
            <?php foreach($modules as $m): ?>
                <option value="<?= $m['id'] ?>">
                    [<?= htmlspecialchars($m['course']) ?>] <?= htmlspecialchars($m['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="title" placeholder="Lecture Title" required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;border:1px solid #cbd5e1;">
        <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;border:1px solid #cbd5e1;">
        <input type="number" name="lecture_order" placeholder="Order (1,2,3...)" value="1" min="1" required 
               style="width:100%;padding:15px;margin:15px 0;border-radius:12px;border:1px solid #cbd5e1;">
        <button type="submit" name="create_lecture" class="btn" 
                style="background:#06b6d4;width:100%;margin-top:20px;padding:15px;font-size:1.1rem;border-radius:12px;color:white;">
            Add Lecture
        </button>
    </form>
</div>

<!-- List of All Lectures -->
<div class="card" style="margin-top:30px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <h3 style="color:#1e293b;margin:0;">
            All Lectures <span style="background:#06b6d4;color:white;padding:5px 12px;border-radius:20px;font-size:0.9rem;"><?= count($lectures) ?></span>
        </h3>
        <div style="color:#64748b;">
            Showing <?= count($lectures) ?> lecture<?= count($lectures) !== 1 ? 's' : '' ?>
        </div>
    </div>
    
    <?php if(empty($lectures)): ?>
        <div style="text-align:center;padding:40px;color:#64748b;background:#f8fafc;border-radius:12px;">
            No lectures found. Add your first lecture above!
        </div>
    <?php endif; ?>
    
    <?php 
    $currentCourse = '';
    foreach($lectures as $l): 
        if($currentCourse != $l['course_title']):
            $currentCourse = $l['course_title'];
    ?>
        <div style="background:#f1f5f9;padding:10px 15px;margin:20px 0 10px;border-radius:8px;color:#475569;font-weight:bold;">
            📚 <?= htmlspecialchars($currentCourse) ?>
        </div>
    <?php endif; ?>
    
    <div style="background:#f8fafc;padding:20px;margin:10px 0;border-radius:12px;border:1px solid #e2e8f0;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
            <div style="flex:1;min-width:300px;">
                <div style="display:flex;align-items:flex-start;gap:15px;">
                    <div style="flex-shrink:0;">
                        <div style="width:60px;height:60px;background:#ef4444;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;">
                            ▶
                        </div>
                    </div>
                    <div>
                        <strong style="font-size:1.1rem;color:#1e293b;"><?= htmlspecialchars($l['title']) ?></strong><br>
                        <div style="color:#64748b;margin-top:5px;">
                            <small>Module: <?= htmlspecialchars($l['module_title']) ?> • Order: #<?= $l['lecture_order'] ?></small><br>
                            <small style="color:#94a3b8;">
                                ID: <?= $l['id'] ?> • 
                                <?php if($l['created_at']): ?>
                                    Created: <?= date('M d, Y', strtotime($l['created_at'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <a href="?edit_lecture=<?= $l['id'] ?>" class="btn" 
                   style="background:#06b6d4;padding:8px 16px;border-radius:6px;color:white;text-decoration:none;font-size:0.9rem;">
                    Edit
                </a>
                <a href="?delete_lecture=<?= $l['id'] ?>" class="btn" 
                   style="background:#ef4444;padding:8px 16px;border-radius:6px;color:white;text-decoration:none;font-size:0.9rem;"
                   onclick="return confirm('Delete this lecture forever?')">
                    Delete
                </a>
                <a href="<?= htmlspecialchars($l['youtube_url']) ?>" target="_blank" class="btn" 
                   style="background:#10b981;padding:8px 16px;border-radius:6px;color:white;text-decoration:none;font-size:0.9rem;">
                    Watch
                </a>
            </div>
        </div>
        
        <!-- Edit Form (shown when ?edit_lecture=id) -->
        <?php if(isset($_GET['edit_lecture']) && $_GET['edit_lecture'] == $l['id']): ?>
            <div style="background:#e0f2fe;padding:25px;border-radius:12px;margin-top:15px;border:2px solid #06b6d4;">
                <h4 style="color:#0e7490;margin-bottom:15px;">Edit Lecture: <?= htmlspecialchars($l['title']) ?></h4>
                <form method="POST">
                    <input type="hidden" name="lecture_id" value="<?= $l['id'] ?>">
                    
                    <select name="module_id" required 
                            style="width:100%;padding:12px;margin:10px 0;border-radius:8px;border:1px solid #cbd5e1;background:white;">
                        <?php foreach($modules as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $l['module_id'] == $m['id'] ? 'selected' : '' ?>>
                                [<?= htmlspecialchars($m['course']) ?>] <?= htmlspecialchars($m['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" name="title" value="<?= htmlspecialchars($l['title']) ?>" placeholder="Lecture Title" required 
                           style="width:100%;padding:12px;margin:10px 0;border-radius:8px;border:1px solid #cbd5e1;">
                    
                    <input type="url" name="youtube_url" value="<?= htmlspecialchars($l['youtube_url']) ?>" placeholder="YouTube URL" required 
                           style="width:100%;padding:12px;margin:10px 0;border-radius:8px;border:1px solid #cbd5e1;">
                    
                    <input type="number" name="lecture_order" value="<?= $l['lecture_order'] ?>" min="1" required 
                           style="width:100%;padding:12px;margin:10px 0;border-radius:8px;border:1px solid #cbd5e1;">
                    
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
    </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
