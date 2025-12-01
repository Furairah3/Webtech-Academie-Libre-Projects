<?php
require_once 'includes/functions.php';
if(!is_logged_in()) redirect('login.php');
require_once 'config/db.php';

$resources = $pdo->query("
    SELECT cr.*, c.title as course_title
    FROM course_resources cr
    JOIN courses c ON cr.course_id = c.id
    ORDER BY c.title, cr.added_at DESC
")->fetchAll();

$grouped = [];
foreach($resources as $r) {
    $grouped[$r['course_title']][] = $r;
}

include 'includes/header.php';
?>

<div style="max-width:1300px;margin:100px auto 50px;padding:0 30px;">
    <h1 style="text-align:center;color:#8b5cf6;font-size:3.5rem;margin-bottom:60px;font-weight:bold;">
        Learning Resources
    </h1>

    <?php if(empty($grouped)): ?>
        <div style="text-align:center;padding:120px;background:#1e293b;border-radius:20px;">
            <h2 style="color:#94a3b8;">No resources available yet</h2>
            <p>Your teachers will add them soon!</p>
        </div>
    <?php else: ?>
        <?php foreach($grouped as $course => $items): ?>
            <div style="background:#1e293b;border-radius:20px;padding:35px;margin-bottom:50px;box-shadow:0 15px 40px rgba(0,0,0,0.4);">
                <h2 style="color:#06b6d4;font-size:2.4rem;margin-bottom:30px;padding-bottom:15px;border-bottom:4px solid #334155;">
                    <?= htmlspecialchars($course) ?>
                </h2>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:25px;">
                    <?php foreach($items as $r): ?>
                        <div style="background:#dce5fa;padding:25px;border-radius:16px;border-left:7px solid #8b5cf6;height:100%;display:flex;flex-direction:column;justify-content:space-between;">
                            <div>
                                <span style="background:#8b5cf6;color:black;padding:6px 14px;border-radius:50px;font-size:0.9rem;font-weight:bold;">
                                    <?= strtoupper($r['type']) ?>
                                </span>
                                <h3 style="color:#f59e0b;margin:15px 0;font-size:1.4rem;">
                                    <?= htmlspecialchars($r['title']) ?>
                                </h3>
                            </div>

                            <?php if($r['type'] === 'pdf'): ?>
                                <a href="<?= htmlspecialchars($r['url']) ?>" 
                                target="_blank"
                                style="background:#10b981;color:white;padding:14px;text-align:center;border-radius:12px;margin-top:15px;display:block;font-weight:bold;text-decoration:none;">
                                    View PDF in Browser
                                </a>
                                <br>
                                <a href="<?= htmlspecialchars($r['url']) ?>" 
                                download 
                                style="color:#94a3b8;font-size:0.9rem;text-decoration:underline;">
                                    Download instead
                                </a>
                            <?php elseif($r['type'] === 'book'): ?>
                                <div style="background:#1e40af;color:white;padding:14px;border-radius:12px;margin-top:15px;font-family:monospace;">
                                    <?= nl2br(htmlspecialchars($r['url'])) ?>
                                </div>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" 
                                   style="background:#8b5cf6;color:white;padding:14px;text-align:center;border-radius:12px;margin-top:15px;display:block;font-weight:bold;">
                                    Open in New Tab
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
