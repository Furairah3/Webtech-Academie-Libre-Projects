<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$user_id      = $_SESSION['user_id'];
$module_id    = (int)($_GET['module_id'] ?? 0);
$MAX_ATTEMPTS = 3;

// ===================================================================
// 1. DASHBOARD – NO module_id → Show all quiz results
// ===================================================================
if ($module_id === 0) {
    include 'includes/header.php';
    ?>
    <div class="card" style="max-width:1100px;margin:60px auto;padding:40px;background:#dce5fa;color:white;border-radius:20px;">
        <h2 style="text-align:center;color:#8b5cf6;font-size:3rem;margin-bottom:40px;">My Quiz Results</h2>

        <?php
        $results = $pdo->prepare("
            SELECT 
                qa.attempt_count, qa.best_score, qa.best_percentage, qa.passed, qa.last_attempted_at,
                q.id AS quiz_id, q.title AS quiz_title, q.passing_score,
                m.id AS module_id, m.title AS module_title, c.title AS course_title
            FROM (
                SELECT user_id, quiz_id,
                       COUNT(*) AS attempt_count,
                       MAX(score) AS best_score,
                       ROUND(MAX(score / total_questions * 100),1) AS best_percentage,
                       MAX(passed) AS passed,
                       MAX(attempted_at) AS last_attempted_at
                FROM quiz_attempts WHERE user_id = ? GROUP BY quiz_id
            ) qa
            JOIN quizzes q ON qa.quiz_id = q.id
            JOIN modules m ON q.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            ORDER BY qa.last_attempted_at DESC
        ");
        $results->execute([$user_id]);
        $attempts = $results->fetchAll();
        ?>

        <?php if(empty($attempts)): ?>
            <div style="text-align:center;padding:100px;color:#64748b;">
                <h3>You haven't taken any quizzes yet.</h3>
                <a href="course.php" class="btn" style="background:#06b6d4;margin-top:20px;padding:16px 40px;">Browse Courses</a>
            </div>
        <?php else: ?>
            <div style="display:grid;gap:22px;">
                <?php foreach($attempts as $a): ?>
                    <div style="background:#1e293b;padding:28px;border-radius:16px;border-left:8px solid <?= $a['passed']?'#10b981':'#f59e0b' ?>;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
                            <div>
                                <h3 style="color:#06b6d4;margin:0;"><?= htmlspecialchars($a['course_title']) ?></h3>
                                <p style="margin:8px 0 12px;color:#94a3b8;">
                                    → <?= htmlspecialchars($a['module_title']) ?> → <?= htmlspecialchars($a['quiz_title']) ?>
                                </p>
                                <div style="font-size:2.2rem;font-weight:bold;color:#8b5cf6;">
                                    <?= $a['best_percentage'] ?>%
                                    <span style="font-size:1rem;color:<?= $a['passed']?'#10b981':'#ef4444' ?>;">
                                        (<?= $a['attempt_count'] ?>/<?= $MAX_ATTEMPTS ?> attempts)
                                    </span>
                                </div>
                            </div>
                            <div>
                                <?php if($a['attempt_count'] < $MAX_ATTEMPTS): ?>
                                    <a href="take-quiz.php?module_id=<?= $a['module_id'] ?>" class="btn" style="background:#f59e0b;color:black;padding:14px 30px;">
                                        Attempt <?= $a['attempt_count']+1 ?> of <?= $MAX_ATTEMPTS ?>
                                    </a>
                                <?php else: ?>
                                    <a href="review-quiz.php?module_id=<?= $a['module_id'] ?>" class="btn" style="background:#8b5cf6;padding:14px 30px;">
                                        Review Answers
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <small style="color:#64748b;display:block;margin-top:10px;">
                            Last attempt: <?= date('d M Y H:i', strtotime($a['last_attempted_at'])) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    include 'includes/footer.php';
    exit();
}

// ===================================================================
// 2. TAKING A QUIZ
// ===================================================================
$quiz_stmt = $pdo->prepare("SELECT q.*, m.title AS module_title, c.title AS course_title FROM quizzes q JOIN modules m ON q.module_id = m.id JOIN courses c ON m.course_id = c.id WHERE q.module_id = ?");
$quiz_stmt->execute([$module_id]);
$quiz = $quiz_stmt->fetch();

if(!$quiz) { include 'includes/header.php'; echo '<div class="card text-center"><h2>Quiz not found</h2></div>'; include 'includes/footer.php'; exit(); }

// Count attempts
$cnt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
$cnt->execute([$user_id, $quiz['id']]);
$attempts_made = $cnt->fetchColumn();

if($attempts_made >= $MAX_ATTEMPTS) {
    header("Location: review-quiz.php?module_id=$module_id");
    exit();
}

$questions = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
$questions->execute([$quiz['id']]);
$questions = $questions->fetchAll();

if(empty($questions)) { include 'includes/header.php'; echo '<div class="card text-center"><h2>No questions</h2></div>'; include 'includes/footer.php'; exit(); }

// ✅ SHUFFLE QUESTIONS AND OPTIONS
// Shuffle questions
shuffle($questions);

// Shuffle options for each question and store original letters
foreach ($questions as &$q) {
    $options_array = explode(',', $q['options']);
    $correct_array = explode(',', $q['correct_option']);
    
    // Create array with letters and text
    $option_data = [];
    $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    for ($i = 0; $i < count($options_array); $i++) {
        $option_data[] = [
            'letter' => $letters[$i],
            'text' => trim($options_array[$i])
        ];
    }
    
    shuffle($option_data);
    $q['options_display'] = $option_data;
    
    // Keep correct options as letters for scoring
    $q['correct_options'] = array_map('trim', $correct_array);
}
unset($q);

// ===================================================================
// SUBMIT QUIZ
// ===================================================================
if($_POST) {
    $total_score = 0.0;
    
    // ✅ UPDATED SCORING LOGIC
    foreach($questions as $q) {
        $user_ans = $_POST['q'.$q['id']] ?? [];

        if (!is_array($user_ans)) {
            $user_ans = [$user_ans];
        }

        $correct_options = $q['correct_options'];
        $correct_count   = count($correct_options);
        $user_correct    = 0;

        foreach ($user_ans as $ans) {
            if (in_array(trim($ans), $correct_options)) {
                $user_correct++;
            }
        }

        // Multiple-select partial scoring
        if ($q['question_type'] === 'multiple' && $correct_count > 0) {
            $question_score = $user_correct / $correct_count;
        } else {
            // Single choice scoring
            $question_score = ($user_correct == $correct_count && count($user_ans) == $correct_count) ? 1 : 0;
        }

        $total_score += $question_score;
    }

    $total_questions = count($questions);
    $percentage = round(($total_score / $total_questions) * 100, 1);
    $passed = $percentage >= $quiz['passing_score'];

    // ✅ UPDATED SAVE ATTEMPTS
    $stmt = $pdo->prepare("
        INSERT INTO quiz_attempts 
        (user_id, quiz_id, score, total_questions, percentage, passed, attempted_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $user_id,
        $quiz['id'],
        $total_score,
        $total_questions,
        $percentage,
        $passed
    ]);

    // Save answers
    // === SAVE USER ANSWERS SAFELY ===
    foreach($_POST as $key => $value) {
        if(strpos($key, 'q') === 0) {
            $qid = (int)substr($key, 1);
            $answers = is_array($value) ? $value : [$value];
            foreach($answers as $ans) {
                $ans = trim($ans);
                if($ans !== '') {
                    $pdo->prepare("INSERT IGNORE INTO user_answers (user_id, question_id, answer, attempt_at) 
                                   VALUES (?,?,?,NOW())")
                        ->execute([$user_id, $qid, $ans]);
                }
            }
        }
    }

    // Mark module complete if passed
    if($passed) {
        $pdo->prepare("INSERT INTO user_module_progress (user_id,module_id,completed,completed_at) VALUES (?,?,1,NOW()) ON DUPLICATE KEY UPDATE completed=1")
            ->execute([$user_id, $module_id]);
    }

    // FINAL CHECK: Was this the 3rd attempt?
    $final = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
    $final->execute([$user_id, $quiz['id']]);
    if($final->fetchColumn() >= $MAX_ATTEMPTS) {
        header("Location: review-quiz.php?module_id=$module_id");
        exit();
    }

    $_SESSION['quiz_result'] = [
        'score' => $total_score,
        'total' => $total_questions,
        'percentage' => $percentage,
        'passed' => $passed,
        'quiz_title' => $quiz['title'],
        'passing_score' => $quiz['passing_score']
    ];
    header("Location: take-quiz.php?module_id=$module_id&result=1");
    exit();
}

// Show result - FIXED: Added proper result display
if(isset($_GET['result'])) {
    $r = $_SESSION['quiz_result'] ?? null; 
    if(!$r) {
        header("Location: take-quiz.php?module_id=$module_id");
        exit();
    }
    include 'includes/header.php';
    ?>
    <style>
    .result-card {
        max-width: 600px;
        margin: 60px auto;
        padding: 40px;
        background: #dce5fa;
        color: white;
        border-radius: 20px;
        text-align: center;
    }
    .result-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    .score-display {
        font-size: 3rem;
        font-weight: bold;
        margin: 20px 0;
    }
    .passed {
        color: #10b981;
    }
    .failed {
        color: #ef4444;
    }
    .btn-container {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    </style>
    
    <div class="result-card">
        <div class="result-icon">
            <?= $r['passed'] ? '✅' : '❌' ?>
        </div>
        <h2 style="color:#8b5cf6;">Quiz: <?= htmlspecialchars($r['quiz_title']) ?></h2>
        
        <div class="score-display <?= $r['passed'] ? 'passed' : 'failed' ?>">
            <?= $r['percentage'] ?>%
        </div>
        
        <p style="font-size:1.2rem;color:#94a3b8;">
            You scored <?= $r['score'] ?> out of <?= $r['total'] ?> questions
            <br>
            Passing score: <?= $r['passing_score'] ?>%
        </p>
        
        <div style="background:#1e293b;padding:20px;border-radius:12px;margin:25px 0;">
            <p style="font-size:1.3rem;margin:0;color:<?= $r['passed']?'#10b981':'#ef4444' ?>;">
                <?= $r['passed'] ? '🎉 Congratulations! You passed!' : '📚 Keep studying! You can try again.' ?>
            </p>
        </div>
        
        <div class="btn-container">
            <?php if($attempts_made < $MAX_ATTEMPTS - 1): ?>
                <a href="take-quiz.php?module_id=<?= $module_id ?>" class="btn" style="background:#8b5cf6;padding:14px 30px;">
                    Try Again (<?= $attempts_made + 1 ?>/<?= $MAX_ATTEMPTS ?>)
                </a>
            <?php endif; ?>
            <a href="course.php" class="btn" style="background:#06b6d4;padding:14px 30px;">
                Back to Courses
            </a>
            <a href="take-quiz.php?module_id=0" class="btn" style="background:#f59e0b;color:black;padding:14px 30px;">
                My Results
            </a>
        </div>
    </div>
    <?php
    unset($_SESSION['quiz_result']);
    include 'includes/footer.php';
    exit();
}

include 'includes/header.php';
?>

<style>
/* Improved CSS for option layout */
.quiz-option-label {
    display: flex !important;
    align-items: center !important;
    gap: 15px !important;
    padding: 16px !important;
    background: #334155 !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    margin-bottom: 0 !important;
    transition: background 0.2s;
}

.quiz-option-label:hover {
    background: #475569 !important;
}

.quiz-option-label input[type="radio"],
.quiz-option-label input[type="checkbox"] {
    margin: 0 !important;
    flex-shrink: 0 !important;
    width: 20px !important;
    height: 20px !important;
}

.quiz-option-label .option-text {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    flex: 1 !important;
    line-height: 1.5 !important;
    color: white;
}

.option-letter {
    font-weight: bold;
    color: #8b5cf6;
    min-width: 25px;
}

/* Override any inline styles that might break layout */
div[style*="display:grid;gap:14px;"] > * {
    margin-bottom: 0 !important;
}
</style>

<div class="card" style="max-width:900px;margin:40px auto;padding:40px;background:#dce5fa;color:white;border-radius:20px;">
    <h2 style="text-align:center;color:#8b5cf6;">Quiz: <?= htmlspecialchars($quiz['title']) ?></h2>
    <p style="text-align:center;color:#94a3b8;">
        <?= htmlspecialchars($quiz['course_title']) ?> → <?= htmlspecialchars($quiz['module_title']) ?>
    </p>
    <p style="text-align:center;font-size:1.6rem;margin:30px 0;color:#f59e0b;font-weight:bold;">
        Attempt <strong><?= $attempts_made + 1 ?></strong> of <?= $MAX_ATTEMPTS ?> | Passing: <?= $quiz['passing_score'] ?>%
    </p>

    <form method="POST">
        <?php foreach($questions as $i => $q): ?>
            <div style="background:#1e293b;padding:30px;margin:25px 0;border-radius:16px;border-left:6px solid #8b5cf6;">
                <p style="font-size:1.5rem;margin-bottom:20px;">
                    <strong>Q<?= $i+1 ?>.</strong> <?= htmlspecialchars($q['question_text']) ?>
                </p>
                <?php if($q['question_type'] === 'multiple'): ?>
                    <p style="color:#10b981;background:#064e3b;padding:10px;border-radius:8px;margin-bottom:15px;font-weight:bold;">
                        Select all that apply — Partial credit!
                    </p>
                <?php endif; ?>
                <div style="display:grid;gap:14px;">
                    <?php foreach($q['options_display'] as $option): ?>
                        <label class="quiz-option-label">
                            <input 
                                type="<?= $q['question_type']==='multiple' ? 'checkbox' : 'radio' ?>"
                                name="q<?= $q['id'] ?><?= $q['question_type']==='multiple'?'[]':'' ?>"
                                value="<?= htmlspecialchars($option['letter']) ?>"
                                <?= $q['question_type']==='radio'?'required':'' ?>
                            >
                            <span class="option-text">
                                <span class="option-letter"><?= $option['letter'] ?>)</span>
                                <?= htmlspecialchars($option['text']) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" style="width:100%;padding:22px;background:#8b5cf6;color:white;border:none;border-radius:16px;font-size:1.8rem;font-weight:bold;margin-top:40px;cursor:pointer;">
            Submit Quiz (Attempt <?= $attempts_made + 1 ?>)
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
