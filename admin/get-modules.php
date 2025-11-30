<?php
require_once '../config/db.php';
$course_id = (int)$_GET['course_id'];

$stmt = $pdo->prepare("
    SELECT m.id, m.title, ma.id as has_assignment 
    FROM modules m 
    LEFT JOIN module_assignments ma ON ma.module_id = m.id 
    WHERE m.course_id = ? 
    ORDER BY m.module_order
");
$stmt->execute([$course_id]);
$modules = $stmt->fetchAll();

foreach($modules as $m) {
    $status = $m['has_assignment'] ? ' (Already Posted)' : '';
    echo '<option value="'.$m['id'].'">'.htmlspecialchars($m['title']).$status.'</option>';
}
?>