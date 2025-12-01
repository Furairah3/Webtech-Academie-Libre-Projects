<?php
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) die("Access denied");
require_once '../config/db.php';

if($_POST) {
    $course_id = (int)$_POST['course_id'];
    $title     = trim($_POST['title']);
    $type      = $_POST['type'];
    $url       = '';

    if($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $file = $_FILES['pdf_file'];
        if($file['size'] > 50*1024*1024) die("PDF too large! Max 50MB");
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if($ext !== 'pdf') die("Only PDF files allowed!");

        $filename = "resource_pdf_c{$course_id}_" . time() . ".pdf";
        $path = "../uploads/resources/" . $filename;
        if(!is_dir('../uploads/resources')) mkdir('../uploads/resources', 0777, true);
        if(move_uploaded_file($file['tmp_name'], $path)) {
            $url = "uploads/resources/" . $filename;
        }
    }
    elseif($type === 'book') {
        $url = trim($_POST['book_info'] ?? '');
    }
    else {
        $url = trim($_POST['url'] ?? '');
    }

    if($url) {
        $pdo->prepare("INSERT INTO course_resources (course_id, title, type, url, added_by) VALUES (?,?,?,?,?)")
            ->execute([$course_id, $title, $type, $url, $_SESSION['user_id']]);
        $_SESSION['success'] = "Resource added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add resource.";
    }
    header("Location: ".$_SERVER['PHP_SELF']); exit();
}

$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();
include 'includes/admin-header.php';
?>

<div class="card" style="max-width:900px;margin:80px auto;padding:50px;background:#0f172a;color:white;border-radius:20px;">
    <h1 style="text-align:center;color:#8b5cf6;margin-bottom:40px;font-size:2.5rem;">Add Learning Resources</h1>

    <?php if(isset($_SESSION['success'])): ?>
        <div style="background:#166534;padding:15px;border-radius:10px;margin-bottom:20px;text-align:center;color:#86efac;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="resourceForm" style="display:grid;gap:22px;max-width:650px;margin:0 auto;">
        <select name="course_id" required style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <option value="">Select Course</option>
            <?php foreach($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Resource Title (e.g. Advanced Calculus Notes)" required 
               style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">

        <select name="type" id="resourceType" required style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <option value="">Select Resource Type</option>
            <option value="pdf">PDF File (Upload)</option>
            <option value="book">Physical Book Reference</option>
            <option value="youtube">YouTube Video / Playlist</option>
            <option value="website">Helpful Website</option>
        </select>

        <div id="inputContainer" style="min-height:80px;">
            <input type="url" name="url" placeholder="https://youtube.com/watch?v=..." 
                   style="padding:16px;width:100%;border-radius:12px;background:#1e293b;color:white;font-size:1rem;" 
                   id="urlInput">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter YouTube or website link</small>
        </div>

        <button type="submit" style="background:#8b5cf6;color:white;padding:18px;font-size:1.3rem;border:none;border-radius:12px;cursor:pointer;font-weight:bold;">
            Add Resource
        </button>
    </form>
</div>

<script>
document.getElementById('resourceType').addEventListener('change', function() {
    const type = this.value;
    const container = document.getElementById('inputContainer');

    if(type === 'pdf') {
        container.innerHTML = `
            <label style="color:#f59e0b;font-weight:bold;margin-bottom:8px;display:block;">Upload PDF File (Max 50MB)</label>
            <input type="file" name="pdf_file" accept=".pdf" required 
                   style="padding:14px;background:#334155;color:white;border-radius:12px;width:100%;">
            <small style="color:#94a3b8;">Only PDF files are allowed</small>
        `;
    } else if(type === 'book') {
        container.innerHTML = `
            <input type="text" name="book_info" placeholder="e.g. Author: Griffiths, Edition: 4th, ISBN: 978-0131118928" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter full book details</small>
        `;
    } else {
        const placeholder = type === 'youtube' ? 'https://youtube.com/watch?v=...' : 'https://example.com';
        const label = type === 'youtube' ? 'YouTube Link' : 'Website URL';
        container.innerHTML = `
            <input type="url" name="url" placeholder="${placeholder}" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">${label}</small>
        `;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
