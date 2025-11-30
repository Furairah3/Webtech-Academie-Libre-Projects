<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('index.php');
require_once 'config/db.php';

$user_id   = $_SESSION['user_id'];
$module_id = (int)($_GET['module_id'] ?? 0);

if($module_id <= 0) {
    die('<div style="text-align:center;padding:100px;font-family:sans-serif;"><h2>Invalid Access</h2><a href="take-quiz.php">Back to Quizzes</a></div>');
}

// Get quiz + course info
$quiz_info = $pdo->prepare("
    SELECT q.id AS quiz_id, q.title AS quiz_title, q.passing_score,
           m.title AS module_title, m.id AS module_id,
           c.title AS course_title
    FROM quizzes q
    JOIN modules m ON q.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE q.module_id = ?
");
$quiz_info->execute([$module_id]);
$quiz = $quiz_info->fetch();

if(!$quiz) {
    die('<div style="text-align:center;padding:100px;"><h2>Quiz Not Found</h2></div>');
}

// Get user's BEST attempt
$best = $pdo->prepare("
    SELECT score, total_questions, attempted_at
    FROM quiz_attempts 
    WHERE user_id = ? AND quiz_id = ?
    ORDER BY score DESC LIMIT 1
");
$best->execute([$user_id, $quiz['quiz_id']]);
$best_attempt = $best->fetch();

$total_questions = $pdo->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
$total_questions->execute([$quiz['quiz_id']]);
$total_q = $total_questions->fetchColumn();

include 'includes/header.php';
?>

<div class="card" style="max-width:950px;margin:60px auto;padding:50px;background:#dce5fa;color:white;border-radius:25px;box-shadow:0 20px 50px rgba(139,92,246,0.3);">
    <h1 style="text-align:center;color:#8b5cf6;font-size:3.5rem;margin-bottom:10px;">
        Quiz Review
    </h1>
    <p style="text-align:center;color:#94a3b8;font-size:1.4rem;margin-bottom:30px;">
        <strong><?php echo htmlspecialchars($quiz['course_title']); ?></strong> → 
        <strong><?php echo htmlspecialchars($quiz['module_title']); ?></strong>
    </p>

    <!-- BEST SCORE BOX -->
    <div style="text-align:center;background:#1e293b;padding:30px;border-radius:20px;margin:30px 0;border:3px solid #8b5cf6;">
        <h2 style="color:#8b5cf6;font-size:4rem;margin:0;">
            <?php echo number_format($best_attempt['score'], 2); ?> / <?php echo $total_q; ?>
        </h2>
        <p style="font-size:2rem;color:#06b6d4;margin:15px 0;">
            Your Best Score: <strong><?php echo round(($best_attempt['score']/$total_q)*100, 1); ?>%</strong>
        </p>
        <p style="color:#64748b;">
            Completed on <?php echo date('d M Y \a\t H:i', strtotime($best_attempt['attempted_at'])); ?>
        </p>
    </div>

    <!-- QUESTIONS -->
    <?php
    $questions = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
    $questions->execute([$quiz['quiz_id']]);
    $questions = $questions->fetchAll();

    foreach($questions as $i => $q):
        $correct_opts = array_map('trim', explode(',', $q['correct_option']));
        $user_answers = $pdo->prepare("
            SELECT GROUP_CONCAT(answer ORDER BY answer SEPARATOR ',') as answers
            FROM user_answers 
            WHERE user_id = ? AND question_id = ? AND attempt_at = ?
        ");
        $user_answers->execute([$user_id, $q['id'], $best_attempt['attempted_at']]);
        $user_ans_str = $user_answers->fetchColumn();
        $user_opts = $user_ans_str ? explode(',', $user_ans_str) : [];
    ?>
        <div style="background:#1e293b;padding:35px;margin:30px 0;border-radius:20px;border-left:8px solid #8b5cf6;">
            <p style="font-size:1.6rem;margin-bottom:25px;color:#e2e8f0;">
                <strong>Q<?php echo $i+1 ?>.</strong> <?php echo htmlspecialchars($q['question_text']); ?>
            </p>

            <?php if($q['question_type'] === 'multiple'): ?>
                <p style="color:#10b981;background:#064e3b;padding:12px;border-radius:10px;font-weight:bold;margin-bottom:20px;">
                    Select all that apply
                </p>
            <?php endif; ?>

            <div style="display:grid;gap:16px;">
                <?php foreach(['A','B','C','D'] as $opt):
                    $text = $q['option_'.strtolower($opt)];
                    if(empty(trim($text))) continue;

                    $is_correct = in_array($opt, $correct_opts);
                    $is_chosen  = in_array($opt, $user_opts);

                    if($is_correct && $is_chosen) {
                        $bg = '#166534'; $icon = 'Correct'; $color = '#86efac';
                    } elseif($is_correct) {
                        $bg = '#166534'; $icon = 'Correct Answer'; $color = '#86efac';
                    } elseif($is_chosen) {
                        $bg = '#7f1d1d'; $icon = 'Your Wrong Answer'; $color = '#fca5a5';
                    } else {
                        $bg = '#334155'; $icon = ''; $color = '#94a3b8';
                    }
                ?>
                    <div style="padding:18px;background:<?php echo $bg; ?>;border-radius:14px;display:flex;align-items:center;gap:15px;">
                        <?php if($icon): ?>
                            <strong style="color:<?php echo $color; ?>;font-size:1.1rem;min-width:140px;">
                                <?php echo $icon; ?>
                            </strong>
                        <?php else: ?><div style="width:140px;"></div><?php endif; ?>
                        <span style="font-size:1.2rem;">
                            <strong style="color:#8b5cf6;"><?php echo $opt; ?>)</strong> 
                            <?php echo htmlspecialchars($text); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div style="text-align:center;margin-top:50px;">
        <a href="take-quiz.php" class="btn" style="background:#06b6d4;padding:20px 60px;font-size:1.6rem;border-radius:16px;">
            Back to My Quizzes
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>