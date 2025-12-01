<?php 
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$module_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get module + course
$stmt = $pdo->prepare("SELECT m.*, c.title as course_title FROM modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ?");
$stmt->execute([$module_id]);
$module = $stmt->fetch();
if(!$module) die("Module not found");

// Get all lectures
$lectures_stmt = $pdo->prepare("SELECT * FROM lectures WHERE module_id = ? ORDER BY lecture_order");
$lectures_stmt->execute([$module_id]);
$lectures = $lectures_stmt->fetchAll();

if(empty($lectures)) {
    die('<div class="card text-center" style="padding:80px;"><h2>No lectures yet</h2></div>');
}

// === FIND CURRENT LECTURE (smart unlock) ===
$current_index = 0;
$unlocked_up_to = -1;

foreach($lectures as $i => $lec) {
    $prog = $pdo->prepare("SELECT completed FROM user_progress WHERE user_id = ? AND lecture_id = ?");
    $prog->execute([$user_id, $lec['id']]);
    $done = $prog->fetch();
    if($done && $done['completed'] == 1) {
        $unlocked_up = $i;
    } else {
        break;
    }
}

if(isset($_GET['lec']) && is_numeric($_GET['lec'])) {
    $req = (int)$_GET['lec'];
    if($req >= 0 && $req <= $unlocked_up + 1 && $req < count($lectures)) {
        $current_index = $req;
    }
}

$current_lecture = $lectures[$current_index];

// Is current lecture completed?
$check = $pdo->prepare("SELECT completed FROM user_progress WHERE user_id = ? AND lecture_id = ?");
$check->execute([$user_id, $current_lecture['id']]);
$is_completed = $check->fetchColumn() == 1;

