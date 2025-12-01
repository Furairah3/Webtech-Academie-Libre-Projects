<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user) { session_destroy(); redirect('index.php'); }

$is_admin_user = ($user['role'] === 'admin');

// CHOOSE HEADER AUTOMATICALLY
if($is_admin_user) {
    include 'admin/includes/admin-header.php';
} else {
    include 'includes/header.php';
}
?><?php

$is_admin_user = ($user['role'] === 'admin');
// Handle form submission (PICTURE + PROFILE UPDATE)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $error = null;
    // === 1. PROFILE PICTURE UPLOAD ===
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
       
        if(in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            if($user['profile_pic'] && $user['profile_pic'] !== 'default.jpg' && file_exists("assets/uploads/".$user['profile_pic'])) {
                unlink("assets/uploads/".$user['profile_pic']);
            }
            $new_filename = "profile_{$user_id}.{$ext}";
            if(move_uploaded_file($file['tmp_name'], "assets/uploads/{$new_filename}")) {
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")
                    ->execute([$new_filename, $user_id]);
                $_SESSION['profile_pic'] = $new_filename;
                $user['profile_pic'] = $new_filename;
                $success = "Profile picture updated!";
            }
        } else {
            $error = "Only JPG, PNG, GIF, WebP allowed!";
        }
    }
    // === 2. TEXT FIELDS UPDATE ===
    if(isset($_POST['fname'])) {
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $new_pass = $_POST['new_password'] ?? '';
        // Username & email check
        if($user['username'] !== $username) {
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$username, $user_id]);
            if($check->fetch()) $error = "Username already taken!";
        }
        if($user['email'] !== $email) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if($check->fetch()) $error = "Email already registered!";
        }
        if(!$error) {
            $pass_sql = "";
            $pass_params = [];
            if(!empty($new_pass) && strlen($new_pass) >= 6) {
                $pass_sql = ", password = ?";
                $pass_params = [password_hash($new_pass, PASSWORD_DEFAULT)];
            }
            $sql = "UPDATE users SET fname=?, lname=?, username=?, email=?, phone=?, bio=? $pass_sql WHERE id=?";
            $params = array_merge([$fname, $lname, $username, $email, $phone, $bio], $pass_params, [$user_id]);
            $pdo->prepare($sql)->execute($params);
            $_SESSION['user_name'] = "$fname $lname";
            $success = "Profile updated successfully!";
           
            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
}
// Fetch data based on user role
if($is_admin_user) {
    // Admin stats
    $stats = [
        'total_courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
        'total_modules' => $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn(),
        'total_lectures' => $pdo->query("SELECT COUNT(*) FROM lectures")->fetchColumn(),
        'total_students' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
        'total_submitted' => $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn(),
        'total_quizzes' => $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn()
    ];
} else {
    // Student stats
    $stats = [
        'courses_completed' => 0,
        'modules_completed' => 0,
        'assignments_submitted' => 0,
        'quizzes_passed' => 0,
        'day_streak' => 0,
        'average_score' => 0
    ];
    try {
        $stats['courses_completed'] = $pdo->query("SELECT COUNT(DISTINCT c.id) FROM courses c JOIN modules m ON m.course_id=c.id JOIN user_module_progress ump ON ump.module_id=m.id WHERE ump.user_id=$user_id AND ump.completed=1")->fetchColumn();
        $stats['modules_completed'] = $pdo->query("SELECT COUNT(*) FROM user_module_progress WHERE user_id=$user_id AND completed=1")->fetchColumn();
        $stats['assignments_submitted'] = $pdo->query("SELECT COUNT(*) FROM assignments WHERE user_id=$user_id")->fetchColumn();
        if($pdo->query("SHOW COLUMNS FROM quiz_attempts LIKE 'passed'")->rowCount()) {
            $stats['quizzes_passed'] = $pdo->query("SELECT COUNT(*) FROM quiz_attempts WHERE user_id=$user_id AND passed=1")->fetchColumn();
        }
       
        // Get detailed subject progress with modules and lectures
        $detailed_progress_stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.title,
                COUNT(DISTINCT m.id) as total_modules,
                COUNT(DISTINCT CASE WHEN ump.completed = 1 THEN m.id END) as completed_modules,
                COUNT(DISTINCT l.id) as total_lectures,
                COUNT(DISTINCT CASE WHEN ulp.completed = 1 THEN l.id END) as completed_lectures
            FROM courses c
            LEFT JOIN modules m ON m.course_id = c.id
            LEFT JOIN user_module_progress ump ON ump.module_id = m.id AND ump.user_id = ?
            LEFT JOIN lectures l ON l.module_id = m.id
            LEFT JOIN user_lecture_progress ulp ON ulp.lecture_id = l.id AND ulp.user_id = ?
            GROUP BY c.id, c.title
        ");
        $detailed_progress_stmt->execute([$user_id, $user_id]);
        $detailed_subject_progress = $detailed_progress_stmt->fetchAll();
       
    } catch(Exception $e) {
        // Fallback to basic progress if detailed query fails
        $progress_stmt = $pdo->prepare("
            SELECT c.id, c.title,
                   COUNT(m.id) as total_modules,
                   COUNT(ump.module_id) as completed_modules
            FROM courses c
            LEFT JOIN modules m ON m.course_id = c.id
            LEFT JOIN user_module_progress ump ON ump.module_id = m.id AND ump.user_id = ? AND ump.completed = 1
            GROUP BY c.id, c.title
        ");
        $progress_stmt->execute([$user_id]);
        $detailed_subject_progress = $progress_stmt->fetchAll();
    }
}
?>
<div class="profile-container" style="max-width:1200px; margin:40px auto; padding:20px;">
    <!-- Success/Error Messages -->
    <?php if(isset($success)): ?>
        <div style="background:#10b981;color:white;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div style="background:#ef4444;color:white;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <!-- Learning Overview Section -->
    <div class="learning-overview" style="background:white;border-radius:15px;padding:30px;margin-bottom:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color:#1e293b;font-size:2rem;margin-bottom:25px;">Learning Overview</h1>
       
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:30px;">
            <!-- Profile Picture with Upload Functionality -->
            <div style="position:relative;">
                <img src="<?= ASSETS_URL ?>/uploads/<?= $user['profile_pic'] ?? 'default.jpg'; ?>?v=<?= time(); ?>"
                     alt="Profile"
                     style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid #06b6d4;">
                <!-- Change Picture Button -->
                <button onclick="document.getElementById('profile-pic-input').click()"
                        style="position:absolute;bottom:-5px;right:-5px;background:#06b6d4;color:white;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:12px;">
                    📷
                </button>
                <!-- Hidden File Input -->
                <form id="profile-pic-form" method="POST" enctype="multipart/form-data" style="display:none;">
                    <input type="file" id="profile-pic-input" name="profile_pic" accept="image/*" onchange="this.form.submit()">
                </form>
            </div>
            <div>
                <h2 style="color:#1e293b;font-size:1.5rem;margin:0;"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></h2>
                <p style="color:#64748b;margin:5px 0 0 0;">
                    <?php echo $is_admin_user ? 'Administrator' : 'Baccalaureat Program'; ?>
                    <?php if($is_admin_user): ?>
                        <span style="background:#f59e0b;color:black;padding:4px 12px;border-radius:50px;font-size:0.8rem;margin-left:10px;font-weight:bold;">ADMIN</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php if($is_admin_user): ?>
            <!-- Admin Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                <div style="text-align:center;padding:20px;background:#1e40af;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_courses']; ?></div>
                    <div>Total Courses</div>
                </div>
                <div style="text-align:center;padding:20px;background:#7c3aed;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_modules']; ?></div>
                    <div>Modules</div>
                </div>
                <div style="text-align:center;padding:20px;background:#06b6d4;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_lectures']; ?></div>
                    <div>Lectures</div>
                </div>
                <div style="text-align:center;padding:20px;background:#dc2626;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_students']; ?></div>
                    <div>Students</div>
                </div>
                <div style="text-align:center;padding:20px;background:#8b5cf6;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_quizzes']; ?></div>
                    <div>Quizzes</div>
                </div>
                <div style="text-align:center;padding:20px;background:#f59e0b;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['total_submitted']; ?></div>
                    <div>Submissions</div>
                </div>
            </div>
        <?php else: ?>
            <!-- Student Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                <div style="text-align:center;padding:20px;background:#1e40af;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['courses_completed']; ?></div>
                    <div>Courses Completed</div>
                </div>
                <div style="text-align:center;padding:20px;background:#7c3aed;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['modules_completed']; ?></div>
                    <div>Modules Completed</div>
                </div>
                <div style="text-align:center;padding:20px;background:#06b6d4;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['assignments_submitted']; ?></div>
                    <div>Assignments Submitted</div>
                </div>
                <div style="text-align:center;padding:20px;background:#dc2626;border-radius:10px;color:white;">
                    <div style="font-size:2.5rem;font-weight:bold;"><?php echo $stats['quizzes_passed']; ?></div>
                    <div>Quizzes Passed</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Edit Profile and Sign Out Buttons - Same Row, Equal Weight -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px;">
        <!-- Edit Profile Button -->
        <div style="background:white;border-radius:15px;padding:20px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;">
            <button onclick="toggleEditProfile()" style="background:#06b6d4;color:white;border:none;padding:15px 30px;border-radius:8px;cursor:pointer;font-weight:bold;width:100%;font-size:1rem;">
                Edit Profile
            </button>
        </div>
        <!-- Sign Out Button -->
        <div style="background:white;border-radius:15px;padding:20px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;">
            <a href="logout.php" style="background:#ef4444;color:white;text-decoration:none;padding:15px 30px;border-radius:8px;font-weight:bold;width:100%;display:block;font-size:1rem;">
                Sign Out
            </a>
        </div>
    </div>
    <!-- Edit Profile Section (Hidden by Default) -->
    <div id="editProfileSection" style="display:none;background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:30px;">
        <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Edit Profile</h2>
        <form method="POST" action="">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">First Name</label>
                    <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>"
                           style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">Last Name</label>
                    <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>"
                           style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
            </div>
           
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                           style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                           style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                       style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">Bio</label>
                <textarea name="bio" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;height:100px;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:bold;color:#374151;">New Password (leave blank to keep current)</label>
                <input type="password" name="new_password"
                       style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <div style="display:flex;gap:15px;">
                <button type="button" onclick="toggleEditProfile()"
                        style="background:#6b7280;color:white;border:none;padding:12px 30px;border-radius:8px;cursor:pointer;font-weight:bold;">
                    Cancel
                </button>
                <button type="submit"
                        style="background:#06b6d4;color:white;border:none;padding:12px 30px;border-radius:8px;cursor:pointer;font-weight:bold;">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    <!-- Main Content Area -->
    <div>
        <?php if($is_admin_user): ?>
            <!-- Admin Quick Actions -->
            <div style="background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Quick Actions</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                    <a href="admin/add_course.php" style="background:#1e40af;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        Add New Course
                    </a>
                    <a href="admin/add_module.php" style="background:#7c3aed;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        Add Module
                    </a>
                    <a href="admin/add_lecture.php" style="background:#06b6d4;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        Add Lectures
                    </a>
                    <a href="admin/add_quiz.php" style="background:#8b5cf6;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        Create Quiz
                    </a>
                    <a href="admin/post-assignment.php" style="background:#f59e0b;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        Post Assignment
                    </a>
                    <a href="admin/view_assignments.php" style="background:#dc2626;color:white;text-decoration:none;padding:15px;border-radius:8px;text-align:center;font-weight:bold;">
                        View Submissions
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Student Subject Progress - Full Width with Enhanced Tracking -->
            <div style="background:white;border-radius:15px;padding:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="color:#1e293b;margin-bottom:25px;font-size:1.5rem;">Subject Progress</h2>
                <div style="display:flex;flex-direction:column;gap:15px;">
                    <?php 
                    foreach($detailed_subject_progress as $subject):
                        $module_progress = $subject['total_modules'] > 0 ? ($subject['completed_modules'] / $subject['total_modules']) * 100 : 0;
                        $lecture_progress = isset($subject['total_lectures']) && $subject['total_lectures'] > 0 ? ($subject['completed_lectures'] / $subject['total_lectures']) * 100 : 0;
                        $overall_progress = max($module_progress, $lecture_progress);
                        $icons = ['∫', '⚛', '📚', '💹', '🧬', '🔬', '📊', '🌍'];
                        $icon = $icons[array_rand($icons)];
                    ?>
                        <div style="display:flex;align-items:center;gap:15px;padding:15px;background:#f8fafc;border-radius:10px;">
                            <div style="font-size:1.5rem;"><?php echo $icon; ?></div>
                            <div style="flex:1;">
                                <div style="font-weight:bold;color:#1e293b;"><?php echo htmlspecialchars($subject['title']); ?></div>
                                
                                <!-- Modules Progress -->
                                <div style="color:#64748b;font-size:0.9rem;margin-top:5px;">
                                    <strong>Modules:</strong> <?php echo $subject['completed_modules']; ?>/<?php echo $subject['total_modules']; ?> 
                                    (<?php echo round($module_progress); ?>%)
                                </div>
                                
                                <!-- Lectures Progress -->
                                <?php if(isset($subject['total_lectures'])): ?>
                                <div style="color:#64748b;font-size:0.9rem;margin-top:2px;">
                                    <strong>Lectures:</strong> <?php echo $subject['completed_lectures']; ?>/<?php echo $subject['total_lectures']; ?> 
                                    (<?php echo round($lecture_progress); ?>%)
                                </div>
                                <?php endif; ?>
                                
                                <!-- Progress Bar -->
                                <div style="background:#e2e8f0;border-radius:10px;height:8px;margin-top:8px;">
                                    <div style="background:#06b6d4;height:100%;border-radius:10px;width:<?php echo $overall_progress; ?>%;"></div>
                                </div>
                            </div>
                            <div style="color:#06b6d4;font-weight:bold;"><?php echo round($overall_progress); ?>%</div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($detailed_subject_progress)): ?>
                        <div style="text-align:center;padding:20px;color:#64748b;">
                            No course progress found. Start learning to see your progress here!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

    
