<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/base.php';  // ← This gives you BASE_URL & ASSETS_URL

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
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-header { background:#1e293b; padding:1rem 0; border-bottom:4px solid #f59e0b; }
        .admin-header .logo { font-size:2rem; font-weight:bold; color:white; }
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
                <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/add_course.php">Courses</a>
                <a href="<?= BASE_URL ?>/admin/add_module.php">Modules</a>
                <a href="<?= BASE_URL ?>/admin/add_lecture.php">Lectures</a>
                <a href="<?= BASE_URL ?>/admin/add_quiz.php">Quizzes</a>
                <a href="<?= BASE_URL ?>/admin/add_resources.php">Add Resources</a>
                <a href="<?= BASE_URL ?>/admin/post-assignment.php">Post Assignment</a>
                <a href="<?= BASE_URL ?>/admin/view_assignments.php">View Submissions</a>
                <a href="<?= BASE_URL ?>/profile.php" style="display:flex;align-items:center;gap:10px;color:#e2e8f0;">
                    <img src="<?= ASSETS_URL ?>/uploads/<?= htmlspecialchars($_SESSION['profile_pic']) ?>"
                         style="width:40px;height:40px;border-radius:50%;border:2px solid #f59e0b;object-fit:cover;">
                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </a>
            </nav>
        </div>
    </header>
    <div class="container" style="padding:30px 0;">
