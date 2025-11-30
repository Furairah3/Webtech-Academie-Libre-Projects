<?php
require_once 'includes/functions.php';
if (!is_logged_in()) exit();

require_once 'config/db.php';

if (!isset($_POST['lecture_id'])) {
    header("Location: course.php");
    exit();
}

$lecture_id = (int)$_POST['lecture_id'];
$user_id    = $_SESSION['user_id'];

// === 1. Mark this lecture as completed ===
$pdo->prepare("
    INSERT INTO user_progress (user_id, lecture_id, completed, completed_at) 
    VALUES (?, ?, 1, NOW())
    ON DUPLICATE KEY UPDATE completed = 1, completed_at = NOW()
")->execute([$user_id, $lecture_id]);

// === 2. Get module_id of this lecture ===
$module_id = $pdo->prepare("SELECT module_id FROM lectures WHERE id = ?");
$module_id->execute([$lecture_id]);
$module_id = $module_id->fetchColumn();

// === 3. Check if ALL lectures in this module are now completed ===
$check = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END) as done_count
    FROM lectures l
    LEFT JOIN user_progress up ON l.id = up.lecture_id AND up.user_id = ?
    WHERE l.module_id = ?
");
$check->execute([$user_id, $module_id]);
$stats = $check->fetch();

if ($stats['done_count'] == $stats['total'] && $stats['total'] > 0) {
    // Mark entire module as completed
    $pdo->prepare("
        INSERT INTO user_module_progress (user_id, module_id, completed, completed_at)
        VALUES (?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE completed = 1, completed_at = NOW()
    ")->execute([$user_id, $module_id]);
}

// === 4. SMART REDIRECT: Go to next lecture (or stay if last) ===
if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
    $redirect_url = $_POST['redirect_to'];
} else {
    // Fallback: go to module with next lecture highlighted
    $redirect_url = "module-view.php?id=$module_id";
    
    // Try to find current lecture index and go to next
    $current = $pdo->prepare("SELECT lecture_order FROM lectures WHERE id = ?");
    $current->execute([$lecture_id]);
    $current_order = $current->fetchColumn();

    $next = $pdo->prepare("SELECT id FROM lectures WHERE module_id = ? AND lecture_order > ? ORDER BY lecture_order LIMIT 1");
    $next->execute([$module_id, $current_order]);
    $next_lec = $next->fetch();

    if ($next_lec) {
        $next_index = $pdo->prepare("SELECT COUNT(*) FROM lectures WHERE module_id = ? AND lecture_order <= ?");
        $next_index->execute([$module_id, $current_order + 1]);
        $index = $next_index->fetchColumn() - 1;
        $redirect_url .= "&lec=$index";
    }
}

header("Location: $redirect_url");
exit();
?>