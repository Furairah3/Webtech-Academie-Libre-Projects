<?php 
require_once 'auth.php'; 
require_once '../config/base.php';
require_once '../config/db.php';

$modules = $pdo->query("
    SELECT m.id, m.title as module_title, c.title as course_title
    FROM modules m JOIN courses c ON m.course_id = c.id 
    ORDER BY c.title, m.module_order
")->fetchAll();

$success = $error = '';

if($_POST) {
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $passing_score = (int)$_POST['passing_score'];
    $num_questions = (int)$_POST['num_questions'];

    if($module_id <= 0) {
        $error = "Please select a module!";
    } else {
        try {
            // Create quiz
            $stmt = $pdo->prepare("INSERT INTO quizzes (module_id, title, passing_score) VALUES (?,?,?)");
            $stmt->execute([$module_id, $title, $passing_score]);
            $quiz_id = $pdo->lastInsertId();

            // Link quiz to module
            $pdo->prepare("UPDATE modules SET quiz_id = ? WHERE id = ?")
                ->execute([$quiz_id, $module_id]);

            // Add questions
            for($i = 1; $i <= $num_questions; $i++) {
                if(empty($_POST["question_$i"])) continue;

                $q_text = trim($_POST["question_$i"]);
                $type = $_POST["type_$i"] ?? 'single';
                $a = trim($_POST["a_$i"]);
                $b = trim($_POST["b_$i"]);
                $c = trim($_POST["c_$i"]);
                $d = trim($_POST["d_$i"]);

                $correct = [];
                if(isset($_POST["correct_a_$i"])) $correct[] = 'A';
                if(isset($_POST["correct_b_$i"])) $correct[] = 'B';
                if(isset($_POST["correct_c_$i"])) $correct[] = 'C';
                if(isset($_POST["correct_d_$i"])) $correct[] = 'D';

                if(empty($correct)) continue;

                $correct_str = implode(',', $correct);

                $pdo->prepare("INSERT INTO quiz_questions 
                    (quiz_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_option)
                    VALUES (?,?,?,?,?,?,?,?)")
                    ->execute([$quiz_id, $type, $q_text, $a, $b, $c, $d, $correct_str]);
            }

            $success = "Quiz created successfully with $num_questions questions!";
        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include 'includes/admin-header.php'; 
?>

<h2 style="color:#8b5cf6;text-align:center;margin:40px 0;">Create New Quiz</h2>

<?php if($success): ?>
    <div style="background:#10b981;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div style="background:#ef4444;color:white;padding:20px;border-radius:12px;text-align:center;margin:20px auto;max-width:600px;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:900px;margin:0 auto;padding:40px;">
    <form method="POST">
        <label>Select Module</label>
        <select name="module_id" required style="width:100%;padding:15px;margin:15px 0;border-radius:12px;">
            <option value="">Choose Module</option>
            <?php foreach($modules as $m): ?>
                <option value="<?= $m['id'] ?>">
                    [<?= htmlspecialchars($m['course_title']) ?>] <?= htmlspecialchars($m['module_title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Quiz Title" required style="margin:15px 0;">
        <input type="number" name="passing_score" value="70" min="1" max="100" required placeholder="Passing Score %">
        <input type="number" name="num_questions" id="num_questions" value="5" min="1" max="50" required 
               onchange="generateQuestions()" style="margin:15px 0;">

        <div id="questions-container">
            <!-- JavaScript will fill this -->
        </div>

        <button type="submit" class="btn" style="width:100%;background:#8b5cf6;padding:20px;font-size:1.5rem;">
            Create Quiz
        </button>
    </form>
</div>

<script>
function generateQuestions() {
    const num = document.getElementById('num_questions').value;
    const container = document.getElementById('questions-container');
    container.innerHTML = '<h3 style="color:#8b5cf6;margin:30px 0;">Questions</h3>';

    for(let i = 1; i <= num; i++) {
        container.innerHTML += `
        <div style="background:#1e293b;padding:30px;margin:20px 0;border-radius:20px;border:2px solid #334155;">
            <input type="text" name="question_${i}" placeholder="Question ${i}" required style="width:100%;padding:15px;margin:10px 0;">
            
            <select name="type_${i}" style="width:100%;padding:12px;margin:10px 0;">
                <option value="single">Single Answer</option>
                <option value="multiple">Multiple Answers</option>
            </select>

            <input type="text" name="a_${i}" placeholder="A) Answer" required>
            <input type="text" name="b_${i}" placeholder="B) Answer" required>
            <input type="text" name="c_${i}" placeholder="C) Answer" required>
            <input type="text" name="d_${i}" placeholder="D) Answer" required>

            <div style="margin:20px 0;padding:20px;background:#0f172a;border-radius:12px;">
                <strong>Correct Answer(s):</strong><br>
                <label><input type="checkbox" name="correct_a_${i}"> A</label>
                <label><input type="checkbox" name="correct_b_${i}"> B</label>
                <label><input type="checkbox" name="correct_c_${i}"> C</label>
                <label><input type="checkbox" name="correct_d_${i}"> D</label>
            </div>
        </div>`;
    }
}
window.onload = generateQuestions;
</script>

<?php include '../includes/footer.php'; ?>
