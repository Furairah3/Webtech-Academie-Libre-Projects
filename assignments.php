<?php
require_once 'config/base.php';
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';
$user_id = $_SESSION['user_id'];

include 'includes/header.php'; // or your main header
?>

<div style="max-width:1100px;margin:80px auto;padding:40px;background:#dce5fa;color:white;border-radius:20px;">
    <h1 style="text-align:center;color:#8b5cf6;font-size:3rem;margin-bottom:40px;">
        My Assignments
    </h1>

    <?php
    $stmt = $pdo->prepare("
        SELECT ma.id as assign_id, ma.title as assign_title, ma.description, 
               ma.file_path as assign_file, m.id as module_id, m.title as module_title, 
               c.title as course_title, a.submitted_at, a.grade, a.feedback
        FROM module_assignments ma
        JOIN modules m ON ma.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN assignments a ON a.module_id = ma.module_id AND a.user_id = ?
        WHERE EXISTS (
            SELECT 1 FROM user_module_progress ump 
            WHERE ump.user_id = ? AND ump.module_id = ma.module_id AND ump.completed = 1
        )
        ORDER BY c.title, m.title
    ");
    $stmt->execute([$user_id, $user_id]);
    $assignments = $stmt->fetchAll();
    ?>

    <?php if(empty($assignments)): ?>
        <div style="text-align:center;padding:100px;color:#94a3b8;">
            <h2>No assignments yet</h2>
            <p>Complete all lectures in a module to unlock its assignment!</p>
        </div>
    <?php else: ?>
        <div style="display:grid;gap:25px;">
            <?php foreach($assignments as $a): ?>
                <div style="background:#1e293b;padding:30px;border-radius:16px;border-left:8px solid #f59e0b;">
                    <h3 style="color:#06b6d4;">
                        <?= htmlspecialchars($a['course_title']) ?> → <?= htmlspecialchars($a['module_title']) ?>
                    </h3>
                    <h4 style="color:#f59e0b;margin:10px 0;">
                        Assignment: <?= htmlspecialchars($a['assign_title']) ?>
                    </h4>

                    <?php if($a['submitted_at']): ?>
                        <p style="color:#10b981;font-weight:bold;">
                            Submitted on <?= date('d M Y H:i', strtotime($a['submitted_at'])) ?>
                            <?php if($a['grade'] !== null): ?>
                                → <strong>Grade: <?= $a['grade'] ?>/100</strong>
                            <?php else: ?>
                                → <span style="color:#f59e0b;">Waiting for grading...</span>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p style="color:#ef4444;font-weight:bold;">Not submitted yet</p>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>/module-view.php?id=<?= $a['module_id'] ?>" 
                       class="btn" style="background:#8b5cf6;padding:14px 30px;margin-top:10px;display:inline-block;">
                        Go to Assignment
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
