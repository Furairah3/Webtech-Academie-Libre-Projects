<?php require_once 'auth.php'; ?>
<?php 
require_once '../config/db.php';
require_once '../config/base.php';
require_once '../includes/functions.php';

// Check admin
if(!is_admin()) redirect(BASE_URL . '/login.php');

$success = $error = '';

// === DELETE ASSIGNMENT ===
if(isset($_GET['delete_assignment'])) {
    $id = (int)$_GET['delete_assignment'];
    $pdo->prepare("DELETE FROM module_assignments WHERE id = ?")->execute([$id]);
    $success = "Assignment deleted successfully!";
}

// === EDIT ASSIGNMENT (save changes) ===
if(isset($_POST['save_edit'])) {
    $id = (int)$_POST['assignment_id'];
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description'] ?? '');
    $file_path = $_POST['old_file_path'] ?? '';

    if(isset($_FILES['assignment_file']) && $_FILES['assignment_file']['size'] > 0) {
        $file = $_FILES['assignment_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "assign_posted_{$module_id}_" . time() . ".$ext";
        $path = "../assets/uploads/$filename";
        if(move_uploaded_file($file['tmp_name'], $path)) {
            $file_path = "assets/uploads/$filename";
        }
    }

    $pdo->prepare("UPDATE module_assignments SET module_id=?, title=?, description=?, file_path=?, updated_at=NOW() WHERE id=?")
        ->execute([$module_id, $title, $desc, $file_path, $id]);
    $success = "Assignment updated successfully!";
}

// === CREATE/UPDATE ASSIGNMENT ===
if($_POST && isset($_POST['module_id']) && !isset($_POST['save_edit'])) {
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
        $sql = "UPDATE module_assignments SET title=?, description=?, file_path=?, updated_at=NOW() WHERE module_id=?";
        $pdo->prepare($sql)->execute([$title, $desc, $file_path, $module_id]);
    } else {
        $sql = "INSERT INTO module_assignments (module_id, title, description, file_path) VALUES (?,?,?,?)";
        $pdo->prepare($sql)->execute([$module_id, $title, $desc, $file_path]);
    }
    $success = "Assignment posted successfully!";
}

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();

