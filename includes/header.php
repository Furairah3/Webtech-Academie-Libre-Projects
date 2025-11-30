<?php 
require_once 'functions.php'; 

// Only load database connection + user data when someone is logged in
if(is_logged_in()) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/learnstep/config/db.php';

    $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();

    // Update session with fresh data
    $_SESSION['user_name'] = $current_user['username'] ?? $_SESSION['user_name'];
    $_SESSION['profile_pic'] = $current_user['profile_pic'] ?? 'default.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnStep</title>
    <link rel="stylesheet" href="/learnstep/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #F5F7FA;
            color: #0A2647;
            overflow-x: hidden;
        }

        /* ------------------- TOP NAVBAR ------------------- */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 80px;
            background: linear-gradient(135deg, #06b6d4, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-left img {
            width: 180px;
            height: auto;
        }

        .navbar input {
            width: 40%;
            padding: 7px 15px;
            border: none;
            border-radius: 20px;
            outline: none;
        }

        .navbar input:focus {
            box-shadow: 0 0 5px #00C2CB;
            border: #0A2647 solid 1px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #06b6d4;
        }

        /* ------------------- SIDEBAR ------------------- */
        .sidebar {
            width: 200px;
            background-color: #0A2647;
            color: #EAF6F6;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 100px;
            overflow-y: auto;
            z-index: 999;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-links li {
            margin: 5px 0;
        }

        .nav-links li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-links li a:hover {
            background: #4d7194;
            color: #3498db;
        }

        .nav-links li.active a {
            background: #3498db;
            color: white;
            border-right: 3px solid #2980b9;
        }

        /* ------------------- MAIN CONTENT ------------------- */
        .main-content {
            margin-left: 230px;
            padding: 100px 30px 80px 30px;
            min-height: calc(100vh - 120px);
        }

        /* ------------------- FOOTER ------------------- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #0A2647;
            color: #EAF6F6;
            text-align: center;
            padding: 10px 0;
            font-size: 13px;
            z-index: 1000;
        }

        .btn {
            background: white;
            color: #00C2CB;
            border: none;
            border-radius: 10px;
            padding: 8px 15px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #00C2CB;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <!-- Top Navbar (Fixed) -->
    <header class="navbar">
        <div class="nav-left">
            <img src="/learnstep/images/LOGO_H.png" alt="LearnStep Logo">
        </div>
        <input type="text" placeholder="Search resources...">
        <div class="profile">
            <?php if(is_logged_in()): ?>
                <img src="/learnstep/assets/uploads/<?php echo $_SESSION['profile_pic']; ?>" 
                     alt="Profile" 
                     id="navbar-profile-img">
                <span id="navbar-username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <?php else: ?>
                <a href="/learnstep/profile.php">
                    <img src="/learnstep/assets/images/default-profile.jpg" alt="Profile">
                    <span>Guest</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Sidebar -->
        <!-- Sidebar -->
    <?php if(is_logged_in()): ?>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="sidebar">
        <ul class="nav-links">
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php"><span>🏠 Dashboard</span></a>
            </li>
            <li class="<?php echo ($current_page == 'course.php' || $current_page == 'course-view.php') ? 'active' : ''; ?>">
                <a href="course.php"><span>📖 Courses</span></a>
            </li>
            <li class="<?php echo ($current_page == 'take-quiz.php') ? 'active' : ''; ?>">
                <a href="take-quiz.php"><span>🧠 Take Quiz</span></a>
            </li>
            <li class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
                <a href="assignments.php"><span>📝 Assignment</span></a>
            </li>

            <li class="<?php echo ($current_page == 'resources.php') ? 'active' : ''; ?>">
                <a href="resources.php"><span>📚 Resources</span></a>
            </li>

            <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <a href="profile.php"><span>👤 Profile</span></a>
            </li>
            
            <!-- Admin Panel (only for admins) -->
            <?php if(is_admin()): ?>
            <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'admin') !== false || $current_page == 'create-admin.php') ? 'active' : ''; ?>">
                <a href="admin/"><span>⚙️ Admin Panel</span></a>
            </li>
            <li class="<?php echo ($current_page == 'create-admin.php') ? 'active' : ''; ?>">
                <a href="create-admin.php"><span>👨‍💼 Create Admin</span></a>
            </li>
            <?php endif; ?>
            
            <li class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">
                <a href="logout.php"><span>🚪 Logout</span></a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Your page-specific content goes here -->
        <?php // This is where individual page content will be inserted ?>