<?php 
require_once __DIR__ . '/../config/base.php';   // This line fixes EVERYTHING
require_once __DIR__ . '/functions.php'; 

// Only load database connection + user data when someone is logged in
if(is_logged_in()) {
    require_once __DIR__ . '/../config/db.php';

    $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();

    $_SESSION['user_name'] = $current_user['username'] ?? $_SESSION['user_name'];
    $_SESSION['profile_pic'] = $current_user['profile_pic'] ?? 'default.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Académie Libre</title>
    <!-- DYNAMIC CSS PATHS — WORKS ON LIVE & LOCAL -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#F5F7FA; color:#0A2647; overflow-x:hidden; }

        .navbar {
            position:fixed; top:0; left:0; width:100%; height:80px;
            background:linear-gradient(135deg,#06b6d4,#8b5cf6); color:white;
            display:flex; align-items:center; justify-content:space-between;
            padding:0 25px; z-index:1000; box-shadow:0 2px 8px rgba(0,0,0,0.2);
        }
        .nav-left img { width:180px; height:auto; }
        .navbar input { width:40%; padding:7px 15px; border:none; border-radius:20px; outline:none; }
        .navbar input:focus { box-shadow:0 0 5px #00C2CB; border:1px solid #0A2647; }
        .profile img { width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid #06b6d4; }

        .sidebar {
            width:200px; background:#0A2647; color:#EAF6F6; height:100vh;
            position:fixed; left:0; top:0; padding-top:100px; z-index:999; overflow-y:auto;
        }
        .nav-links li a { display:flex; align-items:center; padding:12px 20px; color:#ecf0f1; text-decoration:none; transition:0.3s; }
        .nav-links li a:hover { background:#4d7194; color:#3498db; }
        .nav-links li.active a { background:#3498db; color:white; border-right:3px solid #2980b9; }

        .main-content { margin-left:230px; padding:100px 30px 80px; min-height:calc(100vh - 120px); }

        .btn { background:white; color:#00C2CB; border:none; border-radius:10px; padding:8px 15px; cursor:pointer; font-weight:600; transition:0.3s; text-decoration:none; display:inline-block; }
        .btn:hover { background:#00C2CB; color:white; }
        .btn-danger { background:#dc3545; color:white; }
        .btn-danger:hover { background:#c82333; }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <header class="navbar">
        <div class="nav-left">
            <img src="<?= BASE_URL ?>/images/LOGO_H.png" alt="Logo">
        </div>
        <input type="text" placeholder="Search resources...">
        <div class="profile">
            <?php if(is_logged_in()): ?>
                <img src="<?= ASSETS_URL ?>/uploads/<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="Profile" id="navbar-profile-img">
                <span id="navbar-username"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php">
                    <img src="<?= BASE_URL ?>/assets/images/default-profile.jpg" alt="Guest">
                    <span>Guest</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Sidebar (Only for logged-in users) -->
    <?php if(is_logged_in()): ?>
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <div class="sidebar">
        <ul class="nav-links">
            <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
            </li>
            <li class="<?= in_array($current_page, ['course.php','course-view.php']) ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/course.php">Courses</a>
            </li>
            <li class="<?= $current_page == 'take-quiz.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/take-quiz.php">Take Quiz</a>
            </li>
            <li class="<?= $current_page == 'assignments.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/assignments.php">Assignment</a>
            </li>
            <li class="<?= $current_page == 'resources.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/resources.php">Resources</a>
            </li>
            <li class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/profile.php">Profile</a>
            </li>
            
            <?php if(is_admin()): ?>
            <li class="<?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>">
                <a href="<?= BASE_URL ?>/admin/">Admin Panel</a>
            </li>
            <?php endif; ?>
            
            <li>
                <a href="<?= BASE_URL ?>/logout.php">Logout</a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="main-content">
