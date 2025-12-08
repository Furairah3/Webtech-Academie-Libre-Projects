<?php
require_once 'auth.php';
require_once '../config/base.php';
require_once '../config/db.php';

// Initialize variables
$action = $_GET['action'] ?? 'list';
$quiz_id = $_GET['id'] ?? 0;
$success = $error = '';

// Fetch modules for dropdown
$modules = $pdo->query("
    SELECT m.id, m.title as module_title, c.title as course_title
    FROM modules m JOIN courses c ON m.course_id = c.id 
    ORDER BY c.title, m.module_order
")->fetchAll();

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add'])) {
        // Add new quiz
        $module_id = (int)$_POST['module_id'];
        $title = trim($_POST['title']);
        $passing_score = (int)$_POST['passing_score'];
        $num_questions = (int)$_POST['num_questions'];

        if($module_id <= 0) {
            $error = "Please select a module!";
        } else {
            try {
                // Check if module already has a quiz
                $stmt = $pdo->prepare("SELECT quiz_id FROM modules WHERE id = ?");
                $stmt->execute([$module_id]);
                $module = $stmt->fetch();
                
                if($module && $module['quiz_id']) {
                    $error = "This module already has a quiz! Please edit the existing quiz instead.";
                } else {
                    // Create quiz
                    $stmt = $pdo->prepare("INSERT INTO quizzes (module_id, title, passing_score) VALUES (?,?,?)");
                    $stmt->execute([$module_id, $title, $passing_score]);
                    $quiz_id = $pdo->lastInsertId();

                    // Link quiz to module
                    $pdo->prepare("UPDATE modules SET quiz_id = ? WHERE id = ?")
                        ->execute([$quiz_id, $module_id]);

                    // Add questions
                    $questions_added = 0;
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
                            (quiz_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_option, question_order)
                            VALUES (?,?,?,?,?,?,?,?,?)")
                            ->execute([$quiz_id, $type, $q_text, $a, $b, $c, $d, $correct_str, $i]);
                        $questions_added++;
                    }

                    $success = "Quiz created successfully with $questions_added questions!";
                    $action = 'list'; // Redirect to list view
                }
            } catch(Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
    elseif(isset($_POST['update'])) {
        // Update quiz
        $quiz_id = (int)$_POST['quiz_id'];
        $title = trim($_POST['title']);
        $passing_score = (int)$_POST['passing_score'];
        
        try {
            // Update quiz details
            $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, passing_score = ? WHERE id = ?");
            $stmt->execute([$title, $passing_score, $quiz_id]);
            
            // Update existing questions and add new ones
            $existing_questions = $pdo->prepare("SELECT id FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order");
            $existing_questions->execute([$quiz_id]);
            $existing_ids = $existing_questions->fetchAll(PDO::FETCH_COLUMN);
            
            $question_count = 0;
            for($i = 1; $i <= 50; $i++) { // Allow up to 50 questions
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
                $question_count++;
                
                // Check if this is an existing question or new one
                if(isset($_POST["question_id_$i"])) {
                    // Update existing question
                    $question_id = (int)$_POST["question_id_$i"];
                    $stmt = $pdo->prepare("UPDATE quiz_questions SET 
                        question_type = ?, question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, 
                        correct_option = ?, question_order = ? WHERE id = ? AND quiz_id = ?");
                    $stmt->execute([$type, $q_text, $a, $b, $c, $d, $correct_str, $i, $question_id, $quiz_id]);
                } else {
                    // Add new question
                    $stmt = $pdo->prepare("INSERT INTO quiz_questions 
                        (quiz_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_option, question_order)
                        VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$quiz_id, $type, $q_text, $a, $b, $c, $d, $correct_str, $i]);
                }
            }
            
            // Delete questions that were removed
            $posted_ids = [];
            for($i = 1; $i <= 50; $i++) {
                if(isset($_POST["question_id_$i"])) {
                    $posted_ids[] = (int)$_POST["question_id_$i"];
                }
            }
            
            if(!empty($existing_ids)) {
                $ids_to_delete = array_diff($existing_ids, $posted_ids);
                if(!empty($ids_to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
                    $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE id IN ($placeholders)");
                    $stmt->execute(array_values($ids_to_delete));
                }
            }
            
            $success = "Quiz updated successfully with $question_count questions!";
            $action = 'list'; // Redirect to list view
        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle delete action
if($action === 'delete' && $quiz_id) {
    try {
        $pdo->beginTransaction();
        
        // Get module ID to unlink quiz
        $stmt = $pdo->prepare("SELECT module_id FROM quizzes WHERE id = ?");
        $stmt->execute([$quiz_id]);
        $quiz = $stmt->fetch();
        
        if($quiz) {
            // Unlink quiz from module
            $pdo->prepare("UPDATE modules SET quiz_id = NULL WHERE id = ?")
                ->execute([$quiz['module_id']]);
            
            // Delete questions
            $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?")
                ->execute([$quiz_id]);
            
            // Delete quiz
            $pdo->prepare("DELETE FROM quizzes WHERE id = ?")
                ->execute([$quiz_id]);
            
            $pdo->commit();
            $success = "Quiz deleted successfully!";
        } else {
            $error = "Quiz not found!";
        }
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
    $action = 'list';
}

// Fetch quizzes for listing
if($action === 'list') {
    $quizzes = $pdo->query("
        SELECT q.id, q.title, q.passing_score, q.created_at, 
               m.title as module_title, c.title as course_title,
               (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
        FROM quizzes q
        JOIN modules m ON q.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        ORDER BY c.title, m.module_order
    ")->fetchAll();
}

// Fetch single quiz for editing/viewing
$edit_quiz = null;
$edit_questions = [];
if(($action === 'edit' || $action === 'view') && $quiz_id) {
    // Get quiz details
    $stmt = $pdo->prepare("
        SELECT q.*, m.title as module_title, c.title as course_title
        FROM quizzes q
        JOIN modules m ON q.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        WHERE q.id = ?
    ");
    $stmt->execute([$quiz_id]);
    $edit_quiz = $stmt->fetch();
    
    if($edit_quiz) {
        // Get questions
        $stmt = $pdo->prepare("
            SELECT * FROM quiz_questions 
            WHERE quiz_id = ? 
            ORDER BY question_order
        ");
        $stmt->execute([$quiz_id]);
        $edit_questions = $stmt->fetchAll();
    }
}

include 'includes/admin-header.php';
?>

<div class="container" style="max-width:1200px;margin:40px auto;padding:20px;">
    <!-- Success/Error Messages -->
    <?php if($success): ?>
        <div style="background:#10b981;color:white;padding:20px;border-radius:12px;margin-bottom:20px;text-align:center;">
            <?= $success ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div style="background:#ef4444;color:white;padding:20px;border-radius:12px;margin-bottom:20px;text-align:center;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Quiz List -->
    <?php if($action === 'list'): ?>
    <div style="background:#0f172a;color:white;border-radius:20px;padding:30px;margin-bottom:30px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <h1 style="color:#8b5cf6;margin:0;font-size:2rem;">Quizzes</h1>
            <a href="?action=add" style="background:#8b5cf6;color:white;padding:12px 24px;border:none;border-radius:10px;cursor:pointer;font-weight:bold;text-decoration:none;">
                + Create New Quiz
            </a>
        </div>

        <?php if(empty($quizzes)): ?>
            <p style="text-align:center;color:#94a3b8;padding:40px;">No quizzes found. Create your first quiz!</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#1e293b;">
                        <th style="padding:15px;text-align:left;">Quiz Title</th>
                        <th style="padding:15px;text-align:left;">Course / Module</th>
                        <th style="padding:15px;text-align:left;">Passing Score</th>
                        <th style="padding:15px;text-align:left;">Questions</th>
                        <th style="padding:15px;text-align:left;">Created</th>
                        <th style="padding:15px;text-align:left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($quizzes as $quiz): ?>
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:15px;"><?= htmlspecialchars($quiz['title']) ?></td>
                        <td style="padding:15px;">
                            <div style="color:#94a3b8;font-size:0.9rem;"><?= htmlspecialchars($quiz['course_title']) ?></div>
                            <div><?= htmlspecialchars($quiz['module_title']) ?></div>
                        </td>
                        <td style="padding:15px;">
                            <span style="background:#8b5cf6;padding:4px 12px;border-radius:20px;">
                                <?= $quiz['passing_score'] ?>%
                            </span>
                        </td>
                        <td style="padding:15px;text-align:center;"><?= $quiz['question_count'] ?></td>
                        <td style="padding:15px;color:#94a3b8;"><?= date('M d, Y', strtotime($quiz['created_at'])) ?></td>
                        <td style="padding:15px;">
                            <a href="?action=view&id=<?= $quiz['id'] ?>" style="color:#60a5fa;margin-right:10px;text-decoration:none;">View</a>
                            <a href="?action=edit&id=<?= $quiz['id'] ?>" style="color:#fbbf24;margin-right:10px;text-decoration:none;">Edit</a>
                            <a href="#" onclick="confirmDelete(<?= $quiz['id'] ?>)" style="color:#ef4444;text-decoration:none;">Delete</a>
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
    <?php if($action === 'add' || ($action === 'edit' && $edit_quiz)): ?>
    <div class="card" style="max-width:900px;margin:0 auto;padding:40px;background:#0f172a;color:white;border-radius:20px;">
        <h2 style="color:#8b5cf6;text-align:center;margin-bottom:30px;">
            <?= $action === 'edit' ? 'Edit Quiz' : 'Create New Quiz' ?>
        </h2>

        <form method="POST" id="quizForm">
            <?php if($action === 'edit'): ?>
                <input type="hidden" name="quiz_id" value="<?= $edit_quiz['id'] ?>">
            <?php endif; ?>
            
            <?php if($action === 'add'): ?>
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#94a3b8;">Select Module</label>
                <select name="module_id" required style="width:100%;padding:15px;border-radius:12px;background:#1e293b;color:white;">
                    <option value="">Choose Module</option>
                    <?php foreach($modules as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            [<?= htmlspecialchars($m['course_title']) ?>] <?= htmlspecialchars($m['module_title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <div style="background:#1e293b;padding:20px;border-radius:12px;margin-bottom:20px;">
                <strong>Course:</strong> <?= htmlspecialchars($edit_quiz['course_title']) ?><br>
                <strong>Module:</strong> <?= htmlspecialchars($edit_quiz['module_title']) ?>
            </div>
            <?php endif; ?>

            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#94a3b8;">Quiz Title</label>
                <input type="text" name="title" 
                       value="<?= $action === 'edit' ? htmlspecialchars($edit_quiz['title']) : '' ?>"
                       required style="width:100%;padding:15px;border-radius:12px;background:#1e293b;color:white;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;color:#94a3b8;">Passing Score (%)</label>
                <input type="number" name="passing_score" 
                       value="<?= $action === 'edit' ? $edit_quiz['passing_score'] : '70' ?>"
                       min="1" max="100" required 
                       style="width:100%;padding:15px;border-radius:12px;background:#1e293b;color:white;">
            </div>

            <div style="margin-bottom:30px;">
                <label style="display:block;margin-bottom:8px;color:#94a3b8;">Number of Questions</label>
                <input type="number" id="num_questions" 
                       value="<?= $action === 'edit' ? count($edit_questions) : '5' ?>"
                       min="1" max="50" required 
                       onchange="generateQuestions()"
                       style="width:100%;padding:15px;border-radius:12px;background:#1e293b;color:white;">
            </div>

            <div id="questions-container">
                <!-- Questions will be generated here -->
            </div>

            <div style="display:flex;gap:15px;margin-top:30px;">
                <button type="submit" name="<?= $action === 'edit' ? 'update' : 'add' ?>" 
                        style="flex:1;background:#8b5cf6;color:white;padding:18px;font-size:1.3rem;border:none;border-radius:12px;cursor:pointer;font-weight:bold;">
                    <?= $action === 'edit' ? 'Update Quiz' : 'Create Quiz' ?>
                </button>
                <a href="?" style="flex:0.5;background:#475569;color:white;padding:18px;font-size:1.3rem;border-radius:12px;cursor:pointer;font-weight:bold;text-decoration:none;text-align:center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- View Quiz -->
    <?php if($action === 'view' && $edit_quiz): ?>
    <div class="card" style="max-width:900px;margin:0 auto;padding:40px;background:#0f172a;color:white;border-radius:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <h1 style="color:#8b5cf6;margin:0;font-size:2rem;"><?= htmlspecialchars($edit_quiz['title']) ?></h1>
            <div>
                <a href="?action=edit&id=<?= $edit_quiz['id'] ?>" style="background:#fbbf24;color:#1e293b;padding:10px 20px;border-radius:8px;text-decoration:none;margin-right:10px;">Edit</a>
                <a href="?" style="background:#475569;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;">Back to List</a>
            </div>
        </div>
        
        <div style="background:#1e293b;border-radius:15px;padding:30px;margin-bottom:20px;">
            <table style="width:100%;">
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;width:150px;">Course:</td>
                    <td style="padding:10px 0;"><?= htmlspecialchars($edit_quiz['course_title']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;">Module:</td>
                    <td style="padding:10px 0;"><?= htmlspecialchars($edit_quiz['module_title']) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;">Passing Score:</td>
                    <td style="padding:10px 0;">
                        <span style="background:#8b5cf6;padding:4px 12px;border-radius:20px;">
                            <?= $edit_quiz['passing_score'] ?>%
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;">Questions:</td>
                    <td style="padding:10px 0;"><?= count($edit_questions) ?></td>
                </tr>
                <tr>
                    <td style="padding:10px 0;color:#94a3b8;">Created:</td>
                    <td style="padding:10px 0;"><?= date('F j, Y, g:i a', strtotime($edit_quiz['created_at'])) ?></td>
                </tr>
            </table>
        </div>
        
        <h3 style="color:#8b5cf6;margin-top:30px;margin-bottom:20px;">Questions:</h3>
        
        <?php if(empty($edit_questions)): ?>
            <p style="text-align:center;color:#94a3b8;padding:20px;">No questions found.</p>
        <?php else: ?>
            <?php foreach($edit_questions as $index => $question): ?>
            <div style="background:#1e293b;border-radius:15px;padding:30px;margin-bottom:20px;">
                <h4 style="margin-top:0;color:#f59e0b;">
                    Question <?= $index + 1 ?>: 
                    <span style="font-size:0.9rem;color:#94a3b8;background:#334155;padding:2px 8px;border-radius:12px;">
                        <?= ucfirst($question['question_type']) ?>
                    </span>
                </h4>
                <p style="margin-bottom:20px;font-size:1.1rem;"><?= htmlspecialchars($question['question_text']) ?></p>
                
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:15px;margin-bottom:20px;">
                    <div style="background:#0f172a;padding:15px;border-radius:10px;border:1px solid <?= strpos($question['correct_option'], 'A') !== false ? '#10b981' : '#334155' ?>;">
                        <strong>A)</strong> <?= htmlspecialchars($question['option_a']) ?>
                        <?php if(strpos($question['correct_option'], 'A') !== false): ?>
                            <span style="color:#10b981;float:right;">✓ Correct</span>
                        <?php endif; ?>
                    </div>
                    <div style="background:#0f172a;padding:15px;border-radius:10px;border:1px solid <?= strpos($question['correct_option'], 'B') !== false ? '#10b981' : '#334155' ?>;">
                        <strong>B)</strong> <?= htmlspecialchars($question['option_b']) ?>
                        <?php if(strpos($question['correct_option'], 'B') !== false): ?>
                            <span style="color:#10b981;float:right;">✓ Correct</span>
                        <?php endif; ?>
                    </div>
                    <div style="background:#0f172a;padding:15px;border-radius:10px;border:1px solid <?= strpos($question['correct_option'], 'C') !== false ? '#10b981' : '#334155' ?>;">
                        <strong>C)</strong> <?= htmlspecialchars($question['option_c']) ?>
                        <?php if(strpos($question['correct_option'], 'C') !== false): ?>
                            <span style="color:#10b981;float:right;">✓ Correct</span>
                        <?php endif; ?>
                    </div>
                    <div style="background:#0f172a;padding:15px;border-radius:10px;border:1px solid <?= strpos($question['correct_option'], 'D') !== false ? '#10b981' : '#334155' ?>;">
                        <strong>D)</strong> <?= htmlspecialchars($question['option_d']) ?>
                        <?php if(strpos($question['correct_option'], 'D') !== false): ?>
                            <span style="color:#10b981;float:right;">✓ Correct</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="color:#94a3b8;font-size:0.9rem;">
                    Correct answer(s): <?= str_replace(',', ', ', $question['correct_option']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Confirm delete
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this quiz? This will also delete all questions and unlink it from the module.')) {
        window.location.href = '?action=delete&id=' + id;
    }
}

// Generate question form fields
function generateQuestions() {
    const num = parseInt(document.getElementById('num_questions').value) || 5;
    const container = document.getElementById('questions-container');
    const isEdit = <?= $action === 'edit' ? 'true' : 'false' ?>;
    
    let html = '<h3 style="color:#8b5cf6;margin:30px 0 20px 0;">Questions</h3>';
    
    for(let i = 1; i <= num; i++) {
        let questionData = {};
        let questionId = '';
        
        if(isEdit && window.editQuestions && window.editQuestions[i-1]) {
            questionData = window.editQuestions[i-1];
            questionId = questionData.id || '';
        }
        
        const isMultiple = questionData.question_type === 'multiple';
        const correctAnswers = questionData.correct_option ? questionData.correct_option.split(',') : [];
        
        html += `
        <div style="background:#1e293b;padding:30px;margin:20px 0;border-radius:20px;border:2px solid #334155;">
            ${questionId ? `<input type="hidden" name="question_id_${i}" value="${questionId}">` : ''}
            
            <input type="text" name="question_${i}" placeholder="Question ${i}" required 
                   value="${questionData.question_text || ''}"
                   style="width:100%;padding:15px;margin:10px 0;border-radius:12px;background:#0f172a;color:white;">
            
            <select name="type_${i}" style="width:100%;padding:12px;margin:10px 0;border-radius:12px;background:#0f172a;color:white;">
                <option value="single" ${!isMultiple ? 'selected' : ''}>Single Answer</option>
                <option value="multiple" ${isMultiple ? 'selected' : ''}>Multiple Answers</option>
            </select>

            <input type="text" name="a_${i}" placeholder="A) Answer" required 
                   value="${questionData.option_a || ''}"
                   style="width:100%;padding:12px;margin:8px 0;border-radius:12px;background:#0f172a;color:white;">
            <input type="text" name="b_${i}" placeholder="B) Answer" required 
                   value="${questionData.option_b || ''}"
                   style="width:100%;padding:12px;margin:8px 0;border-radius:12px;background:#0f172a;color:white;">
            <input type="text" name="c_${i}" placeholder="C) Answer" required 
                   value="${questionData.option_c || ''}"
                   style="width:100%;padding:12px;margin:8px 0;border-radius:12px;background:#0f172a;color:white;">
            <input type="text" name="d_${i}" placeholder="D) Answer" required 
                   value="${questionData.option_d || ''}"
                   style="width:100%;padding:12px;margin:8px 0;border-radius:12px;background:#0f172a;color:white;">

            <div style="margin:20px 0;padding:20px;background:#0f172a;border-radius:12px;">
                <strong style="display:block;margin-bottom:10px;color:#94a3b8;">Correct Answer(s):</strong>
                <div style="display:flex;gap:20px;">
                    <label style="display:flex;align-items:center;">
                        <input type="checkbox" name="correct_a_${i}" ${correctAnswers.includes('A') ? 'checked' : ''}> 
                        <span style="margin-left:5px;">A</span>
                    </label>
                    <label style="display:flex;align-items:center;">
                        <input type="checkbox" name="correct_b_${i}" ${correctAnswers.includes('B') ? 'checked' : ''}> 
                        <span style="margin-left:5px;">B</span>
                    </label>
                    <label style="display:flex;align-items:center;">
                        <input type="checkbox" name="correct_c_${i}" ${correctAnswers.includes('C') ? 'checked' : ''}> 
                        <span style="margin-left:5px;">C</span>
                    </label>
                    <label style="display:flex;align-items:center;">
                        <input type="checkbox" name="correct_d_${i}" ${correctAnswers.includes('D') ? 'checked' : ''}> 
                        <span style="margin-left:5px;">D</span>
                    </label>
                </div>
            </div>
        </div>`;
    }
    
    container.innerHTML = html;
}

// Store edit questions in global variable if in edit mode
<?php if($action === 'edit' && !empty($edit_questions)): ?>
window.editQuestions = <?= json_encode($edit_questions) ?>;
<?php endif; ?>

// Initialize questions on page load
window.onload = function() {
    generateQuestions();
};
</script>

<?php include '../includes/footer.php'; ?>