// Are ALL lectures done?
$stats_query = $pdo->prepare("
    SELECT COUNT(*) as total,
           SUM(CASE WHEN up.completed = 1 THEN 1 ELSE 0 END) as done_count
    FROM lectures l
    LEFT JOIN user_progress up ON l.id = up.lecture_id AND up.user_id = ?
    WHERE l.module_id = ?
");
$stats_query->execute([$user_id, $module_id]);
$stats = $stats_query->fetch();
$all_lectures_done = ($stats['done_count'] ?? 0) == $stats['total'] && $stats['total'] > 0;

include 'includes/header.php';
?>

<h1 style="color:#06b6d4;">
    <a href="course.php" style="color:#64748b;font-size:0.9em;">Courses</a> / 
    <?php echo htmlspecialchars($module['course_title']); ?>
</h1>
<h2 style="margin:10px 0 30px;">Module: <?php echo htmlspecialchars($module['title']); ?></h2>

<!-- PROGRESS BAR -->
<div style="background:#1e293b;padding:20px;border-radius:16px;margin-bottom:30px;">
    <p style="color:#94a3b8;margin-bottom:10px;">
        <strong>Progress: <?php echo $current_index + 1; ?> of <?php echo count($lectures); ?> lectures</strong>
    </p>
    <div style="height:12px;background:#334155;border-radius:8px;overflow:hidden;">
        <div style="width:<?php echo (($current_index + 1) / count($lectures) * 100); ?>%;background:#06b6d4;height:100%;transition:0.5s;"></div>
    </div>
</div>

<!-- CURRENT VIDEO -->
<div class="card" style="background:#dce5fa;padding:40px;border-radius:20px;">
    <h3 style="color:#06b6d4;font-size:1.8rem;margin-bottom:20px;">
        Lecture <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($current_lecture['title']); ?>
        <?php if($is_completed): ?>
            <span style="color:#10b981;float:right;font-weight:bold;">Completed</span>
        <?php endif; ?>
    </h3>

    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:16px;background:#000;margin:20px 0;">
        <iframe src="https://www.youtube.com/embed/<?php echo extractYouTubeID($current_lecture['youtube_url']); ?>"
                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                allowfullscreen></iframe>
    </div>

    <?php if(!$is_completed): ?>
        <form action="mark-complete.php" method="POST" style="text-align:center;margin:30px 0;">
            <input type="hidden" name="lecture_id" value="<?php echo $current_lecture['id']; ?>">
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <button type="submit" class="btn" style="background:#10b981;padding:18px 60px;font-size:1.5rem;">
                Mark as Complete & Continue
            </button>
        </form>
    <?php else: ?>
        <p style="text-align:center;color:#10b981;font-size:1.4rem;margin:30px 0;">Lecture Completed!</p>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;margin-top:40px;">
        <?php if($current_index > 0): ?>
            <a href="?id=<?php echo $module_id; ?>&lec=<?php echo $current_index - 1; ?>" 
               class="btn" style="background:#64748b;padding:14px 30px;">Previous</a>
        <?php else: ?><div></div><?php endif; ?>

        <?php if($current_index < count($lectures) - 1): ?>
            <a href="?id=<?php echo $module_id; ?>&lec=<?php echo $current_index + 1; ?>" 
               class="btn" style="background:#06b6d4;padding:14px 30px;">Next Lecture</a>
        <?php else: ?><div></div><?php endif; ?>
    </div>
</div>

<!-- ASSIGNMENT SECTION -->
<?php 
$assignment = $pdo->prepare("SELECT ma.*, COALESCE(u.username, 'Admin') as admin_name FROM module_assignments ma LEFT JOIN users u ON ma.created_by = u.id WHERE ma.module_id = ?");
$assignment->execute([$module_id]);
$assign = $assignment->fetch();

$submission = $pdo->prepare("SELECT a.*, COALESCE(g.username, 'Not graded yet') as grader_name FROM assignments a LEFT JOIN users g ON a.graded_by = g.id WHERE a.user_id = ? AND a.module_id = ?");
$submission->execute([$user_id, $module_id]);
$sub = $submission->fetch();
?>

<?php if($assign): ?>
<div class="card" style="background:#1e293b;border:3px solid #f59e0b;margin-top:50px;padding:35px;border-radius:20px;">
    <h3 style="color:#f59e0b;font-size:1.9rem;">Assignment: <?= htmlspecialchars($assign['title']) ?></h3>
    <p style="color:#94a3b8;margin:15px 0;">Posted by <strong>@<?= htmlspecialchars($assign['admin_name']) ?></strong></p>
    
    <?php if($assign['description']): ?>
        <div style="background:#dce5fa;padding:25px;border-radius:16px;margin:20px 0;line-height:1.8;color:#e2e8f0;">
            <?= nl2br(htmlspecialchars($assign['description'])) ?>
        </div>
    <?php endif; ?>

    <!-- ADMIN'S ASSIGNMENT FILE – NOW OPENS IN BROWSER -->
    <?php if($assign['file_path']): ?>
        <div style="margin:25px 0;">
            <a href="<?= $assign['file_path'] ?>" target="_blank"
               style="background:#06b6d4;color:white;padding:14px 32px;border-radius:12px;text-decoration:none;font-weight:bold;margin-right:15px;">
               View Assignment File
            </a>
            <a href="<?= $assign['file_path'] ?>" download
               style="color:#94a3b8;text-decoration:underline;font-size:0.95rem;">
               Download
            </a>
        </div>
    <?php endif; ?>

    <hr style="border-color:#334155;margin:40px 0;">

    <h4 style="color:#8b5cf6;">Your Submission</h4>
    <?php if($sub): ?>
        <div style="background:#16653422;padding:25px;border-radius:12px;">
            <p style="color:#10b981;font-weight:bold;">
                Submitted on <?= date('d M Y \a\t H:i', strtotime($sub['submitted_at'])) ?>
            </p>

            <!-- STUDENT'S FILE – NOW OPENS IN BROWSER -->
            <div style="margin:15px 0;">
                <a href="<?= $sub['file_path'] ?>" target="_blank"
                   style="background:#64748b;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:bold;margin-right:12px;">
                   View My Submission
                </a>
                <a href="<?= $sub['file_path'] ?>" download style="color:#e2e8f0;text-decoration:underline;">
                   Download
                </a>
            </div>

            <?php if($sub['grade'] !== null): ?>
                <div style="margin-top:25px;padding:20px;background:#166534;color:#86efac;border-radius:12px;">
                    <h4>Grade: <?= $sub['grade'] ?>/100</h4>
                    <p>Graded by <strong><?= htmlspecialchars($sub['grader_name']) ?></strong></p>
                    <p><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($sub['feedback'])) ?></p>
                    <?php if($sub['corrected_file']): ?>
                        <div style="margin-top:15px;">
                            <a href="<?= $sub['corrected_file'] ?>" target="_blank"
                               style="background:#10b981;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;">
                               View Corrected File
                            </a>
                            <a href="<?= $sub['corrected_file'] ?>" download style="corrected_file" style="color:#86efac;margin-left:12px;text-decoration:underline;">
                               Download
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p style="color:#f59e0b;margin-top:15px;">Waiting for grading...</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form action="upload-assignment.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="module_id" value="<?= (int)$module_id ?>">
            <p><strong>Upload your answer (PDF, DOCX, ZIP):</strong></p>
            <input type="file" name="assignment_file" required accept=".pdf,.doc,.docx,.zip" style="margin:15px 0;padding:10px;background:#334155;border-radius:8px;">
            <button type="submit" class="btn" style="background:#f59e0b;padding:16px 40px;font-size:1.4rem;">
                Submit Assignment
            </button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- QUIZ SECTION (unchanged) -->
<?php if($all_lectures_done && $module['quiz_id']): 
    $attempt = $pdo->prepare("SELECT score, total_questions, passed FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
    $attempt->execute([$user_id, $module['quiz_id']]);
    $done = $attempt->fetch();
?>
<div class="card" style="background:#1e1b4b;border:3px solid #8b5cf6;margin-top:50px;padding:35px;">
    <h3>Module Quiz Available!</h3>
    <?php if($done): ?>
        <p>Your score: <strong><?php echo $done['score']; ?>/<?php echo $done['total_questions']; ?></strong>
           <?php echo $done['passed'] ? '<span style="color:#10b981;">(PASSED)</span>' : '<span style="color:#ef4444;">(Try Again)</span>'; ?>
        </p>
    <?php else: ?>
        <a href="take-quiz.php?module_id=<?php echo $module_id; ?>" 
           class="btn" style="background:#8b5cf6;padding:16px 40px;font-size:1.3rem;">
            Take Quiz Now
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<?php
function extractYouTubeID($url) {
    preg_match('/(?:youtube\.com\/(?:.*v=|embed\/)|youtu\.be\/)([^&?]+)/', $url, $matches);
    return $matches[1] ?? 'dQw4w9WgXcQ';
}
?>
