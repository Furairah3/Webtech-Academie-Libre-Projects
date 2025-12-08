<?php
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) die("Access denied");
require_once '../config/db.php';

$success = $error = '';

// === DELETE RESOURCE ===
if(isset($_GET['delete_resource'])) {
    $id = (int)$_GET['delete_resource'];
    
    // Get file path if it's a PDF to delete the file
    $stmt = $pdo->prepare("SELECT url, type FROM course_resources WHERE id = ?");
    $stmt->execute([$id]);
    $resource = $stmt->fetch();
    
    if($resource && $resource['type'] === 'pdf' && $resource['url']) {
        $file_path = "../" . $resource['url'];
        if(file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $pdo->prepare("DELETE FROM course_resources WHERE id = ?")->execute([$id]);
    $success = "Resource deleted successfully!";
}

// === EDIT RESOURCE (save changes) ===
if(isset($_POST['save_edit'])) {
    $id = (int)$_POST['resource_id'];
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $url = $_POST['old_url'] ?? '';
    
    if($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $file = $_FILES['pdf_file'];
        if($file['size'] > 50*1024*1024) {
            $error = "PDF too large! Max 50MB";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf') {
                $error = "Only PDF files allowed!";
            } else {
                // Delete old file if exists
                if($url && file_exists("../" . $url)) {
                    unlink("../" . $url);
                }
                
                $filename = "resource_pdf_c{$course_id}_" . time() . ".pdf";
                $path = "../uploads/resources/" . $filename;
                if(!is_dir('../uploads/resources')) mkdir('../uploads/resources', 0777, true);
                if(move_uploaded_file($file['tmp_name'], $path)) {
                    $url = "uploads/resources/" . $filename;
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
    
    if(!$error && $url) {
        $pdo->prepare("UPDATE course_resources SET course_id=?, title=?, type=?, url=?, updated_at=NOW() WHERE id=?")
            ->execute([$course_id, $title, $type, $url, $id]);
        $success = "Resource updated successfully!";
    } elseif(!$error) {
        $error = "Failed to update resource.";
    }
}

// === CREATE NEW RESOURCE ===
if($_POST && isset($_POST['course_id']) && !isset($_POST['save_edit'])) {
    $course_id = (int)$_POST['course_id'];
    $title     = trim($_POST['title']);
    $type      = $_POST['type'];
    $url       = '';

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
                if(!is_dir('../uploads/resources')) mkdir('../uploads/resources', 0777, true);
                if(move_uploaded_file($file['tmp_name'], $path)) {
                    $url = "uploads/resources/" . $filename;
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

    if(!$error && $url) {
        $pdo->prepare("INSERT INTO course_resources (course_id, title, type, url, added_by) VALUES (?,?,?,?,?)")
            ->execute([$course_id, $title, $type, $url, $_SESSION['user_id']]);
        $success = "Resource added successfully!";
    } elseif(!$error) {
        $error = "Failed to add resource.";
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();

// Fetch all resources with course info
$resources = $pdo->query("
    SELECT cr.*, c.title as course_title, u.username as added_by_name,
           DATE_FORMAT(cr.created_at, '%b %d, %Y') as created_date,
           DATE_FORMAT(cr.updated_at, '%b %d, %Y') as updated_date
    FROM course_resources cr
    JOIN courses c ON cr.course_id = c.id
    LEFT JOIN users u ON cr.added_by = u.id
    ORDER BY c.title, cr.created_at DESC
")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="card" style="max-width:1200px;margin:40px auto;padding:0;background:#0f172a;color:white;border-radius:20px;overflow:hidden;">
    
    <!-- Success/Error Messages -->
    <?php if($success): ?>
        <div style="background:#166534;padding:15px;border-radius:10px;margin:20px;text-align:center;color:#86efac;">
            <?= $success ?>
            <a href="?" style="color:#86efac;margin-left:15px;text-decoration:underline;">Close</a>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div style="background:#7f1d1d;padding:15px;border-radius:10px;margin:20px;text-align:center;color:#fca5a5;">
            <?= $error ?>
            <a href="?" style="color:#fca5a5;margin-left:15px;text-decoration:underline;">Close</a>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Container -->
    <div style="display:flex;flex-direction:column;gap:40px;padding:40px;">
        
        <!-- Add New Resource Form -->
        <div style="background:#1e293b;padding:40px;border-radius:16px;">
            <h1 style="text-align:center;color:#8b5cf6;margin-bottom:40px;font-size:2.2rem;font-weight:bold;">
                Add Learning Resources
            </h1>

            <form method="POST" enctype="multipart/form-data" id="resourceForm" style="display:grid;gap:22px;max-width:650px;margin:0 auto;">
                <select name="course_id" required style="padding:16px;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
                    <option value="">Select Course</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="title" placeholder="Resource Title (e.g. Advanced Calculus Notes)" required 
                       style="padding:16px;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">

                <select name="type" id="resourceType" required 
                        style="padding:16px;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
                    <option value="">Select Resource Type</option>
                    <option value="pdf">PDF File (Upload)</option>
                    <option value="book">Physical Book Reference</option>
                    <option value="youtube">YouTube Video / Playlist</option>
                    <option value="website">Helpful Website</option>
                </select>

                <div id="inputContainer" style="min-height:80px;">
                    <input type="url" name="url" placeholder="https://youtube.com/watch?v=..." 
                           style="padding:16px;width:100%;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;" 
                           id="urlInput">
                    <small style="color:#94a3b8;display:block;margin-top:8px;">Enter YouTube or website link</small>
                </div>

                <button type="submit" style="background:#8b5cf6;color:white;padding:18px;font-size:1.2rem;border:none;border-radius:12px;cursor:pointer;font-weight:bold;transition:background 0.3s;" 
                        onmouseover="this.style.background='#7c3aed'" onmouseout="this.style.background='#8b5cf6'">
                    Add Resource
                </button>
            </form>
        </div>

        <!-- All Resources List -->
        <div style="background:#1e293b;padding:40px;border-radius:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;flex-wrap:wrap;gap:15px;">
                <h2 style="color:#f1f5f9;margin:0;font-size:1.8rem;">
                    All Resources <span style="background:#8b5cf6;color:white;padding:6px 16px;border-radius:20px;font-size:0.9rem;margin-left:10px;">
                        <?= count($resources) ?>
                    </span>
                </h2>
                <div style="color:#cbd5e1;font-size:0.9rem;">
                    <?= count($resources) ?> resource<?= count($resources) !== 1 ? 's' : '' ?> available
                </div>
            </div>
            
            <?php if(empty($resources)): ?>
                <div style="text-align:center;padding:60px;color:#94a3b8;font-size:1.1rem;">
                    No resources added yet. Add your first resource above!
                </div>
            <?php endif; ?>
            
            <?php 
            $currentCourse = '';
            foreach($resources as $r): 
                if($currentCourse != $r['course_title']):
                    $currentCourse = $r['course_title'];
            ?>
                <div style="background:#0f172a;padding:15px 20px;margin:25px 0 15px;border-radius:10px;color:#c4b5fd;font-weight:bold;font-size:1.1rem;border-left:4px solid #8b5cf6;">
                    📚 <?= htmlspecialchars($currentCourse) ?>
                </div>
            <?php endif; ?>
            
            <div style="background:#0f172a;padding:25px;margin:15px 0;border-radius:14px;border:1px solid #334155;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;">
                    <div style="flex:1;min-width:300px;">
                        <div style="display:flex;align-items:flex-start;gap:15px;margin-bottom:15px;">
                            <div style="flex-shrink:0;">
                                <?php 
                                $icon = match($r['type']) {
                                    'pdf' => '📄',
                                    'book' => '📚',
                                    'youtube' => '🎬',
                                    'website' => '🌐',
                                    default => '📎'
                                };
                                $color = match($r['type']) {
                                    'pdf' => '#ef4444',
                                    'book' => '#f59e0b',
                                    'youtube' => '#ef4444',
                                    'website' => '#3b82f6',
                                    default => '#8b5cf6'
                                };
                                ?>
                                <div style="width:60px;height:60px;background:<?= $color ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.8rem;font-weight:bold;">
                                    <?= $icon ?>
                                </div>
                            </div>
                            <div>
                                <strong style="font-size:1.2rem;color:#f1f5f9;"><?= htmlspecialchars($r['title']) ?></strong><br>
                                <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">
                                    <span style="background:#334155;color:#cbd5e1;padding:4px 12px;border-radius:6px;font-size:0.85rem;">
                                        <?= strtoupper($r['type']) ?>
                                    </span>
                                    <span style="background:#1e293b;color:#94a3b8;padding:4px 12px;border-radius:6px;font-size:0.85rem;">
                                        Added by: <?= htmlspecialchars($r['added_by_name'] ?? 'Admin') ?>
                                    </span>
                                </div>
                                <small style="color:#64748b;display:block;margin-top:10px;">
                                    Created: <?= $r['created_date'] ?>
                                    <?php if($r['updated_date'] && $r['updated_date'] != $r['created_date']): ?>
                                        • Updated: <?= $r['updated_date'] ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        
                        <div style="margin-top:15px;">
                            <?php if($r['type'] === 'pdf'): ?>
                                <a href="../<?= htmlspecialchars($r['url']) ?>" target="_blank" 
                                   style="display:inline-flex;align-items:center;gap:8px;background:#ef4444;color:white;padding:8px 20px;border-radius:8px;text-decoration:none;font-weight:bold;">
                                    📥 Download PDF
                                </a>
                            <?php elseif($r['type'] === 'book'): ?>
                                <div style="background:#1e293b;padding:15px;border-radius:10px;color:#cbd5e1;border-left:4px solid #f59e0b;">
                                    📖 <?= nl2br(htmlspecialchars($r['url'])) ?>
                                </div>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" 
                                   style="display:inline-flex;align-items:center;gap:8px;background:#3b82f6;color:white;padding:8px 20px;border-radius:8px;text-decoration:none;font-weight:bold;">
                                    🔗 Open Link
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="?edit_resource=<?= $r['id'] ?>" 
                           style="background:#8b5cf6;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;border:none;cursor:pointer;">
                            Edit
                        </a>
                        <a href="?delete_resource=<?= $r['id'] ?>" 
                           style="background:#7f1d1d;color:#fca5a5;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;border:none;cursor:pointer;"
                           onclick="return confirm('Delete this resource? This action cannot be undone.')">
                            Delete
                        </a>
                    </div>
                </div>
                
                <!-- Edit Form (shown when ?edit_resource=id) -->
                <?php if(isset($_GET['edit_resource']) && $_GET['edit_resource'] == $r['id']): ?>
                    <div style="background:#0f172a;padding:30px;margin-top:25px;border-radius:14px;border:2px solid #8b5cf6;">
                        <h3 style="color:#c4b5fd;margin-bottom:20px;font-size:1.3rem;">Edit Resource</h3>
                        <form method="POST" enctype="multipart/form-data" id="editForm_<?= $r['id'] ?>" style="display:grid;gap:18px;">
                            <input type="hidden" name="resource_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="old_url" value="<?= htmlspecialchars($r['url'] ?? '') ?>">
                            
                            <select name="course_id" required style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">
                                <option value="">Select Course</option>
                                <?php foreach($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $r['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="title" value="<?= htmlspecialchars($r['title']) ?>" placeholder="Resource Title" required 
                                   style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;">

                            <select name="type" id="editType_<?= $r['id'] ?>" required 
                                    style="padding:15px;border-radius:10px;background:#334155;color:white;font-size:1rem;border:none;" 
                                    onchange="showEditInput(this.value, <?= $r['id'] ?>, '<?= addslashes($r['url']) ?>')">
                                <option value="">Select Resource Type</option>
                                <option value="pdf" <?= $r['type'] == 'pdf' ? 'selected' : '' ?>>PDF File (Upload)</option>
                                <option value="book" <?= $r['type'] == 'book' ? 'selected' : '' ?>>Physical Book Reference</option>
                                <option value="youtube" <?= $r['type'] == 'youtube' ? 'selected' : '' ?>>YouTube Video / Playlist</option>
                                <option value="website" <?= $r['type'] == 'website' ? 'selected' : '' ?>>Helpful Website</option>
                            </select>

                            <div id="editInputContainer_<?= $r['id'] ?>" style="min-height:80px;">
                                <!-- Dynamic content loaded by JavaScript -->
                            </div>

                            <div style="display:flex;gap:15px;margin-top:10px;">
                                <button type="submit" name="save_edit" 
                                        style="background:#10b981;color:white;padding:15px 30px;border-radius:10px;border:none;cursor:pointer;font-weight:bold;flex:1;">
                                    Save Changes
                                </button>
                                <a href="?" 
                                   style="background:#64748b;color:white;padding:15px 30px;border-radius:10px;text-decoration:none;font-weight:bold;text-align:center;">
                                    Cancel
                                </a>
                            </div>
                        </form>
                        
                        <script>
                        // Initialize edit form
                        document.addEventListener('DOMContentLoaded', function() {
                            const type = '<?= $r['type'] ?>';
                            const url = '<?= addslashes($r['url']) ?>';
                            showEditInput(type, <?= $r['id'] ?>, url);
                        });
                        </script>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// Function for main form
document.getElementById('resourceType').addEventListener('change', function() {
    const type = this.value;
    const container = document.getElementById('inputContainer');

    if(type === 'pdf') {
        container.innerHTML = `
            <label style="color:#f59e0b;font-weight:bold;margin-bottom:8px;display:block;">Upload PDF File (Max 50MB)</label>
            <input type="file" name="pdf_file" accept=".pdf" required 
                   style="padding:14px;background:#334155;color:white;border-radius:12px;width:100%;border:none;">
            <small style="color:#94a3b8;">Only PDF files are allowed</small>
        `;
    } else if(type === 'book') {
        container.innerHTML = `
            <input type="text" name="book_info" placeholder="e.g. Author: Griffiths, Edition: 4th, ISBN: 978-0131118928" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter full book details</small>
        `;
    } else {
        const placeholder = type === 'youtube' ? 'https://youtube.com/watch?v=...' : 'https://example.com';
        const label = type === 'youtube' ? 'YouTube Link' : 'Website URL';
        container.innerHTML = `
            <input type="url" name="url" placeholder="${placeholder}" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">${label}</small>
        `;
    }
});

// Function for edit forms
function showEditInput(type, resourceId, currentUrl) {
    const container = document.getElementById('editInputContainer_' + resourceId);
    
    if(type === 'pdf') {
        let fileInfo = '';
        if(currentUrl) {
            const fileName = currentUrl.split('/').pop();
            fileInfo = `<p style="color:#86efac;margin-bottom:10px;">Current file: ${fileName}</p>`;
        }
        container.innerHTML = fileInfo + `
            <label style="color:#f59e0b;font-weight:bold;margin-bottom:8px;display:block;">
                ${currentUrl ? 'Replace PDF File' : 'Upload PDF File'} (Max 50MB)
            </label>
            <input type="file" name="pdf_file" accept=".pdf" ${!currentUrl ? 'required' : ''} 
                   style="padding:14px;background:#334155;color:white;border-radius:12px;width:100%;border:none;">
            <small style="color:#94a3b8;">Only PDF files are allowed</small>
        `;
    } else if(type === 'book') {
        container.innerHTML = `
            <input type="text" name="book_info" value="${currentUrl ? currentUrl.replace(/"/g, '&quot;') : ''}" 
                   placeholder="e.g. Author: Griffiths, Edition: 4th, ISBN: 978-0131118928" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter full book details</small>
        `;
    } else {
        const placeholder = type === 'youtube' ? 'https://youtube.com/watch?v=...' : 'https://example.com';
        const label = type === 'youtube' ? 'YouTube Link' : 'Website URL';
        container.innerHTML = `
            <input type="url" name="url" value="${currentUrl ? currentUrl.replace(/"/g, '&quot;') : ''}" 
                   placeholder="${placeholder}" required 
                   style="padding:16px;width:100%;border-radius:12px;background:#334155;color:white;font-size:1rem;border:none;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">${label}</small>
        `;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
