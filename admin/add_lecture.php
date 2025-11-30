<?php require_once 'auth.php'; ?>
<?php 
require_once '../includes/functions.php';
if(!is_admin()) redirect('../index.php');
require_once '../config/db.php';

$modules = $pdo->query("
    SELECT m.id, m.title, c.title as course 
    FROM modules m JOIN courses c ON m.course_id = c.id 
    ORDER BY c.title, m.module_order
")->fetchAll();

if($_POST){
    $module_id = $_POST['module_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['youtube_url']);
    $order = (int)$_POST['lecture_order'];

    $pdo->prepare("INSERT INTO lectures (module_id, title, youtube_url, lecture_order) VALUES (?,?,?,?)")
        ->execute([$module_id, $title, $url, $order]);
    echo "<p style='color:#10b981;'>Lecture added!</p>";
}
include 'includes/admin-header.php'; 
?>

<h2 style="color:#06b6d4;">Add YouTube Lecture</h2>
<form method="POST" class="card">
    <select name="module_id" required>
        <option value="">Select Module</option>
        <?php foreach($modules as $m): ?>
            <option value="<?= $m['id'] ?>">[<?= $m['course'] ?>] <?= htmlspecialchars($m['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="title" placeholder="Lecture Title (e.g. Introduction to Functions)" required>
    <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=ABC123 or https://youtu.be/ABC123" required>
    <input type="number" name="lecture_order" placeholder="Order (1,2,3...)" required>
    <button type="submit" class="btn" style="background:#06b6d4;width:100%;margin-top:15px;">Add Lecture</button>
</form>

<div style="margin-top:40px;">
    <h3 style="color:#94a3b8;">Pro Tip:</h3>
    <p>You can copy-paste 50+ links at once — just add one by one. Takes 3 seconds each.</p>
</div>

<?php include '../includes/footer.php'; ?>