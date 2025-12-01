<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$user_id   = $_SESSION['user_id'];
$module_id = (int)($_POST['module_id'] ?? 0);

if($module_id <= 0) {
    $_SESSION['error'] = "Invalid module";
    header("Location: course.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['assignment_file'])) {
    $file = $_FILES['assignment_file'];
    $allowed = ['pdf','doc','docx','zip'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if($file['error'] !== 0 || !in_array($ext, $allowed) || $file['size'] > 10*1024*1024) {
        $_SESSION['error'] = "Invalid file. Only PDF/DOCX/ZIP ≤ 10MB";
    } else {
        $new_name = "assign_user{$user_id}_module{$module_id}_".time().".$ext";
        $path = "uploads/assignments/".$new_name;

        if(!is_dir('uploads/assignments')) mkdir('uploads/assignments', 0777, true);

        if(move_uploaded_file($file['tmp_name'], $path)) {
            $pdo->prepare("INSERT INTO assignments (user_id, module_id, file_path) VALUES (?,?,?) 
                           ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), submitted_at = NOW()")
                ->execute([$user_id, $module_id, $path]);

            $_SESSION['success'] = "Assignment submitted successfully!";
        } else {
            $_SESSION['error'] = "Upload failed";
        }
    }
}
header("Location: module-view.php?id=$module_id");
exit();
