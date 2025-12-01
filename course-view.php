<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$course_id = (int)$_GET['id'] ?? 0;
if($course_id == 0) die("Invalid course");

$user_id = $_SESSION['user_id'];

// Get course info
$stmt = $pdo->prepare("SELECT id, title, description FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
if(!$course) die("Course not found");

// Get modules + quiz + assignment info
$modules_stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.title,
        m.module_order,
        q.id          AS quiz_id,
        q.title       AS quiz_title,
        ma.id         AS assignment_id,
        ma.title      AS assignment_title,
        ma.posted_at  AS assignment_posted_at
    FROM modules m
    LEFT JOIN quizzes q            ON q.module_id = m.id
    LEFT JOIN module_assignments ma ON ma.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.module_order
");
$modules_stmt->execute([$course_id]);
$modules = $modules_stmt->fetchAll();

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/course-view.css?v=<?php echo time(); ?>">

<div class="course-view-container">
    <div class="course-view-header">
        <h1 class="course-view-title"><?php echo htmlspecialchars($course['title']); ?></h1>
        <p class="course-view-desc"><?php echo htmlspecialchars($course['description'] ?: 'No description'); ?></p>
    </div>

    <div class="course-view-layout">
        <!-- Sidebar -->
        <aside class="course-view-sidebar">
            <div class="card">
                <h3>Quick Actions</h3>
                <a href="course.php" style="display:block;margin:15px 0;color:#06b6d4;font-weight:600;">
                    ← Back to All Courses
                </a>
            </div>
            <div class="card">
                <h3>Course Progress</h3>
                <p style="margin:15px 0;color:#64748b;">
                    <?php echo count($modules); ?> modules<br>
                    <?php 
                    $completed = $pdo->prepare("SELECT COUNT(*) FROM user_module_progress 
                                               WHERE user_id = ? AND completed = 1 
                                               AND module_id IN (SELECT id FROM modules WHERE course_id = ?)");
                    $completed->execute([$user_id, $course_id]);
                    echo $completed->fetchColumn() . " completed";
                    ?>
                </p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="course-view-main">
            <?php if(empty($modules)): ?>
                <div class="empty-state">
                    <h3>No modules yet</h3>
                    <p>The instructor will add content soon!</p>
                </div>
            <?php else: ?>
                <div class="module-grid">
                    <?php 
                    $prev_completed = true; // first module always unlocked
                    foreach($modules as $i => $module): 
                        // Check if this module is completed
                        $prog = $pdo->prepare("SELECT completed FROM user_module_progress WHERE user_id = ? AND module_id = ?");
                        $prog->execute([$user_id, $module['id']]);
                        $is_completed = ($prog->fetchColumn() == 1);

                        $unlocked = $i === 0 || $prev_completed;
                        $prev_completed = $is_completed; // for next module
                    ?>
                        <div class="module-card <?php echo !$unlocked ? 'locked' : ''; ?>">
                            <div class="module-card-header">
                                <div class="module-number"><?php echo $i + 1; ?></div>
                                <h3 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h3>
                            </div>

                            <div class="module-card-body">
                                <?php if($is_completed): ?>
                                    <span class="module-status status-completed">Completed</span>
                                <?php endif; ?>

                                <!-- QUIZ BUTTON – only if quiz exists AND module is unlocked -->
                                <?php if($module['quiz_id'] && $unlocked): ?>
                                    <a href="take-quiz.php?module_id=<?php echo $module['id']; ?>" 
                                       class="module-status status-quiz">
                                       Quiz Available
                                    </a>
                                <?php endif; ?>

                                <!-- ASSIGNMENT BUTTON – only if assignment exists AND module is unlocked -->
                                <?php if($module['assignment_id'] && $unlocked): ?>
                                    <a href="upload-assignment.php?module_id=<?php echo $module['id']; ?>" 
                                       class="module-status status-assignment">
                                       Assignment Posted
                                    </a>
                                <?php endif; ?>

                                <a href="module-view.php?id=<?php echo $module['id']; ?>" 
                                   class="module-btn <?php echo !$unlocked ? 'disabled' : ''; ?>">
                                    <?php echo $is_completed ? 'Review Module' : ($unlocked ? 'Start Module' : 'Locked'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