// Fetch all assignments with module and course info
$assignments = $pdo->query("
    SELECT ma.*, m.title as module_title, c.title as course_title, c.id as course_id
    FROM module_assignments ma
    JOIN modules m ON ma.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.title, m.module_order, ma.created_at DESC
")->fetchAll();

include 'includes/admin-header.php'; 
?>

<h2 style="color:#f59e0b;text-align:center;margin:40px 0;">Manage Assignments</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $success; ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>
<?php if($error): ?>
    <div style="background:#ef4444;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $error; ?>
        <a href="?" style="color:white;margin-left:15px;text-decoration:underline;">Close</a>
    </div>
<?php endif; ?>

<!-- Post New Assignment Form -->
<div class="card" style="max-width:800px;margin:0 auto 40px;padding:40px;">
    <h3 style="color:#1e293b;text-align:center;margin-bottom:30px;font-size:1.5rem;">Post New Assignment</h3>
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

        <input type="text" name="title" placeholder="Assignment Title" required style="width:100%;padding:14px;margin:15px 0;border-radius:8px;">
        <textarea name="description" rows="5" placeholder="Instructions (optional)" style="width:100%;padding:16px;margin:15px 0;border-radius:8px;"></textarea>

        <p><strong>Attach File (PDF, DOCX, TXT)</strong></p>
        <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.txt" style="margin:15px 0;">

        <button type="submit" class="btn" style="width:100%;background:#f59e0b;padding:18px;font-size:1.4rem;">
            Post Assignment
        </button>
    </form>
</div>

<!-- List of All Assignments -->
<div class="card" style="max-width:1000px;margin:40px auto;padding:40px;">
    <h3 style="color:#1e293b;text-align:center;margin-bottom:30px;font-size:1.5rem;">
        All Assignments <span style="background:#f59e0b;color:white;padding:5px 15px;border-radius:20px;"><?= count($assignments) ?></span>
    </h3>
    
    <?php if(empty($assignments)): ?>
        <div style="text-align:center;padding:40px;color:#64748b;">
            No assignments posted yet. Create your first assignment above!
        </div>
    <?php endif; ?>
    
    <?php 
    $currentCourse = '';
    foreach($assignments as $a): 
        if($currentCourse != $a['course_title']):
            $currentCourse = $a['course_title'];
    ?>
        <div style="background:#fef3c7;padding:12px 20px;margin:25px 0 15px;border-radius:8px;color:#92400e;font-weight:bold;font-size:1.1rem;">
            📚 <?= htmlspecialchars($currentCourse) ?>
        </div>
    <?php endif; ?>
    
    <div style="background:#f8fafc;padding:25px;margin:15px 0;border-radius:12px;border:1px solid #e2e8f0;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;">
            <div style="flex:1;min-width:300px;">
                <strong style="font-size:1.2rem;color:#1e293b;"><?= htmlspecialchars($a['title']) ?></strong><br>
                <div style="color:#64748b;margin-top:8px;">
                    Module: <strong><?= htmlspecialchars($a['module_title']) ?></strong><br>
                    <small>ID: <?= $a['id'] ?> • 
                    <?php if($a['created_at']): ?>
                        Posted: <?= date('M d, Y', strtotime($a['created_at'])) ?>
                    <?php endif; ?>
                    <?php if($a['updated_at'] && $a['updated_at'] != $a['created_at']): ?>
                        • Updated: <?= date('M d, Y', strtotime($a['updated_at'])) ?>
                    <?php endif; ?>
                    </small>
                </div>
                <?php if($a['description']): ?>
                    <div style="background:#f1f5f9;padding:12px;margin-top:12px;border-radius:8px;color:#475569;">
                        <?= nl2br(htmlspecialchars($a['description'])) ?>
                    </div>
                <?php endif; ?>
                <?php if($a['file_path']): ?>
                    <div style="margin-top:12px;">
                        <a href="../<?= htmlspecialchars($a['file_path']) ?>" target="_blank" 
                           style="display:inline-flex;align-items:center;gap:8px;background:#3b82f6;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
                            📎 Download File
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="?edit_assignment=<?= $a['id'] ?>" class="btn" 
                   style="background:#f59e0b;padding:10px 20px;border-radius:8px;color:white;text-decoration:none;">
                    Edit
                </a>
                <a href="?delete_assignment=<?= $a['id'] ?>" class="btn" 
                   style="background:#ef4444;padding:10px 20px;border-radius:8px;color:white;text-decoration:none;"
                   onclick="return confirm('Delete this assignment? Students will no longer have access.')">
                    Delete
                </a>
            </div>
        </div>
        
        <!-- Edit Form (shown when ?edit_assignment=id) -->
        <?php if(isset($_GET['edit_assignment']) && $_GET['edit_assignment'] == $a['id']): ?>
            <div style="background:#fef3c7;padding:25px;margin-top:20px;border-radius:12px;border:2px solid #f59e0b;">
                <h4 style="color:#92400e;margin-bottom:15px;">Edit Assignment</h4>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="old_file_path" value="<?= htmlspecialchars($a['file_path'] ?? '') ?>">
                    
                    <label>Select Course</label>
                    <select name="course_id" id="edit_course_<?= $a['id'] ?>" required onchange="loadEditModules(<?= $a['id'] ?>)" 
                            style="width:100%;padding:14px;margin:10px 0;border-radius:8px;">
                        <option value="">Choose Course</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $a['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Select Module</label>
                    <select name="module_id" id="edit_module_<?= $a['id'] ?>" required 
                            style="width:100%;padding:14px;margin:10px 0;border-radius:8px;">
                        <option value="">First select a course</option>
                    </select>

                    <input type="text" name="title" value="<?= htmlspecialchars($a['title']) ?>" placeholder="Assignment Title" required 
                           style="width:100%;padding:14px;margin:15px 0;border-radius:8px;">

                    <textarea name="description" rows="5" placeholder="Instructions (optional)" 
                              style="width:100%;padding:16px;margin:15px 0;border-radius:8px;"><?= htmlspecialchars($a['description']) ?></textarea>

                    <?php if($a['file_path']): ?>
                        <p><strong>Current File:</strong> 
                            <a href="../<?= htmlspecialchars($a['file_path']) ?>" target="_blank" style="color:#3b82f6;">
                                <?= basename($a['file_path']) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <p><strong><?= $a['file_path'] ? 'Replace File' : 'Attach File' ?> (PDF, DOCX, TXT)</strong></p>
                    <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.txt" style="margin:15px 0;">

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
                <script>
                // Load modules for this edit form
                document.addEventListener('DOMContentLoaded', function() {
                    const courseId = <?= $a['course_id'] ?>;
                    const moduleId = <?= $a['module_id'] ?>;
                    if(courseId) {
                        loadEditModules(<?= $a['id'] ?>, courseId, moduleId);
                    }
                });
                </script>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
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

function loadEditModules(assignId, courseId = null, selectedModuleId = null) {
    if(!courseId) {
        courseId = document.getElementById('edit_course_' + assignId).value;
    }
    const moduleSelect = document.getElementById('edit_module_' + assignId);
    if(!courseId) {
        moduleSelect.innerHTML = '<option value="">First select a course</option>';
        return;
    }
    fetch('get-modules.php?course_id=' + courseId)
        .then(r => r.text())
        .then(data => {
            let html = '<option value="">Select Module</option>' + data;
            if(selectedModuleId) {
                html = html.replace(`value="${selectedModuleId}"`, `value="${selectedModuleId}" selected`);
            }
            moduleSelect.innerHTML = html;
        });
}
</script>

<?php include '../includes/footer.php'; ?>
