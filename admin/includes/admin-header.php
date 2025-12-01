<?php 
require_once __DIR__ . '/../../includes/functions.php'; 
if(is_logged_in()) {
    require_once __DIR__ . '/../../config/db.php';
    $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $_SESSION['user_name'] = $u['username'];
    $_SESSION['profile_pic'] = $u['profile_pic'] ?? 'default.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Academie Libre</title>
    <link rel="stylesheet" href="/Webtech-Academie-Libre-Projects/assets/css/style.css">
    <link rel="stylesheet" href="/Webtech-Academie-Libre-Projects/assets/css/admin.css">
    <style>
        .admin-header { background:#1e293b; padding:1rem 0; border-bottom:4px solid #f59e0b; }
        .admin-header .logo { font-size:2rem; font-weight:bold; }
        .admin-header nav a { color:#e2e8f0; margin:0 15px; font-weight:500; padding:8px 16px; border-radius:8px; transition:0.3s; }
        .admin-header nav a:hover { background:#f59e0b; color:black; }
        .admin-header .btn-danger { background:#ef4444; }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="logo">Academie Libre <span style="color:#f59e0b;">ADMIN</span></div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="add_course.php">Courses</a>
                <a href="add_module.php">Modules</a>
                <a href="add_lecture.php">Lectures</a>
                <a href="add_quiz.php">Quizzes</a>
                <a href="add_resources.php">Add Resources</a>
                <a href="post-assignment.php">Post Assignment</a>
                <a href="view_assignments.php">View Submissions</a>

                <a href="/Webtech-Academie-Libre-Projects/profile.php" style="display:flex;align-items:center;gap:10px;color:#e2e8f0;">
                    <img src="/Webtech-Academie-Libre-Projects/assets/uploads/<?php echo $_SESSION['profile_pic']; ?>" 
                         style="width:40px;height:40px;border-radius:50%;border:2px solid #f59e0b;">
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </a>
            </nav>
        </div>
    </header>
    <div class="container" style="padding:30px 0;">
