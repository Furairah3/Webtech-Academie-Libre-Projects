<?php 
require_once 'config/base.php';
require_once 'includes/functions.php';
if(!is_logged_in() || is_admin()) redirect('login.php');
require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

// === 100% REAL & ACCURATE STATS ===
$stats = [
    'courses_started' => 0,
    'modules_completed' => 0,
    'total_modules' => 0,
    'lectures_completed' => 0,
    'total_lectures' => 0,
    'assignments_submitted' => 0,
    'quizzes_taken' => 0
];

try {
    // Courses Started = any course with at least one lecture watched
    $stats['courses_started'] = $pdo->query("
        SELECT COUNT(DISTINCT c.id) 
        FROM courses c
        JOIN modules m ON m.course_id = c.id
        JOIN lectures l ON l.module_id = m.id
        JOIN user_progress up ON up.lecture_id = l.id
        WHERE up.user_id = $user_id AND up.completed = 1
    ")->fetchColumn();

    // Modules completed
    $stats['modules_completed'] = $pdo->query("
        SELECT COUNT(*) FROM user_module_progress 
        WHERE user_id = $user_id AND completed = 1
    ")->fetchColumn();

    // Total modules in started courses
    $stats['total_modules'] = $pdo->query("
        SELECT COUNT(m.id) 
        FROM modules m
        JOIN courses c ON m.course_id = c.id
        WHERE c.id IN (
            SELECT DISTINCT c2.id 
            FROM courses c2
            JOIN modules m2 ON m2.course_id = c2.id
            JOIN lectures l ON l.module_id = m2.id
            JOIN user_progress up ON up.lecture_id = l.id
            WHERE up.user_id = $user_id AND up.completed = 1
        )
    ")->fetchColumn();

    // Lectures completed + total
    $stmt = $pdo->query("
        SELECT 
            COUNT(up.lecture_id) as completed,
            (SELECT COUNT(l.id) FROM lectures l 
             JOIN modules m ON l.module_id = m.id 
             JOIN courses c ON m.course_id = c.id
             WHERE c.id IN (
                SELECT DISTINCT c2.id FROM courses c2
                JOIN modules m2 ON m2.course_id = c2.id
                JOIN lectures l2 ON l2.module_id = m2.id
                JOIN user_progress up2 ON up2.lecture_id = l2.id
                WHERE up2.user_id = $user_id AND up2.completed = 1
             )) as total
        FROM user_progress up
        WHERE up.user_id = $user_id AND up.completed = 1
    ");
    $lec = $stmt->fetch();
    $stats['lectures_completed'] = $lec['completed'] ?? 0;
    $stats['total_lectures'] = $lec['total'] ?? 0;

    // Assignments & Quizzes
    $stats['assignments_submitted'] = $pdo->query("SELECT COUNT(*) FROM assignments WHERE user_id = $user_id")->fetchColumn();
    $stats['quizzes_taken'] = $pdo->query("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = $user_id")->fetchColumn();

} catch(Exception $e) {}

include 'includes/header.php';
?>

<!-- Main Content -->
<main >
    <div class="profile-container" style="max-width:1200px; margin:0 auto;">
        <!-- Welcome Section -->
        <div style="background:white;border-radius:15px;padding:30px;margin-bottom:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="color:#1e293b;font-size:2rem;margin-bottom:10px;">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
            <p style="color:#64748b;font-size:1.1rem;margin-bottom:25px;">Ready to continue your learning journey?</p>
            
            <!-- Main Action Button -->
            <div style="text-align:center;margin-top:20px;">
                <a href="<?= BASE_URL ?>/course.php" style="background:#06b6d4;color:white;text-decoration:none;padding:15px 40px;border-radius:8px;font-weight:bold;font-size:1.1rem;display:inline-block;">
                    Browse All Courses
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
            <div style="text-align:center;padding:20px;background:#1e40af;border-radius:10px;color:white;">
                <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['courses_started']; ?></div>
                <div>Courses Started</div>
            </div>
            <div style="text-align:center;padding:20px;background:#7c3aed;border-radius:10px;color:white;">
                <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['modules_completed']; ?></div>
                <div>Modules Completed</div>
            </div>
            <div style="text-align:center;padding:20px;background:#06b6d4;border-radius:10px;color:white;">
                <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['lectures_completed']; ?></div>
                <div>Lectures Watched</div>
            </div>
            <div style="text-align:center;padding:20px;background:#dc2626;border-radius:10px;color:white;">
                <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['assignments_submitted']; ?></div>
                <div>Assignments Submitted</div>
            </div>
            <div style="text-align:center;padding:20px;background:#f59e0b;border-radius:10px;color:white;">
                <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['quizzes_taken']; ?></div>
                <div>Quizzes Taken</div>
            </div>
        </div>

        <!-- Progress Overview -->
        <div style="background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:30px;">
            <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Progress Overview</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">
                <!-- Modules Progress -->
                <div style="background:#f8fafc;padding:25px;border-radius:10px;">
                    <h3 style="color:#1e293b;margin-bottom:15px;font-size:1.2rem;">📦 Modules Progress</h3>
                    <div style="font-size:2rem;font-weight:bold;color:#06b6d4;text-align:center;">
                        <?php echo $stats['modules_completed']; ?>/<?php echo $stats['total_modules']; ?>
                    </div>
                    <div style="background:#e2e8f0;border-radius:10px;height:12px;margin-top:15px;">
                        <div style="background:#06b6d4;height:100%;border-radius:10px;width:<?php echo $stats['total_modules'] > 0 ? ($stats['modules_completed'] / $stats['total_modules']) * 100 : 0; ?>%;"></div>
                    </div>
                    <div style="text-align:center;color:#64748b;margin-top:10px;">
                        <?php echo $stats['total_modules'] > 0 ? round(($stats['modules_completed'] / $stats['total_modules']) * 100) : 0; ?>% Complete
                    </div>
                </div>
                
                <!-- Lectures Progress -->
                <div style="background:#f8fafc;padding:25px;border-radius:10px;">
                    <h3 style="color:#1e293b;margin-bottom:15px;font-size:1.2rem;">📖 Lectures Progress</h3>
                    <div style="font-size:2rem;font-weight:bold;color:#7c3aed;text-align:center;">
                        <?php echo $stats['lectures_completed']; ?>/<?php echo $stats['total_lectures']; ?>
                    </div>
                    <div style="background:#e2e8f0;border-radius:10px;height:12px;margin-top:15px;">
                        <div style="background:#7c3aed;height:100%;border-radius:10px;width:<?php echo $stats['total_lectures'] > 0 ? ($stats['lectures_completed'] / $stats['total_lectures']) * 100 : 0; ?>%;"></div>
                    </div>
                    <div style="text-align:center;color:#64748b;margin-top:10px;">
                        <?php echo $stats['total_lectures'] > 0 ? round(($stats['lectures_completed'] / $stats['total_lectures']) * 100) : 0; ?>% Complete
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:30px;">
            <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Quick Actions</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                <a href="<?= BASE_URL ?>/course.php" style="background:#1e40af;color:white;text-decoration:none;padding:20px;border-radius:8px;text-align:center;font-weight:bold;display:flex;flex-direction:column;align-items:center;gap:10px;">
                    <span style="font-size:1.5rem;">📚</span>
                    View Courses
                </a>
                <a href="<?= BASE_URL ?>/take-quiz.php" style="background:#7c3aed;color:white;text-decoration:none;padding:20px;border-radius:8px;text-align:center;font-weight:bold;display:flex;flex-direction:column;align-items:center;gap:10px;">
                    <span style="font-size:1.5rem;">🧠</span>
                    Take Quiz
                </a>
                <a href="<?= BASE_URL ?>/upload-assignment.php" style="background:#06b6d4;color:white;text-decoration:none;padding:20px;border-radius:8px;text-align:center;font-weight:bold;display:flex;flex-direction:column;align-items:center;gap:10px;">
                    <span style="font-size:1.5rem;">📝</span>
                    Submit Assignment
                </a>
                <a href="<?= BASE_URL ?>/profile.php" style="background:#f59e0b;color:black;text-decoration:none;padding:20px;border-radius:8px;text-align:center;font-weight:bold;display:flex;flex-direction:column;align-items:center;gap:10px;">
                    <span style="font-size:1.5rem;">👤</span>
                    View Profile
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div style="background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Recent Activity</h2>
            <div style="display:flex;flex-direction:column;gap:15px;">
                <?php
                try {
                    $stmt = $pdo->prepare("
                        (SELECT 'lecture' as type, l.title as item, up.completed_at as date FROM user_progress up JOIN lectures l ON up.lecture_id = l.id WHERE up.user_id = ? AND up.completed = 1 ORDER BY up.completed_at DESC LIMIT 3)
                        UNION ALL
                        (SELECT 'assignment' as type, 'Assignment Submitted' as item, a.submitted_at as date FROM assignments a WHERE a.user_id = ? ORDER BY a.submitted_at DESC LIMIT 2)
                        UNION ALL
                        (SELECT 'quiz' as type, 'Quiz Completed' as item, qa.attempted_at as date FROM quiz_attempts qa WHERE qa.user_id = ? ORDER BY qa.attempted_at DESC LIMIT 2)
                        ORDER BY date DESC LIMIT 7
                    ");
                    $stmt->execute([$user_id, $user_id, $user_id]);
                    $activities = $stmt->fetchAll();

                    if($activities) {
                        foreach($activities as $a) {
                            $icon = $a['type'] === 'lecture' ? '📹' : ($a['type'] === 'assignment' ? '📝' : '🧠');
                            $type = $a['type'] === 'lecture' ? 'Lecture' : ($a['type'] === 'assignment' ? 'Assignment' : 'Quiz');
                            echo "<div style='display:flex;align-items:center;gap:15px;padding:15px;background:#f8fafc;border-radius:10px;'>
                                    <div style='font-size:1.5rem;'>$icon</div>
                                    <div style='flex:1;'>
                                        <div style='font-weight:bold;color:#1e293b;'>{$a['item']}</div>
                                        <div style='color:#64748b;font-size:0.9rem;'>$type • ".date('M j, Y g:i A', strtotime($a['date']))."</div>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<div style='text-align:center;padding:30px;color:#64748b;'>
                                <div style='font-size:3rem;margin-bottom:15px;'>📚</div>
                                <div>No recent activity yet. Start learning to see your progress here!</div>
                              </div>";
                    }
                } catch(Exception $e) {
                    echo "<div style='text-align:center;padding:30px;color:#64748b;'>
                            <div>Start your learning journey to see activity here!</div>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>
</main>

<style>
    .profile-container a {
        transition: all 0.3s ease;
    }
    
    .profile-container a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<?php include 'includes/footer.php'; ?>
