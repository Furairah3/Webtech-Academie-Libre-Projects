<?php require_once '../includes/functions.php'; 
if(!is_logged_in() || !is_admin()) die("Access denied");
require_once '../config/db.php';

if($_POST['action'] ?? '' === 'grade') {
    $assign_id = (int)$_POST['assign_id'];
    $grade     = $_POST['grade'];
    $feedback  = $_POST['feedback'];

    $update = "UPDATE assignments SET grade = ?, feedback = ?, graded_by = ?, graded_at = NOW()";
    $params = [$grade, $feedback, $_SESSION['user_id']];

    if(isset($_FILES['corrected_file']) && $_FILES['corrected_file']['error'] === 0) {
        $ext = pathinfo($_FILES['corrected_file']['name'], PATHINFO_EXTENSION);
        $new = "uploads/corrected/corrected_{$assign_id}_".time().".$ext";
        if(!is_dir('uploads/corrected')) mkdir('uploads/corrected', 0777, true);
        move_uploaded_file($_FILES['corrected_file']['tmp_name'], $new);
        $update .= ", corrected_file = ?";
        $params[] = $new;
    }
    $update .= " WHERE id = ?";
    $params[] = $assign_id;
    $pdo->prepare($update)->execute($params);
    $_SESSION['success'] = "Graded successfully!";
    header("Location: ".$_SERVER['PHP_SELF']); exit();
}

include 'includes/admin-header.php';
?>

<div class="card" style="max-width:1200px;margin:60px auto;padding:40px;background:#0f172a;color:white;border-radius:20px;">
    <h1 style="color:#8b5cf6;text-align:center;margin-bottom:40px;">Grade Student Assignments</h1>

    <?php if(isset($_SESSION['success'])): ?>
        <div style="background:#166534;padding:15px;border-radius:10px;margin:20px 0;text-align:center;color:#86efac;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php
    $subs = $pdo->query("
        SELECT a.*, 
               u.username, 
               CONCAT(u.fname, ' ', u.lname) AS full_name,
               m.title as module_title, 
               c.title as course_title,
               COALESCE(g.username, 'Not graded') as grader_name,
               g.fname as grader_fname,
               g.lname as grader_lname
        FROM assignments a
        JOIN users u ON a.user_id = u.id
        JOIN modules m ON a.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        LEFT JOIN users g ON a.graded_by = g.id
        ORDER BY a.submitted_at DESC
    ")->fetchAll();
    ?>

    <?php foreach($subs as $s): ?>
    <div style="background:#1e293b;padding:30px;margin:25px 0;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,0.3);">
        <h3 style="color:#06b6d4;">
            <?= htmlspecialchars($s['full_name']) ?> (@<?= $s['username'] ?>)
            → <?= htmlspecialchars($s['course_title']) ?> → <?= htmlspecialchars($s['module_title']) ?>
        </h3>
        <p style="color:#94a3b8;">Submitted: <?= date('d M Y H:i', strtotime($s['submitted_at'])) ?></p>

        <!-- STUDENT SUBMISSION – NOW OPENS IN BROWSER -->
        <div style="margin:20px 0;">
            <a href="../<?= $s['file_path'] ?>" target="_blank"
               style="background:#f59e0b;color:white;padding:14px 32px;border-radius:12px;text-decoration:none;font-weight:bold;margin-right:15px;">
               View Submission
            </a>
            <a href="../<?= $s['file_path'] ?>" download
               style="color:#fcd34d;text-decoration:underline;font-size:0.95rem;">
               Download File
            </a>
        </div>

        <?php if($s['grade'] !== null): ?>
            <div style="margin:25px 0;padding:25px;background:#166534;color:#86efac;border-radius:12px;">
                <strong style="font-size:1.4rem;">Grade: <?= $s['grade'] ?>/100</strong><br>
                Graded by: <strong><?= $s['graded_by'] ? htmlspecialchars($s['grader_fname'].' '.$s['grader_lname']) : 'Unknown' ?></strong>
                <p style="margin-top:12px;"><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($s['feedback'] ?? '')) ?></p>

                <?php if($s['corrected_file']): ?>
                    <div style="margin-top:20px;">
                        <a href="../<?= $s['corrected_file'] ?>" target="_blank"
                           style="background:#10b981;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:bold;">
                           View Corrected File
                        </a>
                        <a href="../<?= $s['corrected_file'] ?>" download
                           style="color:#86efac;margin-left:12px;text-decoration:underline;">
                           Download
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" style="margin-top:30px;">
                <input type="hidden" name="action" value="grade">
                <input type="hidden" name="assign_id" value="<?= $s['id'] ?>">
                <div style="display:grid;gap:18px;">
                    <input type="number" name="grade" min="0" max="100" step="0.5" placeholder="Grade /100" required 
                           style="padding:14px;background:#334155;border:none;border-radius:10px;color:white;">
                    <textarea name="feedback" placeholder="Write feedback here..." rows="5" 
                              style="padding:14px;background:#334155;border:none;border-radius:10px;color:white;"></textarea>
                    <div>
                        <label style="color:#94a3b8;">Attach corrected file (optional):</label><br>
                        <input type="file" name="corrected_file" accept=".pdf,.doc,.docx,.zip" 
                               style="margin-top:8px;padding:10px;background:#334155;border-radius:8px;">
                    </div>
                    <button type="submit" style="background:#8b5cf6;color:white;padding:16px;font-size:1.3rem;border:none;border-radius:12px;cursor:pointer;">
                        Submit Grade & Feedback
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>