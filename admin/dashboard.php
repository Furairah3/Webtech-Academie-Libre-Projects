<?php require_once 'auth.php'; ?>
<?php 
require_once '../config/db.php';

// Get stats
$total_courses   = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_modules   = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
$total_lectures  = $pdo->query("SELECT COUNT(*) FROM lectures")->fetchColumn();
$total_students  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_submitted = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();

include 'includes/admin-header.php';
?>

<div class="container" style="padding:40px 0;">
    <h1 style="color:#f59e0b; font-size:3rem; text-align:center; margin-bottom:40px;">
        Welcome Back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
    </h1>

    <!-- Stats Cards -->
    <div class="grid" style="margin-bottom:50px;">
        <div class="card text-center" style="background:#1e40af;">
            <h2 style="font-size:4rem;color:#06b6d4;"><?php echo $total_courses; ?></h2>
            <p>Total Courses</p>
        </div>
        <div class="card text-center" style="background:#7c3aed;">
            <h2 style="font-size:4rem;color:#06b6d4;"><?php echo $total_modules; ?></h2>
            <p>Modules</p>
        </div>
        <div class="card text-center" style="background:#06b6d4;">
            <h2 style="font-size:4rem;color:#06b6d4;"><?php echo $total_lectures; ?></h2>
            <p>Lectures</p>
        </div>
        <div class="card text-center" style="background:#dc2626;">
            <h2 style="font-size:4rem;color:#06b6d4;"><?php echo $total_students; ?></h2>
            <p>Students</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 style="color:#f59e0b; text-align:center; margin:50px 0 30px;">Quick Actions</h2>
    <div class="grid">
        <a href="add_course.php" class="card text-center" style="background:#1e40af;padding:40px;">
            <h3>Add New Course</h3>
        </a>
        <a href="add_module.php" class="card text-center" style="background:#7c3aed;padding:40px;">
            <h3>Add Module</h3>
        </a>
        <a href="add_lecture.php" class="card text-center" style="background:#06b6d4;padding:40px;">
            <h3>Add Lectures</h3>
        </a>
        <a href="add_quiz.php" class="card text-center" style="background:#8b5cf6;padding:40px;">
            <h3>Create Quiz</h3>
        </a>
        <a href="post-assignment.php" class="card text-center" style="background:#f59e0b;padding:padding:40px;">
            <h3>Post Assignment</h3>
        </a>
        <a href="view_assignments.php" class="card text-center" style="background:#dc2626;padding:40px;">
            <h3>View Submissions</h3>
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>