<?php 
require_once 'auth.php'; 
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) redirect(BASE_URL . '/login.php');
require_once '../config/db.php';

$modules = $pdo->query("
    SELECT m.id, m.title, c.title as course 
    FROM modules m JOIN courses c ON m.course_id = c.id 
    ORDER BY c.title, m.module_order
")->fetchAll();

$success = '';

if($_POST){
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['youtube_url']);
    $order = (int)$_POST['lecture_order'];

    if($module_id && $title && $url) {
        $pdo->prepare("INSERT INTO lectures (module_id, title, youtube_url, lecture_order) VALUES (?,?,?,?)")
            ->execute([$module_id, $title, $url, $order]);
        $success = "Lecture added successfully!";
    }
}

include 'includes/admin-header.php'; 
?>

<h2 style="color:#06b6d4;">Add YouTube Lecture</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px;">
        <?= $success ?>
    </div>
<?php endif; ?>

<form method="POST" class="card" style="max-width:800px;margin:0 auto;">
    <select name="module_id" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
        <option value="">Select Module</option>
        <?php foreach($modules as $m): ?>
            <option value="<?= $m['id'] ?>">[<?= htmlspecialchars($m['course']) ?>] <?= htmlspecialchars($m['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="title" placeholder="Lecture Title" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
    <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
    <input type="number" name="lecture_order" placeholder="Order (1,2,3...)" value="1" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
    <button type="submit" class="btn" style="background:#06b6d4;width:100%;margin-top:20px;padding:18px;font-size:1.3rem;">
        Add Lecture
    </button>
</form>

<?php include '../includes/footer.php'; ?>
