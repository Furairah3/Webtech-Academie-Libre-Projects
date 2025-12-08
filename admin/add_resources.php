<?php
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) die("Access denied");
require_once '../config/db.php';

// Initialize variables
$success = '';
$error = '';

// === DELETE RESOURCE ===
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM course_resources WHERE id = ?")->execute([$id]);
        $success = "Resource deleted successfully!";
    } catch(Exception $e) {
        $error = "Failed to delete resource.";
    }
}

// === EDIT RESOURCE (save changes) ===
if(isset($_POST['update'])) {
    $id = (int)$_POST['resource_id'];
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $url = trim($_POST['url'] ?? '');
    
    if($type === 'book') {
        $url = trim($_POST['book_info'] ?? '');
    }
    
    try {
        $pdo->prepare("UPDATE course_resources SET course_id=?, title=?, type=?, url=? WHERE id=?")
            ->execute([$course_id, $title, $type, $url, $id]);
        $success = "Resource updated successfully!";
    } catch(Exception $e) {
        $error = "Failed to update resource.";
    }
}

// === CREATE NEW RESOURCE ===
if($_POST && isset($_POST['course_id']) && !isset($_POST['update'])) {
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $url = '';
    $file_uploaded = false;

    if($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $file = $_FILES['pdf_file'];
        if($file['size'] > 50*1024*1024) {
            $error = "PDF too large! Max 50MB";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf') {
                $error = "Only PDF files allowed!";
            } else {
                $filename = "resource_pdf_c{$course_id}_" . time() . ".pdf";
                $path = "../uploads/resources/" . $filename;
                if(!is_dir('../uploads/resources')) {
                    mkdir('../uploads/resources', 0777, true);
                }
                if(move_uploaded_file($file['tmp_name'], $path)) {
                    $url = "uploads/resources/" . $filename;
                    $file_uploaded = true;
                }
            }
        }
    }
    elseif($type === 'book') {
        $url = trim($_POST['book_info'] ?? '');
    }
    else {
        $url = trim($_POST['url'] ?? '');
    }

    if(!$error && ($url || $file_uploaded)) {
        try {
            $pdo->prepare("INSERT INTO course_resources (course_id, title, type, url, added_by) VALUES (?,?,?,?,?)")
                ->execute([$course_id, $title, $type, $url, $_SESSION['user_id'] ?? 1]);
            $success = "Resource added successfully!";
        } catch(Exception $e) {
            $error = "Failed to add resource: " . $e->getMessage();
        }
    } elseif(!$error) {
        $error = "Please provide all required information.";
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();

// Fetch all resources with course info
$resources = [];
try {
    $resources = $pdo->query("
        SELECT cr.*, c.title as course_title 
        FROM course_resources cr
        JOIN courses c ON cr.course_id = c.id
        ORDER BY c.title, cr.created_at DESC
    ")->fetchAll();
} catch(Exception $e) {
    $error = "Error loading resources: " . $e->getMessage();
}

include 'includes/admin-header.php';
?>

<div class="card" style="max-width:1200px;margin:40px auto;padding:40px;background:#0f172a;color:white;border-radius:20px;">
    
    <?php if($success): ?>
        <div style="background:#166534;padding:15px;border-radius:10px;margin-bottom:20px;text-align:center;color:#86efac;">
            <?= htmlspecialchars($success) ?>
            <a href="?" style="color:#86efac;margin-left:15px;text-decoration:underline;">Close</a>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div style="background:#7f1d1d;padding:15px;border-radius:10px;margin-bottom:20px;text-align:center;color:#fca5a5;">
            <?= htmlspecialchars($error) ?>
            <a href="?" style="color:#fca5a5;margin-left:15px;text-decoration:underline;">Close</a>
        </div>
    <?php endif; ?>
    
    <h1 style="text-align:center;color:#8b5cf6;margin-bottom:40px;font-size:2.2rem;">Manage Learning Resources</h1>
    
    <!-- Add New Resource Form -->
    <div style="background:#1e293b;padding:30px;border-radius:15px;margin-bottom:40px;">
        <h2 style="color:#f1f5f9;margin-bottom:25px;font-size:1.5rem;">Add New Resource</h2>
        
        <form method="POST" enctype="multipart/form-data" id="resourceForm" style="display:grid;gap:20px;max-width:650px;margin:0 auto;">
            <select name="course_id" required style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
                <option value="">Select Course</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="title" placeholder="Resource Title" required 
                   style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">

            <select name="type" id="resourceType" required 
                    style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
                <option value="">Select Resource Type</option>
                <option value="pdf">PDF File (Upload)</option>
                <option value="book">Physical Book Reference</option>
                <option value="youtube">YouTube Video / Playlist</option>
                <option value="website">Helpful Website</option>
            </select>

            <div id="inputContainer" style="min-height:60px;">
                <input type="url" name="url" placeholder="https://youtube.com/watch?v=..." 
                       style="padding:15px;width:100%;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
                <small style="color:#94a3b8;display:block;margin-top:8px;">Enter YouTube or website link</small>
            </div>

            <button type="submit" style="background:#8b5cf6;color:white;padding:16px;font-size:1.1rem;border:none;border-radius:10px;cursor:pointer;font-weight:bold;">
                Add Resource
            </button>
        </form>
    </div>
    
    <!-- List of All Resources -->
    <div style="background:#1e293b;padding:30px;border-radius:15px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
            <h2 style="color:#f1f5f9;margin:0;font-size:1.5rem;">
                All Resources <span style="background:#8b5cf6;color:white;padding:5px 12px;border-radius:15px;font-size:0.9rem;">
                    <?= count($resources) ?>
                </span>
            </h2>
        </div>
        
        <?php if(empty($resources)): ?>
            <div style="text-align:center;padding:40px;color:#94a3b8;">
                No resources added yet.
            </div>
        <?php endif; ?>
        
        <?php 
        $currentCourse = '';
        foreach($resources as $r): 
            if($currentCourse != $r['course_title']):
                $currentCourse = $r['course_title'];
        ?>
            <div style="background:#0f172a;padding:12px 20px;margin:20px 0 15px;border-radius:8px;color:#c4b5fd;font-weight:bold;font-size:1rem;">
                📚 <?= htmlspecialchars($currentCourse) ?>
            </div>
        <?php endif; ?>
        
        <div style="background:#0f172a;padding:20px;margin-bottom:15px;border-radius:12px;border:1px solid #334155;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:15px;">
                <div style="flex:1;min-width:300px;">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <?php 
                        $icon = match($r['type']) {
                            'pdf' => '📄',
                            'book' => '📚',
                            'youtube' => '🎬',
                            'website' => '🌐',
                            default => '📎'
                        };
                        ?>
                        <div style="width:50px;height:50px;background:#8b5cf6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;">
                            <?= $icon ?>
                        </div>
                        <div>
                            <strong style="font-size:1.1rem;color:#f1f5f9;"><?= htmlspecialchars($r['title']) ?></strong><br>
                            <div style="color:#94a3b8;font-size:0.9rem;margin-top:5px;">
                                Type: <?= strtoupper($r['type']) ?> • 
                                <?php if($r['created_at']): ?>
                                    Added: <?= date('M d, Y', strtotime($r['created_at'])) ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($r['type'] === 'pdf' && $r['url']): ?>
                                <a href="../<?= htmlspecialchars($r['url']) ?>" target="_blank" 
                                   style="display:inline-block;margin-top:10px;color:#8b5cf6;text-decoration:none;font-weight:bold;">
                                    📥 Download PDF
                                </a>
                            <?php elseif($r['type'] === 'book' && $r['url']): ?>
                                <div style="margin-top:10px;color:#cbd5e1;font-size:0.9rem;">
                                    <?= htmlspecialchars($r['url']) ?>
                                </div>
                            <?php elseif($r['url']): ?>
                                <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" 
                                   style="display:inline-block;margin-top:10px;color:#8b5cf6;text-decoration:none;font-weight:bold;">
                                    🔗 Open Link
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex;gap:10px;">
                    <a href="?edit=<?= $r['id'] ?>" 
                       style="background:#8b5cf6;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:0.9rem;">
                        Edit
                    </a>
                    <a href="?delete=<?= $r['id'] ?>" 
                       style="background:#7f1d1d;color:#fca5a5;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:0.9rem;"
                       onclick="return confirm('Delete this resource?')">
                        Delete
                    </a>
                </div>
            </div>
            
            <!-- Edit Form (shown when ?edit=id) -->
            <?php if(isset($_GET['edit']) && $_GET['edit'] == $r['id']): ?>
                <div style="background:#1e293b;padding:25px;margin-top:20px;border-radius:10px;border:2px solid #8b5cf6;">
                    <h3 style="color:#c4b5fd;margin-bottom:15px;font-size:1.2rem;">Edit Resource</h3>
                    <form method="POST" style="display:grid;gap:15px;">
                        <input type="hidden" name="resource_id" value="<?= $r['id'] ?>">
                        
                        <select name="course_id" required style="padding:12px;border-radius:8px;background:#334155;color:white;font-size:0.9rem;border:none;">
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $r['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="text" name="title" value="<?= htmlspecialchars($r['title']) ?>" placeholder="Resource Title" required 
                               style="padding:12px;border-radius:8px;background:#334155;color:white;font-size:0.9rem;border:none;">

                        <select name="type" required style="padding:12px;border-radius:8px;background:#334155;color:white;font-size:0.9rem;border:none;">
                            <option value="pdf" <?= $r['type'] == 'pdf' ? 'selected' : '' ?>>PDF File</option>
                            <option value="book" <?= $r['type'] == 'book' ? 'selected' : '' ?>>Physical Book</option>
                            <option value="youtube" <?= $r['type'] == 'youtube' ? 'selected' : '' ?>>YouTube Video</option>
                            <option value="website" <?= $r['type'] == 'website' ? 'selected' : '' ?>>Website</option>
                        </select>

                        <?php if($r['type'] === 'book'): ?>
                            <input type="text" name="book_info" value="<?= htmlspecialchars($r['url']) ?>" placeholder="Book details" required 
                                   style="padding:12px;border-radius:8px;background:#334155;color:white;font-size:0.9rem;border:none;">
                        <?php else: ?>
                            <input type="text" name="url" value="<?= htmlspecialchars($r['url']) ?>" placeholder="URL" required 
                                   style="padding:12px;border-radius:8px;background:#334155;color:white;font-size:0.9rem;border:none;">
                        <?php endif; ?>

                        <div style="display:flex;gap:10px;margin-top:10px;">
                            <button type="submit" name="update" 
                                    style="background:#10b981;color:white;padding:12px 24px;border-radius:8px;border:none;cursor:pointer;font-weight:bold;">
                                Save Changes
                            </button>
                            <a href="?" 
                               style="background:#64748b;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;text-align:center;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.getElementById('resourceType').addEventListener('change', function() {
    const type = this.value;
    const container = document.getElementById('inputContainer');

    if(type === 'pdf') {
        container.innerHTML = `
            <label style="color:#f59e0b;font-weight:bold;margin-bottom:8px;display:block;">Upload PDF File (Max 50MB)</label>
            <input type="file" name="pdf_file" accept=".pdf" required 
                   style="padding:12px;background:#334155;color:white;border-radius:10px;width:100%;border:none;">
            <small style="color:#94a3b8;">Only PDF files are allowed</small>
        `;
    } else if(type === 'book') {
        container.innerHTML = `
            <input type="text" name="book_info" placeholder="e.g. Author: Griffiths, Edition: 4th, ISBN: 978-0131118928" required 
                   style="padding:15px;width:100%;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter full book details</small>
        `;
    } else {
        const placeholder = type === 'youtube' ? 'https://youtube.com/watch?v=...' : 'https://example.com';
        container.innerHTML = `
            <input type="url" name="url" placeholder="${placeholder}" required 
                   style="padding:15px;width:100%;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter ${type === 'youtube' ? 'YouTube' : 'website'} link</small>
        `;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
