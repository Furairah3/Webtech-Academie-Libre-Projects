<?php
require_once 'includes/functions.php';
require_once 'config/base.php';

if(!is_logged_in()) redirect('login.php');
include 'includes/header.php';
require_once 'config/db.php';
?>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/courses.css?v=<?= time(); ?>">


<div class="courses-container">
    <h1 class="courses-title">Available Courses</h1>
    <p class="courses-subtitle">Choose a course to start your learning journey</p>

    <div class="courses-grid">
        <?php
        $stmt = $pdo->query("SELECT id, title, description, thumbnail FROM courses ORDER BY id");
        if($stmt->rowCount() == 0):
        ?>
            <div class="empty-state">
                <h3>No courses available yet</h3>
                <p>The admin will add courses soon. Please check back later!</p>
            </div>
        <?php
        else:
            while($course = $stmt->fetch()):
        ?>
            <div class="course-card">
                <div class="course-image <?php echo $course['thumbnail'] ? '' : 'no-image'; ?>"
                     style="<?php echo $course['thumbnail'] ? 'background-image:url(<?= ASSETS_URL ?>/uploads/'.$course['thumbnail'].');' : ''; ?>">
                </div>
                <div class="course-content">
                    <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="course-description">
                        <?php echo htmlspecialchars($course['description'] ?: 'No description available'); ?>
                    </p>
                    <a href="<?= BASE_URL ?>/course-view.php?id=<?php echo $course['id']; ?>" class="course-btn">
                        Enter Course
                    </a>
                </div>
            </div>
        <?php
            endwhile;
        endif;
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