// function toggleEditProfile() {
//     const section = document.getElementById('editProfileSection');
//     if (section.style.display === 'none') {
//         section.style.display = 'block';
//         // Scroll to the edit section
//         section.scrollIntoView({ behavior: 'smooth' });
//     } else {
//         section.style.display = 'none';
//     }
// }
// // Profile picture upload functionality
// document.getElementById('profile-pic-input').addEventListener('change', function() {
//     if (this.files && this.files[0]) {
//         document.getElementById('profile-pic-form').submit();
//     }
// });


<script>
// Wait for page to fully load first
document.addEventListener("DOMContentLoaded", function() {
    
    // Toggle Edit Profile Section
    window.toggleEditProfile = function() {
        const section = document.getElementById('editProfileSection');
        if (section.style.display === 'none' || section.style.display === '') {
            section.style.display = 'block';
            section.scrollIntoView({ behavior: 'smooth' });
        } else {
            section.style.display = 'none';
        }
    };

    // Profile Picture Upload – NOW WORKS 100%
    const fileInput = document.getElementById('profile-pic-input');
    const form = document.getElementById('profile-pic-form');

    // Trigger file input when camera button is clicked
    document.querySelector('button[onclick="document.getElementById(\'profile-pic-input\').click()"]')
        .addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });

    // When user selects a file → auto submit
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            form.submit(); // This now works because form is in DOM
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
