<?php
require_once '../includes/functions.php';
require_once '../config/base.php';
if(!is_admin()) die("Access denied");
require_once '../config/db.php';

// Initialize variables
$action = $_GET['action'] ?? 'list';
$resource_id = $_GET['id'] ?? 0;

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add'])) {
        // Add new resource
        $course_id = (int)$_POST['course_id'];
        $title     = trim($_POST['title']);
        $type      = $_POST['type'];
        $url       = '';

        if($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
            $file = $_FILES['pdf_file'];
            if($file['size'] > 50*1024*1024) {
                $_SESSION['error'] = "PDF too large! Max 50MB";
                header("Location: ".$_SERVER['PHP_SELF']); exit();
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf') {
                $_SESSION['error'] = "Only PDF files allowed!";
                header("Location: ".$_SERVER['PHP_SELF']); exit();
            }

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

        if($url && $title) {
            $stmt = $pdo->prepare("INSERT INTO course_resources (course_id, title, type, url, added_by) VALUES (?,?,?,?,?)");
            if($stmt->execute([$course_id, $title, $type, $url, $_SESSION['user_id']])) {
                $_SESSION['success'] = "Resource added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add resource.";
            }
        } else {
            $_SESSION['error'] = "Please fill all required fields.";
        }
        header("Location: ".$_SERVER['PHP_SELF']); exit();
    }
    
    elseif(isset($_POST['update'])) {
        // Update existing resource
        $resource_id = (int)$_POST['resource_id'];
        $title = trim($_POST['title']);
        $type = $_POST['type'];
        
        // Get current resource data
        $current = $pdo->prepare("SELECT url, type FROM course_resources WHERE id = ?");
        $current->execute([$resource_id]);
        $current_data = $current->fetch();
        
        $url = $current_data['url'] ?? '';
        
        // Handle file upload if type is pdf and new file is uploaded
        if($type === 'pdf' && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
            $file = $_FILES['pdf_file'];
            if($file['size'] > 50*1024*1024) {
                $_SESSION['error'] = "PDF too large! Max 50MB";
                header("Location: ".$_SERVER['PHP_SELF'] . "?action=edit&id=$resource_id"); exit();
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf') {
                $_SESSION['error'] = "Only PDF files allowed!";
                header("Location: ".$_SERVER['PHP_SELF'] . "?action=edit&id=$resource_id"); exit();
            }

            // Delete old PDF file if exists
            if($current_data['type'] === 'pdf' && !empty($current_data['url']) && file_exists("../" . $current_data['url'])) {
                unlink("../" . $current_data['url']);
            }

            $filename = "resource_pdf_" . time() . ".pdf";
            $path = "../uploads/resources/" . $filename;
            if(!is_dir('../uploads/resources')) mkdir('../uploads/resources', 0777, true);
            if(move_uploaded_file($file['tmp_name'], $path)) {
                $url = "uploads/resources/" . $filename;
            }
        }
        elseif($type === 'book') {
            $url = trim($_POST['book_info'] ?? '');
        }
        elseif($type !== 'pdf') {
            $url = trim($_POST['url'] ?? '');
        }

        if($title) {
            $stmt = $pdo->prepare("UPDATE course_resources SET title = ?, type = ?, url = ? WHERE id = ?");
            if($stmt->execute([$title, $type, $url, $resource_id])) {
                $_SESSION['success'] = "Resource updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update resource.";
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']); exit();
    }
}

// Handle delete action
if($action === 'delete' && $resource_id) {
    // Get resource to check if it has a PDF file to delete
    $stmt = $pdo->prepare("SELECT type, url FROM course_resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch();
    
    // Delete PDF file if exists
    if($resource && $resource['type'] === 'pdf' && !empty($resource['url']) && file_exists("../" . $resource['url'])) {
        unlink("../" . $resource['url']);
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM course_resources WHERE id = ?");
    if($stmt->execute([$resource_id])) {
        $_SESSION['success'] = "Resource deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete resource.";
    }
    header("Location: ".$_SERVER['PHP_SELF']); exit();
}

// Fetch all courses for dropdown
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();

// Fetch resources for listing
$resources = $pdo->query("
    SELECT cr.*, c.title as course_title, u.name as added_by_name 
    FROM course_resources cr
    LEFT JOIN courses c ON cr.course_id = c.id
    LEFT JOIN users u ON cr.added_by = u.id
    ORDER BY cr.created_at DESC
")->fetchAll();

// Fetch single resource for editing
$edit_resource = null;
if($action === 'edit' && $resource_id) {
    $stmt = $pdo->prepare("SELECT * FROM course_resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $edit_resource = $stmt->fetch();
}

include 'includes/admin-header.php';
?>

<div class="container" style="max-width:1200px;margin:40px auto;padding:20px;">
    <!-- Success/Error Messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div style="background:#166534;padding:15px;border-radius:10px;margin-bottom:20px;color:#86efac;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div style="background:#7f1d1d;padding:15px;border-radius:10px;margin-bottom:20px;color:#fca5a5;">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Resource List Table -->
    <?php if($action === 'list' || $action === 'edit'): ?>
    <div style="background:#0f172a;color:white;border-radius:20px;padding:30px;margin-bottom:30px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <h1 style="color:#8b5cf6;margin:0;font-size:2rem;">Learning Resources</h1>
            <button onclick="showAddForm()" style="background:#8b5cf6;color:white;padding:12px 24px;border:none;border-radius:10px;cursor:pointer;font-weight:bold;">
                + Add New Resource
            </button>
        </div>

        <?php if(empty($resources)): ?>
            <p style="text-align:center;color:#94a3b8;padding:40px;">No resources found. Add your first resource!</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#1e293b;">
                        <th style="padding:15px;text-align:left;">Title</th>
                        <th style="padding:15px;text-align:left;">Course</th>
                        <th style="padding:15px;text-align:left;">Type</th>
                        <th style="padding:15px;text-align:left;">Added By</th>
                        <th style="padding:15px;text-align:left;">Date</th>
                        <th style="padding:15px;text-align:left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resources as $resource): ?>
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:15px;"><?= htmlspecialchars($resource['title']) ?></td>
                        <td style="padding:15px;"><?= htmlspecialchars($resource['course_title'] ?? 'N/A') ?></td>
                        <td style="padding:15px;">
                            <span style="background:#334155;padding:4px 12px;border-radius:20px;font-size:0.9rem;">
                                <?= ucfirst($resource['type']) ?>
                            </span>
                        </td>
                        <td style="padding:15px;"><?= htmlspecialchars($resource['added_by_name'] ?? 'Unknown') ?></td>
                        <td style="padding:15px;color:#94a3b8;"><?= date('M d, Y', strtotime($resource['created_at'])) ?></td>
                        <td style="padding:15px;">
                            <a href="?action=view&id=<?= $resource['id'] ?>" style="color:#60a5fa;margin-right:10px;text-decoration:none;">View</a>
                            <a href="?action=edit&id=<?= $resource['id'] ?>" style="color:#fbbf24;margin-right:10px;text-decoration:none;">Edit</a>
                            <a href="#" onclick="confirmDelete(<?= $resource['id'] ?>)" style="color:#ef4444;text-decoration:none;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <?php if($action === 'add' || ($action === 'edit' && $edit_resource)): ?>
    <div class="card" style="max-width:900px;margin:0 auto;padding:50px;background:#0f172a;color:white;border-radius:20px;">
        <h1 style="text-align:center;color:#8b5cf6;margin-bottom:40px;font-size:2.5rem;">
            <?= $action === 'edit' ? 'Edit Resource' : 'Add Learning Resource' ?>
        </h1>

        <form method="POST" enctype="multipart/form-data" id="resourceForm" style="display:grid;gap:22px;max-width:650px;margin:0 auto;">
            <?php if($action === 'edit'): ?>
                <input type="hidden" name="resource_id" value="<?= $edit_resource['id'] ?>">
            <?php endif; ?>
            
            <select name="course_id" required style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
                <option value="">Select Course</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" 
                        <?= ($action === 'edit' && $edit_resource['course_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="title" placeholder="Resource Title (e.g. Advanced Calculus Notes)" required 
                   value="<?= $action === 'edit' ? htmlspecialchars($edit_resource['title']) : '' ?>"
                   style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">

            <select name="type" id="resourceType" required style="padding:16px;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
                <option value="">Select Resource Type</option>
                <option value="pdf" <?= ($action === 'edit' && $edit_resource['type'] === 'pdf') ? 'selected' : '' ?>>PDF File (Upload)</option>
                <option value="book" <?= ($action === 'edit' && $edit_resource['type'] === 'book') ? 'selected' : '' ?>>Physical Book Reference</option>
                <option value="youtube" <?= ($action === 'edit' && $edit_resource['type'] === 'youtube') ? 'selected' : '' ?>>YouTube Video / Playlist</option>
                <option value="website" <?= ($action === 'edit' && $edit_resource['type'] === 'website') ? 'selected' : '' ?>>Helpful Website</option>
            </select>

            <div id="inputContainer" style="min-height:80px;">
                <!-- Dynamic content based on type -->
            </div>

            <div style="display:flex;gap:15px;">
                <button type="submit" name="<?= $action === 'edit' ? 'update' : 'add' ?>" 
                        style="flex:1;background:#8b5cf6;color:white;padding:18px;font-size:1.3rem;border:none;border-radius:12px;cursor:pointer;font-weight:bold;">
                    <?= $action === 'edit' ? 'Update Resource' : 'Add Resource' ?>
                </button>
                <a href="?" style="flex:0.5;background:#475569;color:white;padding:18px;font-size:1.3rem;border-radius:12px;cursor:pointer;font-weight:bold;text-decoration:none;text-align:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- View Resource -->
    <?php if($action === 'view' && $resource_id): ?>
        <?php
        $stmt = $pdo->prepare("
            SELECT cr.*, c.title as course_title, u.name as added_by_name 
            FROM course_resources cr
            LEFT JOIN courses c ON cr.course_id = c.id
            LEFT JOIN users u ON cr.added_by = u.id
            WHERE cr.id = ?
        ");
        $stmt->execute([$resource_id]);
        $resource = $stmt->fetch();
        
        if($resource):
        ?>
        <div class="card" style="max-width:900px;margin:0 auto;padding:50px;background:#0f172a;color:white;border-radius:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
                <h1 style="color:#8b5cf6;margin:0;font-size:2rem;"><?= htmlspecialchars($resource['title']) ?></h1>
                <div>
                    <a href="?action=edit&id=<?= $resource['id'] ?>" style="background:#fbbf24;color:#1e293b;padding:10px 20px;border-radius:8px;text-decoration:none;margin-right:10px;">Edit</a>
                    <a href="?" style="background:#475569;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;">Back to List</a>
                </div>
            </div>
            
            <div style="background:#1e293b;border-radius:15px;padding:30px;margin-bottom:20px;">
                <table style="width:100%;">
                    <tr>
                        <td style="padding:10px 0;color:#94a3b8;width:150px;">Course:</td>
                        <td style="padding:10px 0;"><?= htmlspecialchars($resource['course_title'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#94a3b8;">Type:</td>
                        <td style="padding:10px 0;">
                            <span style="background:#8b5cf6;padding:4px 12px;border-radius:20px;">
                                <?= ucfirst($resource['type']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#94a3b8;">Added By:</td>
                        <td style="padding:10px 0;"><?= htmlspecialchars($resource['added_by_name'] ?? 'Unknown') ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#94a3b8;">Added On:</td>
                        <td style="padding:10px 0;"><?= date('F j, Y, g:i a', strtotime($resource['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
            
            <div style="background:#1e293b;border-radius:15px;padding:30px;">
                <h3 style="color:#8b5cf6;margin-top:0;">Resource Content:</h3>
                <?php if($resource['type'] === 'pdf'): ?>
                    <p>PDF File: <a href="../<?= htmlspecialchars($resource['url']) ?>" target="_blank" style="color:#60a5fa;">View PDF</a></p>
                <?php elseif($resource['type'] === 'book'): ?>
                    <p><?= nl2br(htmlspecialchars($resource['url'])) ?></p>
                <?php elseif($resource['type'] === 'youtube'): ?>
                    <p>YouTube Link: <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" style="color:#60a5fa;"><?= htmlspecialchars($resource['url']) ?></a></p>
                <?php elseif($resource['type'] === 'website'): ?>
                    <p>Website: <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" style="color:#60a5fa;"><?= htmlspecialchars($resource['url']) ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <div style="text-align:center;padding:50px;color:#ef4444;">
                Resource not found!
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Show add form
function showAddForm() {
    window.location.href = '?action=add';
}

// Confirm delete
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this resource? This action cannot be undone.')) {
        window.location.href = '?action=delete&id=' + id;
    }
}

// Dynamic form fields based on resource type
function updateFormFields(type, currentUrl = '') {
    const container = document.getElementById('inputContainer');
    
    if(type === 'pdf') {
        container.innerHTML = `
            <label style="color:#f59e0b;font-weight:bold;margin-bottom:8px;display:block;">Upload PDF File (Max 50MB)</label>
            <input type="file" name="pdf_file" accept=".pdf" 
                   style="padding:14px;background:#334155;color:white;border-radius:12px;width:100%;">
            <small style="color:#94a3b8;">Only PDF files are allowed</small>
            ${currentUrl ? `<p style="margin-top:10px;color:#86efac;">Current file: ${currentUrl}</p>` : ''}
        `;
    } else if(type === 'book') {
        container.innerHTML = `
            <input type="text" name="book_info" placeholder="e.g. Author: Griffiths, Edition: 4th, ISBN: 978-0131118928" required 
                   value="${currentUrl || ''}"
                   style="padding:16px;width:100%;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">Enter full book details</small>
        `;
    } else {
        const placeholder = type === 'youtube' ? 'https://youtube.com/watch?v=...' : 'https://example.com';
        const label = type === 'youtube' ? 'YouTube Link' : 'Website URL';
        container.innerHTML = `
            <input type="url" name="url" placeholder="${placeholder}" required 
                   value="${currentUrl || ''}"
                   style="padding:16px;width:100%;border-radius:12px;background:#1e293b;color:white;font-size:1rem;">
            <small style="color:#94a3b8;display:block;margin-top:8px;">${label}</small>
        `;
    }
}

// Initialize form fields on page load
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('resourceType');
    if(typeSelect) {
        const currentType = typeSelect.value;
        const currentUrl = '<?= isset($edit_resource) && $edit_resource["type"] !== "pdf" ? htmlspecialchars($edit_resource["url"]) : "" ?>';
        
        if(currentType) {
            updateFormFields(currentType, currentUrl);
        }
        
        typeSelect.addEventListener('change', function() {
            updateFormFields(this.value);
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
